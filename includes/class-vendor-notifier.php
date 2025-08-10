<?php
/**
 * Vendor Notifier Class
 * Handles notifications to vendors when customers request products
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_AI_Chat_Vendor_Notifier {
    
    private $email_template_path;
    
    public function __construct() {
        $this->email_template_path = WP_AI_CHAT_PLUGIN_PATH . 'templates/emails/';
    }
    
    /**
     * Notify vendors about a product request
     */
    public function notify_vendors($category, $description, $request_id, $customer_id = 0) {
        $vendors = $this->get_vendors_by_category($category);
        $contacted_vendors = [];
        
        if (empty($vendors)) {
            // Log that no vendors were found
            error_log("AI Chat: No vendors found for category: {$category}");
            return $contacted_vendors;
        }
        
        $customer_info = $this->get_customer_info($customer_id);
        
        foreach ($vendors as $vendor) {
            try {
                $success = $this->send_vendor_notification($vendor, $category, $description, $request_id, $customer_info);
                
                if ($success) {
                    $contacted_vendors[] = $vendor['id'];
                    
                    // Log successful notification
                    $this->log_vendor_notification($vendor['id'], $request_id, 'sent');
                } else {
                    // Log failed notification
                    $this->log_vendor_notification($vendor['id'], $request_id, 'failed');
                }
                
            } catch (Exception $e) {
                error_log('AI Chat Vendor Notification Error: ' . $e->getMessage());
                $this->log_vendor_notification($vendor['id'], $request_id, 'error', $e->getMessage());
            }
        }
        
        // Send confirmation to customer if they're logged in
        if ($customer_id > 0) {
            $this->send_customer_confirmation($customer_info, $category, $description, count($contacted_vendors));
        }
        
        return $contacted_vendors;
    }
    
    /**
     * Get vendors by category
     */
    private function get_vendors_by_category($category) {
        global $wpdb;
        
        $vendors = [];
        
        // Check if we have a custom vendors table
        $vendors_table = $wpdb->prefix . 'ai_chat_vendors';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '{$vendors_table}'") == $vendors_table) {
            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT v.*, vc.category_slug 
                 FROM {$vendors_table} v
                 JOIN {$wpdb->prefix}ai_chat_vendor_categories vc ON v.id = vc.vendor_id
                 WHERE v.status = 'active' 
                 AND v.notifications_enabled = 1
                 AND (vc.category_slug = %s OR vc.category_slug = 'all')",
                sanitize_title($category)
            ), ARRAY_A);
            
            foreach ($results as $result) {
                $vendors[] = [
                    'id' => $result['id'],
                    'name' => $result['name'],
                    'email' => $result['email'],
                    'company' => $result['company'],
                    'phone' => $result['phone'],
                    'user_id' => $result['user_id']
                ];
            }
        }
        
        // Fallback: Look for users with vendor role
        if (empty($vendors)) {
            $vendor_users = get_users([
                'role' => 'vendor',
                'meta_key' => 'vendor_categories',
                'meta_value' => $category,
                'meta_compare' => 'LIKE'
            ]);
            
            foreach ($vendor_users as $user) {
                $vendors[] = [
                    'id' => $user->ID,
                    'name' => $user->display_name,
                    'email' => $user->user_email,
                    'company' => get_user_meta($user->ID, 'company_name', true),
                    'phone' => get_user_meta($user->ID, 'phone', true),
                    'user_id' => $user->ID
                ];
            }
        }
        
        // If still no vendors, get all active vendors (they can choose to respond or not)
        if (empty($vendors) && get_option('ai_chat_notify_all_vendors', false)) {
            $all_vendors = $this->get_all_active_vendors();
            $vendors = array_slice($all_vendors, 0, 10); // Limit to 10 to avoid spam
        }
        
        return apply_filters('ai_chat_vendors_for_category', $vendors, $category);
    }
    
    /**
     * Get all active vendors
     */
    private function get_all_active_vendors() {
        global $wpdb;
        
        $vendors = [];
        $vendors_table = $wpdb->prefix . 'ai_chat_vendors';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '{$vendors_table}'") == $vendors_table) {
            $results = $wpdb->get_results(
                "SELECT * FROM {$vendors_table} WHERE status = 'active' AND notifications_enabled = 1",
                ARRAY_A
            );
            
            foreach ($results as $result) {
                $vendors[] = [
                    'id' => $result['id'],
                    'name' => $result['name'],
                    'email' => $result['email'],
                    'company' => $result['company'],
                    'phone' => $result['phone'],
                    'user_id' => $result['user_id']
                ];
            }
        }
        
        return $vendors;
    }
    
    /**
     * Send notification email to vendor
     */
    private function send_vendor_notification($vendor, $category, $description, $request_id, $customer_info) {
        $subject = sprintf(
            '[%s] New Product Request - %s',
            get_bloginfo('name'),
            $category
        );
        
        $template_data = [
            'vendor_name' => $vendor['name'],
            'vendor_company' => $vendor['company'],
            'category' => $category,
            'description' => $description,
            'request_id' => $request_id,
            'customer_name' => $customer_info['name'],
            'customer_email' => $customer_info['email'],
            'customer_phone' => $customer_info['phone'],
            'site_name' => get_bloginfo('name'),
            'site_url' => home_url(),
            'admin_url' => admin_url('admin.php?page=ai-chat-requests&request=' . $request_id),
            'response_url' => $this->generate_vendor_response_url($vendor['id'], $request_id),
            'date_requested' => current_time('F j, Y \a\t g:i A')
        ];
        
        $message = $this->load_email_template('vendor-notification', $template_data);
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        ];
        
        if (!empty($customer_info['email'])) {
            $headers[] = 'Reply-To: ' . $customer_info['name'] . ' <' . $customer_info['email'] . '>';
        }
        
        return wp_mail($vendor['email'], $subject, $message, $headers);
    }
    
    /**
     * Send confirmation to customer
     */
    private function send_customer_confirmation($customer_info, $category, $description, $vendors_count) {
        if (empty($customer_info['email'])) {
            return false;
        }
        
        $subject = sprintf(
            '[%s] Your Product Request Has Been Sent',
            get_bloginfo('name')
        );
        
        $template_data = [
            'customer_name' => $customer_info['name'],
            'category' => $category,
            'description' => $description,
            'vendors_count' => $vendors_count,
            'site_name' => get_bloginfo('name'),
            'site_url' => home_url(),
            'my_requests_url' => $this->get_customer_requests_url(),
            'date_sent' => current_time('F j, Y \a\t g:i A')
        ];
        
        $message = $this->load_email_template('customer-confirmation', $template_data);
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        ];
        
        return wp_mail($customer_info['email'], $subject, $message, $headers);
    }
    
    /**
     * Load email template
     */
    private function load_email_template($template_name, $data = []) {
        $template_file = $this->email_template_path . $template_name . '.php';
        
        // If custom template doesn't exist, use built-in template
        if (!file_exists($template_file)) {
            return $this->get_builtin_template($template_name, $data);
        }
        
        // Extract data array to variables
        extract($data);
        
        ob_start();
        include $template_file;
        return ob_get_clean();
    }
    
    /**
     * Get built-in email template
     */
    private function get_builtin_template($template_name, $data) {
        switch ($template_name) {
            case 'vendor-notification':
                return $this->get_vendor_notification_template($data);
                
            case 'customer-confirmation':
                return $this->get_customer_confirmation_template($data);
                
            default:
                return 'Template not found.';
        }
    }
    
    /**
     * Get vendor notification template
     */
    private function get_vendor_notification_template($data) {
        $template = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
                .content { background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
                .button { display: inline-block; background: #007cba; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 3px; margin: 10px 5px; }
                .request-details { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; }
                .customer-info { background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>New Product Request</h2>
                    <p>Hello ' . esc_html($data['vendor_name']) . ',</p>
                    <p>A customer is looking for a product in the <strong>' . esc_html($data['category']) . '</strong> category and we thought you might be able to help!</p>
                </div>
                
                <div class="content">
                    <div class="request-details">
                        <h3>Product Request Details</h3>
                        <p><strong>Category:</strong> ' . esc_html($data['category']) . '</p>
                        <p><strong>Description:</strong></p>
                        <p>' . nl2br(esc_html($data['description'])) . '</p>
                        <p><strong>Request ID:</strong> #' . esc_html($data['request_id']) . '</p>
                        <p><strong>Date:</strong> ' . esc_html($data['date_requested']) . '</p>
                    </div>
                    
                    <div class="customer-info">
                        <h3>Customer Information</h3>
                        <p><strong>Name:</strong> ' . esc_html($data['customer_name']) . '</p>
                        <p><strong>Email:</strong> ' . esc_html($data['customer_email']) . '</p>';
                        
        if (!empty($data['customer_phone'])) {
            $template .= '<p><strong>Phone:</strong> ' . esc_html($data['customer_phone']) . '</p>';
        }
        
        $template .= '
                    </div>
                    
                    <div style="text-align: center; margin: 20px 0;">
                        <a href="' . esc_url($data['response_url']) . '" class="button">Respond to Request</a>
                        <a href="' . esc_url($data['admin_url']) . '" class="button">View in Dashboard</a>
                    </div>
                    
                    <p><small>You are receiving this email because you are registered as a vendor for the ' . esc_html($data['category']) . ' category on ' . esc_html($data['site_name']) . '. If you no longer wish to receive these notifications, please update your vendor settings.</small></p>
                </div>
            </div>
        </body>
        </html>';
        
        return $template;
    }
    
    /**
     * Get customer confirmation template
     */
    private function get_customer_confirmation_template($data) {
        $template = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
                .content { background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
                .success { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 15px 0; }
                .button { display: inline-block; background: #28a745; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 3px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Product Request Submitted</h2>
                    <p>Hello ' . esc_html($data['customer_name']) . ',</p>
                </div>
                
                <div class="content">
                    <div class="success">
                        <h3>✓ Your request has been sent successfully!</h3>
                        <p>We\'ve notified <strong>' . esc_html($data['vendors_count']) . ' vendors</strong> about your product request in the <strong>' . esc_html($data['category']) . '</strong> category.</p>
                    </div>
                    
                    <h3>Request Details</h3>
                    <p><strong>Category:</strong> ' . esc_html($data['category']) . '</p>
                    <p><strong>Description:</strong></p>
                    <p>' . nl2br(esc_html($data['description'])) . '</p>
                    <p><strong>Date Submitted:</strong> ' . esc_html($data['date_sent']) . '</p>
                    
                    <h3>What happens next?</h3>
                    <ul>
                        <li>Vendors will review your request</li>
                        <li>If they have matching products, they will contact you directly</li>
                        <li>You can track your request status in your account</li>
                    </ul>
                    
                    <div style="text-align: center; margin: 20px 0;">
                        <a href="' . esc_url($data['my_requests_url']) . '" class="button">Track My Requests</a>
                    </div>
                    
                    <p>Thank you for using ' . esc_html($data['site_name']) . '!</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $template;
    }
    
    /**
     * Get customer information
     */
    private function get_customer_info($customer_id) {
        if ($customer_id <= 0) {
            return [
                'name' => 'Guest User',
                'email' => '',
                'phone' => ''
            ];
        }
        
        $user = get_user_by('ID', $customer_id);
        
        if (!$user) {
            return [
                'name' => 'Unknown User',
                'email' => '',
                'phone' => ''
            ];
        }
        
        return [
            'name' => $user->display_name ?: $user->user_login,
            'email' => $user->user_email,
            'phone' => get_user_meta($customer_id, 'phone', true) ?: get_user_meta($customer_id, 'billing_phone', true)
        ];
    }
    
    /**
     * Generate vendor response URL
     */
    private function generate_vendor_response_url($vendor_id, $request_id) {
        $base_url = home_url('/vendor-response/');
        $token = wp_generate_password(32, false);
        
        // Store token temporarily for verification
        set_transient('vendor_response_' . $token, [
            'vendor_id' => $vendor_id,
            'request_id' => $request_id
        ], DAY_IN_SECONDS);
        
        return add_query_arg([
            'token' => $token,
            'vendor' => $vendor_id,
            'request' => $request_id
        ], $base_url);
    }
    
    /**
     * Get customer requests URL
     */
    private function get_customer_requests_url() {
        return home_url('/my-account/product-requests/');
    }
    
    /**
     * Log vendor notification
     */
    private function log_vendor_notification($vendor_id, $request_id, $status, $error_message = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_vendor_notifications';
        
        $wpdb->insert(
            $table_name,
            [
                'vendor_id' => $vendor_id,
                'request_id' => $request_id,
                'status' => $status,
                'error_message' => $error_message,
                'created_at' => current_time('mysql')
            ],
            [
                '%d', '%d', '%s', '%s', '%s'
            ]
        );
    }
}