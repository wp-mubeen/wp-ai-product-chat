<?php
/**
 * Admin functionality of the plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_AI_Chat_Admin {
    
    private $plugin_name;
    private $version;
    
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }
    
    /**
     * Enqueue admin styles
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name . '-admin',
            WP_AI_CHAT_PLUGIN_URL . 'admin/css/admin-styles.css',
            [],
            $this->version,
            'all'
        );
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name . '-admin',
            WP_AI_CHAT_PLUGIN_URL . 'admin/js/admin-scripts.js',
            ['jquery'],
            $this->version,
            false
        );
        
        // Localize script
        wp_localize_script($this->plugin_name . '-admin', 'aiChatAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ai_chat_admin_nonce'),
            'strings' => [
                'confirm_delete' => __('Are you sure you want to delete this item?', 'wp-ai-product-chat'),
                'confirm_reset' => __('Are you sure you want to reset all settings?', 'wp-ai-product-chat'),
                'saving' => __('Saving...', 'wp-ai-product-chat'),
                'saved' => __('Saved!', 'wp-ai-product-chat'),
                'error' => __('Error occurred', 'wp-ai-product-chat')
            ]
        ]);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('AI Chat', 'wp-ai-product-chat'),
            __('AI Chat', 'wp-ai-product-chat'),
            'manage_ai_chat',
            'ai-chat-dashboard',
            [$this, 'display_dashboard'],
            'dashicons-format-chat',
            30
        );
        
        // Dashboard submenu
        add_submenu_page(
            'ai-chat-dashboard',
            __('Dashboard', 'wp-ai-product-chat'),
            __('Dashboard', 'wp-ai-product-chat'),
            'manage_ai_chat',
            'ai-chat-dashboard',
            [$this, 'display_dashboard']
        );
        
        // Requests submenu
        add_submenu_page(
            'ai-chat-dashboard',
            __('Product Requests', 'wp-ai-product-chat'),
            __('Requests', 'wp-ai-product-chat'),
            'manage_ai_chat_requests',
            'ai-chat-requests',
            [$this, 'display_requests']
        );
        
        // Vendors submenu
        add_submenu_page(
            'ai-chat-dashboard',
            __('Vendors', 'wp-ai-product-chat'),
            __('Vendors', 'wp-ai-product-chat'),
            'manage_ai_chat_vendors',
            'ai-chat-vendors',
            [$this, 'display_vendors']
        );
        
        // Support Tickets submenu
        add_submenu_page(
            'ai-chat-dashboard',
            __('Support Tickets', 'wp-ai-product-chat'),
            __('Support', 'wp-ai-product-chat'),
            'manage_ai_chat_requests',
            'ai-chat-support',
            [$this, 'display_support_tickets']
        );
        
        // Settings submenu
        add_submenu_page(
            'ai-chat-dashboard',
            __('Settings', 'wp-ai-product-chat'),
            __('Settings', 'wp-ai-product-chat'),
            'manage_ai_chat_settings',
            'ai-chat-settings',
            [$this, 'display_settings']
        );
        
        // Documentation submenu
        add_submenu_page(
            'ai-chat-dashboard',
            __('Documentation', 'wp-ai-product-chat'),
            __('Documentation', 'wp-ai-product-chat'),
            'manage_ai_chat',
            'ai-chat-documentation',
            [$this, 'display_documentation']
        );
    }
    
    /**
     * Display dashboard page
     */
    public function display_dashboard() {
        include_once WP_AI_CHAT_PLUGIN_PATH . 'admin/views/dashboard.php';
    }
    
    /**
     * Display requests page
     */
    public function display_requests() {
        include_once WP_AI_CHAT_PLUGIN_PATH . 'admin/views/request-list.php';
    }
    
    /**
     * Display vendors page
     */
    public function display_vendors() {
        include_once WP_AI_CHAT_PLUGIN_PATH . 'admin/views/vendor-list.php';
    }
    
    /**
     * Display support tickets page
     */
    public function display_support_tickets() {
        include_once WP_AI_CHAT_PLUGIN_PATH . 'admin/views/support-tickets.php';
    }
    
    /**
     * Display settings page
     */
    public function display_settings() {
        include_once WP_AI_CHAT_PLUGIN_PATH . 'admin/views/settings-page.php';
    }
    
    /**
     * Display documentation page
     */
    public function display_documentation() {
        include_once WP_AI_CHAT_PLUGIN_PATH . 'admin/views/documentation.php';
    }
    
    /**
     * Add admin notices
     */
    public function admin_notices() {
        // Check if API key is missing
        if (empty(get_option('ai_chat_api_key')) && current_user_can('manage_ai_chat_settings')) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <strong><?php _e('AI Chat:', 'wp-ai-product-chat'); ?></strong>
                    <?php _e('Please configure your OpenAI API key to enable full AI functionality.', 'wp-ai-product-chat'); ?>
                    <a href="<?php echo admin_url('admin.php?page=ai-chat-settings'); ?>">
                        <?php _e('Configure now', 'wp-ai-product-chat'); ?>
                    </a>
                </p>
            </div>
            <?php
        }
        
        // Check for plugin updates
        if (get_transient('ai_chat_update_notice') && current_user_can('update_plugins')) {
            ?>
            <div class="notice notice-info is-dismissible">
                <p>
                    <strong><?php _e('AI Chat:', 'wp-ai-product-chat'); ?></strong>
                    <?php _e('A new version is available.', 'wp-ai-product-chat'); ?>
                    <a href="<?php echo admin_url('plugins.php'); ?>">
                        <?php _e('Update now', 'wp-ai-product-chat'); ?>
                    </a>
                </p>
            </div>
            <?php
        }
    }
    
    /**
     * Add settings link to plugins page
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=ai-chat-settings') . '">' . 
                        __('Settings', 'wp-ai-product-chat') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    /**
     * Handle settings form submission
     */
    public function handle_settings_form() {
        if (!isset($_POST['ai_chat_settings_nonce']) || 
            !wp_verify_nonce($_POST['ai_chat_settings_nonce'], 'ai_chat_settings')) {
            return;
        }
        
        if (!current_user_can('manage_ai_chat_settings')) {
            return;
        }
        
        // Save settings
        $settings = [
            'ai_chat_enabled' => isset($_POST['ai_chat_enabled']),
            'ai_chat_api_key' => sanitize_text_field($_POST['ai_chat_api_key'] ?? ''),
            'ai_chat_model' => sanitize_text_field($_POST['ai_chat_model'] ?? 'gpt-3.5-turbo'),
            'ai_chat_widget_position' => sanitize_text_field($_POST['ai_chat_widget_position'] ?? 'bottom-right'),
            'ai_chat_widget_color' => sanitize_hex_color($_POST['ai_chat_widget_color'] ?? '#667eea'),
            'ai_chat_greeting_message' => sanitize_textarea_field($_POST['ai_chat_greeting_message'] ?? ''),
            'ai_chat_show_for_guests' => isset($_POST['ai_chat_show_for_guests']),
            'ai_chat_vendor_notifications' => isset($_POST['ai_chat_vendor_notifications']),
            'ai_chat_email_notifications' => isset($_POST['ai_chat_email_notifications'])
        ];
        
        foreach ($settings as $key => $value) {
            update_option($key, $value);
        }
        
        add_action('admin_notices', function() {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e('Settings saved successfully!', 'wp-ai-product-chat'); ?></p>
            </div>
            <?php
        });
    }
    
    /**
     * Get admin page tabs
     */
    public function get_admin_tabs($current_page) {
        $tabs = [
            'ai-chat-dashboard' => [
                'title' => __('Dashboard', 'wp-ai-product-chat'),
                'capability' => 'manage_ai_chat'
            ],
            'ai-chat-requests' => [
                'title' => __('Requests', 'wp-ai-product-chat'),
                'capability' => 'manage_ai_chat_requests'
            ],
            'ai-chat-vendors' => [
                'title' => __('Vendors', 'wp-ai-product-chat'),
                'capability' => 'manage_ai_chat_vendors'
            ],
            'ai-chat-support' => [
                'title' => __('Support', 'wp-ai-product-chat'),
                'capability' => 'manage_ai_chat_requests'
            ],
            'ai-chat-settings' => [
                'title' => __('Settings', 'wp-ai-product-chat'),
                'capability' => 'manage_ai_chat_settings'
            ],
            'ai-chat-documentation' => [
                'title' => __('Documentation', 'wp-ai-product-chat'),
                'capability' => 'manage_ai_chat'
            ]
        ];
        
        return apply_filters('ai_chat_admin_tabs', $tabs, $current_page);
    }
    
    /**
     * Render admin tabs
     */
    public function render_admin_tabs($current_page) {
        $tabs = $this->get_admin_tabs($current_page);
        
        if (count($tabs) <= 1) {
            return;
        }
        
        echo '<nav class="nav-tab-wrapper wp-clearfix">';
        
        foreach ($tabs as $page => $tab) {
            if (!current_user_can($tab['capability'])) {
                continue;
            }
            
            $active_class = ($current_page === $page) ? ' nav-tab-active' : '';
            $url = admin_url('admin.php?page=' . $page);
            
            printf(
                '<a href="%s" class="nav-tab%s">%s</a>',
                esc_url($url),
                $active_class,
                esc_html($tab['title'])
            );
        }
        
        echo '</nav>';
    }
}

// Initialize admin hooks
add_action('admin_menu', [new WP_AI_Chat_Admin('wp-ai-product-chat', WP_AI_CHAT_VERSION), 'add_admin_menu']);
add_action('admin_notices', [new WP_AI_Chat_Admin('wp-ai-product-chat', WP_AI_CHAT_VERSION), 'admin_notices']);
add_action('admin_init', [new WP_AI_Chat_Admin('wp-ai-product-chat', WP_AI_CHAT_VERSION), 'handle_settings_form']);

// Add settings link to plugins page
add_filter('plugin_action_links_' . plugin_basename(WP_AI_CHAT_PLUGIN_PATH . 'wp-ai-product-chat.php'), 
    [new WP_AI_Chat_Admin('wp-ai-product-chat', WP_AI_CHAT_VERSION), 'add_settings_link']);