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
 */
add_action('plugins_loaded', function() {
    // Initialize Product Stock Data endpoint (for wp-import-dashboard)
    if (class_exists('YOYAKU_Product_Stock_Endpoint')) {
        new YOYAKU_Product_Stock_Endpoint();
    }

    // Future endpoints can be added here
    // if (class_exists('YOYAKU_Product_Labels_Endpoint')) {
    //     new YOYAKU_Product_Labels_Endpoint();
    // }
});

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
