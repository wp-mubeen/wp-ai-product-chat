<?php
/**
 * Support Handler Class
 * Handles customer support tickets and general support functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_AI_Chat_Support_Handler {
    
    private $db_manager;
    
    public function __construct() {
        $this->db_manager = new WP_AI_Chat_Database_Manager();
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // AJAX handlers
        add_action('wp_ajax_ai_create_support_ticket', [$this, 'create_support_ticket']);
        add_action('wp_ajax_nopriv_ai_create_support_ticket', [$this, 'create_support_ticket']);
        
        // Cron hooks
        add_action('ai_chat_process_support_queue', [$this, 'process_support_queue']);
    }
    
    /**
     * Handle order support requests
     */
    public function handle_order_support($message, $user_data = []) {
        // Extract order information from message
        $order_info = $this->extract_order_info($message);
        
        // Generate appropriate response
        if (!empty($order_info['order_number'])) {
            return $this->handle_order_inquiry($order_info['order_number'], $message, $user_data);
        } else {
            return $this->request_order_details($message);
        }
    }
    
    /**
     * Handle site problem requests
     */
    public function handle_site_problem($message, $user_data = []) {
        // Categorize the problem
        $problem_category = $this->categorize_site_problem($message);
        
        // Generate response based on problem type
        switch ($problem_category) {
            case 'login_issue':
                return $this->handle_login_problem($message, $user_data);
                
            case 'payment_issue':
                return $this->handle_payment_problem($message, $user_data);
                
            case 'page_error':
                return $this->handle_page_error($message, $user_data);
                
            case 'performance':
                return $this->handle_performance_issue($message, $user_data);
                
            default:
                return $this->handle_general_site_problem($message, $user_data);
        }
    }
    
    /**
     * Create support ticket via AJAX
     */
    public function create_support_ticket() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'ai_chat_nonce')) {
            wp_send_json_error('Invalid nonce');
            return;
        }
        
        $subject = sanitize_text_field($_POST['subject'] ?? '');
        $message = sanitize_textarea_field($_POST['message'] ?? '');
        $category = sanitize_text_field($_POST['category'] ?? 'general');
        $priority = sanitize_text_field($_POST['priority'] ?? 'normal');
        
        if (empty($subject) || empty($message)) {
            wp_send_json_error('Subject and message are required');
            return;
        }
        
        try {
            $ticket_id = $this->create_ticket([
                'subject' => $subject,
                'message' => $message,
                'category' => $category,
                'priority' => $priority,
                'user_id' => get_current_user_id()
            ]);
            
            wp_send_json_success([
                'ticket_id' => $ticket_id,
                'message' => 'Support ticket created successfully'
            ]);
            
        } catch (Exception $e) {
            wp_send_json_error('Failed to create support ticket: ' . $e->getMessage());
        }
    }
    
    /**
     * Create a new support ticket
     */
    public function create_ticket($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_support_tickets';
        
        // Generate unique ticket number
        $ticket_number = $this->generate_ticket_number();
        
        // Prepare ticket data
        $ticket_data = [
            'ticket_number' => $ticket_number,
            'user_id' => intval($data['user_id'] ?? 0),
            'subject' => sanitize_text_field($data['subject']),
            'message' => sanitize_textarea_field($data['message']),
            'category' => sanitize_text_field($data['category'] ?? 'general'),
            'priority' => sanitize_text_field($data['priority'] ?? 'normal'),
            'status' => 'open',
            'customer_name' => sanitize_text_field($data['customer_name'] ?? ''),
            'customer_email' => sanitize_email($data['customer_email'] ?? ''),
            'conversation_history' => json_encode([
                [
                    'type' => 'customer',
                    'message' => $data['message'],
                    'timestamp' => current_time('mysql')
                ]
            ]),
            'created_at' => current_time('mysql')
        ];
        
        // Auto-fill customer info if user is logged in
        if ($ticket_data['user_id'] > 0) {
            $user = get_user_by('ID', $ticket_data['user_id']);
            if ($user) {
                $ticket_data['customer_name'] = $ticket_data['customer_name'] ?: $user->display_name;
                $ticket_data['customer_email'] = $ticket_data['customer_email'] ?: $user->user_email;
            }
        }
        
        $result = $wpdb->insert($table_name, $ticket_data, [
            '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'
        ]);
        
        if (!$result) {
            throw new Exception('Failed to create support ticket');
        }
        
        $ticket_id = $wpdb->insert_id;
        
        // Send notification to admin
        $this->notify_admin_new_ticket($ticket_id, $ticket_data);
        
        // Send confirmation to customer
        $this->send_ticket_confirmation($ticket_id, $ticket_data);
        
        // Hook for after ticket creation
        do_action('ai_chat_support_ticket_created', $ticket_id, $ticket_data);
        
        return $ticket_id;
    }
    
    
    /**
     * Extract order information from message
     */
    private function extract_order_info($message) {
        $order_info = [];
        
        // Look for order number patterns
        $patterns = [
            '/order\s*#?\s*(\d+)/i',
            '/order\s*number\s*:?\s*(\d+)/i',
            '/#(\d+)/',
            '/(\d{4,})/' // Any 4+ digit number
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                $order_info['order_number'] = $matches[1];
                break;
            }
        }
        
        // Look for keywords to categorize the inquiry
        $keywords = [
            'tracking' => ['track', 'shipping', 'delivery', 'shipped', 'status'],
            'return' => ['return', 'refund', 'exchange', 'cancel'],
            'payment' => ['payment', 'charge', 'billing', 'card', 'pay'],
            'modification' => ['change', 'modify', 'update', 'edit', 'address']
        ];
        
        foreach ($keywords as $category => $terms) {
            foreach ($terms as $term) {
                if (stripos($message, $term) !== false) {
                    $order_info['category'] = $category;
                    break 2;
                }
            }
        }
        
        return $order_info;
    }

    
    /**
     * Handle order inquiry with order number
     */
    private function handle_order_inquiry($order_number, $message, $user_data) {
        // Check if WooCommerce is active
        if (class_exists('WooCommerce')) {
            return $this->handle_woocommerce_order($order_number, $message, $user_data);
        }
        
        // Check if EDD is active
        if (class_exists('Easy_Digital_Downloads')) {
            return $this->handle_edd_order($order_number, $message, $user_data);
        }
        
        // Generic order response
        return "I found your order reference #{$order_number}. Let me connect you with our support team who can provide specific details about your order status, shipping, or any other concerns you may have.";
    }
    
    /**
     * Handle WooCommerce order inquiry
     */
    private function handle_woocommerce_order($order_number, $message, $user_data) {
        $order = wc_get_order($order_number);
        
        if (!$order) {
            return "I couldn't find an order with number #{$order_number}. Please double-check the order number or contact our support team for assistance.";
        }
        
        $status = $order->get_status();
        $status_name = wc_get_order_status_name($status);
        
        $response = "I found your order #{$order_number}. Here's the current status:\n\n";
        $response .= "**Status:** {$status_name}\n";
        $response .= "**Order Date:** " . $order->get_date_created()->format('F j, Y') . "\n";
        $response .= "**Total:** " . $order->get_formatted_order_total() . "\n";
        
        // Add tracking info if available
        $tracking_number = $order->get_meta('_tracking_number');
        if ($tracking_number) {
            $response .= "**Tracking Number:** {$tracking_number}\n";
        }
        
        // Status-specific information
        switch ($status) {
            case 'processing':
                $response .= "\nYour order is being processed and will be shipped soon.";
                break;
            case 'shipped':
            case 'completed':
                $response .= "\nYour order has been shipped!";
                break;
            case 'cancelled':
                $response .= "\nThis order has been cancelled. If you have questions, please contact support.";
                break;
        }
        
        $response .= "\n\nIs there anything specific you'd like to know about this order?";
        
        return $response;
    }
    
    /**
     * Handle EDD order inquiry
     */
    private function handle_edd_order($order_number, $message, $user_data) {
        $payment = edd_get_payment($order_number);
        
        if (!$payment) {
            return "I couldn't find a purchase with ID #{$order_number}. Please verify the purchase ID or contact our support team.";
        }
        
        $status = $payment->post_status;
        $status_name = edd_get_payment_status($payment, true);
        
        $response = "I found your purchase #{$order_number}. Here are the details:\n\n";
        $response .= "**Status:** {$status_name}\n";
        $response .= "**Purchase Date:** " . date('F j, Y', strtotime($payment->post_date)) . "\n";
        $response .= "**Total:** " . edd_currency_filter(edd_format_amount(edd_get_payment_amount($order_number))) . "\n";
        
        if ($status === 'publish') {
            $response .= "\nYour purchase is complete! You should have received download links via email.";
        } elseif ($status === 'pending') {
            $response .= "\nYour purchase is pending. Please complete the payment process.";
        }
        
        $response .= "\n\nHow can I help you with this purchase?";
        
        return $response;
    }
    
    /**
     * Request order details when no order number found
     */
    private function request_order_details($message) {
        return "I'd be happy to help you with your order! To provide the most accurate information, could you please share:\n\n" .
               "• Your order number (usually starts with # followed by numbers)\n" .
               "• The email address used for the order\n" .
               "• Approximate order date\n\n" .
               "What specific information do you need about your order?";
    }
    
    /**
     * Categorize site problems
     */
    private function categorize_site_problem($message) {
        $categories = [
            'login_issue' => ['login', 'password', 'sign in', 'log in', 'account', 'username'],
            'payment_issue' => ['payment', 'checkout', 'card', 'billing', 'transaction', 'pay'],
            'page_error' => ['error', '404', '500', 'page', 'broken', 'not found', 'loading'],
            'performance' => ['slow', 'loading', 'timeout', 'speed', 'performance', 'lag']
        ];
        
        $message_lower = strtolower($message);
        
        foreach ($categories as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($message_lower, $keyword) !== false) {
                    return $category;
                }
            }
        }
        
        return 'general';
    }
    
    /**
     * Handle login problems
     */
    private function handle_login_problem($message, $user_data) {
        return "I understand you're having trouble logging in. Here are some quick solutions:\n\n" .
               "**Password Reset:**\n" .
               "1. Go to the login page\n" .
               "2. Click 'Lost Password'\n" .
               "3. Enter your email address\n" .
               "4. Check your email for reset instructions\n\n" .
               "**Common Issues:**\n" .
               "• Make sure Caps Lock is off\n" .
               "• Try clearing your browser cache\n" .
               "• Disable browser password managers temporarily\n\n" .
               "If you're still having trouble, I can create a support ticket for our technical team to help you directly.";
    }
    
    /**
     * Handle payment problems
     */
    private function handle_payment_problem($message, $user_data) {
        return "I'm here to help with your payment issue. Let me provide some immediate assistance:\n\n" .
               "**Common Payment Solutions:**\n" .
               "• Try a different payment method\n" .
               "• Clear your browser cache and cookies\n" .
               "• Ensure your billing address matches your card\n" .
               "• Check that your card hasn't expired\n\n" .
               "**If payment was declined:**\n" .
               "• Contact your bank to ensure the transaction isn't blocked\n" .
               "• Try using a different browser\n\n" .
               "Would you like me to connect you with our payment support team for further assistance?";
    }
    
    /**
     * Handle page errors
     */
    private function handle_page_error($message, $user_data) {
        return "I'm sorry you're experiencing page errors. Let's try to resolve this:\n\n" .
               "**Quick Fixes:**\n" .
               "1. **Refresh the page** - Press F5 or Ctrl+R\n" .
               "2. **Clear browser cache** - This often fixes loading issues\n" .
               "3. **Try incognito/private mode** - This helps identify browser conflicts\n" .
               "4. **Check your internet connection**\n\n" .
               "**Still not working?**\n" .
               "Please share:\n" .
               "• The exact page URL where you're seeing the error\n" .
               "• Any error message displayed\n" .
               "• Your browser type (Chrome, Firefox, etc.)\n\n" .
               "I'll make sure our technical team investigates this immediately.";
    }
    
    /**
     * Handle performance issues
     */
    private function handle_performance_issue($message, $user_data) {
        return "I understand the site is running slowly for you. Let's improve your experience:\n\n" .
               "**Immediate Solutions:**\n" .
               "• **Clear browser cache** - This often speeds things up significantly\n" .
               "• **Close unused browser tabs** - Frees up memory\n" .
               "• **Check your internet speed** - Run a speed test\n" .
               "• **Try a different browser** - Sometimes switching helps\n\n" .
               "**On Mobile?**\n" .
               "• Close other apps running in the background\n" .
               "• Switch from WiFi to mobile data (or vice versa)\n\n" .
               "I've also notified our technical team about potential performance issues. Is there a specific page that's particularly slow?";
    }
    
    /**
     * Handle general site problems
     */
    private function handle_general_site_problem($message, $user_data) {
        return "I'm sorry you're experiencing issues with our site. I want to help resolve this quickly!\n\n" .
               "To provide the best assistance, could you please share:\n\n" .
               "• **What specific problem** you're encountering\n" .
               "• **Which page or feature** isn't working\n" .
               "• **Any error messages** you're seeing\n" .
               "• **Your device type** (computer, phone, tablet)\n" .
               "• **Your browser** (Chrome, Safari, Firefox, etc.)\n\n" .
               "In the meantime, try:\n" .
               "1. Refreshing the page\n" .
               "2. Clearing your browser cache\n" .
               "3. Trying a different browser\n\n" .
               "I'm here to help get this sorted out for you!";
    }
    
    /**
     * Generate unique ticket number
     */
    private function generate_ticket_number() {
        $prefix = get_option('ai_chat_ticket_prefix', 'TICKET');
        $date = date('Ymd');
        
        // Get next sequential number for today
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_chat_support_tickets';
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE DATE(created_at) = %s",
            date('Y-m-d')
        ));
        
        $sequence = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        
        return $prefix . '-' . $date . '-' . $sequence;
    }
    
    /**
     * Notify admin of new ticket
     */
    private function notify_admin_new_ticket($ticket_id, $ticket_data) {
        if (!get_option('ai_chat_admin_notifications', true)) {
            return false;
        }
        
        $admin_email = get_option('admin_email');
        $site_name = get_bloginfo('name');
        
        $subject = sprintf('[%s] New Support Ticket: %s', $site_name, $ticket_data['ticket_number']);
        
        $message = sprintf(
            "A new support ticket has been created.\n\n" .
            "Ticket Details:\n" .
            "- Ticket Number: %s\n" .
            "- Customer: %s (%s)\n" .
            "- Category: %s\n" .
            "- Priority: %s\n" .
            "- Subject: %s\n\n" .
            "Message:\n%s\n\n" .
            "View ticket: %s\n\n" .
            "Please respond as soon as possible.",
            $ticket_data['ticket_number'],
            $ticket_data['customer_name'],
            $ticket_data['customer_email'],
            ucfirst($ticket_data['category']),
            ucfirst($ticket_data['priority']),
            $ticket_data['subject'],
            $ticket_data['message'],
            admin_url('admin.php?page=ai-chat-support&ticket=' . $ticket_id)
        );
        
        return wp_mail($admin_email, $subject, $message);
    }
    
    /**
     * Send ticket confirmation to customer
     */
    private function send_ticket_confirmation($ticket_id, $ticket_data) {
        if (!get_option('ai_chat_customer_confirmations', true) || empty($ticket_data['customer_email'])) {
            return false;
        }
        
        $subject = sprintf('[%s] Support Ticket Created: %s', get_bloginfo('name'), $ticket_data['ticket_number']);
        
        $message = sprintf(
            "Hello %s,\n\n" .
            "Thank you for contacting our support team. We've received your request and created ticket %s.\n\n" .
            "Ticket Details:\n" .
            "- Subject: %s\n" .
            "- Category: %s\n" .
            "- Priority: %s\n" .
            "- Created: %s\n\n" .
            "Your Message:\n%s\n\n" .
            "Our support team will review your ticket and respond as soon as possible. " .
            "You'll receive email updates when there are responses to your ticket.\n\n" .
            "If you need to add more information, you can reply to this email.\n\n" .
            "Thank you for your patience!\n\n" .
            "Best regards,\n%s Support Team",
            $ticket_data['customer_name'] ?: 'Customer',
            $ticket_data['ticket_number'],
            $ticket_data['subject'],
            ucfirst($ticket_data['category']),
            ucfirst($ticket_data['priority']),
            current_time('F j, Y \a\t g:i A'),
            $ticket_data['message'],
            get_bloginfo('name')
        );
        
        return wp_mail($ticket_data['customer_email'], $subject, $message);
    }
    
    /**
     * Process support queue (scheduled task)
     */
    public function process_support_queue() {
        // Get pending tickets that need attention
        $pending_tickets = $this->get_pending_tickets();
        
        foreach ($pending_tickets as $ticket) {
            // Check if ticket is overdue
            if ($this->is_ticket_overdue($ticket)) {
                $this->escalate_ticket($ticket['id']);
            }
            
            // Auto-assign tickets if enabled
            if (get_option('ai_chat_auto_assign_tickets', false)) {
                $this->auto_assign_ticket($ticket['id']);
            }
        }
        
        // Clean up old resolved tickets
        $this->cleanup_old_tickets();
    }
    
    /**
     * Get pending tickets
     */
    private function get_pending_tickets() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_support_tickets';
        
        return $wpdb->get_results(
            "SELECT * FROM $table_name 
             WHERE status IN ('open', 'pending') 
             ORDER BY priority DESC, created_at ASC",
            ARRAY_A
        );
    }
    
    /**
     * Check if ticket is overdue
     */
    private function is_ticket_overdue($ticket) {
        $response_times = [
            'high' => 4,      // 4 hours
            'normal' => 24,   // 24 hours
            'low' => 48       // 48 hours
        ];
        
        $max_hours = $response_times[$ticket['priority']] ?? 24;
        $created_time = strtotime($ticket['created_at']);
        $overdue_time = $created_time + ($max_hours * 3600);
        
        return time() > $overdue_time;
    }
    
    /**
     * Escalate overdue ticket
     */
    private function escalate_ticket($ticket_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_support_tickets';
        
        // Update priority
        $wpdb->update(
            $table_name,
            ['priority' => 'high'],
            ['id' => $ticket_id],
            ['%s'],
            ['%d']
        );
        
        // Notify admin about escalation
        $ticket = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $ticket_id
        ), ARRAY_A);
        
        if ($ticket) {
            $this->send_escalation_notification($ticket);
        }
    }
    
    /**
     * Send escalation notification
     */
    private function send_escalation_notification($ticket) {
        $admin_email = get_option('admin_email');
        $subject = sprintf('[URGENT] Overdue Ticket: %s', $ticket['ticket_number']);
        
        $message = sprintf(
            "This support ticket is overdue and has been escalated to HIGH priority.\n\n" .
            "Ticket: %s\n" .
            "Customer: %s\n" .
            "Subject: %s\n" .
            "Created: %s\n" .
            "Time Overdue: %s\n\n" .
            "Please respond immediately: %s",
            $ticket['ticket_number'],
            $ticket['customer_name'],
            $ticket['subject'],
            $ticket['created_at'],
            human_time_diff(strtotime($ticket['created_at'])),
            admin_url('admin.php?page=ai-chat-support&ticket=' . $ticket['id'])
        );
        
        wp_mail($admin_email, $subject, $message);
    }
    
    /**
     * Auto-assign ticket to available agent
     */
    private function auto_assign_ticket($ticket_id) {
        // Get available support agents (users with capability)
        $agents = get_users([
            'capability' => 'manage_ai_chat_requests',
            'meta_key' => 'ai_chat_agent_available',
            'meta_value' => '1'
        ]);
        
        if (empty($agents)) {
            return false;
        }
        
        // Simple round-robin assignment
        $agent_loads = [];
        foreach ($agents as $agent) {
            $load = $this->get_agent_ticket_load($agent->ID);
            $agent_loads[$agent->ID] = $load;
        }
        
        // Assign to agent with lowest load
        $assigned_agent_id = array_keys($agent_loads, min($agent_loads))[0];
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_chat_support_tickets';
        
        return $wpdb->update(
            $table_name,
            ['assigned_to' => $assigned_agent_id],
            ['id' => $ticket_id],
            ['%d'],
            ['%d']
        );
    }
    
    /**
     * Get agent's current ticket load
     */
    private function get_agent_ticket_load($agent_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_support_tickets';
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name 
             WHERE assigned_to = %d AND status IN ('open', 'pending')",
            $agent_id
        ));
    }
    
    /**
     * Cleanup old resolved tickets
     */
    private function cleanup_old_tickets() {
        $retention_days = get_option('ai_chat_ticket_retention_days', 90);
        
        if ($retention_days <= 0) {
            return; // Cleanup disabled
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_chat_support_tickets';
        
        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_name 
             WHERE status IN ('resolved', 'closed') 
             AND resolved_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $retention_days
        ));
        
        if ($deleted > 0) {
            error_log("AI Chat: Cleaned up {$deleted} old support tickets");
        }
    }
    
    /**
     * Get ticket statistics
     */
    public function get_ticket_statistics($days = 30) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_support_tickets';
        $date_limit = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as total_tickets,
                COUNT(CASE WHEN status = 'open' THEN 1 END) as open_tickets,
                COUNT(CASE WHEN status = 'resolved' THEN 1 END) as resolved_tickets,
                COUNT(CASE WHEN priority = 'high' THEN 1 END) as high_priority,
                AVG(TIMESTAMPDIFF(HOUR, created_at, COALESCE(resolved_at, NOW()))) as avg_resolution_time
             FROM $table_name 
             WHERE created_at >= %s",
            $date_limit
        ), ARRAY_A);
        
        // Get category breakdown
        $category_stats = $wpdb->get_results($wpdb->prepare(
            "SELECT category, COUNT(*) as count 
             FROM $table_name 
             WHERE created_at >= %s 
             GROUP BY category 
             ORDER BY count DESC",
            $date_limit
        ), ARRAY_A);
        
        $stats['category_breakdown'] = $category_stats;
        
        return $stats;
    }
    
    /**
     * Get suggested responses based on ticket content
     */
    public function get_suggested_responses($ticket_content) {
        $suggestions = [];
        
        // Common response templates
        $templates = [
            'order_status' => "Thank you for contacting us about your order. Let me check the status for you right away...",
            'refund_request' => "I understand you'd like to request a refund. I'll be happy to help you with this process...",
            'technical_issue' => "I'm sorry to hear you're experiencing technical difficulties. Let me help you resolve this...",
            'account_problem' => "I can help you with your account issue. Let me look into this for you...",
            'general_inquiry' => "Thank you for reaching out. I'll make sure to address all your questions..."
        ];
        
        // Simple keyword matching for suggestions
        $content_lower = strtolower($ticket_content);
        
        if (strpos($content_lower, 'order') !== false || strpos($content_lower, 'shipping') !== false) {
            $suggestions[] = $templates['order_status'];
        }
        
        if (strpos($content_lower, 'refund') !== false || strpos($content_lower, 'return') !== false) {
            $suggestions[] = $templates['refund_request'];
        }
        
        if (strpos($content_lower, 'error') !== false || strpos($content_lower, 'bug') !== false) {
            $suggestions[] = $templates['technical_issue'];
        }
        
        if (strpos($content_lower, 'account') !== false || strpos($content_lower, 'login') !== false) {
            $suggestions[] = $templates['account_problem'];
        }
        
        if (empty($suggestions)) {
            $suggestions[] = $templates['general_inquiry'];
        }
        
        return $suggestions;
    }
    
    /**
     * Close ticket
     */
    public function close_ticket($ticket_id, $resolution_notes = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_support_tickets';
        
        $update_data = [
            'status' => 'resolved',
            'resolved_at' => current_time('mysql')
        ];
        
        if (!empty($resolution_notes)) {
            // Add to conversation history
            $ticket = $wpdb->get_row($wpdb->prepare(
                "SELECT conversation_history FROM $table_name WHERE id = %d",
                $ticket_id
            ));
            
            if ($ticket) {
                $history = json_decode($ticket->conversation_history, true) ?: [];
                $history[] = [
                    'type' => 'resolution',
                    'message' => $resolution_notes,
                    'timestamp' => current_time('mysql'),
                    'user_id' => get_current_user_id()
                ];
                
                $update_data['conversation_history'] = json_encode($history);
            }
        }
        
        $result = $wpdb->update(
            $table_name,
            $update_data,
            ['id' => $ticket_id],
            ['%s', '%s', '%s'],
            ['%d']
        );
        
        if ($result) {
            // Send closure notification to customer
            $this->send_ticket_closure_notification($ticket_id);
            
            // Hook for ticket closure
            do_action('ai_chat_support_ticket_closed', $ticket_id, $resolution_notes);
        }
        
        return $result;
    }
    
    /**
     * Send ticket closure notification
     */
    private function send_ticket_closure_notification($ticket_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_support_tickets';
        $ticket = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $ticket_id
        ), ARRAY_A);
        
        if (!$ticket || empty($ticket['customer_email'])) {
            return false;
        }
        
        $subject = sprintf('[%s] Ticket Resolved: %s', get_bloginfo('name'), $ticket['ticket_number']);
        
        $message = sprintf(
            "Hello %s,\n\n" .
            "Good news! Your support ticket %s has been resolved.\n\n" .
            "Original Issue: %s\n\n" .
            "If you're satisfied with the resolution, no further action is needed. " .
            "If you need additional assistance or have follow-up questions, " .
            "please reply to this email or create a new support ticket.\n\n" .
            "We hope we were able to help resolve your issue satisfactorily.\n\n" .
            "Thank you for choosing %s!\n\n" .
            "Best regards,\n%s Support Team",
            $ticket['customer_name'] ?: 'Customer',
            $ticket['ticket_number'],
            $ticket['subject'],
            get_bloginfo('name'),
            get_bloginfo('name')
        );
        
        return wp_mail($ticket['customer_email'], $subject, $message);
    }
}