<?php
/**
 * AJAX Handler for AI Chat
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_AI_Chat_Ajax_Handler {
    
    public function __construct() {
        // Constructor can be used for initialization if needed
    }
    
    /**
     * Handle chat message AJAX request
     */
    public function handle_chat_message() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'ai_chat_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }
        
        $message = sanitize_text_field($_POST['message']);
        $context = sanitize_text_field($_POST['context']);
        $conversation = isset($_POST['conversation']) ? $_POST['conversation'] : [];
        
        if (empty($message)) {
            wp_send_json_error('Message is required');
            return;
        }
        
        // Get AI handler instance
        $ai_handler = new WP_AI_Chat_AI_Handler();
        
        try {
            $response = $ai_handler->process_message($message, $context, $conversation);
            
            // Log the conversation
            $this->log_conversation($message, $response, $context);
            
            wp_send_json_success([
                'message' => $response,
                'context' => $context,
                'timestamp' => current_time('mysql')
            ]);
            
        } catch (Exception $e) {
            error_log('AI Chat Error: ' . $e->getMessage());
            wp_send_json_error('Failed to process message');
        }
    }
    
    /**
     * Handle image upload AJAX request
     */
    public function handle_image_upload() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'ai_chat_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }
        
        if (!isset($_FILES['image'])) {
            wp_send_json_error('No image uploaded');
            return;
        }
        
        $file = $_FILES['image'];
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed_types)) {
            wp_send_json_error('Invalid file type');
            return;
        }
        
        // Validate file size (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
            wp_send_json_error('File too large');
            return;
        }
        
        // Handle file upload
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        
        $attachment_id = media_handle_upload('image', 0);
        
        if (is_wp_error($attachment_id)) {
            wp_send_json_error('Upload failed: ' . $attachment_id->get_error_message());
            return;
        }
        
        // Get image URL
        $image_url = wp_get_attachment_url($attachment_id);
        $image_path = get_attached_file($attachment_id);
        
        // Analyze image using AI
        $ai_handler = new WP_AI_Chat_AI_Handler();
        
        try {
            $analysis = $ai_handler->analyze_image($image_path, $image_url);
            
            wp_send_json_success([
                'attachment_id' => $attachment_id,
                'image_url' => $image_url,
                'description' => $analysis['description'],
                'categories' => $analysis['categories'],
                'confidence' => $analysis['confidence']
            ]);
            
        } catch (Exception $e) {
            error_log('Image Analysis Error: ' . $e->getMessage());
            
            // Return basic success even if AI analysis fails
            wp_send_json_success([
                'attachment_id' => $attachment_id,
                'image_url' => $image_url,
                'description' => 'Product image uploaded',
                'categories' => [],
                'confidence' => 0.5
            ]);
        }
    }
    
    /**
     * Handle product search AJAX request
     */
    public function handle_product_search() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'ai_chat_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }
        
        $query = sanitize_text_field($_POST['query']);
        $type = sanitize_text_field($_POST['type']); // 'text' or 'image'
        
        if (empty($query)) {
            wp_send_json_error('Query is required');
            return;
        }
        
        // Get product matcher instance
        $product_matcher = new WP_AI_Chat_Product_Matcher();
        
        try {
            $products = $product_matcher->search_products($query, $type);
            
            wp_send_json_success([
                'products' => $products,
                'query' => $query,
                'total_found' => count($products)
            ]);
            
        } catch (Exception $e) {
            error_log('Product Search Error: ' . $e->getMessage());
            wp_send_json_error('Failed to search products');
        }
    }
    
    /**
     * Handle category suggestions AJAX request
     */
    public function handle_category_suggestions() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'ai_chat_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }
        
        $query = sanitize_text_field($_POST['query']);
        
        // Get AI handler for category suggestions
        $ai_handler = new WP_AI_Chat_AI_Handler();
        
        try {
            $categories = $ai_handler->suggest_categories($query);
            
            wp_send_json_success([
                'categories' => $categories,
                'query' => $query
            ]);
            
        } catch (Exception $e) {
            error_log('Category Suggestion Error: ' . $e->getMessage());
            
            // Fallback to default categories
            $default_categories = $this->get_default_categories();
            
            wp_send_json_success([
                'categories' => $default_categories,
                'query' => $query
            ]);
        }
    }
    
    /**
     * Handle vendor contact AJAX request
     */
    public function handle_vendor_contact() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'ai_chat_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }
        
        $category = sanitize_text_field($_POST['category']);
        $description = sanitize_textarea_field($_POST['description']);
        $user_id = intval($_POST['user_id']);
        
        if (empty($category) || empty($description)) {
            wp_send_json_error('Category and description are required');
            return;
        }
        
        // Get vendor notifier instance
        $vendor_notifier = new WP_AI_Chat_Vendor_Notifier();
        
        // Get request manager instance
        $request_manager = new WP_AI_Chat_Request_Manager();
        
        try {
            // Create product request record
            $request_id = $request_manager->create_request([
                'user_id' => $user_id,
                'category' => $category,
                'description' => $description,
                'status' => 'pending',
                'created_at' => current_time('mysql')
            ]);
            
            if (!$request_id) {
                wp_send_json_error('Failed to create request');
                return;
            }
            
            // Notify vendors
            $vendors_contacted = $vendor_notifier->notify_vendors($category, $description, $request_id, $user_id);
            
            // Update request with vendor count
            $request_manager->update_request($request_id, [
                'vendors_contacted' => count($vendors_contacted)
            ]);
            
            wp_send_json_success([
                'request_id' => $request_id,
                'vendors_contacted' => count($vendors_contacted),
                'category' => $category,
                'message' => sprintf(
                    'Your request has been sent to %d vendors in the %s category.',
                    count($vendors_contacted),
                    $category
                )
            ]);
            
        } catch (Exception $e) {
            error_log('Vendor Contact Error: ' . $e->getMessage());
            wp_send_json_error('Failed to contact vendors');
        }
    }
    
    /**
     * Log conversation for analytics and improvement
     */
    private function log_conversation($user_message, $ai_response, $context) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_conversations';
        
        $wpdb->insert(
            $table_name,
            [
                'user_id' => get_current_user_id(),
                'user_message' => $user_message,
                'ai_response' => $ai_response,
                'context' => $context,
                'session_id' => session_id() ?: wp_generate_uuid4(),
                'ip_address' => $this->get_client_ip(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'created_at' => current_time('mysql')
            ],
            [
                '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s'
            ]
        );
    }
    
    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Get default product categories
     */
    private function get_default_categories() {
        return [
            ['name' => 'Electronics', 'slug' => 'electronics'],
            ['name' => 'Clothing & Fashion', 'slug' => 'clothing-fashion'],
            ['name' => 'Home & Garden', 'slug' => 'home-garden'],
            ['name' => 'Sports & Outdoors', 'slug' => 'sports-outdoors'],
            ['name' => 'Health & Beauty', 'slug' => 'health-beauty'],
            ['name' => 'Books & Media', 'slug' => 'books-media'],
            ['name' => 'Toys & Games', 'slug' => 'toys-games'],
            ['name' => 'Automotive', 'slug' => 'automotive'],
            ['name' => 'Food & Beverages', 'slug' => 'food-beverages'],
            ['name' => 'Office Supplies', 'slug' => 'office-supplies']
        ];
    }
}

// Register AJAX actions
add_action('wp_ajax_ai_chat_message', [new WP_AI_Chat_Ajax_Handler(), 'handle_chat_message']);
add_action('wp_ajax_nopriv_ai_chat_message', [new WP_AI_Chat_Ajax_Handler(), 'handle_chat_message']);

add_action('wp_ajax_ai_upload_image', [new WP_AI_Chat_Ajax_Handler(), 'handle_image_upload']);
add_action('wp_ajax_nopriv_ai_upload_image', [new WP_AI_Chat_Ajax_Handler(), 'handle_image_upload']);

add_action('wp_ajax_ai_search_products', [new WP_AI_Chat_Ajax_Handler(), 'handle_product_search']);
add_action('wp_ajax_nopriv_ai_search_products', [new WP_AI_Chat_Ajax_Handler(), 'handle_product_search']);

add_action('wp_ajax_ai_suggest_categories', [new WP_AI_Chat_Ajax_Handler(), 'handle_category_suggestions']);
add_action('wp_ajax_nopriv_ai_suggest_categories', [new WP_AI_Chat_Ajax_Handler(), 'handle_category_suggestions']);

add_action('wp_ajax_ai_contact_vendors', [new WP_AI_Chat_Ajax_Handler(), 'handle_vendor_contact']);
add_action('wp_ajax_nopriv_ai_contact_vendors', [new WP_AI_Chat_Ajax_Handler(), 'handle_vendor_contact']);