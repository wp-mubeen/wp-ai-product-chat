<?php
if (!defined('ABSPATH')) {
    exit;
}

$request_id = isset($atts['id']) ? intval($atts['id']) : 0;

if (!$request_id) {
    echo '<p>Invalid request ID.</p>';
    return;
}

global $wpdb;

// Get request details
$request = $wpdb->get_row($wpdb->prepare("
    SELECT r.*, u.display_name as user_name, t.name as category_name
    FROM {$wpdb->prefix}wpapc_product_requests r
    LEFT JOIN {$wpdb->users} u ON r.user_id = u.ID
    LEFT JOIN {$wpdb->terms} t ON r.category_id = t.term_id
    WHERE r.id = %d
", $request_id));

if (!$request) {
    echo '<p>Request not found.</p>';
    return;
}

// Get vendor responses
$responses = $wpdb->get_results($wpdb->prepare("
    SELECT vr.*, u.display_name as vendor_name, u.ID as vendor_id
    FROM {$wpdb->prefix}wpapc_vendor_responses vr
    LEFT JOIN {$wpdb->users} u ON vr.vendor_id = u.ID
    WHERE vr.request_id = %d
    ORDER BY vr.created_at DESC
", $request_id));

// Calculate deadlines
$now = time();
$expires = strtotime($request->expires_at);
$vendor_deadline = $expires - (10 * 86400);
$selection_deadline = $expires;

$is_vendor_period = $now < $vendor_deadline;
$is_selection_period = $now >= $vendor_deadline && $now < $selection_deadline;
$is_expired = $now >= $selection_deadline;

// Check if current user is the request owner
$current_user_id = get_current_user_id();
$is_owner = ($current_user_id == $request->user_id);

// Check if current user is a vendor
$is_vendor = current_user_can('vendor') || current_user_can('seller');
?>

<div class="wpapc-request-page">
    <div class="request-header">
        <h1>Product Request #<?php echo $request_id; ?></h1>
        
        <div class="request-status-bar">
            <div class="status-item <?php echo $is_vendor_period ? 'active' : ''; ?>">
                <span class="status-icon">📝</span>
                <span class="status-label">Vendor Submission</span>
                <span class="status-date">Until <?php echo date('M d, Y', $vendor_deadline); ?></span>
            </div>
            
            <div class="status-item <?php echo $is_selection_period ? 'active' : ''; ?>">
                <span class="status-icon">🎯</span>
                <span class="status-label">Customer Selection</span>
                <span class="status-date">Until <?php echo date('M d, Y', $selection_deadline); ?></span>
            </div>
            
            <div class="status-item <?php echo $is_expired ? 'active' : ''; ?>">
                <span class="status-icon">⏰</span>
                <span class="status-label"><?php echo $is_expired ? 'Expired' : 'Completion'; ?></span>
            </div>
        </div>
    </div>
    
    <div class="request-details">
        <div class="detail-section">
            <h2>Request Details</h2>
            
            <?php if ($request->image_url): ?>
                <div class="request-image-large">
                    <img src="<?php echo esc_url($request->image_url); ?>" alt="Product request image">
                </div>
            <?php endif; ?>
            
            <div class="detail-info">
                <p><strong>Requested by:</strong> <?php echo esc_html($request->user_name); ?></p>
                <p><strong>Category:</strong> <?php echo esc_html($request->category_name ?: 'General'); ?></p>
                <p><strong>Description:</strong></p>
                <div class="description-box">
                    <?php echo nl2br(esc_html($request->description)); ?>
                </div>
            </div>
        </div>
        
        <?php if ($is_vendor && $is_vendor_period): ?>
            <div class="vendor-submission-form">
                <h2>Submit Your Product</h2>
                <form id="vendor-product-submission" method="post">
                    <?php wp_nonce_field('submit_vendor_product', 'vendor_nonce'); ?>
                    <input type="hidden" name="request_id" value="<?php echo $request_id; ?>">
                    
                    <div class="form-group">
                        <label>Select Product from Your Catalog</label>
                        <?php
                        // Get vendor's products
                        $vendor_products = wc_get_products([
                            'author' => $current_user_id,
                            'limit' => -1,
                            'status' => 'publish'
                        ]);
                        
                        if ($vendor_products):
                        ?>
                            <select name="product_id" required>
                                <option value="">Choose a product...</option>
                                <?php foreach ($vendor_products as $product): ?>
                                    <option value="<?php echo $product->get_id(); ?>">
                                        <?php echo $product->get_name(); ?> - <?php echo wc_price($product->get_price()); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <p>You don't have any products. Please add products first.</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Additional Notes (Optional)</label>
                        <textarea name="notes" rows="4"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Special Price for This Request (Optional)</label>
                        <input type="number" name="special_price" step="0.01" min="0">
                    </div>
                    
                    <button type="submit" class="submit-product-btn">Submit Product</button>
                </form>
            </div>
        <?php endif; ?>
        
        <div class="submitted-products">
            <h2>Submitted Products (<?php echo count($responses); ?>)</h2>
            
            <?php if ($responses): ?>
                <div class="products-grid">
                    <?php foreach ($responses as $response): 
                        $response_data = json_decode($response->response_data, true);
                        $product = wc_get_product($response_data['product_id'] ?? 0);
                        
                        if (!$product) continue;
                    ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php echo $product->get_image(); ?>
                            </div>
                            
                            <div class="product-info">
                                <h3><?php echo $product->get_name(); ?></h3>
                                <p class="vendor-name">by <?php echo esc_html($response->vendor_name); ?></p>
                                
                                <div class="price-section">
                                    <?php if (isset($response_data['special_price']) && $response_data['special_price']): ?>
                                        <span class="special-price"><?php echo wc_price($response_data['special_price']); ?></span>
                                        <span class="regular-price struck"><?php echo wc_price($product->get_price()); ?></span>
                                    <?php else: ?>
                                        <span class="regular-price"><?php echo wc_price($product->get_price()); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (isset($response_data['notes']) && $response_data['notes']): ?>
                                    <div class="vendor-notes">
                                        <strong>Vendor Notes:</strong>
                                        <p><?php echo esc_html($response_data['notes']); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="product-actions">
                                    <a href="<?php echo get_permalink($product->get_id()); ?>" 
                                       target="_blank" 
                                       class="view-product-btn">View Product</a>
                                    
                                    <?php if ($is_owner && $is_selection_period): ?>
                                        <button class="select-product-btn" 
                                                data-response-id="<?php echo $response->id; ?>"
                                                data-product-id="<?php echo $product->get_id(); ?>">
                                            Select This Product
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-products">No products have been submitted yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Handle product selection
    $('.select-product-btn').on('click', function() {
        if (!confirm('Are you sure you want to select this product? This action cannot be undone.')) {
            return;
        }
        
        const responseId = $(this).data('response-id');
        const productId = $(this).data('product-id');
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            method: 'POST',
            data: {
                action: 'wpapc_select_product',
                response_id: responseId,
                product_id: productId,
                request_id: <?php echo $request_id; ?>,
                nonce: '<?php echo wp_create_nonce('select_product'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert('Product selected successfully! You will be redirected to checkout.');
                    window.location.href = response.data.checkout_url;
                } else {
                    alert('Failed to select product. Please try again.');
                }
            }
        });
    });
    
    // Handle vendor product submission
    $('#vendor-product-submission').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            method: 'POST',
            data: formData + '&action=wpapc_submit_vendor_product',
            success: function(response) {
                if (response.success) {
                    alert('Product submitted successfully!');
                    location.reload();
                } else {
                    alert('Failed to submit product: ' + response.data);
                }
            }
        });
    });
});
</script>

<style>
.wpapc-request-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.request-header {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    margin-bottom: 30px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.request-status-bar {
    display: flex;
    justify-content: space-between;
    margin-top: 30px;
    padding-top: 30px;
    border-top: 1px solid #eee;
}

.status-item {
    flex: 1;
    text-align: center;
    opacity: 0.5;
    transition: opacity 0.3s;
}

.status-item.active {
    opacity: 1;
}

.status-icon {
    display: block;
    font-size: 30px;
    margin-bottom: 10px;
}

.status-label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
}

.status-date {
    display: block;
    font-size: 12px;
    color: #666;
}

.request-details {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.detail-section {
    margin-bottom: 40px;
}

.request-image-large {
    max-width: 500px;
    margin: 20px 0;
}

.request-image-large img {
    width: 100%;
    height: auto;
    border-radius: 8px;
}

.description-box {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-top: 10px;
}

.vendor-submission-form {
    background: #f0f4ff;
    padding: 25px;
    border-radius: 8px;
    margin: 30px 0;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: bold;
    margin-bottom: 8px;
}

.form-group select,
.form-group input,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.submit-product-btn {
    background: #667eea;
    color: #fff;
    padding: 12px 30px;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s;
}

.submit-product-btn:hover {
    background: #5567d8;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.product-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    transition: box-shadow 0.3s;
}

.product-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.product-image {
    width: 100%;
    height: 200px;
    overflow: hidden;
    background: #f8f9fa;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-info {
    padding: 20px;
}

.product-info h3 {
    margin: 0 0 10px 0;
    font-size: 18px;
}

.vendor-name {
    color: #666;
    font-size: 14px;
    margin-bottom: 15px;
}

.price-section {
    margin: 15px 0;
}

.special-price {
    font-size: 24px;
    font-weight: bold;
    color: #28a745;
    margin-right: 10px;
}

.regular-price {
    font-size: 20px;
    color: #333;
}

.struck {
    text-decoration: line-through;
    color: #999;
    font-size: 16px;
}

.vendor-notes {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    margin: 15px 0;
    font-size: 14px;
}

.product-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.view-product-btn,
.select-product-btn {
    flex: 1;
    padding: 8px 15px;
    text-align: center;
    border-radius: 5px;
    text-decoration: none;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
}

.view-product-btn {
    background: #6c757d;
    color: #fff;
}

.select-product-btn {
    background: #28a745;
    color: #fff;
}

.view-product-btn:hover {
    background: #5a6268;
}

.select-product-btn:hover {
    background: #218838;
}

.no-products {
    text-align: center;
    padding: 40px;
    background: #f8f9fa;
    border-radius: 8px;
    color: #666;
}
</style>