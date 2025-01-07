<?php
/**
 * Plugin Name: Christendom SSO Token Handler
 * Description: Manages JWT tokens for SSO integration between WordPress and Annunciate
 * Version: 1.0.3
 * Author: Allan Delmare
 */

// Prevent direct file access
defined('ABSPATH') || exit;

// Autoloader for classes in src directory
spl_autoload_register(function ($class) {
    // Only handle classes in our namespace
    if (strpos($class, 'ChristendomSSO\\') !== 0) {
        return;
    }

    // Convert namespace to file path
    $class_path = str_replace('ChristendomSSO\\', '', $class);
    $class_path = str_replace('\\', DIRECTORY_SEPARATOR, $class_path);
    $file = plugin_dir_path(__FILE__) . 'src' . DIRECTORY_SEPARATOR . $class_path . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Initialize main plugin components
function init_christendom_sso() {
    $settings_manager = new ChristendomSSO\Settings_Manager();
    $token_handler = new ChristendomSSO\Token_Handler();
    
    if (ChristendomSSO\Settings_Manager::is_debug_enabled()) {
        $debug_dashboard = new ChristendomSSO\Debug_Dashboard();
    }
}

// Hook initialization to WordPress
add_action('plugins_loaded', 'init_christendom_sso');

// Register activation hook
register_activation_hook(__FILE__, 'activate_christendom_sso');

function activate_christendom_sso() {
    if (false === get_option('christendom_sso_options')) {
        add_option('christendom_sso_options', [
            'debug_mode' => false,
            'samesite_policy' => 'Lax',
            'http_only' => true,
            'cookie_name' => 'sso_jwt'
        ]);
    }
}