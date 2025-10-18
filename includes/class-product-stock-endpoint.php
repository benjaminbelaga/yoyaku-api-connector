<?php
/**
 * Product Stock Data Endpoint
 * For wp-import-dashboard Google Apps Script
 * Returns: images, stock, custom fields in batch
 *
 * @package YOYAKU_API_Connector
 */

defined('ABSPATH') || exit;

class YOYAKU_Product_Stock_Endpoint extends YOYAKU_Base_Endpoint {

    public function __construct() {
        error_log('YOYAKU_Product_Stock_Endpoint: Constructor called');
        add_action('rest_api_init', array($this, 'register_routes'));
        error_log('YOYAKU_Product_Stock_Endpoint: rest_api_init hook added');
    }

    /**
     * Register REST API routes
     */
    public function register_routes() {
        error_log('YOYAKU_Product_Stock_Endpoint: register_routes() method called');

        // Single product endpoint
        $result1 = register_rest_route('yoyaku/v1', '/product-stock-data/(?P<sku>[a-zA-Z0-9-_]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_product_by_sku'),
            'permission_callback' => '__return_true', // Public endpoint
            'args' => array(
                'sku' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ));
        error_log('YOYAKU_Product_Stock_Endpoint: Single route registered, result: ' . ($result1 ? 'TRUE' : 'FALSE'));

        // Batch endpoint - process multiple SKUs at once
        $result2 = register_rest_route('yoyaku/v1', '/product-stock-data/batch', array(
            'methods' => 'POST',
            'callback' => array($this, 'get_products_batch'),
            'permission_callback' => '__return_true',
            'args' => array(
                'skus' => array(
                    'required' => true,
                    'type' => 'array',
                    'validate_callback' => function($param) {
                        return is_array($param) && count($param) > 0 && count($param) <= 50;
                    }
                )
            )
        ));
        error_log('YOYAKU_Product_Stock_Endpoint: Batch route registered, result: ' . ($result2 ? 'TRUE' : 'FALSE'));
        error_log('YOYAKU_Product_Stock_Endpoint: register_routes() completed successfully');
    }

    /**
     * Get single product by SKU
     */
    public function get_product_by_sku($request) {
        $sku = strtoupper($request['sku']);

        $data = $this->fetch_product_stock_data($sku);

        if (!$data) {
            return new WP_Error('not_found', 'Product not found', array('status' => 404));
        }

        $this->add_cors_headers();
        return rest_ensure_response($data);
    }

    /**
     * Get multiple products in one request
     * ULTRA FAST: Batch processing
     */
    public function get_products_batch($request) {
        $skus = $request['skus'];
        $results = array();

        foreach ($skus as $sku) {
            $data = $this->fetch_product_stock_data(strtoupper($sku));
            if ($data) {
                $results[] = $data;
            } else {
                // Return error info for missing products
                $results[] = array(
                    'sku' => strtoupper($sku),
                    'error' => 'Product not found',
                    'found' => false
                );
            }
        }

        $this->add_cors_headers();
        return rest_ensure_response($results);
    }

    /**
     * Fetch product stock data directly from database
     * Bypasses WooCommerce filters/hooks completely
     *
     * @param string $sku Product SKU
     * @return array|null Product data or null if not found
     */
    private function fetch_product_stock_data($sku) {
        // Get product ID
        $product_id = $this->get_product_id_by_sku($sku);
        if (!$product_id) {
            return null;
        }

        // Get basic product data
        $product = $this->get_product_basic_data($product_id);
        if (!$product || $product->post_status !== 'publish') {
            return null;
        }

        // Get stock data
        $stock = $this->get_stock_data($product_id);

        // Get image URL
        $image_url = $this->get_image_url($product_id);

        // Get custom fields
        $depot_vente = $this->get_custom_field($product_id, '_depot_vente');
        $initial_quantity = $this->get_custom_field($product_id, '_initial_quantity');
        $shelf_quantity = $this->get_custom_field($product_id, 'yid_total_shelf');
        $total_preorders = $this->get_custom_field($product_id, '_total_preorders');

        // Format response - optimized for Google Sheets
        return array(
            'sku' => $sku,
            'product_id' => $product_id,
            'title' => $product->post_title,
            'found' => true,

            // Image data
            'image_url' => $image_url,

            // Stock data
            'stock_quantity' => $stock['quantity'],
            'stock_status' => $stock['status'],

            // Custom fields
            'depot_vente' => $depot_vente,
            'initial_quantity' => $initial_quantity,
            'shelf_quantity' => $shelf_quantity,
            'total_preorders' => $total_preorders
        );
    }
}
