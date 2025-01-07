<?php
/**
* Plugin Name: Christendom SSO Token Handler
* Description: Manages JWT tokens for SSO integration between WordPress and Annunciate
* Version: 1.0.0
* Author: Allan Delmare
*/

// Prevent direct file access
defined('ABSPATH') || exit;

// Required files
require_once plugin_dir_path(__FILE__) . 'class-settings-manager.php';
require_once plugin_dir_path(__FILE__) . 'class-cookie-manager.php';
require_once plugin_dir_path(__FILE__) . 'class-token-handler.php';
require_once plugin_dir_path(__FILE__) . 'class-debug-dashboard.php';

// Initialize main plugin components
function init_christendom_sso() {
    // Initialize settings manager first since other components depend on it
    $settings_manager = new ChristendomSSO\Settings_Manager();
    
    // Initialize token handler
    $token_handler = new ChristendomSSO\Token_Handler();
    
    // Initialize debug dashboard if debug mode is enabled
    if (ChristendomSSO\Settings_Manager::is_debug_enabled()) {
        $debug_dashboard = new ChristendomSSO\Debug_Dashboard();
    }
}

// Hook initialization to WordPress
add_action('plugins_loaded', 'init_christendom_sso');

// Register activation hook
register_activation_hook(__FILE__, 'activate_christendom_sso');

function activate_christendom_sso() {
    // Set default options if they don't exist
    if (false === get_option('christendom_sso_options')) {
        add_option('christendom_sso_options', [
            'debug_mode' => false,
            'samesite_policy' => 'Lax',
            'http_only' => true
        ]);
    }
}