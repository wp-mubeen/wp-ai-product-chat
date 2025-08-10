<?php
/**
 * Request Manager Class
 * Handles product request operations and management
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_AI_Chat_Request_Manager {
    
    private $db_manager;
    
    public function __construct() {
        $this->db_manager = new WP_AI_Chat_Database_Manager();
    }
    
    /**
     * Create a new product request
     */
    public function create_request($data) {
        // Validate required fields
        if (empty($data['category']) || empty($data['description'])) {
            throw new Exception('Category and description are required');
        }
        
        // Sanitize and prepare data
        $request_data = [
            'user_id' => intval($data['user_id'] ?? 0),
            'category' => sanitize_text_field($data['category']),
            'description' => sanitize_textarea_field($data['description']),
            'status' => sanitize_text_field($data['status'] ?? 'pending'),
            'vendors_contacted' => intval($data['vendors_contacted'] ?? 0),
            'responses_received' => intval($data['responses_received'] ?? 0),
            'image_url' => esc_url_raw($data['image_url'] ?? ''),
            'customer_name' => sanitize_text_field($data['customer_name'] ?? ''),
            'customer_email' => sanitize_email($data['customer_email'] ?? ''),
            'customer_phone' => sanitize_text_field($data['customer_phone'] ?? ''),
            'priority' => sanitize_text_field($data['priority'] ?? 'normal'),
            'notes' => sanitize_textarea_field($data['notes'] ?? ''),
            'created_at' => current_time('mysql')
        ];
        
        // Auto-fill customer info if user is logged in
        if ($request_data['user_id'] > 0) {
            $user = get_user_by('ID', $request_data['user_id']);
            if ($user) {
                $request_data['customer_name'] = $request_data['customer_name'] ?: $user->display_name;
                $request_data['customer_email'] = $request_data['customer_email'] ?: $user->user_email;
                $request_data['customer_phone'] = $request_data['customer_phone'] ?: get_user_meta($user->ID, 'phone', true);
            }
        }
        
        // Generate unique request number
        $request_data['request_number'] = $this->generate_request_number();
        
        // Insert request
        $request_id = $this->db_manager->insert_request($request_data);
        
        if (!$request_id) {
            throw new Exception('Failed to create request');
        }
        
        // Log request creation
        $this->log_request_activity($request_id, 'created', 'Request created by ' . ($request_data['customer_name'] ?: 'guest'));
        
        // Hook for after request creation
        do_action('ai_chat_request_created', $request_id, $request_data);
        
        return $request_id;
    }
    
    /**
     * Update an existing request
     */
    public function update_request($request_id, $data) {
        if (!$this->request_exists($request_id)) {
            throw new Exception('Request not found');
        }
        
        // Sanitize update data
        $update_data = [];
        
        if (isset($data['status'])) {
            $update_data['status'] = sanitize_text_field($data['status']);
        }
        
        if (isset($data['vendors_contacted'])) {
            $update_data['vendors_contacted'] = intval($data['vendors_contacted']);
        }
        
        if (isset($data['responses_received'])) {
            $update_data['responses_received'] = intval($data['responses_received']);
        }
        
        if (isset($data['priority'])) {
            $update_data['priority'] = sanitize_text_field($data['priority']);
        }
        
        if (isset($data['notes'])) {
            $update_data['notes'] = sanitize_textarea_field($data['notes']);
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        $result = $this->db_manager->update_request($request_id, $update_data);
        
        if ($result) {
            // Log update activity
            $changes = implode(', ', array_keys($update_data));
            $this->log_request_activity($request_id, 'updated', "Updated fields: {$changes}");
            
            // Hook for after request update
            do_action('ai_chat_request_updated', $request_id, $update_data);
        }
        
        return $result;
    }
    
    /**
     * Get request by ID
     */
    public function get_request($request_id) {
        return $this->db_manager->get_request($request_id);
    }
    
    /**
     * Get requests with filters and pagination
     */
    public function get_requests($args = []) {
        $defaults = [
            'status' => '',
            'category' => '',
            'user_id' => 0,
            'priority' => '',
            'date_from' => '',
            'date_to' => '',
            'search' => '',
            'orderby' => 'created_at',
            'order' => 'DESC',
            'limit' => 20,
            'offset' => 0
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        return $this->db_manager->get_requests($args);
    }
    
    /**
     * Get user requests
     */
    public function get_user_requests($user_id, $limit = 20, $offset = 0) {
        return $this->db_manager->get_user_requests($user_id, $limit, $offset);
    }
    
    /**
     * Delete a request
     */
    public function delete_request($request_id) {
        global $wpdb;
        
        if (!$this->request_exists($request_id)) {
            throw new Exception('Request not found');
        }
        
        $table_name = $wpdb->prefix . 'ai_chat_requests';
        
        $result = $wpdb->delete($table_name, ['id' => $request_id], ['%d']);
        
        if ($result) {
            // Delete related notifications
            $this->delete_request_notifications($request_id);
            
            // Log deletion
            $this->log_request_activity($request_id, 'deleted', 'Request deleted');
            
            // Hook for after request deletion
            do_action('ai_chat_request_deleted', $request_id);
        }
        
        return $result;
    }
    
    /**
     * Mark request as completed
     */
    public function complete_request($request_id, $completion_notes = '') {
        $update_data = [
            'status' => 'completed',
            'completed_at' => current_time('mysql')
        ];
        
        if (!empty($completion_notes)) {
            $update_data['notes'] = sanitize_textarea_field($completion_notes);
        }
        
        $result = $this->update_request($request_id, $update_data);
        
        if ($result) {
            $this->log_request_activity($request_id, 'completed', 'Request marked as completed: ' . $completion_notes);
            
            // Send completion notification to customer
            $this->send_completion_notification($request_id);
            
            // Hook for request completion
            do_action('ai_chat_request_completed', $request_id);
        }
        
        return $result;
    }
    
    /**
     * Cancel a request
     */
    public function cancel_request($request_id, $reason = '') {
        $update_data = [
            'status' => 'cancelled',
            'cancelled_at' => current_time('mysql')
        ];
        
        if (!empty($reason)) {
            $current_notes = $this->get_request($request_id)['notes'] ?? '';
            $update_data['notes'] = $current_notes . "\n\nCancellation reason: " . sanitize_textarea_field($reason);
        }
        
        $result = $this->update_request($request_id, $update_data);
        
        if ($result) {
            $this->log_request_activity($request_id, 'cancelled', 'Request cancelled: ' . $reason);
            
            // Hook for request cancellation
            do_action('ai_chat_request_cancelled', $request_id, $reason);
        }
        
        return $result;
    }
    
    /**
     * Get request statistics
     */
    public function get_request_statistics($days = 30) {
        return $this->db_manager->get_request_stats($days);
    }
    
    /**
     * Get requests by status
     */
    public function get_requests_by_status($status) {
        return $this->get_requests(['status' => $status]);
    }
    
    /**
     * Get requests by category
     */
    public function get_requests_by_category($category) {
        return $this->get_requests(['category' => $category]);
    }
    
    /**
     * Get overdue requests
     */
    public function get_overdue_requests() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_requests';
        $overdue_hours = get_option('ai_chat_overdue_threshold', 48);
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name 
             WHERE status = 'pending' 
             AND created_at < DATE_SUB(NOW(), INTERVAL %d HOUR)
             ORDER BY created_at ASC",
            $overdue_hours
        ), ARRAY_A);
    }
    
    /**
     * Auto-close old requests
     */
    public function auto_close_old_requests() {
        global $wpdb;
        
        $auto_close_days = get_option('ai_chat_auto_close_requests', 7);
        
        if ($auto_close_days <= 0) {
            return 0; // Auto-close disabled
        }
        
        $table_name = $wpdb->prefix . 'ai_chat_requests';
        
        $old_requests = $wpdb->get_results($wpdb->prepare(
            "SELECT id FROM $table_name 
             WHERE status = 'pending' 
             AND created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $auto_close_days
        ));
        
        $closed_count = 0;
        
        foreach ($old_requests as $request) {
            $result = $this->update_request($request->id, [
                'status' => 'auto_closed',
                'notes' => "Automatically closed after {$auto_close_days} days of inactivity."
            ]);
            
            if ($result) {
                $closed_count++;
                $this->log_request_activity($request->id, 'auto_closed', 'Request auto-closed due to inactivity');
            }
        }
        
        return $closed_count;
    }
    
    /**
     * Generate unique request number
     */
    private function generate_request_number() {
        $prefix = get_option('ai_chat_request_prefix', 'REQ');
        $date = date('Ymd');
        
        // Get next sequential number for today
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_chat_requests';
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE DATE(created_at) = %s",
            date('Y-m-d')
        ));
        
        $sequence = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        
        return $prefix . '-' . $date . '-' . $sequence;
    }
    
    /**
     * Check if request exists
     */
    private function request_exists($request_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_requests';
        
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE id = %d",
            $request_id
        ));
        
        return !empty($exists);
    }
    
    /**
     * Log request activity
     */
    private function log_request_activity($request_id, $action, $description) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_request_activity';
        
        // Create activity table if it doesn't exist
        $this->maybe_create_activity_table();
        
        $wpdb->insert(
            $table_name,
            [
                'request_id' => $request_id,
                'action' => $action,
                'description' => $description,
                'user_id' => get_current_user_id(),
                'created_at' => current_time('mysql')
            ],
            ['%d', '%s', '%s', '%d', '%s']
        );
    }
    
    /**
     * Create activity table if needed
     */
    private function maybe_create_activity_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_request_activity';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            
            $sql = "CREATE TABLE $table_name (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                request_id bigint(20) NOT NULL,
                action varchar(50) NOT NULL,
                description text DEFAULT '',
                user_id bigint(20) DEFAULT 0,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY request_id (request_id),
                KEY action (action),
                KEY created_at (created_at)
            ) $charset_collate;";
            
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }
    }
    
    /**
     * Delete request notifications
     */
    private function delete_request_notifications($request_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_vendor_notifications';
        
        $wpdb->delete($table_name, ['request_id' => $request_id], ['%d']);
    }
    
    /**
     * Send completion notification to customer
     */
    private function send_completion_notification($request_id) {
        $request = $this->get_request($request_id);
        
        if (!$request || empty($request['customer_email'])) {
            return false;
        }
        
        $subject = sprintf(
            '[%s] Your Product Request Has Been Completed',
            get_bloginfo('name')
        );
        
        $message = sprintf(
            "Hello %s,\n\n" .
            "Good news! Your product request has been completed.\n\n" .
            "Request Details:\n" .
            "- Request ID: %s\n" .
            "- Category: %s\n" .
            "- Description: %s\n" .
            "- Completion Date: %s\n\n" .
            "Thank you for using %s!\n\n" .
            "Best regards,\n%s Team",
            $request['customer_name'],
            $request['request_number'] ?? $request_id,
            $request['category'],
            $request['description'],
            current_time('F j, Y \a\t g:i A'),
            get_bloginfo('name'),
            get_bloginfo('name')
        );
        
        return wp_mail($request['customer_email'], $subject, $message);
    }
    
    /**
     * Export requests to CSV
     */
    public function export_requests_csv($args = []) {
        $requests = $this->get_requests(array_merge($args, ['limit' => -1]));
        
        if (empty($requests)) {
            return false;
        }
        
        $filename = 'ai-chat-requests-' . date('Y-m-d-H-i-s') . '.csv';
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['basedir'] . '/ai-chat/exports/' . $filename;
        
        // Ensure directory exists
        wp_mkdir_p(dirname($file_path));
        
        $file = fopen($file_path, 'w');
        
        if (!$file) {
            return false;
        }
        
        // CSV headers
        $headers = [
            'ID', 'Request Number', 'Customer Name', 'Customer Email', 
            'Category', 'Description', 'Status', 'Priority', 'Vendors Contacted', 
            'Responses Received', 'Created At', 'Updated At'
        ];
        
        fputcsv($file, $headers);
        
        // CSV data
        foreach ($requests as $request) {
            $row = [
                $request['id'],
                $request['request_number'] ?? $request['id'],
                $request['customer_name'],
                $request['customer_email'],
                $request['category'],
                $request['description'],
                $request['status'],
                $request['priority'],
                $request['vendors_contacted'],
                $request['responses_received'],
                $request['created_at'],
                $request['updated_at']
            ];
            
            fputcsv($file, $row);
        }
        
        fclose($file);
        
        return [
            'filename' => $filename,
            'path' => $file_path,
            'url' => $upload_dir['baseurl'] . '/ai-chat/exports/' . $filename,
            'size' => filesize($file_path),
            'count' => count($requests)
        ];
    }
}