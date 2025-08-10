<?php
if (!defined('ABSPATH')) {
    exit;
}

$user_id = get_current_user_id();
if (!$user_id) {
    echo '<p>Please login to view your product requests.</p>';
    return;
}

global $wpdb;

// Get user's requests
$requests = $wpdb->get_results($wpdb->prepare("
    SELECT r.*, t.name as category_name,
           (SELECT COUNT(*) FROM {$wpdb->prefix}wpapc_vendor_responses WHERE request_id = r.id) as response_count
    FROM {$wpdb->prefix}wpapc_product_requests r
    LEFT JOIN {$wpdb->terms} t ON r.category_id = t.term_id
    WHERE r.user_id = %d
    ORDER BY r.created_at DESC
", $user_id));

// Get notifications
$notifications = get_user_meta($user_id, 'wpapc_notifications', true);
$unread_count = 0;
if (is_array($notifications)) {
    foreach ($notifications as $notification) {
        if (!$notification['read']) {
            $unread_count++;
        }
    }
}
?>

<div class="wpapc-my-requests">
    <h2>My Product Requests</h2>
    
    <?php if ($unread_count > 0): ?>
        <div class="wpapc-notifications-alert">
            <span class="notification-icon">🔔</span>
            You have <?php echo $unread_count; ?> new notification<?php echo $unread_count > 1 ? 's' : ''; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($requests): ?>
        <div class="wpapc-requests-grid">
            <?php foreach ($requests as $request): 
                $days_left = ceil((strtotime($request->expires_at) - time()) / 86400);
                $vendor_deadline = ceil((strtotime($request->expires_at) - time() - (10 * 86400)) / 86400);
            ?>
                <div class="wpapc-request-card">
                    <div class="request-header">
                        <span class="request-id">#<?php echo $request->id; ?></span>
                        <span class="request-status status-<?php echo esc_attr($request->status); ?>">
                            <?php echo ucfirst($request->status); ?>
                        </span>
                    </div>
                    
                    <?php if ($request->image_url): ?>
                        <div class="request-image">
                            <img src="<?php echo esc_url($request->image_url); ?>" alt="Product request">
                        </div>
                    <?php endif; ?>
                    
                    <div class="request-content">
                        <h3><?php echo esc_html($request->category_name ?: 'General Request'); ?></h3>
                        <p><?php echo esc_html($request->description); ?></p>
                        
                        <div class="request-meta">
                            <div class="meta-item">
                                <span class="meta-label">Created:</span>
                                <span><?php echo date('M d, Y', strtotime($request->created_at)); ?></span>
                            </div>
                            
                            <?php if ($vendor_deadline > 0): ?>
                                <div class="meta-item">
                                    <span class="meta-label">Vendor deadline:</span>
                                    <span class="deadline-warning"><?php echo $vendor_deadline; ?> days left</span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="meta-item">
                                <span class="meta-label">Selection deadline:</span>
                                <span><?php echo $days_left; ?> days left</span>
                            </div>
                            
                            <div class="meta-item">
                                <span class="meta-label">Vendor responses:</span>
                                <span class="response-count"><?php echo $request->response_count; ?></span>
                            </div>
                        </div>
                        
                        <div class="request-actions">
                            <a href="<?php echo home_url('/product-request/?id=' . $request->id); ?>" 
                               class="button view-request">View Request</a>
                            
                            <?php if ($request->response_count > 0): ?>
                                <span class="new-responses">New products available!</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-requests">
            <p>You haven't created any product requests yet.</p>
            <p>Start a chat to request products that you can't find!</p>
        </div>
    <?php endif; ?>
</div>

<style>
.wpapc-my-requests {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.wpapc-notifications-alert {
    background: #fff3cd;
    border: 1px solid #ffc107;
    color: #856404;
    padding: 10px 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.notification-icon {
    font-size: 20px;
}

.wpapc-requests-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

.wpapc-request-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: box-shadow 0.3s;
}

.wpapc-request-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.request-header {
    display: flex;
    justify-content: space-between;
    padding: 15px;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.request-id {
    font-weight: bold;
    color: #666;
}

.request-status {
    padding: 3px 10px;
    border-radius: 3px;
    font-size: 12px;
    text-transform: uppercase;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-active {
    background: #d4edda;
    color: #155724;
}

.status-completed {
    background: #cce5ff;
    color: #004085;
}

.request-image {
    width: 100%;
    height: 200px;
    overflow: hidden;
}

.request-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.request-content {
    padding: 20px;
}

.request-content h3 {
    margin: 0 0 10px 0;
    color: #333;
}

.request-content p {
    color: #666;
    line-height: 1.6;
    margin-bottom: 15px;
}

.request-meta {
    border-top: 1px solid #eee;
    padding-top: 15px;
    margin-bottom: 15px;
}

.meta-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 14px;
}

.meta-label {
    color: #999;
}

.deadline-warning {
    color: #dc3545;
    font-weight: bold;
}

.response-count {
    background: #667eea;
    color: #fff;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
}

.request-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.view-request {
    background: #667eea;
    color: #fff;
    padding: 8px 20px;
    text-decoration: none;
    border-radius: 5px;
    transition: background 0.3s;
}

.view-request:hover {
    background: #5567d8;
}

.new-responses {
    color: #28a745;
    font-weight: bold;
    font-size: 14px;
}

.no-requests {
    text-align: center;
    padding: 60px 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.no-requests p {
    margin: 10px 0;
    color: #666;
}
</style>