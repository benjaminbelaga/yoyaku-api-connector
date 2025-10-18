<?php
/**
 * Plugin Name: YOYAKU API Connector
 * Plugin URI: https://github.com/benjaminbelaga/yoyaku-api-connector
 * Description: Centralized REST API endpoints for Google Apps Scripts integration - Ultra-fast direct database queries
 * Version: 1.0.0
 * Author: Benjamin Belaga
 * Author URI: https://yoyaku.io
 * License: GPL-2.0+
 * Text Domain: yoyaku-api-connector
 *
 * @package YOYAKU_API_Connector
 */

defined('ABSPATH') || exit;

// Plugin constants
define('YOYAKU_API_VERSION', '1.0.0');
define('YOYAKU_API_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('YOYAKU_API_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Autoloader for plugin classes
 */
spl_autoload_register(function($class) {
    // Only autoload YOYAKU classes
    if (strpos($class, 'YOYAKU_') !== 0) {
        return;
    }

    // Convert class name to file path
    // YOYAKU_Base_Endpoint → class-base-endpoint.php
    $class_file = 'class-' . strtolower(str_replace('_', '-', str_replace('YOYAKU_', '', $class))) . '.php';
    $file_path = YOYAKU_API_PLUGIN_DIR . 'includes/' . $class_file;

    if (file_exists($file_path)) {
        require_once $file_path;
    }
});

/**
 * Initialize plugin endpoints
 * Using init hook instead of plugins_loaded to ensure REST API is available
 */
add_action('init', function() {
    error_log('YOYAKU API Connector: init hook fired');

    // Initialize Product Stock Data endpoint (for wp-import-dashboard)
    if (class_exists('YOYAKU_Product_Stock_Endpoint')) {
        error_log('YOYAKU API Connector: YOYAKU_Product_Stock_Endpoint class exists, instantiating...');
        new YOYAKU_Product_Stock_Endpoint();
        error_log('YOYAKU API Connector: YOYAKU_Product_Stock_Endpoint instantiated');
    } else {
        error_log('YOYAKU API Connector: ERROR - YOYAKU_Product_Stock_Endpoint class not found!');
    }

    // Future endpoints can be added here
    // if (class_exists('YOYAKU_Product_Labels_Endpoint')) {
    //     new YOYAKU_Product_Stack_Endpoint();
    // }
}, 10);

/**
 * Activation hook
 */
register_activation_hook(__FILE__, function() {
    // Flush rewrite rules to register REST routes
    flush_rewrite_rules();
});

/**
 * Deactivation hook
 */
register_deactivation_hook(__FILE__, function() {
    // Clean up
    flush_rewrite_rules();
});
