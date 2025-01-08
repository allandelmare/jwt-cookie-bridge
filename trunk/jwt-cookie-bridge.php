<?php
/**
 * Plugin Name: JWT Cookie Bridge for MiniOrange SSO
 * Plugin URI: https://github.com/allandelmare/jwt-cookie-bridge
 * Description: Securely stores JWT tokens from MiniOrange OAuth/OpenID in a cookie for seamless SSO integration
 * Version: 1.0.7
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Author: Allan Delmare
 * Author URI: https://github.com/allandelmare
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: jwt-cookie-bridge
 * Domain Path: /languages
 */

namespace JWTCookieBridge;

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('JWT_COOKIE_BRIDGE_VERSION', '1.0.7');
define('JWT_COOKIE_BRIDGE_MINIMUM_WP_VERSION', '5.0');
define('JWT_COOKIE_BRIDGE_MINIMUM_PHP_VERSION', '7.4');
define('JWT_COOKIE_BRIDGE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JWT_COOKIE_BRIDGE_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main plugin class
 */
class JWT_Cookie_Bridge {
    private static $instance = null;
    private $components = [];

    public static function get_instance(): JWT_Cookie_Bridge {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);

        if (!$this->check_requirements()) {
            return;
        }

        $this->init_hooks();
        $this->init_components();
    }

    private function init_hooks(): void {
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'add_settings_link']);
    }

    private function check_requirements(): bool {
        if (version_compare(PHP_VERSION, JWT_COOKIE_BRIDGE_MINIMUM_PHP_VERSION, '<')) {
            add_action('admin_notices', [$this, 'php_version_notice']);
            return false;
        }

        if (version_compare($GLOBALS['wp_version'], JWT_COOKIE_BRIDGE_MINIMUM_WP_VERSION, '<')) {
            add_action('admin_notices', [$this, 'wp_version_notice']);
            return false;
        }

        return true;
    }

    private function init_components(): void {
        spl_autoload_register(function ($class) {
            if (strpos($class, __NAMESPACE__ . '\\') !== 0) {
                return;
            }

            $class_path = str_replace(__NAMESPACE__ . '\\', '', $class);
            $class_path = str_replace('\\', DIRECTORY_SEPARATOR, $class_path);
            $file = JWT_COOKIE_BRIDGE_PLUGIN_DIR . 'src' . DIRECTORY_SEPARATOR . $class_path . '.php';

            if (file_exists($file)) {
                require_once $file;
            }
        });

        $this->components['settings'] = new Settings_Manager();
        $this->components['token_handler'] = new Token_Handler();
        
        if (Settings_Manager::is_debug_enabled()) {
            $this->components['debug'] = new Debug_Dashboard();
        }
    }

    public function activate(): void {
        if (!current_user_can('activate_plugins')) {
            return;
        }

        flush_rewrite_rules();
        $this->initialize_options();
    }

    public function deactivate(): void {
        if (!current_user_can('activate_plugins')) {
            return;
        }

        flush_rewrite_rules();
        $this->clear_plugin_data();
    }

    private function initialize_options(): void {
        $default_options = [
            'debug_mode' => false,
            'samesite_policy' => 'Lax',
            'http_only' => true,
            'cookie_name' => 'mo_jwt',
            'cookie_duration' => 3600,
        ];

        if (false === get_option('jwt_cookie_bridge_options')) {
            add_option('jwt_cookie_bridge_options', $default_options);
        }
    }

    private function clear_plugin_data(): void {
        if (isset($this->components['token_handler'])) {
            $cookie_manager = new Cookie_Manager();
            $cookie_manager->clear_token_cookie();
        }

        delete_transient('jwt_bridge_token_status');
    }

    public function add_settings_link(array $links): array {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('options-general.php?page=jwt-cookie-bridge'),
            __('Settings', 'jwt-cookie-bridge')
        );
        array_unshift($links, $settings_link);
        return $links;
    }

    public function load_textdomain(): void {
        load_plugin_textdomain(
            'jwt-cookie-bridge',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }

    public function php_version_notice(): void {
        $message = sprintf(
            /* translators: %s: Minimum PHP version */
            esc_html__('JWT Cookie Bridge requires PHP version %s or higher.', 'jwt-cookie-bridge'),
            JWT_COOKIE_BRIDGE_MINIMUM_PHP_VERSION
        );
        echo '<div class="notice notice-error"><p>' . esc_html($message) . '</p></div>';
    }

    public function wp_version_notice(): void {
        $message = sprintf(
            /* translators: %s: Minimum WordPress version */
            esc_html__('JWT Cookie Bridge requires WordPress version %s or higher.', 'jwt-cookie-bridge'),
            JWT_COOKIE_BRIDGE_MINIMUM_WP_VERSION
        );
        echo '<div class="notice notice-error"><p>' . esc_html($message) . '</p></div>';
    }
}

// Initialize plugin
add_action('plugins_loaded', function() {
    JWT_Cookie_Bridge::get_instance();
});

// Activation hook
register_activation_hook(__FILE__, 'JWTCookieBridge\\activate_jwt_cookie_bridge');

function activate_jwt_cookie_bridge(): void {
    if (!current_user_can('activate_plugins')) {
        return;
    }

    $default_options = [
        'debug_mode' => false,
        'samesite_policy' => 'Lax',
        'http_only' => true,
        'cookie_name' => 'mo_jwt',
        'cookie_duration' => 3600,
    ];

    if (false === get_option('jwt_cookie_bridge_options')) {
        add_option('jwt_cookie_bridge_options', $default_options);
    }
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'JWTCookieBridge\\deactivate_jwt_cookie_bridge');

function deactivate_jwt_cookie_bridge(): void {
    if (!current_user_can('activate_plugins')) {
        return;
    }

    if (class_exists(__NAMESPACE__ . '\Cookie_Manager')) {
        $cookie_manager = new Cookie_Manager();
        $cookie_manager->clear_token_cookie();
    }
}

// Uninstall hook - must be a static function
register_uninstall_hook(__FILE__, 'JWTCookieBridge\\uninstall_jwt_cookie_bridge');

function uninstall_jwt_cookie_bridge(): void {
    if (!current_user_can('activate_plugins')) {
        return;
    }

    delete_option('jwt_cookie_bridge_options');
    delete_transient('jwt_bridge_token_status');
}