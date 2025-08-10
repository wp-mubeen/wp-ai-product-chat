<?php
/**
 * Uninstall Script
 * Runs when the plugin is uninstalled (deleted) from WordPress
 */

// If uninstall not called from WordPress, then exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Check if user has permission to uninstall plugins
if (!current_user_can('delete_plugins')) {
    exit;
}

// Define plugin constants
define('WP_AI_CHAT_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Include necessary files
require_once WP_AI_CHAT_PLUGIN_PATH . 'database/class-database-manager.php';

/**
 * Complete plugin cleanup
 */
class WP_AI_Chat_Uninstaller {
    
    public static function uninstall() {
        // Check if user really wants to delete all data
        if (!get_option('ai_chat_delete_data_on_uninstall', false)) {
            // Just deactivate, don't delete data
            self::deactivate_only();
            return;
        }
        
        // Perform complete cleanup
        self::drop_database_tables();
        self::delete_plugin_options();
        self::delete_user_meta();
        self::remove_custom_roles();
        self::delete_uploaded_files();
        self::clear_scheduled_events();
        self::delete_custom_pages();
        self::clear_transients();
        self::cleanup_logs();
        
        // Final cleanup
        self::final_cleanup();
        
        // Log uninstall
        error_log('AI Product Chat Plugin: Complete uninstall completed');
    }
    
    /**
     * Drop all plugin database tables
     */
    private static function drop_database_tables() {
        $db_manager = new WP_AI_Chat_Database_Manager();
        $db_manager->drop_tables();
        
        // Drop additional tables that might have been created
        global $wpdb;
        
        $additional_tables = [
            $wpdb->prefix . 'ai_chat_request_activity',
            $wpdb->prefix . 'ai_chat_vendor_responses',
            $wpdb->prefix . 'ai_chat_analytics'
        ];
        
        foreach ($additional_tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }
    
    /**
     * Delete all plugin options
     */
    private static function delete_plugin_options() {
        global $wpdb;
        
        // Delete all options that start with 'ai_chat_'
        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'ai_chat_%'"
        );
        
        // Delete specific options that might not follow the naming convention
        $specific_options = [
            'ai_chat_plugin_activated',
            'ai_chat_activation_time',
            'ai_chat_deactivation_time',
            'ai_chat_db_version',
            'ai_chat_vendor_response_page_id',
            'ai_chat_customer_requests_page_id'
        ];
        
        foreach ($specific_options as $option) {
            delete_option($option);
        }
    }
    
    /**
     * Delete user meta data
     */
    private static function delete_user_meta() {
        global $wpdb;
        
        // Delete user meta that starts with 'ai_chat_'
        $wpdb->query(
            "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'ai_chat_%'"
        );
        
        // Delete vendor-specific meta
        $vendor_meta_keys = [
            'vendor_categories',
            'vendor_notification_settings',
            'ai_chat_agent_available',
            'ai_chat_vendor_profile'
        ];
        
        foreach ($vendor_meta_keys as $meta_key) {
            delete_metadata('user', 0, $meta_key, '', true);
        }
    }
    
    /**
     * Remove custom user roles and capabilities
     */
    private static function remove_custom_roles() {
        // Remove vendor role
        remove_role('vendor');
        
        // Remove custom capabilities from existing roles
        $roles_to_clean = ['administrator', 'shop_manager', 'editor'];
        $capabilities_to_remove = [
            'manage_ai_chat',
            'manage_ai_chat_vendors',
            'manage_ai_chat_requests',
            'manage_ai_chat_settings',
            'view_ai_chat_analytics',
            'respond_to_requests',
            'manage_ai_chat_products'
        ];
        
        foreach ($roles_to_clean as $role_name) {
            $role = get_role($role_name);
            if ($role) {
                foreach ($capabilities_to_remove as $cap) {
                    $role->remove_cap($cap);
                }
            }
        }
    }
    
    /**
     * Delete uploaded files and directories
     */
    private static function delete_uploaded_files() {
        $upload_dir = wp_upload_dir();
        $ai_chat_dir = $upload_dir['basedir'] . '/ai-chat/';
        
        if (is_dir($ai_chat_dir)) {
            self::delete_directory($ai_chat_dir);
        }
        
        // Delete any plugin-specific attachments
        global $wpdb;
        
        $attachments = $wpdb->get_results(
            "SELECT ID FROM {$wpdb->posts} 
             WHERE post_type = 'attachment' 
             AND post_content LIKE '%ai-chat%'"
        );
        
        foreach ($attachments as $attachment) {
            wp_delete_attachment($attachment->ID, true);
        }
    }
    
    /**
     * Clear all scheduled events
     */
    private static function clear_scheduled_events() {
        // Clear all plugin cron jobs
        $cron_hooks = [
            'ai_chat_daily_cleanup',
            'ai_chat_weekly_cleanup',
            'ai_chat_check_updates',
            'ai_chat_process_support_queue',
            'ai_chat_vendor_reminder',
            'ai_chat_analytics_update'
        ];
        
        foreach ($cron_hooks as $hook) {
            wp_clear_scheduled_hook($hook);
        }
    }
    
    /**
     * Delete custom pages created by plugin
     */
    private static function delete_custom_pages() {
        // Delete vendor response page
        $vendor_page_id = get_option('ai_chat_vendor_response_page_id');
        if ($vendor_page_id) {
            wp_delete_post($vendor_page_id, true);
        }
        
        // Delete customer requests page
        $requests_page_id = get_option('ai_chat_customer_requests_page_id');
        if ($requests_page_id) {
            wp_delete_post($requests_page_id, true);
        }
        
        // Delete any other pages with plugin shortcodes
        global $wpdb;
        
        $pages_with_shortcodes = $wpdb->get_results(
            "SELECT ID FROM {$wpdb->posts} 
             WHERE post_type = 'page' 
             AND (post_content LIKE '%[ai_chat_%' 
                  OR post_content LIKE '%ai_chat_vendor_response%'
                  OR post_content LIKE '%ai_chat_customer_requests%')"
        );
        
        foreach ($pages_with_shortcodes as $page) {
            // Check if page only contains plugin shortcodes
            $content = get_post_field('post_content', $page->ID);
            $content_without_shortcodes = preg_replace('/\[ai_chat_[^\]]*\]/', '', $content);
            
            if (trim($content_without_shortcodes) === '') {
                wp_delete_post($page->ID, true);
            }
        }
    }
    
    /**
     * Clear all plugin transients
     */
    private static function clear_transients() {
        global $wpdb;
        
        // Delete all plugin transients
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_ai_chat_%' 
             OR option_name LIKE '_transient_timeout_ai_chat_%'"
        );
        
        // Clear object cache
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
    }
    
    /**
     * Cleanup log files
     */
    private static function cleanup_logs() {
        // Remove log entries from WordPress error log
        $log_file = ini_get('error_log');
        
        if ($log_file && file_exists($log_file)) {
            $log_content = file_get_contents($log_file);
            $cleaned_content = preg_replace('/.*AI Chat.*\n/', '', $log_content);
            file_put_contents($log_file, $cleaned_content);
        }
        
        // Remove custom log files
        $upload_dir = wp_upload_dir();
        $log_files = glob($upload_dir['basedir'] . '/ai-chat/logs/*.log');
        
        foreach ($log_files as $log_file) {
            unlink($log_file);
        }
    }
    
    /**
     * Deactivate only (preserve data)
     */
    private static function deactivate_only() {
        // Just clear caches and temporary data
        self::clear_transients();
        
        // Set deactivation flag
        update_option('ai_chat_plugin_activated', false);
        update_option('ai_chat_uninstall_date', current_time('mysql'));
        
        error_log('AI Product Chat Plugin: Uninstalled but data preserved');
    }
    
    /**
     * Delete directory recursively
     */
    private static function delete_directory($dir) {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = scandir($dir);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $file_path = $dir . $file;
            
            if (is_dir($file_path)) {
                self::delete_directory($file_path . '/');
            } else {
                unlink($file_path);
            }
        }
        
        return rmdir($dir);
    }
    
    /**
     * Final cleanup
     */
    private static function final_cleanup() {
        // Remove any remaining plugin traces
        global $wpdb;
        
        // Check for any remaining plugin data in unexpected places
        $remaining_options = $wpdb->get_results(
            "SELECT option_name FROM {$wpdb->options} 
             WHERE option_value LIKE '%ai-chat%' 
             OR option_value LIKE '%AI Chat%'"
        );
        
        // Clean up remaining references (be careful not to break other plugins)
        foreach ($remaining_options as $option) {
            $option_value = get_option($option->option_name);
            
            if (is_string($option_value) && strpos($option_value, 'ai-chat') !== false) {
                // Remove only ai-chat references, preserve rest of the option
                $cleaned_value = str_replace(['ai-chat', 'AI Chat'], '', $option_value);
                update_option($option->option_name, $cleaned_value);
            }
        }
        
        // Final database optimization
        $wpdb->query("OPTIMIZE TABLE {$wpdb->options}");
        $wpdb->query("OPTIMIZE TABLE {$wpdb->usermeta}");
        
        // Clear any persistent object cache
        if (function_exists('wp_cache_flush_group')) {
            wp_cache_flush_group('ai_chat');
        }
    }
    
    /**
     * Send uninstall notification
     */
    private static function send_uninstall_notification() {
        if (get_option('ai_chat_send_uninstall_feedback', false)) {
            $admin_email = get_option('admin_email');
            $site_name = get_bloginfo('name');
            
            $subject = sprintf('[%s] AI Product Chat Plugin Uninstalled', $site_name);
            $message = sprintf(
                "The AI Product Chat plugin has been completely uninstalled from %s.\n\n" .
                "All plugin data has been removed including:\n" .
                "- Database tables\n" .
                "- Configuration options\n" .
                "- Uploaded files\n" .
                "- User roles and capabilities\n" .
                "- Custom pages\n\n" .
                "Thank you for using AI Product Chat!\n\n" .
                "Site: %s\n" .
                "Uninstall date: %s",
                $site_name,
                home_url(),
                current_time('mysql')
            );
            
            wp_mail($admin_email, $subject, $message);
        }
    }
    
    /**
     * Export data before deletion (if requested)
     */
    private static function maybe_export_data() {
        if (get_option('ai_chat_export_before_uninstall', false)) {
            try {
                self::export_all_data();
            } catch (Exception $e) {
                error_log('AI Chat: Failed to export data before uninstall: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * Export all plugin data
     */
    private static function export_all_data() {
        global $wpdb;
        
        $export_data = [
            'export_date' => current_time('mysql'),
            'plugin_version' => get_option('ai_chat_version', '1.0.0'),
            'site_url' => home_url(),
            'conversations' => [],
            'requests' => [],
            'vendors' => [],
            'settings' => []
        ];
        
        // Export conversations
        $conversations_table = $wpdb->prefix . 'ai_chat_conversations';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$conversations_table}'") == $conversations_table) {
            $export_data['conversations'] = $wpdb->get_results("SELECT * FROM $conversations_table", ARRAY_A);
        }
        
        // Export requests
        $requests_table = $wpdb->prefix . 'ai_chat_requests';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$requests_table}'") == $requests_table) {
            $export_data['requests'] = $wpdb->get_results("SELECT * FROM $requests_table", ARRAY_A);
        }
        
        // Export vendors
        $vendors_table = $wpdb->prefix . 'ai_chat_vendors';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$vendors_table}'") == $vendors_table) {
            $export_data['vendors'] = $wpdb->get_results("SELECT * FROM $vendors_table", ARRAY_A);
        }
        
        // Export settings
        $settings = $wpdb->get_results(
            "SELECT option_name, option_value FROM {$wpdb->options} 
             WHERE option_name LIKE 'ai_chat_%'",
            ARRAY_A
        );
        
        foreach ($settings as $setting) {
            $export_data['settings'][$setting['option_name']] = $setting['option_value'];
        }
        
        // Save export file
        $upload_dir = wp_upload_dir();
        $export_file = $upload_dir['basedir'] . '/ai-chat-export-' . date('Y-m-d-H-i-s') . '.json';
        
        file_put_contents($export_file, json_encode($export_data, JSON_PRETTY_PRINT));
        
        // Notify admin about export
        $admin_email = get_option('admin_email');
        $subject = 'AI Chat Data Export - Uninstall';
        $message = "Your AI Chat data has been exported before uninstall.\n\nExport file: $export_file\n\nPlease download this file before it's automatically deleted.";
        
        wp_mail($admin_email, $subject, $message);
    }
}

// Check user intention and proceed with uninstall
if (get_option('ai_chat_confirmed_uninstall', false)) {
    // User confirmed they want to delete all data
    WP_AI_Chat_Uninstaller::maybe_export_data();
    WP_AI_Chat_Uninstaller::send_uninstall_notification();
    WP_AI_Chat_Uninstaller::uninstall();
} else {
    // Default behavior - preserve data
    WP_AI_Chat_Uninstaller::deactivate_only();
}

// Clean up the confirmation flag
delete_option('ai_chat_confirmed_uninstall');