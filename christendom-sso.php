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
require_once plugin_dir_path(__FILE__) . 'class-token-handler.php';
require_once plugin_dir_path(__FILE__) . 'class-cookie-manager.php';
require_once plugin_dir_path(__FILE__) . 'class-debug-dashboard.php';
require_once plugin_dir_path(__FILE__) . 'class-settings-manager.php';

// Initialize main plugin components
function init_christendom_sso() {
    // Initialize settings manager
    new ChristendomSSO\Settings_Manager();
    
    // Initialize token handler
    new ChristendomSSO\Token_Handler();
    
    // Initialize debug dashboard if debug mode is enabled
    if (ChristendomSSO\Settings_Manager::is_debug_enabled()) {
        new ChristendomSSO\Debug_Dashboard();
    }
}

// Hook initialization to WordPress
add_action('plugins_loaded', 'init_christendom_sso');