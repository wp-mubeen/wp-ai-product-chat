<?php
/**
 * Plugin Activator Class
 * Handles plugin activation tasks
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_AI_Chat_Plugin_Activator {
    
    /**
     * Plugin activation
     */
    public static function activate() {
        // Check requirements first
        self::check_requirements();
        
        // Create database tables
        self::create_database_tables();
        
        // Set default options
        self::set_default_options();
        
        // Create custom roles and capabilities
        self::create_custom_roles();
        
        // Create upload directories
        self::create_upload_directories();
        
        // Schedule cleanup cron job
        self::schedule_cleanup_job();
        
        // Add rewrite rules
        self::add_rewrite_rules();
        
        // Create default pages
        self::create_default_pages();
        
        // Create sample data if in debug mode
        self::create_sample_data();
        
        // Set up automatic updates checker
        self::setup_update_checker();
        
        // Send activation notification to admin
        self::send_activation_notification();
        
        // Set activation flag
        update_option('ai_chat_plugin_activated', true);
        update_option('ai_chat_activation_time', current_time('mysql'));
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Log activation
        if (function_exists('error_log')) {
            error_log('AI Product Chat Plugin activated successfully');
        }
    }
    
    /**
     * Create database tables
     */
    private static function create_database_tables() {
        $db_manager = new WP_AI_Chat_Database_Manager();
        $db_manager->create_tables();
    }
    
    /**
     * Set default plugin options
     */
    private static function set_default_options() {
        $default_options = [
            // General Settings
            'ai_chat_enabled' => true,
            'ai_chat_widget_position' => 'bottom-right',
            'ai_chat_widget_color' => '#667eea',
            'ai_chat_show_on_mobile' => true,
            'ai_chat_show_for_guests' => true,
            'ai_chat_auto_open' => false,
            'ai_chat_greeting_message' => 'Hello! How can I help you today?',
            
            // AI Settings
            'ai_chat_api_key' => '',
            'ai_chat_api_endpoint' => 'https://api.openai.com/v1/chat/completions',
            'ai_chat_model' => 'gpt-3.5-turbo',
            'ai_chat_max_tokens' => 500,
            'ai_chat_temperature' => 0.7,
            'ai_chat_fallback_enabled' => true,
            
            // Product Search Settings
            'ai_chat_search_enabled' => true,
            'ai_chat_search_post_types' => ['product'],
            'ai_chat_search_limit' => 10,
            'ai_chat_image_analysis_enabled' => true,
            'ai_chat_max_image_size' => 5242880, // 5MB
            'ai_chat_allowed_image_types' => ['jpeg', 'jpg', 'png', 'gif', 'webp'],
            
            // Vendor Settings
            'ai_chat_vendor_notifications' => true,
            'ai_chat_notify_all_vendors' => false,
            'ai_chat_vendor_response_time' => 24, // hours
            'ai_chat_auto_close_requests' => 7, // days
            
            // Email Settings
            'ai_chat_email_notifications' => true,
            'ai_chat_admin_notifications' => true,
            'ai_chat_customer_confirmations' => true,
            'ai_chat_email_from_name' => get_bloginfo('name'),
            'ai_chat_email_from_address' => get_option('admin_email'),
            
            // Display Settings
            'ai_chat_show_on_pages' => ['all'],
            'ai_chat_hide_on_pages' => [],
            'ai_chat_show_for_roles' => ['all'],
            'ai_chat_widget_theme' => 'default',
            'ai_chat_custom_css' => '',
            
            // Data & Privacy
            'ai_chat_store_conversations' => true,
            'ai_chat_anonymize_guests' => false,
            'ai_chat_data_retention_days' => 90,
            'ai_chat_gdpr_compliance' => true,
            
            // Performance
            'ai_chat_cache_responses' => false,
            'ai_chat_cache_duration' => 3600, // 1 hour
            'ai_chat_rate_limiting' => true,
            'ai_chat_rate_limit' => 10, // messages per minute
            
            // Advanced
            'ai_chat_debug_mode' => false,
            'ai_chat_log_level' => 'error',
            'ai_chat_custom_prompts' => [],
            'ai_chat_webhooks' => [],
        ];
        
        foreach ($default_options as $option => $value) {
            if (get_option($option) === false) {
                add_option($option, $value);
            }
        }
    }
    
    /**
     * Create upload directories
     */
    private static function create_upload_directories() {
        $upload_dir = wp_upload_dir();
        $plugin_upload_dir = $upload_dir['basedir'] . '/ai-chat/';
        
        // Create main directory
        if (!file_exists($plugin_upload_dir)) {
            wp_mkdir_p($plugin_upload_dir);
        }
        
        // Create subdirectories
        $subdirs = ['images', 'exports', 'logs', 'cache'];
        
        foreach ($subdirs as $subdir) {
            $subdir_path = $plugin_upload_dir . $subdir . '/';
            if (!file_exists($subdir_path)) {
                wp_mkdir_p($subdir_path);
            }
            
            // Add .htaccess for security
            $htaccess_file = $subdir_path . '.htaccess';
            if (!file_exists($htaccess_file)) {
                $htaccess_content = "Order deny,allow\nDeny from all\n";
                if ($subdir === 'images') {
                    $htaccess_content = "Order allow,deny\nAllow from all\n";
                }
                file_put_contents($htaccess_file, $htaccess_content);
            }
        }
        
        // Add index.php files to prevent directory listing
        foreach ($subdirs as $subdir) {
            $index_file = $plugin_upload_dir . $subdir . '/index.php';
            if (!file_exists($index_file)) {
                file_put_contents($index_file, '<?php // Silence is golden');
            }
        }
    }
    
    /**
     * Schedule cleanup cron job
     */
    private static function schedule_cleanup_job() {
        if (!wp_next_scheduled('ai_chat_daily_cleanup')) {
            wp_schedule_event(time(), 'daily', 'ai_chat_daily_cleanup');
        }
        
        if (!wp_next_scheduled('ai_chat_weekly_cleanup')) {
            wp_schedule_event(time(), 'weekly', 'ai_chat_weekly_cleanup');
        }
    }
    
    /**
     * Add rewrite rules
     */
    private static function add_rewrite_rules() {
        // Add rewrite rule for vendor response page
        add_rewrite_rule(
            '^vendor-response/?$',
            'index.php?ai_chat_vendor_response=1',
            'top'
        );
        
        // Add rewrite rule for customer requests page
        add_rewrite_rule(
            '^my-requests/?$',
            'index.php?ai_chat_customer_requests=1',
            'top'
        );
        
        // Add query vars
        add_filter('query_vars', function($vars) {
            $vars[] = 'ai_chat_vendor_response';
            $vars[] = 'ai_chat_customer_requests';
            return $vars;
        });
    }
    
    /**
     * Create default pages
     */
    private static function create_default_pages() {
        // Create vendor response page
        $vendor_page = get_page_by_path('vendor-response');
        if (!$vendor_page) {
            $vendor_page_id = wp_insert_post([
                'post_title' => 'Vendor Response',
                'post_name' => 'vendor-response',
                'post_content' => '[ai_chat_vendor_response]',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_author' => 1
            ]);
            
            if (!is_wp_error($vendor_page_id)) {
                update_option('ai_chat_vendor_response_page_id', $vendor_page_id);
            }
        }
        
        // Create customer requests page
        $requests_page = get_page_by_path('my-requests');
        if (!$requests_page) {
            $requests_page_id = wp_insert_post([
                'post_title' => 'My Product Requests',
                'post_name' => 'my-requests',
                'post_content' => '[ai_chat_customer_requests]',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_author' => 1
            ]);
            
            if (!is_wp_error($requests_page_id)) {
                update_option('ai_chat_customer_requests_page_id', $requests_page_id);
            }
        }
    }
    
    /**
     * Create custom user roles and capabilities
     */
    private static function create_custom_roles() {
        // Create vendor role
        add_role('vendor', __('Vendor', 'wp-ai-product-chat'), [
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
            'publish_posts' => false,
            'upload_files' => true,
            'manage_ai_chat_products' => true,
            'respond_to_requests' => true
        ]);
        
        // Define capabilities array
        $capabilities = [
            'manage_ai_chat',
            'manage_ai_chat_vendors',
            'manage_ai_chat_requests',
            'manage_ai_chat_settings',
            'view_ai_chat_analytics'
        ];
        
        // Add capabilities to administrator role
        $admin_role = get_role('administrator');
        if ($admin_role) {
            foreach ($capabilities as $cap) {
                $admin_role->add_cap($cap);
            }
        }
        
        // Add capabilities to shop manager (if WooCommerce is active)
        $shop_manager = get_role('shop_manager');
        if ($shop_manager) {
            $shop_manager->add_cap('manage_ai_chat_requests');
            $shop_manager->add_cap('view_ai_chat_analytics');
        }
        
        // Also add basic capability to editor role
        $editor_role = get_role('editor');
        if ($editor_role) {
            $editor_role->add_cap('manage_ai_chat_requests');
        }
        
        // Force add capabilities to current user if they're admin
        $current_user = wp_get_current_user();
        if ($current_user && $current_user->ID > 0 && user_can($current_user, 'manage_options')) {
            foreach ($capabilities as $cap) {
                $current_user->add_cap($cap);
            }
        }
        
        // Set flag that capabilities were added
        update_option('ai_chat_capabilities_set', true);
    }
    
    /**
     * Create sample data (for demo purposes)
     */
    private static function create_sample_data() {
        if (defined('WP_DEBUG') && WP_DEBUG && get_option('ai_chat_create_sample_data', false)) {
            self::create_sample_vendors();
            self::create_sample_categories();
        }
    }
    
    /**
     * Create sample vendors
     */
    private static function create_sample_vendors() {
        $db_manager = new WP_AI_Chat_Database_Manager();
        
        $sample_vendors = [
            [
                'name' => 'Tech Solutions Inc',
                'email' => 'contact@techsolutions.example',
                'company' => 'Tech Solutions Inc',
                'phone' => '+1-555-0123',
                'description' => 'Leading provider of electronic devices and accessories',
                'categories' => ['electronics', 'computers', 'mobile-phones']
            ],
            [
                'name' => 'Fashion World',
                'email' => 'orders@fashionworld.example',
                'company' => 'Fashion World Ltd',
                'phone' => '+1-555-0456',
                'description' => 'Trendy clothing and accessories for all ages',
                'categories' => ['clothing-fashion', 'footwear', 'accessories']
            ],
            [
                'name' => 'Home & Garden Pro',
                'email' => 'sales@homegardens.example', 
                'company' => 'Home & Garden Pro',
                'phone' => '+1-555-0789',
                'description' => 'Everything for your home and garden needs',
                'categories' => ['home-garden', 'tools', 'furniture']
            ]
        ];
        
        foreach ($sample_vendors as $vendor_data) {
            $categories = $vendor_data['categories'];
            unset($vendor_data['categories']);
            
            $vendor_id = $db_manager->insert_vendor($vendor_data);
            
            if ($vendor_id) {
                foreach ($categories as $index => $category) {
                    $db_manager->add_vendor_category(
                        $vendor_id,
                        ucwords(str_replace('-', ' ', $category)),
                        $category,
                        $index === 0 // First category is primary
                    );
                }
            }
        }
    }
    
    /**
     * Create sample categories
     */
    private static function create_sample_categories() {
        $sample_categories = [
            'electronics' => 'Electronics',
            'clothing-fashion' => 'Clothing & Fashion',
            'home-garden' => 'Home & Garden',
            'sports-outdoors' => 'Sports & Outdoors',
            'health-beauty' => 'Health & Beauty',
            'books-media' => 'Books & Media',
            'toys-games' => 'Toys & Games',
            'automotive' => 'Automotive',
            'food-beverages' => 'Food & Beverages',
            'office-supplies' => 'Office Supplies'
        ];
        
        update_option('ai_chat_product_categories', $sample_categories);
    }
    
    /**
     * Check system requirements
     */
    private static function check_requirements() {
        $requirements = [
            'php_version' => '7.4',
            'wp_version' => '5.0',
            'mysql_version' => '5.6'
        ];
        
        $errors = [];
        
        // Check PHP version
        if (version_compare(PHP_VERSION, $requirements['php_version'], '<')) {
            $errors[] = sprintf(
                'PHP version %s or higher is required. You are running %s.',
                $requirements['php_version'],
                PHP_VERSION
            );
        }
        
        // Check WordPress version
        global $wp_version;
        if (version_compare($wp_version, $requirements['wp_version'], '<')) {
            $errors[] = sprintf(
                'WordPress version %s or higher is required. You are running %s.',
                $requirements['wp_version'],
                $wp_version
            );
        }
        
        // Check MySQL version
        global $wpdb;
        $mysql_version = $wpdb->get_var('SELECT VERSION()');
        if (version_compare($mysql_version, $requirements['mysql_version'], '<')) {
            $errors[] = sprintf(
                'MySQL version %s or higher is required. You are running %s.',
                $requirements['mysql_version'],
                $mysql_version
            );
        }
        
        // Check required PHP extensions
        $required_extensions = ['curl', 'json', 'mbstring'];
        foreach ($required_extensions as $extension) {
            if (!extension_loaded($extension)) {
                $errors[] = sprintf('PHP extension %s is required.', $extension);
            }
        }
        
        if (!empty($errors)) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                '<h1>Plugin Activation Error</h1>' . 
                '<p>The AI Product Chat plugin cannot be activated due to the following issues:</p>' .
                '<ul><li>' . implode('</li><li>', $errors) . '</li></ul>',
                'Plugin Activation Error',
                ['back_link' => true]
            );
        }
        
        return true;
    }
    
    /**
     * Setup update checker
     */
    private static function setup_update_checker() {
        // Schedule daily check for plugin updates
        if (!wp_next_scheduled('ai_chat_check_updates')) {
            wp_schedule_event(time(), 'daily', 'ai_chat_check_updates');
        }
    }
    
    /**
     * Send activation notification
     */
    private static function send_activation_notification() {
        if (get_option('ai_chat_send_activation_email', false)) {
            $admin_email = get_option('admin_email');
            $site_name = get_bloginfo('name');
            
            $subject = sprintf('[%s] AI Product Chat Plugin Activated', $site_name);
            
            $message = sprintf(
                "The AI Product Chat plugin has been successfully activated on %s.\n\n" .
                "Next steps:\n" .
                "1. Configure your AI API settings\n" .
                "2. Set up vendor accounts\n" .
                "3. Customize the chat widget appearance\n" .
                "4. Test the functionality\n\n" .
                "Visit the admin dashboard to get started: %s\n\n" .
                "Documentation: %s",
                $site_name,
                admin_url('admin.php?page=ai-chat-settings'),
                'https://your-docs-site.com'
            );
            
            wp_mail($admin_email, $subject, $message);
        }
    }
}