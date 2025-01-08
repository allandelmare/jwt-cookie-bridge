<?php
/**
 * Plugin Name: JWT Cookie Bridge for MiniOrange SSO
 * Description: Securely stores JWT tokens from MiniOrange OAuth/OpenID in a cookie for seamless SSO integration
 * Version: 1.0.4
 * Author: Allan Delmare
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: jwt-cookie-bridge
 * Requires PHP: 7.4
 */

// Prevent direct file access
defined('ABSPATH') || exit;

// Autoloader for classes in src directory
spl_autoload_register(function ($class) {
    if (strpos($class, 'JWTCookieBridge\\') !== 0) {
        return;
    }

    $class_path = str_replace('JWTCookieBridge\\', '', $class);
    $class_path = str_replace('\\', DIRECTORY_SEPARATOR, $class_path);
    $file = plugin_dir_path(__FILE__) . 'src' . DIRECTORY_SEPARATOR . $class_path . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Initialize main plugin components
function init_jwt_cookie_bridge() {
    $settings_manager = new JWTCookieBridge\Settings_Manager();
    $token_handler = new JWTCookieBridge\Token_Handler();
    
    if (JWTCookieBridge\Settings_Manager::is_debug_enabled()) {
        $debug_dashboard = new JWTCookieBridge\Debug_Dashboard();
    }
}

// Hook initialization to WordPress
add_action('plugins_loaded', 'init_jwt_cookie_bridge');

// Register activation hook
register_activation_hook(__FILE__, 'activate_jwt_cookie_bridge');

function activate_jwt_cookie_bridge() {
    if (false === get_option('jwt_cookie_bridge_options')) {
        add_option('jwt_cookie_bridge_options', [
            'debug_mode' => false,
            'samesite_policy' => 'Lax',
            'http_only' => true,
            'cookie_name' => 'mo_jwt',
            'cookie_duration' => 3600,
            'allowed_domains' => ''
        ]);
    }
}

// Register deactivation hook
register_deactivation_hook(__FILE__, 'deactivate_jwt_cookie_bridge');

function deactivate_jwt_cookie_bridge() {
    // Clear any existing cookies
    if (class_exists('JWTCookieBridge\Cookie_Manager')) {
        $cookie_manager = new JWTCookieBridge\Cookie_Manager();
        $cookie_manager->clear_token_cookie();
    }
}

// Register uninstall hook
register_uninstall_hook(__FILE__, 'uninstall_jwt_cookie_bridge');

function uninstall_jwt_cookie_bridge() {
    delete_option('jwt_cookie_bridge_options');
}