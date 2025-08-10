<?php
/**
 * Request List Template
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get request manager
$request_manager = new WP_AI_Chat_Request_Manager();

// Handle bulk actions
if (isset($_POST['action']) && $_POST['action'] !== '-1' && !empty($_POST['request_ids'])) {
    $action = sanitize_text_field($_POST['action']);
    $request_ids = array_map('intval', $_POST['request_ids']);
    
    foreach ($request_ids as $request_id) {
        switch ($action) {
            case 'complete':
                $request_manager->complete_request($request_id, 'Bulk completed by admin');
                break;
            case 'cancel':
                $request_manager->cancel_request($request_id, 'Bulk cancelled by admin');
                break;
            case 'delete':
                $request_manager->delete_request($request_id);
                break;
        }
    }
    
    echo '<div class="notice notice-success"><p>Bulk action completed successfully!</p></div>';
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$category_filter = $_GET['category'] ?? '';
$date_filter = $_GET['date'] ?? '';

// Build filter arguments
$filter_args = [
    'limit' => 20,
    'offset' => (max(1, intval($_GET['paged'] ?? 1)) - 1) * 20
];

if ($status_filter) {
    $filter_args['status'] = $status_filter;
}

if ($category_filter) {
    $filter_args['category'] = $category_filter;
}

// Get requests
$requests = $request_manager->get_requests($filter_args);
$total_requests = count($request_manager->get_requests(array_merge($filter_args, ['limit' => -1])));

// Get filter options
global $wpdb;
$statuses = $wpdb->get_results("SELECT DISTINCT status FROM {$wpdb->prefix}ai_chat_requests ORDER BY status", ARRAY_A);
$categories = $wpdb->get_results("SELECT DISTINCT category FROM {$wpdb->prefix}ai_chat_requests ORDER BY category", ARRAY_A);
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <!-- Summary Stats -->
    <div class="ai-chat-summary" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin: 20px 0;">
        <?php
        $summary_stats = [
            ['label' => 'Total', 'value' => count($request_manager->get_requests(['limit' => -1])), 'color' => '#0073aa'],
            ['label' => 'Pending', 'value' => count($request_manager->get_requests(['status' => 'pending', 'limit' => -1])), 'color' => '#ffb900'],
            ['label' => 'Completed', 'value' => count($request_manager->get_requests(['status' => 'completed', 'limit' => -1])), 'color' => '#46b450'],
            ['label' => 'Cancelled', 'value' => count($request_manager->get_requests(['status' => 'cancelled', 'limit' => -1])), 'color' => '#d63638']
        ];
        
        foreach ($summary_stats as $stat):
        ?>
        <div style="text-align: center; padding: 15px; background: #fff; border: 1px solid #ccd0d4; border-radius: 4px;">
            <div style="font-size: 24px; font-weight: 700; color: <?php echo $stat['color']; ?>;">
                <?php echo number_format($stat['value']); ?>
            </div>
            <div style="font-size: 13px; color: #666; margin-top: 5px;">
                <?php echo $stat['label']; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Filters -->
    <div class="tablenav top">
        <div class="alignleft actions">
            <form method="get" style="display: inline-flex; align-items: center; gap: 10px;">
                <input type="hidden" name="page" value="ai-chat-requests">
                
                <select name="status">
                    <option value="">All Statuses</option>
                    <?php foreach ($statuses as $status): ?>
                    <option value="<?php echo esc_attr($status['status']); ?>" <?php selected($status_filter, $status['status']); ?>>
                        <?php echo ucfirst($status['status']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?php echo esc_attr($category['category']); ?>" <?php selected($category_filter, $category['category']); ?>>
                        <?php echo ucwords(str_replace('-', ' ', $category['category'])); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                
                <input type="submit" class="button" value="Filter">
                
                <?php if ($status_filter || $category_filter): ?>
                <a href="<?php echo admin_url('admin.php?page=ai-chat-requests'); ?>" class="button">Clear Filters</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="alignright">
            <a href="#" id="export-requests" class="button">Export CSV</a>
        </div>
    </div>

    <!-- Requests Table -->
    <?php if (!empty($requests)): ?>
    <form method="post" id="requests-form">
        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <select name="action" id="bulk-actions">
                    <option value="-1">Bulk Actions</option>
                    <option value="complete">Mark as Completed</option>
                    <option value="cancel">Cancel Requests</option>
                    <option value="delete">Delete Requests</option>
                </select>
                <input type="submit" class="button action" value="Apply">
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <td class="manage-column column-cb check-column">
                        <input type="checkbox" id="cb-select-all">
                    </td>
                    <th class="manage-column">ID</th>
                    <th class="manage-column">Customer</th>
                    <th class="manage-column">Category</th>
                    <th class="manage-column">Description</th>
                    <th class="manage-column">Status</th>
                    <th class="manage-column">Vendors</th>
                    <th class="manage-column">Date</th>
                    <th class="manage-column">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $request): ?>
                <tr>
                    <th class="check-column">
                        <input type="checkbox" name="request_ids[]" value="<?php echo $request['id']; ?>" class="request-checkbox">
                    </th>
                    <td>
                        <strong>#<?php echo $request['id']; ?></strong>
                    </td>
                    <td>
                        <?php if ($request['customer_name']): ?>
                            <strong><?php echo esc_html($request['customer_name']); ?></strong><br>
                            <small><?php echo esc_html($request['customer_email']); ?></small>
                        <?php else: ?>
                            <em>Guest User</em>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="category-badge" style="background: #e1f5fe; color: #01579b; padding: 3px 8px; border-radius: 3px; font-size: 12px;">
                            <?php echo esc_html(ucwords(str_replace('-', ' ', $request['category']))); ?>
                        </span>
                    </td>
                    <td>
                        <div style="max-width: 300px;">
                            <?php echo wp_trim_words(esc_html($request['description']), 15); ?>
                            <?php if ($request['image_url']): ?>
                                <br><small>📷 Image attached</small>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <?php
                        $status_colors = [
                            'pending' => '#ffb900',
                            'completed' => '#46b450',
                            'cancelled' => '#d63638',
                            'processing' => '#0073aa'
                        ];
                        $status_color = $status_colors[$request['status']] ?? '#666';
                        ?>
                        <select name="status_<?php echo $request['id']; ?>" class="update-status" data-request-id="<?php echo $request['id']; ?>" style="background: <?php echo $status_color; ?>; color: white; border: none; padding: 4px 8px; border-radius: 3px;">
                            <option value="pending" <?php selected($request['status'], 'pending'); ?>>Pending</option>
                            <option value="processing" <?php selected($request['status'], 'processing'); ?>>Processing</option>
                            <option value="completed" <?php selected($request['status'], 'completed'); ?>>Completed</option>
                            <option value="cancelled" <?php selected($request['status'], 'cancelled'); ?>>Cancelled</option>
                        </select>
                    </td>
                    <td>
                        <div class="text-center">
                            <strong><?php echo intval($request['vendors_contacted']); ?></strong> contacted<br>
                            <small style="color: #46b450;"><?php echo intval($request['responses_received']); ?> responses</small>
                        </div>
                    </td>
                    <td>
                        <div style="font-size: 13px;">
                            <?php echo date('M j, Y', strtotime($request['created_at'])); ?><br>
                            <small style="color: #666;"><?php echo date('g:i A', strtotime($request['created_at'])); ?></small>
                        </div>
                    </td>
                    <td>
                        <div class="row-actions">
                            <a href="#" class="view-request" data-request-id="<?php echo $request['id']; ?>">View</a> |
                            <a href="#" class="add-note" data-request-id="<?php echo $request['id']; ?>">Add Note</a> |
                            <a href="#" class="delete-request" data-request-id="<?php echo $request['id']; ?>" style="color: #d63638;">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="tablenav bottom">
            <div class="alignleft actions bulkactions">
                <select name="action" id="bulk-actions-2">
                    <option value="-1">Bulk Actions</option>
                    <option value="complete">Mark as Completed</option>
                    <option value="cancel">Cancel Requests</option>
                    <option value="delete">Delete Requests</option>
                </select>
                <input type="submit" class="button action" value="Apply">
            </div>
            
            <!-- Pagination -->
            <div class="tablenav-pages">
                <?php
                $total_pages = ceil($total_requests / 20);
                $current_page = max(1, intval($_GET['paged'] ?? 1));
                
                if ($total_pages > 1):
                ?>
                <span class="displaying-num"><?php echo number_format($total_requests); ?> items</span>
                
                <?php if ($current_page > 1): ?>
                <a class="prev-page button" href="<?php echo admin_url('admin.php?page=ai-chat-requests&paged=' . ($current_page - 1)); ?>">‹</a>
                <?php endif; ?>
                
                <span class="paging-input">
                    Page <?php echo $current_page; ?> of <?php echo $total_pages; ?>
                </span>
                
                <?php if ($current_page < $total_pages): ?>
                <a class="next-page button" href="<?php echo admin_url('admin.php?page=ai-chat-requests&paged=' . ($current_page + 1)); ?>">›</a>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </form>
    
    <?php else: ?>
    <!-- Empty State -->
    <div style="text-align: center; padding: 60px 20px; background: #fff; border: 1px solid #ccd0d4; border-radius: 4px;">
        <div style="font-size: 64px; margin-bottom: 20px;">🔍</div>
        <h2 style="color: #333;">No Product Requests Yet</h2>
        <p style="color: #666; margin-bottom: 30px;">
            Product requests will appear here when customers can't find what they're looking for.<br>
            The AI will automatically contact relevant vendors for hard-to-find items.
        </p>
        
        <div style="display: inline-flex; gap: 15px;">
            <a href="<?php echo admin_url('admin.php?page=ai-chat-settings'); ?>" class="button-primary">
                Configure Settings
            </a>
            <a href="<?php echo home_url(); ?>" target="_blank" class="button">
                Test Chat Widget
            </a>
        </div>
        
        <div style="background: #f0f8ff; padding: 20px; border-radius: 5px; margin-top: 30px; max-width: 600px; margin-left: auto; margin-right: auto;">
            <h3 style="margin: 0 0 15px 0; color: #0073aa;">🎯 How it works:</h3>
            <ol style="text-align: left; line-height: 1.8; margin: 0;">
                <li>Customer clicks "I can't find the product I want"</li>
                <li>They upload a photo or describe the item</li>
                <li>AI searches your product database</li>
                <li>If no match found, system suggests categories</li>
                <li>Request is automatically sent to relevant vendors</li>
                <li>Vendors receive email notifications with customer details</li>
            </ol>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Request Details Modal -->
<div id="request-details-modal" class="ai-chat-modal" style="display: none;">
    <div class="ai-chat-modal-content" style="max-width: 600px;">
        <div class="ai-chat-modal-header">
            <h3>Request Details</h3>
            <button class="ai-chat-modal-close">&times;</button>
        </div>
        <div class="ai-chat-modal-body" id="request-details-content">
            <!-- Content loaded via AJAX -->
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div id="add-note-modal" class="ai-chat-modal" style="display: none;">
    <div class="ai-chat-modal-content" style="max-width: 500px;">
        <div class="ai-chat-modal-header">
            <h3>Add Note</h3>
            <button class="ai-chat-modal-close">&times;</button>
        </div>
        <div class="ai-chat-modal-body">
            <form id="add-note-form">
                <input type="hidden" id="note-request-id" value="">
                <div class="form-field">
                    <label for="request-note">Note:</label>
                    <textarea id="request-note" rows="4" style="width: 100%;"></textarea>
                </div>
                <div style="text-align: right; padding-top: 15px;">
                    <button type="button" class="button ai-chat-modal-close">Cancel</button>
                    <button type="submit" class="button-primary">Add Note</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Select all checkbox
    $('#cb-select-all').change(function() {
        $('.request-checkbox').prop('checked', this.checked);
    });
    
    // Update status
    $('.update-status').change(function() {
        const requestId = $(this).data('request-id');
        const newStatus = $(this).val();
        const statusElement = $(this);
        
        $.post(ajaxurl, {
            action: 'ai_chat_update_request_status',
            nonce: '<?php echo wp_create_nonce('ai_chat_admin_nonce'); ?>',
            request_id: requestId,
            status: newStatus
        }, function(response) {
            if (response.success) {
                // Update status color
                const colors = {
                    'pending': '#ffb900',
                    'processing': '#0073aa',
                    'completed': '#46b450',
                    'cancelled': '#d63638'
                };
                statusElement.css('background-color', colors[newStatus] || '#666');
                
                // Show success message
                $('<div class="notice notice-success is-dismissible"><p>Status updated successfully!</p></div>')
                    .insertAfter('.wrap h1').delay(3000).fadeOut();
            } else {
                alert('Failed to update status');
            }
        });
    });
    
    // View request details
    $('.view-request').click(function(e) {
        e.preventDefault();
        const requestId = $(this).data('request-id');
        
        // Load request details via AJAX
        $('#request-details-content').html('<div style="text-align: center; padding: 40px;">Loading...</div>');
        $('#request-details-modal').show();
        
        $.post(ajaxurl, {
            action: 'ai_chat_get_request_details',
            nonce: '<?php echo wp_create_nonce('ai_chat_admin_nonce'); ?>',
            request_id: requestId
        }, function(response) {
            if (response.success) {
                $('#request-details-content').html(response.data.html);
            } else {
                $('#request-details-content').html('<div style="color: #d63638;">Failed to load request details.</div>');
            }
        });
    });
    
    // Add note
    $('.add-note').click(function(e) {
        e.preventDefault();
        const requestId = $(this).data('request-id');
        $('#note-request-id').val(requestId);
        $('#add-note-modal').show();
    });
    
    // Add note form submission
    $('#add-note-form').submit(function(e) {
        e.preventDefault();
        const requestId = $('#note-request-id').val();
        const note = $('#request-note').val().trim();
        
        if (!note) {
            alert('Please enter a note');
            return;
        }
        
        $.post(ajaxurl, {
            action: 'ai_chat_add_request_note',
            nonce: '<?php echo wp_create_nonce('ai_chat_admin_nonce'); ?>',
            request_id: requestId,
            note: note
        }, function(response) {
            if (response.success) {
                $('#add-note-modal').hide();
                $('#request-note').val('');
                $('<div class="notice notice-success is-dismissible"><p>Note added successfully!</p></div>')
                    .insertAfter('.wrap h1').delay(3000).fadeOut();
            } else {
                alert('Failed to add note');
            }
        });
    });
    
    // Delete request
    $('.delete-request').click(function(e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete this request?')) return;
        
        const requestId = $(this).data('request-id');
        const row = $(this).closest('tr');
        
        $.post(ajaxurl, {
            action: 'ai_chat_delete_request',
            nonce: '<?php echo wp_create_nonce('ai_chat_admin_nonce'); ?>',
            request_id: requestId
        }, function(response) {
            if (response.success) {
                row.fadeOut(function() {
                    $(this).remove();
                });
            } else {
                alert('Failed to delete request');
            }
        });
    });
    
    // Export CSV
    $('#export-requests').click(function(e) {
        e.preventDefault();
        window.location.href = ajaxurl + '?action=ai_chat_export_requests&nonce=<?php echo wp_create_nonce('ai_chat_admin_nonce'); ?>&' + window.location.search.substring(1);
    });
    
    // Modal close
    $('.ai-chat-modal-close').click(function() {
        $(this).closest('.ai-chat-modal').hide();
    });
    
    // Close modal on background click
    $('.ai-chat-modal').click(function(e) {
        if (e.target === this) {
            $(this).hide();
        }
    });
});
</script>

<style>
.category-badge {
    display: inline-block;
    background: #e1f5fe;
    color: #01579b;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 500;
}

.update-status {
    border: none;
    padding: 4px 8px;
    border-radius: 3px;
    color: white;
    font-size: 12px;
    cursor: pointer;
}

.row-actions {
    font-size: 13px;
}

.row-actions a {
    text-decoration: none;
}

.ai-chat-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ai-chat-modal-content {
    background: #fff;
    border-radius: 4px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    max-height: 90vh;
    overflow-y: auto;
}

.ai-chat-modal-header {
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.ai-chat-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 3px;
}

.ai-chat-modal-close:hover {
    background: #f1f1f1;
}

.ai-chat-modal-body {
    padding: 20px;
}
</style>