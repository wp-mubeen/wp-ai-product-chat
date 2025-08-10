<?php
/**
 * Core plugin class
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_AI_Chat_Plugin_Core {
    
    protected $loader;
    protected $plugin_name;
    protected $version;
    
    public function __construct() {
        $this->plugin_name = 'wp-ai-product-chat';
        $this->version = WP_AI_CHAT_VERSION;
        
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_api_hooks();
    }
    
    private function load_dependencies() {
        // Load the plugin loader
        require_once WP_AI_CHAT_PLUGIN_PATH . 'includes/class-plugin-loader.php';
        
        // Load database manager
        require_once WP_AI_CHAT_PLUGIN_PATH . 'database/class-database-manager.php';
        
        // Load AI handler
        require_once WP_AI_CHAT_PLUGIN_PATH . 'includes/class-ai-handler.php';
        
        // Load admin class
        require_once WP_AI_CHAT_PLUGIN_PATH . 'admin/class-admin.php';
        
        // Load public class
        require_once WP_AI_CHAT_PLUGIN_PATH . 'public/class-public.php';
        
        // Load API handlers
        require_once WP_AI_CHAT_PLUGIN_PATH . 'api/class-ajax-handler.php';
        require_once WP_AI_CHAT_PLUGIN_PATH . 'api/class-rest-api.php';
        
        // Load additional handlers
        require_once WP_AI_CHAT_PLUGIN_PATH . 'includes/class-product-matcher.php';
        require_once WP_AI_CHAT_PLUGIN_PATH . 'includes/class-request-manager.php';
        require_once WP_AI_CHAT_PLUGIN_PATH . 'includes/class-support-handler.php';
        require_once WP_AI_CHAT_PLUGIN_PATH . 'includes/class-vendor-notifier.php';
        
        $this->loader = new WP_AI_Chat_Plugin_Loader();
    }
    
    private function define_admin_hooks() {
        $plugin_admin = new WP_AI_Chat_Admin($this->get_plugin_name(), $this->get_version());
        
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
    }
    
    private function define_public_hooks() {
        $plugin_public = new WP_AI_Chat_Public($this->get_plugin_name(), $this->get_version());
        
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_action('wp_footer', $plugin_public, 'render_chat_widget');
    }
    
    private function define_api_hooks() {
        $ajax_handler = new WP_AI_Chat_Ajax_Handler();
        $rest_api = new WP_AI_Chat_Rest_API();
        
        // AJAX hooks
        $this->loader->add_action('wp_ajax_ai_chat_message', $ajax_handler, 'handle_chat_message');
        $this->loader->add_action('wp_ajax_nopriv_ai_chat_message', $ajax_handler, 'handle_chat_message');
        $this->loader->add_action('wp_ajax_ai_upload_image', $ajax_handler, 'handle_image_upload');
        $this->loader->add_action('wp_ajax_nopriv_ai_upload_image', $ajax_handler, 'handle_image_upload');
        
        // REST API
        $this->loader->add_action('rest_api_init', $rest_api, 'register_routes');
    }
    
    public function run() {
        $this->loader->run();
    }
    
    public function get_plugin_name() {
        return $this->plugin_name;
    }
    
    public function get_version() {
        return $this->version;
    }
    
    public function get_loader() {
        return $this->loader;
    }
}