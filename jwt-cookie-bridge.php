<?php
/**
 * Plugin Name: JWT Cookie Bridge for MiniOrange SSO
 * Plugin URI: https://github.com/allandelmare/jwt-cookie-bridge
 * Description: Securely stores JWT tokens from MiniOrange OAuth/OpenID in a cookie for seamless SSO integration
 * Version: 1.0.6
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

// Plugin version
define('JWT_COOKIE_BRIDGE_VERSION', '1.0.5');
define('JWT_COOKIE_BRIDGE_MINIMUM_WP_VERSION', '5.0');
define('JWT_COOKIE_BRIDGE_MINIMUM_PHP_VERSION', '7.4');

/**
 * Main plugin class
 */
class JWT_Cookie_Bridge {
    private static $instance = null;

    public static function get_instance(): JWT_Cookie_Bridge {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        if (!$this->check_requirements()) {
            return;
        }

        add_action('init', [$this, 'load_textdomain']);
        $this->init_components();
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

    public function load_textdomain(): void {
        load_plugin_textdomain(
            'jwt-cookie-bridge',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }

    private function init_components(): void {
        spl_autoload_register(function ($class) {
            if (strpos($class, __NAMESPACE__ . '\\') !== 0) {
                return;
            }

            $class_path = str_replace(__NAMESPACE__ . '\\', '', $class);
            $class_path = str_replace('\\', DIRECTORY_SEPARATOR, $class_path);
            $file = plugin_dir_path(__FILE__) . 'src' . DIRECTORY_SEPARATOR . $class_path . '.php';

            if (file_exists($file)) {
                require_once $file;
            }
        });

        new Settings_Manager();
        new Token_Handler();
        
        if (Settings_Manager::is_debug_enabled()) {
            new Debug_Dashboard();
        }
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
}