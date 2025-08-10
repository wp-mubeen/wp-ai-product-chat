<?php
/**
 * Plugin Deactivator Class
 * Handles plugin deactivation tasks
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_AI_Chat_Plugin_Deactivator {
    
    /**
     * Plugin deactivation
     */
    public static function deactivate() {
        // Wrap everything in try-catch to prevent fatal errors
        try {
            // Clear scheduled cron jobs
            self::clear_scheduled_hooks();
            
            // Clear rewrite rules
            self::clear_rewrite_rules();
            
            // Clear transients
            self::clear_transients();
            
            // Clear cache if any
            self::clear_cache();
            
            // Set deactivation flag
            update_option('ai_chat_plugin_activated', false);
            update_option('ai_chat_deactivation_time', current_time('mysql'));
            
            // Send deactivation notification if enabled (only if safe)
            if (function_exists('wp_mail') && get_option('ai_chat_send_deactivation_email', false)) {
                self::send_deactivation_notification();
            }
            
            // Log deactivation (only if error_log exists)
            if (function_exists('error_log')) {
                error_log('AI Product Chat Plugin deactivated');
            }
            
        } catch (Exception $e) {
            // Silently fail - don't break deactivation
            if (function_exists('error_log')) {
                error_log('AI Chat Deactivation Error: ' . $e->getMessage());
            }
        } catch (Error $e) {
            // Handle PHP 7+ errors
            if (function_exists('error_log')) {
                error_log('AI Chat Deactivation Fatal Error: ' . $e->getMessage());
            }
        }
        
        // Note: We don't delete data during deactivation
        // Data is only removed during uninstall
    }
    
    /**
     * Clear all scheduled cron jobs
     */
    private static function clear_scheduled_hooks() {
        // Clear cleanup cron jobs
        $timestamp = wp_next_scheduled('ai_chat_daily_cleanup');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'ai_chat_daily_cleanup');
        }
        
        $timestamp = wp_next_scheduled('ai_chat_weekly_cleanup');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'ai_chat_weekly_cleanup');
        }
        
        // Clear update check cron
        $timestamp = wp_next_scheduled('ai_chat_check_updates');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'ai_chat_check_updates');
        }
        
        // Clear any other scheduled events
        wp_clear_scheduled_hook('ai_chat_daily_cleanup');
        wp_clear_scheduled_hook('ai_chat_weekly_cleanup');
        wp_clear_scheduled_hook('ai_chat_check_updates');
    }
    
    /**
     * Clear rewrite rules
     */
    private static function clear_rewrite_rules() {
        try {
            // Simple flush - don't try to manipulate rewrite rules directly
            if (function_exists('flush_rewrite_rules')) {
                flush_rewrite_rules();
            }
        } catch (Exception $e) {
            // Silently fail
        }
    }
    
    /**
     * Clear all plugin transients
     */
    private static function clear_transients() {
        try {
            global $wpdb;
            
            // Only proceed if wpdb is available
            if (!isset($wpdb) || !is_object($wpdb)) {
                return;
            }
            
            // Delete all plugin-specific transients safely
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$wpdb->options} 
                     WHERE option_name LIKE %s 
                     OR option_name LIKE %s",
                    '_transient_ai_chat_%',
                    '_transient_timeout_ai_chat_%'
                )
            );
            
            // Clear specific known transients
            $transients_to_clear = [
                'ai_chat_system_status',
                'ai_chat_vendor_stats',
                'ai_chat_conversation_stats',
                'ai_chat_api_health'
            ];
            
            foreach ($transients_to_clear as $transient) {
                if (function_exists('delete_transient')) {
                    delete_transient($transient);
                }
            }
        } catch (Exception $e) {
            // Silently fail
        }
    }
    
    /**
     * Clear plugin cache
     */
    private static function clear_cache() {
        try {
            // Clear WordPress object cache if available
            if (function_exists('wp_cache_flush')) {
                wp_cache_flush();
            }
            
            // Clear plugin-specific cache directory safely
            $upload_dir = wp_upload_dir();
            if (is_array($upload_dir) && !empty($upload_dir['basedir'])) {
                $cache_dir = $upload_dir['basedir'] . '/ai-chat/cache/';
                
                if (is_dir($cache_dir) && is_writable($cache_dir)) {
                    self::clear_directory($cache_dir);
                }
            }
            
            // Clear any cached API responses
            if (function_exists('wp_cache_delete_group')) {
                wp_cache_delete_group('ai_chat_api_responses');
                wp_cache_delete_group('ai_chat_product_searches');
            }
        } catch (Exception $e) {
            // Silently fail
        }
    }
    
    /**
     * Send deactivation notification
     */
    private static function send_deactivation_notification() {
        if (!get_option('ai_chat_send_deactivation_email', false)) {
            return;
        }
        
        $admin_email = get_option('admin_email');
        $site_name = get_bloginfo('name');
        
        $subject = sprintf('[%s] AI Product Chat Plugin Deactivated', $site_name);
        
        $message = sprintf(
            "The AI Product Chat plugin has been deactivated on %s.\n\n" .
            "Deactivation time: %s\n" .
            "Site URL: %s\n\n" .
            "Plugin data has been preserved and will be restored if you reactivate the plugin.\n" .
            "To completely remove all data, please use the uninstall option.",
            $site_name,
            current_time('mysql'),
            home_url()
        );
        
        wp_mail($admin_email, $subject, $message);
    }
    
    /**
     * Clear directory contents
     */
    private static function clear_directory($dir) {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = scandir($dir);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $file_path = $dir . $file;
            
            if (is_dir($file_path)) {
                self::clear_directory($file_path . '/');
                rmdir($file_path);
            } else {
                unlink($file_path);
            }
        }
    }
    
    /**
     * Remove custom capabilities
     */
    private static function remove_custom_capabilities() {
        // Remove capabilities from administrator
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_role->remove_cap('manage_ai_chat');
            $admin_role->remove_cap('manage_ai_chat_vendors');
            $admin_role->remove_cap('manage_ai_chat_requests');
            $admin_role->remove_cap('manage_ai_chat_settings');
            $admin_role->remove_cap('view_ai_chat_analytics');
        }
        
        // Remove capabilities from shop manager
        $shop_manager = get_role('shop_manager');
        if ($shop_manager) {
            $shop_manager->remove_cap('manage_ai_chat_requests');
            $shop_manager->remove_cap('view_ai_chat_analytics');
        }
        
        // Note: We don't remove the vendor role during deactivation
        // as it might be used by other plugins or have posts assigned
    }
    
    /**
     * Cleanup temporary files
     */
    private static function cleanup_temporary_files() {
        $upload_dir = wp_upload_dir();
        $temp_dirs = [
            $upload_dir['basedir'] . '/ai-chat/temp/',
            $upload_dir['basedir'] . '/ai-chat/logs/',
        ];
        
        foreach ($temp_dirs as $temp_dir) {
            if (is_dir($temp_dir)) {
                self::clear_directory($temp_dir);
            }
        }
    }
    
    /**
     * Reset plugin options to defaults (if requested)
     */
    private static function reset_options_if_requested() {
        if (get_option('ai_chat_reset_on_deactivate', false)) {
            // Get all plugin options
            $plugin_options = $GLOBALS['wpdb']->get_results(
                "SELECT option_name FROM {$GLOBALS['wpdb']->options} 
                 WHERE option_name LIKE 'ai_chat_%'"
            );
            
            // Delete each option
            foreach ($plugin_options as $option) {
                delete_option($option->option_name);
            }
            
            // Recreate default options
            WP_AI_Chat_Plugin_Activator::set_default_options();
        }
    }
    
    /**
     * Log deactivation reason (if provided)
     */
    private static function log_deactivation_reason() {
        $reason = get_option('ai_chat_deactivation_reason', '');
        
        if (!empty($reason)) {
            error_log('AI Chat Deactivation Reason: ' . $reason);
            
            // Send feedback if enabled
            if (get_option('ai_chat_send_deactivation_feedback', false)) {
                self::send_deactivation_feedback($reason);
            }
            
            // Clear the reason
            delete_option('ai_chat_deactivation_reason');
        }
    }
    
    /**
     * Send deactivation feedback
     */
    private static function send_deactivation_feedback($reason) {
        $feedback_data = [
            'site_url' => home_url(),
            'wp_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'plugin_version' => WP_AI_CHAT_VERSION,
            'reason' => $reason,
            'timestamp' => current_time('mysql')
        ];
        
        // Send to feedback endpoint (replace with your actual endpoint)
        wp_remote_post('https://your-feedback-endpoint.com/deactivation', [
            'body' => json_encode($feedback_data),
            'headers' => ['Content-Type' => 'application/json'],
            'timeout' => 10
        ]);
    }
    
    /**
     * Complete deactivation process
     */
    public static function complete_deactivation() {
        // Remove custom capabilities if requested
        if (get_option('ai_chat_remove_caps_on_deactivate', false)) {
            self::remove_custom_capabilities();
        }
        
        // Cleanup temporary files
        self::cleanup_temporary_files();
        
        // Reset options if requested
        self::reset_options_if_requested();
        
        // Log deactivation reason
        self::log_deactivation_reason();
        
        // Final cleanup
        wp_cache_flush();
    }
}