<?php
/**
 * Base Endpoint Class
 * Abstract class with common methods for all YOYAKU API endpoints
 *
 * @package YOYAKU_API_Connector
 */

defined('ABSPATH') || exit;

abstract class YOYAKU_Base_Endpoint {

    /**
     * Get product ID by SKU
     * Direct database query - no filters/hooks
     *
     * @param string $sku Product SKU
     * @return int|null Product ID or null if not found
     */
    protected function get_product_id_by_sku($sku) {
        global $wpdb;

        $product_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta}
             WHERE meta_key = '_sku' AND meta_value = %s
             LIMIT 1",
            strtoupper($sku)
        ));

        return $product_id ? (int)$product_id : null;
    }

    /**
     * Get product basic info
     *
     * @param int $product_id Product ID
     * @return object|null Product data or null
     */
    protected function get_product_basic_data($product_id) {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare(
            "SELECT post_title, post_name, post_status
             FROM {$wpdb->posts}
             WHERE ID = %d AND post_type = 'product'",
            $product_id
        ));
    }

    /**
     * Get image URL from thumbnail ID
     *
     * @param int $product_id Product ID
     * @return string Image URL or empty string
     */
    protected function get_image_url($product_id) {
        global $wpdb;

        $image_id = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->postmeta}
             WHERE post_id = %d AND meta_key = '_thumbnail_id' LIMIT 1",
            $product_id
        ));

        if (!$image_id) {
            return '';
        }

        // Get image URL
        $image_url = wp_get_attachment_url($image_id);
        return $image_url ? $image_url : '';
    }

    /**
     * Get custom field value
     *
     * @param int $product_id Product ID
     * @param string $meta_key Meta key
     * @return string Meta value or empty string
     */
    protected function get_custom_field($product_id, $meta_key) {
        global $wpdb;

        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->postmeta}
             WHERE post_id = %d AND meta_key = %s LIMIT 1",
            $product_id,
            $meta_key
        ));

        return $value !== null ? $value : '';
    }

    /**
     * Get stock data
     *
     * @param int $product_id Product ID
     * @return array Stock quantity and status
     */
    protected function get_stock_data($product_id) {
        global $wpdb;

        $stock_quantity = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->postmeta}
             WHERE post_id = %d AND meta_key = '_stock' LIMIT 1",
            $product_id
        ));

        $stock_status = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->postmeta}
             WHERE post_id = %d AND meta_key = '_stock_status' LIMIT 1",
            $product_id
        ));

        return array(
            'quantity' => $stock_quantity !== null ? (int)$stock_quantity : 0,
            'status' => $stock_status ? $stock_status : 'outofstock'
        );
    }

    /**
     * Get taxonomy terms directly from database
     *
     * @param int $product_id Product ID
     * @param string $taxonomy Taxonomy name
     * @return array Array of term names
     */
    protected function get_terms_direct($product_id, $taxonomy) {
        global $wpdb;

        $terms = $wpdb->get_col($wpdb->prepare(
            "SELECT t.name
             FROM {$wpdb->terms} t
             INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
             INNER JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
             WHERE tr.object_id = %d AND tt.taxonomy = %s
             ORDER BY t.name ASC",
            $product_id,
            $taxonomy
        ));

        // Decode HTML entities
        return array_map(function($term) {
            return html_entity_decode($term, ENT_QUOTES, 'UTF-8');
        }, $terms);
    }

    /**
     * Add CORS headers for public access
     */
    protected function add_cors_headers() {
        header('Access-Control-Allow-Origin: *');
        header('Cache-Control: no-cache, must-revalidate, max-age=0');
    }

    /**
     * Abstract method - must be implemented by child classes
     * Register REST API routes
     */
    abstract public function register_routes();
}
