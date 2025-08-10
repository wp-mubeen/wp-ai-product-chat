<?php
/**
 * Public-facing functionality of the plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_AI_Chat_Public {
    
    private $plugin_name;
    private $version;
    
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Shortcodes
        add_shortcode('ai_chat_widget', [$this, 'chat_widget_shortcode']);
        add_shortcode('ai_chat_customer_requests', [$this, 'customer_requests_shortcode']);
        add_shortcode('ai_chat_vendor_response', [$this, 'vendor_response_shortcode']);
        
        // Template redirects
        add_action('template_redirect', [$this, 'handle_custom_pages']);
        
        // AJAX handlers for public
        add_action('wp_ajax_nopriv_ai_chat_message', [$this, 'handle_guest_chat']);
    }
    
    /**
     * Enqueue public styles
     */
    public function enqueue_styles() {
        if (!$this->should_load_chat()) {
            return;
        }
        
        wp_enqueue_style(
            $this->plugin_name,
            WP_AI_CHAT_PLUGIN_URL . 'public/css/chat-styles.css',
            [],
            $this->version,
            'all'
        );
        
        // Add custom CSS if any
        $custom_css = get_option('ai_chat_custom_css', '');
        if (!empty($custom_css)) {
            wp_add_inline_style($this->plugin_name, $custom_css);
        }
    }
    
    /**
     * Enqueue public scripts
     */
    public function enqueue_scripts() {
        if (!$this->should_load_chat()) {
            return;
        }
        
        wp_enqueue_script(
            $this->plugin_name,
            WP_AI_CHAT_PLUGIN_URL . 'public/js/chat-scripts.js',
            ['jquery'],
            $this->version,
            true
        );
        
        // Localize script with data
        wp_localize_script($this->plugin_name, 'aiChatData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ai_chat_nonce'),
            'userId' => get_current_user_id(),
            'isLoggedIn' => is_user_logged_in(),
            'settings' => $this->get_frontend_settings(),
            'strings' => $this->get_localized_strings()
        ]);
    }
    
    /**
     * Render chat widget
     */
    public function render_chat_widget() {
        if (!$this->should_load_chat()) {
            return;
        }
        
        include WP_AI_CHAT_PLUGIN_PATH . 'public/views/chat-widget.php';
    }
    
    /**
     * Chat widget shortcode
     */
    public function chat_widget_shortcode($atts) {
        $atts = shortcode_atts([
            'position' => get_option('ai_chat_widget_position', 'bottom-right'),
            'color' => get_option('ai_chat_widget_color', '#667eea'),
            'auto_open' => get_option('ai_chat_auto_open', false)
        ], $atts);
        
        ob_start();
        
        // Override settings temporarily
        add_filter('ai_chat_widget_position', function() use ($atts) {
            return $atts['position'];
        });
        
        add_filter('ai_chat_widget_color', function() use ($atts) {
            return $atts['color'];
        });
        
        add_filter('ai_chat_auto_open', function() use ($atts) {
            return $atts['auto_open'];
        });
        
        $this->render_chat_widget();
        
        return ob_get_clean();
    }
    
    /**
     * Customer requests shortcode
     */
    public function customer_requests_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p>Please <a href="' . wp_login_url(get_permalink()) . '">log in</a> to view your requests.</p>';
        }
        
        $atts = shortcode_atts([
            'per_page' => 10,
            'show_pagination' => true
        ], $atts);
        
        ob_start();
        include WP_AI_CHAT_PLUGIN_PATH . 'public/views/my-request.php';
        return ob_get_clean();
    }
    
    /**
     * Vendor response shortcode
     */
    public function vendor_response_shortcode($atts) {
        $token = $_GET['token'] ?? '';
        $vendor_id = $_GET['vendor'] ?? '';
        $request_id = $_GET['request'] ?? '';
        
        if (empty($token) || empty($vendor_id) || empty($request_id)) {
            return '<p>Invalid response link.</p>';
        }
        
        // Verify token
        $token_data = get_transient('vendor_response_' . $token);
        if (!$token_data || 
            $token_data['vendor_id'] != $vendor_id || 
            $token_data['request_id'] != $request_id) {
            return '<p>This response link has expired or is invalid.</p>';
        }
        
        ob_start();
        include WP_AI_CHAT_PLUGIN_PATH . 'public/views/vendor-response.php';
        return ob_get_clean();
    }
    
    /**
     * Handle custom pages
     */
    public function handle_custom_pages() {
        global $wp_query;
        
        // Handle vendor response page
        if (get_query_var('ai_chat_vendor_response')) {
            $this->load_vendor_response_page();
            return;
        }
        
        // Handle customer requests page
        if (get_query_var('ai_chat_customer_requests')) {
            $this->load_customer_requests_page();
            return;
        }
    }
    
    /**
     * Load vendor response page
     */
    private function load_vendor_response_page() {
        // Check if we have a dedicated page
        $page_id = get_option('ai_chat_vendor_response_page_id');
        if ($page_id) {
            $page = get_post($page_id);
            if ($page && $page->post_status === 'publish') {
                global $post;
                $post = $page;
                setup_postdata($post);
            }
        }
        
        // Load template
        $template = locate_template(['page-vendor-response.php', 'page.php']);
        if (!$template) {
            $template = WP_AI_CHAT_PLUGIN_PATH . 'public/views/vendor-response-page.php';
        }
        
        include $template;
        exit;
    }
    
    /**
     * Load customer requests page
     */
    private function load_customer_requests_page() {
        if (!is_user_logged_in()) {
            wp_redirect(wp_login_url(home_url('/my-requests/')));
            exit;
        }
        
        // Check if we have a dedicated page
        $page_id = get_option('ai_chat_customer_requests_page_id');
        if ($page_id) {
            $page = get_post($page_id);
            if ($page && $page->post_status === 'publish') {
                global $post;
                $post = $page;
                setup_postdata($post);
            }
        }
        
        // Load template
        $template = locate_template(['page-my-requests.php', 'page.php']);
        if (!$template) {
            $template = WP_AI_CHAT_PLUGIN_PATH . 'public/views/customer-requests-page.php';
        }
        
        include $template;
        exit;
    }
    
    /**
     * Handle guest chat (when not logged in)
     */
    public function handle_guest_chat() {
        if (!get_option('ai_chat_show_for_guests', true)) {
            wp_send_json_error('Chat not available for guests');
            return;
        }
        
        // Use same handler as logged-in users
        $ajax_handler = new WP_AI_Chat_Ajax_Handler();
        $ajax_handler->handle_chat_message();
    }
    
    /**
     * Check if chat should be loaded on current page
     */
    private function should_load_chat() {
        // Check if plugin is enabled
        if (!get_option('ai_chat_enabled', true)) {
            return false;
        }
        
        // Check if user is allowed
        if (!is_user_logged_in() && !get_option('ai_chat_show_for_guests', true)) {
            return false;
        }
        
        // Check mobile setting
        if (wp_is_mobile() && !get_option('ai_chat_show_on_mobile', true)) {
            return false;
        }
        
        // Check page restrictions
        $show_on_pages = get_option('ai_chat_show_on_pages', ['all']);
        $hide_on_pages = get_option('ai_chat_hide_on_pages', []);
        
        $current_page_id = get_queried_object_id();
        $current_post_type = get_post_type();
        
        // Check if current page is in hide list
        if (in_array($current_page_id, $hide_on_pages) || 
            in_array($current_post_type, $hide_on_pages)) {
            return false;
        }
        
        // Check if current page is in show list (if not 'all')
        if (!in_array('all', $show_on_pages)) {
            if (!in_array($current_page_id, $show_on_pages) && 
                !in_array($current_post_type, $show_on_pages)) {
                return false;
            }
        }
        
        // Check user role restrictions
        $show_for_roles = get_option('ai_chat_show_for_roles', ['all']);
        if (!in_array('all', $show_for_roles)) {
            $user = wp_get_current_user();
            $user_roles = $user->roles ?? ['guest'];
            
            if (empty(array_intersect($user_roles, $show_for_roles))) {
                return false;
            }
        }
        
        return apply_filters('ai_chat_should_load', true);
    }
    
    /**
     * Get frontend settings
     */
    private function get_frontend_settings() {
        return [
            'position' => get_option('ai_chat_widget_position', 'bottom-right'),
            'color' => get_option('ai_chat_widget_color', '#667eea'),
            'autoOpen' => get_option('ai_chat_auto_open', false),
            'greetingMessage' => get_option('ai_chat_greeting_message', 'Hello! How can I help you today?'),
            'showOnMobile' => get_option('ai_chat_show_on_mobile', true),
            'maxImageSize' => get_option('ai_chat_max_image_size', 5242880),
            'allowedImageTypes' => get_option('ai_chat_allowed_image_types', ['jpeg', 'jpg', 'png', 'gif', 'webp']),
            'rateLimiting' => get_option('ai_chat_rate_limiting', true),
            'rateLimit' => get_option('ai_chat_rate_limit', 10),
            'theme' => get_option('ai_chat_widget_theme', 'default')
        ];
    }
    
    /**
     * Get localized strings
     */
    private function get_localized_strings() {
        return [
            'uploadError' => __('Error uploading image. Please try again.', 'wp-ai-product-chat'),
            'networkError' => __('Network error. Please check your connection.', 'wp-ai-product-chat'),
            'processingImage' => __('Analyzing image...', 'wp-ai-product-chat'),
            'searchingProducts' => __('Searching for products...', 'wp-ai-product-chat'),
            'contactingVendors' => __('Contacting vendors...', 'wp-ai-product-chat'),
            'rateLimitExceeded' => __('Too many messages. Please wait a moment.', 'wp-ai-product-chat'),
            'sessionExpired' => __('Your session has expired. Please refresh the page.', 'wp-ai-product-chat'),
            'attachImage' => __('Attach Image', 'wp-ai-product-chat'),
            'sendMessage' => __('Send Message', 'wp-ai-product-chat'),
            'typeMessage' => __('Type your message...', 'wp-ai-product-chat'),
            'aiThinking' => __('AI is thinking...', 'wp-ai-product-chat'),
            'connectionLost' => __('Connection lost. Trying to reconnect...', 'wp-ai-product-chat'),
            'reconnected' => __('Connected successfully!', 'wp-ai-product-chat')
        ];
    }
    
    /**
     * Add body classes for styling
     */
    public function add_body_classes($classes) {
        if ($this->should_load_chat()) {
            $classes[] = 'has-ai-chat';
            $classes[] = 'ai-chat-position-' . str_replace('_', '-', get_option('ai_chat_widget_position', 'bottom-right'));
            
            if (get_option('ai_chat_auto_open', false)) {
                $classes[] = 'ai-chat-auto-open';
            }
        }
        
        return $classes;
    }
    
    /**
     * Handle image upload for guests
     */
    public function handle_guest_image_upload() {
        if (!get_option('ai_chat_show_for_guests', true)) {
            wp_send_json_error('Image upload not available for guests');
            return;
        }
        
        // Use same handler as logged-in users
        $ajax_handler = new WP_AI_Chat_Ajax_Handler();
        $ajax_handler->handle_image_upload();
    }
    
    /**
     * Add custom CSS variables
     */
    public function add_css_variables() {
        if (!$this->should_load_chat()) {
            return;
        }
        
        $primary_color = get_option('ai_chat_widget_color', '#667eea');
        $secondary_color = $this->adjust_brightness($primary_color, -20);
        
        $css_vars = "
        :root {
            --ai-chat-primary-color: {$primary_color};
            --ai-chat-secondary-color: {$secondary_color};
            --ai-chat-border-radius: " . get_option('ai_chat_border_radius', '15px') . ";
            --ai-chat-font-family: " . get_option('ai_chat_font_family', '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif') . ";
        }";
        
        wp_add_inline_style($this->plugin_name, $css_vars);
    }
    
    /**
     * Adjust color brightness
     */
    private function adjust_brightness($hex, $steps) {
        // Remove # if present
        $hex = str_replace('#', '', $hex);
        
        // Convert to decimal
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        // Adjust brightness
        $r = max(0, min(255, $r + $steps));
        $g = max(0, min(255, $g + $steps));
        $b = max(0, min(255, $b + $steps));
        
        // Convert back to hex
        return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) . 
                    str_pad(dechex($g), 2, '0', STR_PAD_LEFT) . 
                    str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
    }
}

// Initialize hooks for body classes
add_filter('body_class', [new WP_AI_Chat_Public('wp-ai-product-chat', WP_AI_CHAT_VERSION), 'add_body_classes']);

// Add AJAX handler for guest image uploads
add_action('wp_ajax_nopriv_ai_upload_image', [new WP_AI_Chat_Public('wp-ai-product-chat', WP_AI_CHAT_VERSION), 'handle_guest_image_upload']);