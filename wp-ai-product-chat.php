<?php
/**
 * Plugin Name: AI Product Chat
 * Description: Intelligent chat system with AI integration for product discovery and customer support
 * Version: 1.0.0
 * Author: Mubeen Iqbal
 * Text Domain: wp-ai-product-chat
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WP_AI_CHAT_VERSION', '1.0.0');
define('WP_AI_CHAT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_AI_CHAT_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WP_AI_CHAT_TEXT_DOMAIN', 'wp-ai-product-chat');

// Include core classes
require_once WP_AI_CHAT_PLUGIN_PATH . 'includes/class-plugin-loader.php';
require_once WP_AI_CHAT_PLUGIN_PATH . 'includes/class-plugin-core.php';
require_once WP_AI_CHAT_PLUGIN_PATH . 'includes/class-plugin-activator.php';
require_once WP_AI_CHAT_PLUGIN_PATH . 'includes/class-plugin-deactivator.php';
require_once WP_AI_CHAT_PLUGIN_PATH . 'database/class-database-manager.php';

// Include admin classes when in admin
if (is_admin()) {
    require_once WP_AI_CHAT_PLUGIN_PATH . 'admin/class-admin.php';
}

// Include public classes for frontend
if (!is_admin()) {
    require_once WP_AI_CHAT_PLUGIN_PATH . 'public/class-public.php';
}

// Include API handlers
require_once WP_AI_CHAT_PLUGIN_PATH . 'api/class-ajax-handler.php';
require_once WP_AI_CHAT_PLUGIN_PATH . 'api/class-rest-api.php';

/**
 * Plugin activation hook
 */
function activate_wp_ai_chat() {
    WP_AI_Chat_Plugin_Activator::activate();
}
register_activation_hook(__FILE__, 'activate_wp_ai_chat');

/**
 * Plugin deactivation hook
 */
function deactivate_wp_ai_chat() {
    WP_AI_Chat_Plugin_Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'deactivate_wp_ai_chat');

/**
 * Initialize the plugin
 */
function run_wp_ai_chat() {
    $plugin = new WP_AI_Chat_Plugin_Core();
    $plugin->run();
}

// Start the plugin
add_action('plugins_loaded', 'run_wp_ai_chat');