<?php
/**
 * Admin Dashboard Template
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get statistics
$db_manager = new WP_AI_Chat_Database_Manager();
$conversation_stats = $db_manager->get_conversation_stats(30);
$request_stats = $db_manager->get_request_stats(30);

// Get recent activity
global $wpdb;
$recent_conversations = $wpdb->get_results(
    "SELECT user_message, ai_response, context, created_at 
     FROM {$wpdb->prefix}ai_chat_conversations 
     ORDER BY created_at DESC LIMIT 10",
    ARRAY_A
);

$recent_requests = $wpdb->get_results(
    "SELECT id, category, description, status, customer_name, created_at 
     FROM {$wpdb->prefix}ai_chat_requests 
     ORDER BY created_at DESC LIMIT 10",
    ARRAY_A
);
?>

<div class="wrap ai-chat-admin">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <!-- Welcome Section -->
    <div class="ai-chat-welcome" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px; margin: 20px 0;">
        <h2 style="color: white; margin: 0 0 10px 0;">🚀 Welcome to AI Product Chat!</h2>
        <p style="margin: 0; font-size: 16px; opacity: 0.9;">
            Your intelligent chat system is helping customers find products and get support. 
            <?php if (empty(get_option('ai_chat_api_key'))): ?>
                <strong>⚠️ Please <a href="<?php echo admin_url('admin.php?page=ai-chat-settings'); ?>" style="color: #ffeb3b;">configure your API key</a> to enable full AI functionality.</strong>
            <?php else: ?>
                <strong>✅ System is configured and ready to go!</strong>
            <?php endif; ?>
        </p>
    </div>

    <!-- Quick Stats -->
    <div class="ai-chat-dashboard">
        <!-- Total Conversations -->
        <div class="dashboard-widget">
            <div class="widget-header">
                <h3>💬 Conversations (30 days)</h3>
                <span class="status-indicator status-active"></span>
            </div>
            <div class="widget-content">
                <div class="widget-stat">
                    <span class="widget-stat-number"><?php echo number_format($conversation_stats['total_conversations'] ?? 0); ?></span>
                    <div class="widget-stat-label">Total Conversations</div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                    <div class="text-center">
                        <strong><?php echo number_format($conversation_stats['unique_users'] ?? 0); ?></strong>
                        <div style="font-size: 12px; color: #666;">Unique Users</div>
                    </div>
                    <div class="text-center">
                        <strong><?php echo number_format($conversation_stats['unique_sessions'] ?? 0); ?></strong>
                        <div style="font-size: 12px; color: #666;">Sessions</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Requests -->
        <div class="dashboard-widget">
            <div class="widget-header">
                <h3>🔍 Product Requests (30 days)</h3>
                <span class="status-indicator status-pending"></span>
            </div>
            <div class="widget-content">
                <div class="widget-stat">
                    <span class="widget-stat-number"><?php echo number_format($request_stats['total_requests'] ?? 0); ?></span>
                    <div class="widget-stat-label">Total Requests</div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                    <div class="text-center">
                        <strong style="color: #46b450;"><?php echo number_format($request_stats['completed_requests'] ?? 0); ?></strong>
                        <div style="font-size: 12px; color: #666;">Completed</div>
                    </div>
                    <div class="text-center">
                        <strong style="color: #ffb900;"><?php echo number_format($request_stats['pending_requests'] ?? 0); ?></strong>
                        <div style="font-size: 12px; color: #666;">Pending</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vendor Activity -->
        <div class="dashboard-widget">
            <div class="widget-header">
                <h3>🏪 Vendor Activity</h3>
                <span class="status-indicator status-completed"></span>
            </div>
            <div class="widget-content">
                <div class="widget-stat">
                    <span class="widget-stat-number"><?php echo number_format($request_stats['total_vendors_contacted'] ?? 0); ?></span>
                    <div class="widget-stat-label">Vendors Contacted</div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                    <div class="text-center">
                        <strong><?php echo number_format($request_stats['total_responses'] ?? 0); ?></strong>
                        <div style="font-size: 12px; color: #666;">Responses</div>
                    </div>
                    <div class="text-center">
                        <strong><?php echo round($request_stats['success_rate'] ?? 0, 1); ?>%</strong>
                        <div style="font-size: 12px; color: #666;">Success Rate</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="dashboard-widget">
            <div class="widget-header">
                <h3>⚙️ System Status</h3>
                <?php
                $api_configured = !empty(get_option('ai_chat_api_key'));
                $status_class = $api_configured ? 'status-active' : 'status-error';
                ?>
                <span class="status-indicator <?php echo $status_class; ?>"></span>
            </div>
            <div class="widget-content">
                <div class="system-status">
                    <?php
                    $status_items = [
                        'API Configured' => !empty(get_option('ai_chat_api_key')),
                        'Plugin Enabled' => get_option('ai_chat_enabled', 1),
                        'Database Ready' => !$db_manager->needs_update(),
                        'WooCommerce' => class_exists('WooCommerce'),
                    ];
                    
                    foreach ($status_items as $item => $status):
                        $icon = $status ? '✅' : '❌';
                        $color = $status ? '#46b450' : '#d63638';
                    ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span><?php echo $item; ?></span>
                        <span style="color: <?php echo $color; ?>;"><?php echo $icon; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div style="margin-top: 15px;">
                    <a href="<?php echo admin_url('admin.php?page=ai-chat-settings'); ?>" class="btn-ai-chat">
                        Configure Settings
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 30px;">
        <!-- Recent Conversations -->
        <div class="postbox">
            <div class="postbox-header">
                <h2 class="hndle">💬 Recent Conversations</h2>
            </div>
            <div class="inside">
                <?php if (!empty($recent_conversations)): ?>
                    <div class="recent-activity">
                        <?php foreach (array_slice($recent_conversations, 0, 5) as $conversation): ?>
                        <div class="activity-item" style="padding: 10px 0; border-bottom: 1px solid #eee;">
                            <div style="font-size: 13px; color: #666; margin-bottom: 5px;">
                                <?php echo human_time_diff(strtotime($conversation['created_at'])); ?> ago
                                <span class="context-badge" style="background: #e1f5fe; color: #01579b; padding: 2px 6px; border-radius: 3px; font-size: 11px; margin-left: 8px;">
                                    <?php echo ucfirst($conversation['context']); ?>
                                </span>
                            </div>
                            <div style="font-size: 14px;">
                                <strong>User:</strong> <?php echo wp_trim_words($conversation['user_message'], 10); ?>
                            </div>
                            <div style="font-size: 14px; color: #0073aa;">
                                <strong>AI:</strong> <?php echo wp_trim_words($conversation['ai_response'], 10); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div style="text-align: center; padding: 15px 0;">
                        <a href="<?php echo admin_url('admin.php?page=ai-chat-conversations'); ?>" class="btn-ai-chat btn-secondary">
                            View All Conversations
                        </a>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #666;">
                        <div style="font-size: 48px; margin-bottom: 15px;">💬</div>
                        <p>No conversations yet.</p>
                        <p><small>Conversations will appear here once customers start using the chat.</small></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Requests -->
        <div class="postbox">
            <div class="postbox-header">
                <h2 class="hndle">🔍 Recent Product Requests</h2>
            </div>
            <div class="inside">
                <?php if (!empty($recent_requests)): ?>
                    <div class="recent-activity">
                        <?php foreach (array_slice($recent_requests, 0, 5) as $request): ?>
                        <div class="activity-item" style="padding: 10px 0; border-bottom: 1px solid #eee;">
                            <div style="font-size: 13px; color: #666; margin-bottom: 5px;">
                                <?php echo human_time_diff(strtotime($request['created_at'])); ?> ago
                                <?php
                                $status_colors = [
                                    'pending' => '#ffb900',
                                    'completed' => '#46b450',
                                    'cancelled' => '#d63638'
                                ];
                                $status_color = $status_colors[$request['status']] ?? '#666';
                                ?>
                                <span class="status-badge" style="background: <?php echo $status_color; ?>; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px; margin-left: 8px;">
                                    <?php echo ucfirst($request['status']); ?>
                                </span>
                            </div>
                            <div style="font-size: 14px; margin-bottom: 3px;">
                                <strong>#<?php echo $request['id']; ?></strong> - <?php echo esc_html($request['customer_name'] ?: 'Guest'); ?>
                            </div>
                            <div style="font-size: 13px; color: #0073aa;">
                                <strong><?php echo esc_html($request['category']); ?>:</strong> 
                                <?php echo wp_trim_words($request['description'], 8); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div style="text-align: center; padding: 15px 0;">
                        <a href="<?php echo admin_url('admin.php?page=ai-chat-requests'); ?>" class="btn-ai-chat btn-secondary">
                            View All Requests
                        </a>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #666;">
                        <div style="font-size: 48px; margin-bottom: 15px;">🔍</div>
                        <p>No product requests yet.</p>
                        <p><small>Customer product requests will appear here.</small></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="postbox" style="margin-top: 20px;">
        <div class="postbox-header">
            <h2 class="hndle">🚀 Quick Actions</h2>
        </div>
        <div class="inside">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; padding: 10px 0;">
                <a href="<?php echo admin_url('admin.php?page=ai-chat-settings'); ?>" class="btn-ai-chat" style="text-align: center; padding: 15px; text-decoration: none;">
                    ⚙️ Configure Settings
                </a>
                <a href="<?php echo admin_url('admin.php?page=ai-chat-requests'); ?>" class="btn-ai-chat btn-success" style="text-align: center; padding: 15px; text-decoration: none;">
                    📋 Manage Requests
                </a>
                <a href="<?php echo admin_url('admin.php?page=ai-chat-vendors'); ?>" class="btn-ai-chat btn-warning" style="text-align: center; padding: 15px; text-decoration: none; color: #333;">
                    🏪 Manage Vendors
                </a>
                <a href="<?php echo home_url(); ?>" target="_blank" class="btn-ai-chat btn-secondary" style="text-align: center; padding: 15px; text-decoration: none;">
                    👁️ Test Frontend
                </a>
            </div>
        </div>
    </div>

    <!-- Category Breakdown Chart (if data available) -->
    <?php if (!empty($request_stats['category_breakdown'])): ?>
    <div class="postbox" style="margin-top: 20px;">
        <div class="postbox-header">
            <h2 class="hndle">📊 Popular Categories</h2>
        </div>
        <div class="inside">
            <div class="category-breakdown" style="padding: 15px 0;">
                <?php 
                $total_requests = array_sum(array_column($request_stats['category_breakdown'], 'count'));
                foreach ($request_stats['category_breakdown'] as $category): 
                    $percentage = $total_requests > 0 ? ($category['count'] / $total_requests) * 100 : 0;
                ?>
                <div class="category-item" style="margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span style="font-weight: 600;"><?php echo esc_html(ucwords(str_replace('-', ' ', $category['category']))); ?></span>
                        <span><?php echo $category['count']; ?> requests</span>
                    </div>
                    <div class="progress-bar" style="height: 8px;">
                        <div class="progress-fill" style="width: <?php echo round($percentage, 1); ?>%;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Getting Started Guide (show if no API key) -->
    <?php if (empty(get_option('ai_chat_api_key'))): ?>
    <div class="postbox" style="margin-top: 20px; border-left: 4px solid #ffb900;">
        <div class="postbox-header">
            <h2 class="hndle">🎯 Getting Started</h2>
        </div>
        <div class="inside">
            <div style="padding: 15px 0;">
                <h3 style="color: #333; margin-top: 0;">Complete Your Setup:</h3>
                <ol style="line-height: 1.8;">
                    <li><strong>Get OpenAI API Key:</strong> Visit <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a> and create an API key</li>
                    <li><strong>Configure Plugin:</strong> Go to <a href="<?php echo admin_url('admin.php?page=ai-chat-settings'); ?>">Settings</a> and enter your API key</li>
                    <li><strong>Set Up Vendors:</strong> Add vendors in <a href="<?php echo admin_url('admin.php?page=ai-chat-vendors'); ?>">Vendors</a> section</li>
                    <li><strong>Test Chat:</strong> Visit your <a href="<?php echo home_url(); ?>" target="_blank">frontend</a> and try the chat widget</li>
                    <li><strong>Customize:</strong> Adjust colors, position, and messages in Settings</li>
                </ol>
                
                <div style="background: #f0f8ff; padding: 15px; border-radius: 5px; margin-top: 20px;">
                    <h4 style="margin: 0 0 10px 0; color: #0073aa;">💡 Pro Tips:</h4>
                    <ul style="margin: 0; line-height: 1.6;">
                        <li>Start with GPT-3.5 Turbo for cost-effective AI responses</li>
                        <li>Test all three chat patterns: Product Search, Order Support, Site Problems</li>
                        <li>Upload sample product images to test image recognition</li>
                        <li>Set up vendor categories to match your products</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<style>
.widget-stat-number {
    display: block;
    font-size: 28px;
    font-weight: 700;
    color: #0073aa;
    line-height: 1;
}

.activity-item:last-child {
    border-bottom: none !important;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: #f1f1f1;
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #0073aa, #005a87);
    transition: width 0.3s ease;
}
</style>