<?php
/**
 * Plugin Name: JWT Cookie Bridge for MiniOrange SSO
 * Plugin URI: https://github.com/allandelmare/jwt-cookie-bridge
 * Description: Securely stores JWT tokens from MiniOrange OAuth/OpenID in a cookie for seamless SSO integration
 * Version: 1.0.5
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Author: Allan Delmare
 * Author URI: https://github.com/allandelmare
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: jwt-cookie-bridge
 * Domain Path: /languages
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
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
 * Class JWT_Cookie_Bridge
 * Main plugin class
 */
class JWT_Cookie_Bridge {
    /**
     * Plugin instance
     *
     * @var JWT_Cookie_Bridge|null
     */
    private static $instance = null;

    /**
     * Get plugin instance
     *
     * @return JWT_Cookie_Bridge
     */
    public static function get_instance(): JWT_Cookie_Bridge {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Check requirements
        if (!$this->check_requirements()) {
            return;
        }

        // Load translations
        add_action('init', [$this, 'load_textdomain']);

        // Initialize components
        $this->init_components();
    }

    /**
     * Check if system requirements are met
     *
     * @return bool True if requirements are met
     */
    private function check_requirements(): bool {
        // Check PHP version
        if (version_compare(PHP_VERSION, JWT_COOKIE_BRIDGE_MINIMUM_PHP_VERSION, '<')) {
            add_action('admin_notices', [$this, 'php_version_notice']);
            return false;
        }

        // Check WordPress version
        if (version_compare($GLOBALS['wp_version'], JWT_COOKIE_BRIDGE_MINIMUM_WP_VERSION, '<')) {
            add_action('admin_notices', [$this, 'wp_version_notice']);
            return false;
        }

        return true;
    }

    /**
     * Load plugin textdomain
     */
    public function load_textdomain(): void {
        load_plugin_textdomain(
            'jwt-cookie-bridge',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }

    /**
     * Initialize plugin components
     */
    private function init_components(): void {
        // Register autoloader
        spl_autoload_register(function ($class) {
            // Check if the class is in our namespace
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

        // Initialize components
        new Settings_Manager();
        new Token_Handler();
        
        if (Settings_Manager::is_debug_enabled()) {
            new Debug_Dashboard();
        }
    }

    /**
     * Display PHP version notice
     */
    public function php_version_notice(): void {
        $message = sprintf(
            /* translators: %s: Minimum PHP version */
            esc_html__('JWT Cookie Bridge requires PHP version %s or higher.', 'jwt-cookie-bridge'),
            JWT_COOKIE_BRIDGE_MINIMUM_PHP_VERSION
        );
        echo '<div class="notice notice-error"><p>' . esc_html($message) . '</p></div>';
    }

    /**
     * Display WordPress version notice
     */
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
function init_jwt_cookie_bridge(): void {
    JWT_Cookie_Bridge::get_instance();
}
add_action('plugins_loaded', __NAMESPACE__ . '\init_jwt_cookie_bridge');

// Activation hook
register_activation_hook(__FILE__, function() {
    if (!current_user_can('activate_plugins')) {
        return;
    }

    // Default options
    $default_options = [
        'debug_mode' => false,
        'samesite_policy' => 'Lax',
        'http_only' => true,
        'cookie_name' => 'mo_jwt',
        'cookie_duration' => 3600,
        'allowed_domains' => ''
    ];

    if (false === get_option('jwt_cookie_bridge_options')) {
        add_option('jwt_cookie_bridge_options', $default_options);
    }
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    if (!current_user_can('activate_plugins')) {
        return;
    }

    // Clear cookies
    if (class_exists(__NAMESPACE__ . '\Cookie_Manager')) {
        $cookie_manager = new Cookie_Manager();
        $cookie_manager->clear_token_cookie();
    }
});

// Uninstall hook
register_uninstall_hook(__FILE__, function() {
    if (!current_user_can('activate_plugins')) {
        return;
    }

    delete_option('jwt_cookie_bridge_options');
});