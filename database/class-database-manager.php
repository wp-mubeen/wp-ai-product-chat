<?php
/**
 * Database Manager Class
 * Handles database operations and table management
 */

if (!defined('ABSPATH')) {
    exit;
}
 
class WP_AI_Chat_Database_Manager {
    
    private $version;
    private $tables;
    
    public function __construct() {
        $this->version = '1.0.0';
        $this->tables = [
            'conversations',
            'requests', 
            'vendors',
            'vendor_categories',
            'vendor_notifications',
            'support_tickets'
        ];
    }
    
    /**
     * Create all plugin tables
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Create conversations table
        $this->create_conversations_table($charset_collate);
        
        // Create requests table
        $this->create_requests_table($charset_collate);
        
        // Create vendors table
        $this->create_vendors_table($charset_collate);
        
        // Create vendor categories table
        $this->create_vendor_categories_table($charset_collate);
        
        // Create vendor notifications table
        $this->create_vendor_notifications_table($charset_collate);
        
        // Create support tickets table
        $this->create_support_tickets_table($charset_collate);
        
        // Update database version
        update_option('ai_chat_db_version', $this->version);
    }
    
    /**
     * Create conversations table
     */
    private function create_conversations_table($charset_collate) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_conversations';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) DEFAULT 0,
            session_id varchar(255) NOT NULL,
            user_message text NOT NULL,
            ai_response text NOT NULL,
            context varchar(100) DEFAULT 'general',
            ip_address varchar(45) DEFAULT '',
            user_agent text DEFAULT '',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY session_id (session_id),
            KEY context (context),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
    
    /**
     * Create requests table
     */
    private function create_requests_table($charset_collate) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_requests';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) DEFAULT 0,
            category varchar(255) NOT NULL,
            description text NOT NULL,
            status varchar(50) DEFAULT 'pending',
            vendors_contacted int DEFAULT 0,
            responses_received int DEFAULT 0,
            image_url varchar(500) DEFAULT '',
            customer_name varchar(255) DEFAULT '',
            customer_email varchar(255) DEFAULT '',
            customer_phone varchar(50) DEFAULT '',
            priority varchar(20) DEFAULT 'normal',
            notes text DEFAULT '',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY category (category),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
    
    /**
     * Create vendors table
     */
    private function create_vendors_table($charset_collate) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_vendors';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) DEFAULT 0,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            company varchar(255) DEFAULT '',
            phone varchar(50) DEFAULT '',
            address text DEFAULT '',
            website varchar(500) DEFAULT '',
            description text DEFAULT '',
            status varchar(20) DEFAULT 'active',
            notifications_enabled tinyint(1) DEFAULT 1,
            response_rate decimal(5,2) DEFAULT 0.00,
            total_requests int DEFAULT 0,
            successful_matches int DEFAULT 0,
            last_active datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY email (email),
            KEY user_id (user_id),
            KEY status (status),
            KEY notifications_enabled (notifications_enabled)
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
    
    /**
     * Create vendor categories table
     */
    private function create_vendor_categories_table($charset_collate) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_vendor_categories';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            vendor_id bigint(20) NOT NULL,
            category_name varchar(255) NOT NULL,
            category_slug varchar(255) NOT NULL,
            is_primary tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY vendor_id (vendor_id),
            KEY category_slug (category_slug),
            UNIQUE KEY vendor_category (vendor_id, category_slug)
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
    
    /**
     * Create vendor notifications table
     */
    private function create_vendor_notifications_table($charset_collate) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_vendor_notifications';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            vendor_id bigint(20) NOT NULL,
            request_id bigint(20) NOT NULL,
            status varchar(50) DEFAULT 'sent',
            opened_at datetime DEFAULT NULL,
            responded_at datetime DEFAULT NULL,
            response_type varchar(50) DEFAULT '',
            response_message text DEFAULT '',
            error_message text DEFAULT '',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY vendor_id (vendor_id),
            KEY request_id (request_id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
    
    /**
     * Create support tickets table
     */
    private function create_support_tickets_table($charset_collate) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_support_tickets';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) DEFAULT 0,
            ticket_number varchar(50) NOT NULL,
            subject varchar(500) NOT NULL,
            message text NOT NULL,
            category varchar(100) DEFAULT 'general',
            priority varchar(20) DEFAULT 'normal',
            status varchar(50) DEFAULT 'open',
            assigned_to bigint(20) DEFAULT 0,
            customer_name varchar(255) DEFAULT '',
            customer_email varchar(255) DEFAULT '',
            conversation_history longtext DEFAULT '',
            last_response_at datetime DEFAULT NULL,
            resolved_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY ticket_number (ticket_number),
            KEY user_id (user_id),
            KEY status (status),
            KEY priority (priority),
            KEY assigned_to (assigned_to),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
    
    /**
     * Drop all plugin tables
     */
    public function drop_tables() {
        global $wpdb;
        
        foreach ($this->tables as $table) {
            $table_name = $wpdb->prefix . 'ai_chat_' . $table;
            $wpdb->query("DROP TABLE IF EXISTS $table_name");
        }
        
        delete_option('ai_chat_db_version');
    }
    
    /**
     * Check if database needs update
     */
    public function needs_update() {
        $installed_version = get_option('ai_chat_db_version', '0');
        return version_compare($installed_version, $this->version, '<');
    }
    
    /**
     * Update database if needed
     */
    public function maybe_update() {
        if ($this->needs_update()) {
            $this->create_tables();
        }
    }
    
    /**
     * Insert conversation record
     */
    public function insert_conversation($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_conversations';
        
        $defaults = [
            'user_id' => 0,
            'session_id' => '',
            'user_message' => '',
            'ai_response' => '',
            'context' => 'general',
            'ip_address' => '',
            'user_agent' => '',
            'created_at' => current_time('mysql')
        ];
        
        $data = wp_parse_args($data, $defaults);
        
        return $wpdb->insert($table_name, $data, [
            '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s'
        ]);
    }
    
    /**
     * Insert request record
     */
    public function insert_request($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_requests';
        
        $defaults = [
            'user_id' => 0,
            'category' => '',
            'description' => '',
            'status' => 'pending',
            'vendors_contacted' => 0,
            'responses_received' => 0,
            'image_url' => '',
            'customer_name' => '',
            'customer_email' => '',
            'customer_phone' => '',
            'priority' => 'normal',
            'notes' => '',
            'created_at' => current_time('mysql')
        ];
        
        $data = wp_parse_args($data, $defaults);
        
        $result = $wpdb->insert($table_name, $data, [
            '%d', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s'
        ]);
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update request record
     */
    public function update_request($request_id, $data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_requests';
        
        $data['updated_at'] = current_time('mysql');
        
        return $wpdb->update(
            $table_name,
            $data,
            ['id' => $request_id],
            null,
            ['%d']
        );
    }
    
    /**
     * Get request by ID
     */
    public function get_request($request_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_requests';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $request_id
        ), ARRAY_A);
    }
    
    /**
     * Get requests by user ID
     */
    public function get_user_requests($user_id, $limit = 20, $offset = 0) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_requests';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name 
             WHERE user_id = %d 
             ORDER BY created_at DESC 
             LIMIT %d OFFSET %d",
            $user_id,
            $limit,
            $offset
        ), ARRAY_A);
    }
    
    /**
     * Get all requests with pagination
     */
    public function get_requests($args = []) {
        global $wpdb;
        
        $defaults = [
            'status' => '',
            'category' => '',
            'limit' => 20,
            'offset' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC'
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $table_name = $wpdb->prefix . 'ai_chat_requests';
        $where_clauses = ['1=1'];
        $where_values = [];
        
        if (!empty($args['status'])) {
            $where_clauses[] = 'status = %s';
            $where_values[] = $args['status'];
        }
        
        if (!empty($args['category'])) {
            $where_clauses[] = 'category = %s';
            $where_values[] = $args['category'];
        }
        
        $where_sql = implode(' AND ', $where_clauses);
        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);
        
        $sql = "SELECT * FROM $table_name WHERE $where_sql ORDER BY $orderby LIMIT %d OFFSET %d";
        
        $where_values[] = $args['limit'];
        $where_values[] = $args['offset'];
        
        return $wpdb->get_results($wpdb->prepare($sql, $where_values), ARRAY_A);
    }
    
    /**
     * Insert vendor record
     */
    public function insert_vendor($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_vendors';
        
        $defaults = [
            'user_id' => 0,
            'name' => '',
            'email' => '',
            'company' => '',
            'phone' => '',
            'address' => '',
            'website' => '',
            'description' => '',
            'status' => 'active',
            'notifications_enabled' => 1,
            'response_rate' => 0.00,
            'total_requests' => 0,
            'successful_matches' => 0,
            'created_at' => current_time('mysql')
        ];
        
        $data = wp_parse_args($data, $defaults);
        
        $result = $wpdb->insert($table_name, $data, [
            '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%f', '%d', '%d', '%s'
        ]);
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Add vendor category
     */
    public function add_vendor_category($vendor_id, $category_name, $category_slug, $is_primary = false) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_vendor_categories';
        
        return $wpdb->insert($table_name, [
            'vendor_id' => $vendor_id,
            'category_name' => $category_name,
            'category_slug' => $category_slug,
            'is_primary' => $is_primary ? 1 : 0,
            'created_at' => current_time('mysql')
        ], [
            '%d', '%s', '%s', '%d', '%s'
        ]);
    }
    
    /**
     * Get vendor categories
     */
    public function get_vendor_categories($vendor_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_vendor_categories';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE vendor_id = %d ORDER BY is_primary DESC, category_name ASC",
            $vendor_id
        ), ARRAY_A);
    }
    
    /**
     * Get conversation statistics
     */
    public function get_conversation_stats($days = 30) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_conversations';
        $date_limit = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as total_conversations,
                COUNT(DISTINCT user_id) as unique_users,
                COUNT(DISTINCT session_id) as unique_sessions,
                AVG(CHAR_LENGTH(user_message)) as avg_message_length
             FROM $table_name 
             WHERE created_at >= %s",
            $date_limit
        ), ARRAY_A);
        
        // Get context breakdown
        $context_stats = $wpdb->get_results($wpdb->prepare(
            "SELECT context, COUNT(*) as count 
             FROM $table_name 
             WHERE created_at >= %s 
             GROUP BY context 
             ORDER BY count DESC",
            $date_limit
        ), ARRAY_A);
        
        $stats['context_breakdown'] = $context_stats;
        
        return $stats;
    }
    
    /**
     * Get request statistics
     */
    public function get_request_stats($days = 30) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_requests';
        $date_limit = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as total_requests,
                SUM(vendors_contacted) as total_vendors_contacted,
                SUM(responses_received) as total_responses,
                AVG(vendors_contacted) as avg_vendors_per_request,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_requests,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_requests
             FROM $table_name 
             WHERE created_at >= %s",
            $date_limit
        ), ARRAY_A);
        
        // Calculate success rate
        if ($stats['total_requests'] > 0) {
            $stats['success_rate'] = ($stats['completed_requests'] / $stats['total_requests']) * 100;
        } else {
            $stats['success_rate'] = 0;
        }
        
        // Get category breakdown
        $category_stats = $wpdb->get_results($wpdb->prepare(
            "SELECT category, COUNT(*) as count 
             FROM $table_name 
             WHERE created_at >= %s 
             GROUP BY category 
             ORDER BY count DESC 
             LIMIT 10",
            $date_limit
        ), ARRAY_A);
        
        $stats['category_breakdown'] = $category_stats;
        
        return $stats;
    }
    
    /**
     * Clean up old data
     */
    public function cleanup_old_data($days = 90) {
        global $wpdb;
        
        $date_limit = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        // Clean up old conversations
        $conversations_table = $wpdb->prefix . 'ai_chat_conversations';
        $deleted_conversations = $wpdb->query($wpdb->prepare(
            "DELETE FROM $conversations_table WHERE created_at < %s",
            $date_limit
        ));
        
        // Clean up old completed requests (keep pending ones)
        $requests_table = $wpdb->prefix . 'ai_chat_requests';
        $deleted_requests = $wpdb->query($wpdb->prepare(
            "DELETE FROM $requests_table WHERE created_at < %s AND status IN ('completed', 'cancelled')",
            $date_limit
        ));
        
        return [
            'conversations_deleted' => $deleted_conversations,
            'requests_deleted' => $deleted_requests
        ];
    }
}