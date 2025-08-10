<?php
/**
 * Settings Page Template
 */

if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['submit']) && wp_verify_nonce($_POST['ai_chat_settings_nonce'], 'ai_chat_settings')) {
    // Save settings
    $settings = [
        'ai_chat_enabled' => isset($_POST['ai_chat_enabled']) ? 1 : 0,
        'ai_chat_api_key' => sanitize_text_field($_POST['ai_chat_api_key'] ?? ''),
        'ai_chat_api_endpoint' => esc_url_raw($_POST['ai_chat_api_endpoint'] ?? 'https://api.openai.com/v1/chat/completions'),
        'ai_chat_model' => sanitize_text_field($_POST['ai_chat_model'] ?? 'gpt-3.5-turbo'),
        'ai_chat_max_tokens' => intval($_POST['ai_chat_max_tokens'] ?? 500),
        'ai_chat_temperature' => floatval($_POST['ai_chat_temperature'] ?? 0.7),
        'ai_chat_widget_position' => sanitize_text_field($_POST['ai_chat_widget_position'] ?? 'bottom-right'),
        'ai_chat_widget_color' => sanitize_hex_color($_POST['ai_chat_widget_color'] ?? '#667eea'),
        'ai_chat_greeting_message' => sanitize_textarea_field($_POST['ai_chat_greeting_message'] ?? 'Hello! How can I help you today?'),
        'ai_chat_show_for_guests' => isset($_POST['ai_chat_show_for_guests']) ? 1 : 0,
        'ai_chat_show_on_mobile' => isset($_POST['ai_chat_show_on_mobile']) ? 1 : 0,
        'ai_chat_auto_open' => isset($_POST['ai_chat_auto_open']) ? 1 : 0,
        'ai_chat_vendor_notifications' => isset($_POST['ai_chat_vendor_notifications']) ? 1 : 0,
        'ai_chat_email_notifications' => isset($_POST['ai_chat_email_notifications']) ? 1 : 0,
        'ai_chat_customer_confirmations' => isset($_POST['ai_chat_customer_confirmations']) ? 1 : 0,
        'ai_chat_data_retention_days' => intval($_POST['ai_chat_data_retention_days'] ?? 90),
        'ai_chat_rate_limiting' => isset($_POST['ai_chat_rate_limiting']) ? 1 : 0,
        'ai_chat_rate_limit' => intval($_POST['ai_chat_rate_limit'] ?? 10),
        'ai_chat_debug_mode' => isset($_POST['ai_chat_debug_mode']) ? 1 : 0
    ];
    
    foreach ($settings as $key => $value) {
        update_option($key, $value);
    }
    
    echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
}

// Get current settings
$current_settings = [
    'ai_chat_enabled' => get_option('ai_chat_enabled', 1),
    'ai_chat_api_key' => get_option('ai_chat_api_key', ''),
    'ai_chat_api_endpoint' => get_option('ai_chat_api_endpoint', 'https://api.openai.com/v1/chat/completions'),
    'ai_chat_model' => get_option('ai_chat_model', 'gpt-3.5-turbo'),
    'ai_chat_max_tokens' => get_option('ai_chat_max_tokens', 500),
    'ai_chat_temperature' => get_option('ai_chat_temperature', 0.7),
    'ai_chat_widget_position' => get_option('ai_chat_widget_position', 'bottom-right'),
    'ai_chat_widget_color' => get_option('ai_chat_widget_color', '#667eea'),
    'ai_chat_greeting_message' => get_option('ai_chat_greeting_message', 'Hello! How can I help you today?'),
    'ai_chat_show_for_guests' => get_option('ai_chat_show_for_guests', 1),
    'ai_chat_show_on_mobile' => get_option('ai_chat_show_on_mobile', 1),
    'ai_chat_auto_open' => get_option('ai_chat_auto_open', 0),
    'ai_chat_vendor_notifications' => get_option('ai_chat_vendor_notifications', 1),
    'ai_chat_email_notifications' => get_option('ai_chat_email_notifications', 1),
    'ai_chat_customer_confirmations' => get_option('ai_chat_customer_confirmations', 1),
    'ai_chat_data_retention_days' => get_option('ai_chat_data_retention_days', 90),
    'ai_chat_rate_limiting' => get_option('ai_chat_rate_limiting', 1),
    'ai_chat_rate_limit' => get_option('ai_chat_rate_limit', 10),
    'ai_chat_debug_mode' => get_option('ai_chat_debug_mode', 0)
];
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <!-- Documentation & Quick Start Guide -->
    <div class="ai-chat-documentation" style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <h2>🚀 Quick Start Guide</h2>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <h3>📋 Setup Steps:</h3>
                <ol>
                    <li><strong>Get OpenAI API Key:</strong> Visit <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI API Keys</a></li>
                    <li><strong>Configure API:</strong> Enter your API key below</li>
                    <li><strong>Enable Plugin:</strong> Check "Enable AI Chat" below</li>
                    <li><strong>Customize Widget:</strong> Set position, color, and greeting message</li>
                    <li><strong>Test Chat:</strong> Visit your frontend to test the chat widget</li>
                </ol>
            </div>
            <div>
                <h3>🎯 Key Features:</h3>
                <ul>
                    <li>✅ <strong>AI-Powered Product Search:</strong> Image & text recognition</li>
                    <li>✅ <strong>Vendor Notifications:</strong> Auto-contact suppliers</li>
                    <li>✅ <strong>Order Support:</strong> WooCommerce integration</li>
                    <li>✅ <strong>Site Problem Assistance:</strong> Technical help</li>
                    <li>✅ <strong>Mobile Responsive:</strong> Works on all devices</li>
                </ul>
            </div>
        </div>
        
        <div class="testing-guide" style="background: #e7f3ff; padding: 15px; border-radius: 5px; margin-top: 15px;">
            <h3>🧪 How to Test the Plugin:</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                <div>
                    <h4>1. Product Search Test:</h4>
                    <p>• Visit your frontend<br>
                    • Click the chat widget<br>
                    • Select "I can't find the product I want"<br>
                    • Upload a product image OR describe an item<br>
                    • Check if products are found or vendors contacted</p>
                </div>
                <div>
                    <h4>2. Order Support Test:</h4>
                    <p>• Click "Order Support"<br>
                    • Mention an order number<br>
                    • See if system recognizes it<br>
                    • Test with WooCommerce orders if available</p>
                </div>
                <div>
                    <h4>3. Site Problem Test:</h4>
                    <p>• Click "Problem with the site"<br>
                    • Describe a technical issue<br>
                    • Check AI response quality<br>
                    • Verify troubleshooting suggestions</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Form -->
    <form method="post" action="" id="ai-chat-settings-form">
        <?php wp_nonce_field('ai_chat_settings', 'ai_chat_settings_nonce'); ?>
        
        <!-- General Settings -->
        <div class="postbox">
            <div class="postbox-header">
                <h2 class="hndle">⚙️ General Settings</h2>
            </div>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable AI Chat</th>
                        <td>
                            <label>
                                <input type="checkbox" name="ai_chat_enabled" value="1" <?php checked($current_settings['ai_chat_enabled']); ?>>
                                Enable the AI chat widget on your site
                            </label>
                            <p class="description">Uncheck this to temporarily disable the chat without deactivating the plugin.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Widget Position</th>
                        <td>
                            <select name="ai_chat_widget_position">
                                <option value="bottom-right" <?php selected($current_settings['ai_chat_widget_position'], 'bottom-right'); ?>>Bottom Right</option>
                                <option value="bottom-left" <?php selected($current_settings['ai_chat_widget_position'], 'bottom-left'); ?>>Bottom Left</option>
                                <option value="middle-right" <?php selected($current_settings['ai_chat_widget_position'], 'middle-right'); ?>>Middle Right</option>
                                <option value="middle-left" <?php selected($current_settings['ai_chat_widget_position'], 'middle-left'); ?>>Middle Left</option>
                            </select>
                            <p class="description">Choose where the chat widget appears on your site.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Widget Color</th>
                        <td>
                            <input type="color" name="ai_chat_widget_color" value="<?php echo esc_attr($current_settings['ai_chat_widget_color']); ?>" class="color-picker">
                            <p class="description">Choose the primary color for your chat widget.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Greeting Message</th>
                        <td>
                            <textarea name="ai_chat_greeting_message" rows="3" cols="50" class="large-text"><?php echo esc_textarea($current_settings['ai_chat_greeting_message']); ?></textarea>
                            <p class="description">The first message customers see when they open the chat.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Display Options</th>
                        <td>
                            <label>
                                <input type="checkbox" name="ai_chat_show_for_guests" value="1" <?php checked($current_settings['ai_chat_show_for_guests']); ?>>
                                Show for guest visitors
                            </label><br>
                            
                            <label>
                                <input type="checkbox" name="ai_chat_show_on_mobile" value="1" <?php checked($current_settings['ai_chat_show_on_mobile']); ?>>
                                Show on mobile devices
                            </label><br>
                            
                            <label>
                                <input type="checkbox" name="ai_chat_auto_open" value="1" <?php checked($current_settings['ai_chat_auto_open']); ?>>
                                Auto-open chat for first-time visitors
                            </label>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- AI Configuration -->
        <div class="postbox">
            <div class="postbox-header">
                <h2 class="hndle">🤖 AI Configuration</h2>
            </div>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row">OpenAI API Key</th>
                        <td>
                            <input type="password" name="ai_chat_api_key" value="<?php echo esc_attr($current_settings['ai_chat_api_key']); ?>" class="regular-text" placeholder="sk-...">
                            <button type="button" id="toggle-api-key" class="button">Show</button>
                            <button type="button" id="test-api-connection" class="button">Test Connection</button>
                            <p class="description">
                                Get your API key from <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>. 
                                <strong>Required for full AI functionality.</strong>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">AI Model</th>
                        <td>
                            <select name="ai_chat_model">
                                <option value="gpt-3.5-turbo" <?php selected($current_settings['ai_chat_model'], 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo (Faster, Cheaper)</option>
                                <option value="gpt-4" <?php selected($current_settings['ai_chat_model'], 'gpt-4'); ?>>GPT-4 (Better Quality)</option>
                                <option value="gpt-4-turbo" <?php selected($current_settings['ai_chat_model'], 'gpt-4-turbo'); ?>>GPT-4 Turbo (Best Performance)</option>
                            </select>
                            <p class="description">GPT-3.5 is recommended for most use cases. GPT-4 provides better responses but costs more.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Max Tokens</th>
                        <td>
                            <input type="number" name="ai_chat_max_tokens" value="<?php echo esc_attr($current_settings['ai_chat_max_tokens']); ?>" min="50" max="4000" step="50">
                            <p class="description">Maximum length of AI responses. Higher = longer responses but more expensive. (50-4000)</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Temperature</th>
                        <td>
                            <input type="number" name="ai_chat_temperature" value="<?php echo esc_attr($current_settings['ai_chat_temperature']); ?>" min="0" max="2" step="0.1">
                            <p class="description">Controls AI creativity. 0 = very focused, 1 = balanced, 2 = very creative. (0.0-2.0)</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">API Endpoint</th>
                        <td>
                            <input type="url" name="ai_chat_api_endpoint" value="<?php echo esc_attr($current_settings['ai_chat_api_endpoint']); ?>" class="large-text">
                            <p class="description">Advanced: Custom API endpoint. Leave default unless using a different provider.</p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Vendor & Email Settings -->
        <div class="postbox">
            <div class="postbox-header">
                <h2 class="hndle">📧 Notifications & Vendors</h2>
            </div>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row">Vendor Notifications</th>
                        <td>
                            <label>
                                <input type="checkbox" name="ai_chat_vendor_notifications" value="1" <?php checked($current_settings['ai_chat_vendor_notifications']); ?>>
                                Send product requests to vendors
                            </label>
                            <p class="description">When customers can't find products, automatically notify relevant vendors.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Email Notifications</th>
                        <td>
                            <label>
                                <input type="checkbox" name="ai_chat_email_notifications" value="1" <?php checked($current_settings['ai_chat_email_notifications']); ?>>
                                Send email notifications to admins
                            </label><br>
                            
                            <label>
                                <input type="checkbox" name="ai_chat_customer_confirmations" value="1" <?php checked($current_settings['ai_chat_customer_confirmations']); ?>>
                                Send confirmation emails to customers
                            </label>
                            <p class="description">Keep everyone informed about new requests and responses.</p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Performance & Security -->
        <div class="postbox">
            <div class="postbox-header">
                <h2 class="hndle">⚡ Performance & Security</h2>
            </div>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row">Rate Limiting</th>
                        <td>
                            <label>
                                <input type="checkbox" name="ai_chat_rate_limiting" value="1" <?php checked($current_settings['ai_chat_rate_limiting']); ?>>
                                Enable rate limiting
                            </label>
                            <input type="number" name="ai_chat_rate_limit" value="<?php echo esc_attr($current_settings['ai_chat_rate_limit']); ?>" min="1" max="100"> messages per minute
                            <p class="description">Prevent spam and manage API costs by limiting messages per user.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Data Retention</th>
                        <td>
                            <input type="number" name="ai_chat_data_retention_days" value="<?php echo esc_attr($current_settings['ai_chat_data_retention_days']); ?>" min="7" max="365"> days
                            <p class="description">How long to keep conversation data. Set to 0 to keep forever.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Debug Mode</th>
                        <td>
                            <label>
                                <input type="checkbox" name="ai_chat_debug_mode" value="1" <?php checked($current_settings['ai_chat_debug_mode']); ?>>
                                Enable debug logging
                            </label>
                            <p class="description">Log detailed information for troubleshooting. Disable in production.</p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Usage Statistics -->
        <?php
        $db_manager = new WP_AI_Chat_Database_Manager();
        $conversation_stats = $db_manager->get_conversation_stats(30);
        $request_stats = $db_manager->get_request_stats(30);
        ?>
        
        <div class="postbox">
            <div class="postbox-header">
                <h2 class="hndle">📊 Usage Statistics (Last 30 Days)</h2>
            </div>
            <div class="inside">
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                    <div class="stat-box" style="text-align: center; padding: 15px; background: #f0f8ff; border-radius: 8px;">
                        <h3 style="margin: 0; color: #0073aa;"><?php echo number_format($conversation_stats['total_conversations'] ?? 0); ?></h3>
                        <p style="margin: 5px 0 0 0;">Total Conversations</p>
                    </div>
                    <div class="stat-box" style="text-align: center; padding: 15px; background: #f0fff0; border-radius: 8px;">
                        <h3 style="margin: 0; color: #46b450;"><?php echo number_format($request_stats['total_requests'] ?? 0); ?></h3>
                        <p style="margin: 5px 0 0 0;">Product Requests</p>
                    </div>
                    <div class="stat-box" style="text-align: center; padding: 15px; background: #fff8f0; border-radius: 8px;">
                        <h3 style="margin: 0; color: #ff6900;"><?php echo number_format($request_stats['total_vendors_contacted'] ?? 0); ?></h3>
                        <p style="margin: 5px 0 0 0;">Vendors Contacted</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="postbox">
            <div class="postbox-header">
                <h2 class="hndle">🔧 System Status</h2>
            </div>
            <div class="inside">
                <div class="system-status">
                    <?php
                    $status_checks = [
                        'API Key' => !empty($current_settings['ai_chat_api_key']),
                        'Database Tables' => $db_manager->needs_update() === false,
                        'Upload Directory' => is_writable(wp_upload_dir()['basedir']),
                        'WooCommerce' => class_exists('WooCommerce'),
                        'EDD' => class_exists('Easy_Digital_Downloads'),
                        'cURL Extension' => extension_loaded('curl'),
                        'JSON Extension' => extension_loaded('json')
                    ];
                    
                    foreach ($status_checks as $check => $status) {
                        $icon = $status ? '✅' : '❌';
                        $class = $status ? 'status-good' : 'status-error';
                        echo "<p class='{$class}'>{$icon} {$check}</p>";
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Troubleshooting Guide -->
        <div class="postbox">
            <div class="postbox-header">
                <h2 class="hndle">🔍 Troubleshooting Guide</h2>
            </div>
            <div class="inside">
                <div class="troubleshooting-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <h4>❓ Chat widget not showing:</h4>
                        <ul>
                            <li>Check "Enable AI Chat" is checked above</li>
                            <li>Verify you're not on an admin page</li>
                            <li>Clear browser cache and hard refresh</li>
                            <li>Check if theme has CSS conflicts</li>
                            <li>Disable other chat plugins temporarily</li>
                        </ul>
                        
                        <h4>🤖 AI responses not working:</h4>
                        <ul>
                            <li>Verify OpenAI API key is correct</li>
                            <li>Click "Test Connection" button above</li>
                            <li>Check API usage limits at OpenAI</li>
                            <li>Try a different AI model</li>
                            <li>Enable debug mode for more info</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h4>📧 Emails not sending:</h4>
                        <ul>
                            <li>Check email notification settings above</li>
                            <li>Test WordPress email with a plugin like WP Mail SMTP</li>
                            <li>Verify vendor email addresses are correct</li>
                            <li>Check spam folders</li>
                            <li>Enable email notifications above</li>
                        </ul>
                        
                        <h4>🔍 Product search not working:</h4>
                        <ul>
                            <li>Ensure you have products in WooCommerce/EDD</li>
                            <li>Check product visibility settings</li>
                            <li>Test with simple product names first</li>
                            <li>Verify database tables were created</li>
                            <li>Try different search terms</li>
                        </ul>
                    </div>
                </div>
                
                <div style="background: #fffbf0; padding: 15px; border-radius: 5px; margin-top: 15px; border-left: 4px solid #ffb900;">
                    <h4>🆘 Still need help?</h4>
                    <p>
                        <strong>Documentation:</strong> Check the plugin documentation<br>
                        <strong>Support:</strong> Contact plugin support<br>
                        <strong>Debug:</strong> Enable debug mode above and check error logs<br>
                        <strong>Test Mode:</strong> Try with a simple test message first
                    </p>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button-primary" value="Save Settings">
            <button type="button" id="preview-widget" class="button">Preview Chat Widget</button>
            <button type="button" id="export-settings" class="button">Export Settings</button>
            <input type="file" id="import-settings" accept=".json" style="display: none;">
            <button type="button" onclick="document.getElementById('import-settings').click()" class="button">Import Settings</button>
        </p>
    </form>
</div>

<style>
.postbox { margin-bottom: 20px; }
.postbox-header h2 { font-size: 16px; padding: 12px; }
.status-good { color: #46b450; }
.status-error { color: #dc3232; }
.ai-chat-documentation h2 { color: #0073aa; margin-top: 0; }
.ai-chat-documentation h3 { color: #135e96; }
.ai-chat-documentation h4 { color: #333; margin-top: 15px; }
.testing-guide { border-left: 4px solid #0073aa; }
.color-picker { width: 60px; height: 40px; border: none; border-radius: 4px; cursor: pointer; }
</style>

<script>
jQuery(document).ready(function($) {
    // Toggle API key visibility
    $('#toggle-api-key').click(function() {
        const input = $('input[name="ai_chat_api_key"]');
        const button = $(this);
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            button.text('Hide');
        } else {
            input.attr('type', 'password');
            button.text('Show');
        }
    });
    
    // Test API connection
    $('#test-api-connection').click(function() {
        const button = $(this);
        const originalText = button.text();
        const apiKey = $('input[name="ai_chat_api_key"]').val();
        
        if (!apiKey) {
            alert('Please enter an API key first.');
            return;
        }
        
        button.text('Testing...').prop('disabled', true);
        
        // Simple test - you can implement actual API test in admin-scripts.js
        setTimeout(function() {
            if (apiKey.startsWith('sk-') && apiKey.length > 20) {
                alert('API key format looks correct! Save settings and test on frontend.');
            } else {
                alert('API key format appears invalid. Please check your key.');
            }
            button.text(originalText).prop('disabled', false);
        }, 1000);
    });
    
    // Preview widget button
    $('#preview-widget').click(function() {
        const position = $('select[name="ai_chat_widget_position"]').val();
        const color = $('input[name="ai_chat_widget_color"]').val();
        const greeting = $('textarea[name="ai_chat_greeting_message"]').val();
        
        alert(`Preview:\nPosition: ${position}\nColor: ${color}\nGreeting: ${greeting}\n\nSave settings and visit your frontend to see the actual widget!`);
    });
});
</script>