<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Handle ticket actions
if (isset($_POST['update_ticket']) && wp_verify_nonce($_POST['_wpnonce'], 'update_ticket')) {
    $ticket_id = intval($_POST['ticket_id']);
    $new_status = sanitize_text_field($_POST['status']);
    
    $wpdb->update(
        $wpdb->prefix . 'wpapc_support_tickets',
        ['status' => $new_status],
        ['id' => $ticket_id]
    );
    
    echo '<div class="notice notice-success"><p>Ticket updated successfully.</p></div>';
}

// Get all tickets
$tickets = $wpdb->get_results("
    SELECT t.*, u.display_name as user_name, u.user_email
    FROM {$wpdb->prefix}wpapc_support_tickets t
    LEFT JOIN {$wpdb->users} u ON t.user_id = u.ID
    ORDER BY 
        CASE t.priority 
            WHEN 'high' THEN 1 
            WHEN 'normal' THEN 2 
            WHEN 'low' THEN 3 
        END,
        t.created_at DESC
");
?>

<div class="wrap">
    <h1>Support Tickets
        <a href="#" id="export-tickets" class="page-title-action">Export CSV</a>
    </h1>
    
    <div class="wpapc-tickets-filters">
        <select id="filter-status">
            <option value="">All Status</option>
            <option value="open">Open</option>
            <option value="in_progress">In Progress</option>
            <option value="resolved">Resolved</option>
            <option value="closed">Closed</option>
        </select>
        
        <select id="filter-priority">
            <option value="">All Priority</option>
            <option value="high">High</option>
            <option value="normal">Normal</option>
            <option value="low">Low</option>
        </select>
        
        <button class="button">Filter</button>
    </div>
    
    <table class="wp-list-table widefat fixed striped wpapc-data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Type</th>
                <th>Subject</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Created</th>
                <th>Updated</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($tickets): ?>
                <?php foreach ($tickets as $ticket): ?>
                    <tr>
                        <td>#<?php echo $ticket->id; ?></td>
                        <td>
                            <?php echo esc_html($ticket->user_name ?: 'Guest'); ?><br>
                            <small><?php echo esc_html($ticket->user_email); ?></small>
                        </td>
                        <td><?php echo ucfirst(str_replace('_', ' ', $ticket->type)); ?></td>
                        <td>
                            <strong><?php echo esc_html($ticket->subject); ?></strong><br>
                            <small><?php echo esc_html(substr($ticket->message, 0, 100)); ?>...</small>
                        </td>
                        <td>
                            <span class="priority-<?php echo esc_attr($ticket->priority); ?>">
                                <?php echo ucfirst($ticket->priority); ?>
                            </span>
                        </td>
                        <td>
                            <select class="wpapc-ticket-status" data-ticket-id="<?php echo $ticket->id; ?>">
                                <option value="open" <?php selected($ticket->status, 'open'); ?>>Open</option>
                                <option value="in_progress" <?php selected($ticket->status, 'in_progress'); ?>>In Progress</option>
                                <option value="resolved" <?php selected($ticket->status, 'resolved'); ?>>Resolved</option>
                                <option value="closed" <?php selected($ticket->status, 'closed'); ?>>Closed</option>
                            </select>
                        </td>
                        <td><?php echo human_time_diff(strtotime($ticket->created_at)); ?> ago</td>
                        <td><?php echo human_time_diff(strtotime($ticket->updated_at)); ?> ago</td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=wpapc-support&action=view&ticket_id=' . $ticket->id); ?>" 
                               class="button button-small">View</a>
                            <a href="<?php echo admin_url('admin.php?page=wpapc-support&action=reply&ticket_id=' . $ticket->id); ?>" 
                               class="button button-small button-primary">Reply</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9">No support tickets found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div class="wpapc-quick-replies">
        <h3>Quick Reply Templates</h3>
        <button class="button wpapc-quick-reply" data-template="Thank you for contacting support. We'll look into this issue and get back to you within 24 hours.">
            Acknowledgment
        </button>
        <button class="button wpapc-quick-reply" data-template="We need more information to help you. Could you please provide your order number and describe the issue in detail?">
            Request Info
        </button>
        <button class="button wpapc-quick-reply" data-template="This issue has been resolved. Please check and let us know if you need any further assistance.">
            Resolved
        </button>
    </div>
    
    <style>
        .priority-high { color: #dc3545; font-weight: bold; }
        .priority-normal { color: #28a745; }
        .priority-low { color: #6c757d; }
        
        .wpapc-tickets-filters {
            margin: 20px 0;
        }
        
        .wpapc-tickets-filters select {
            margin-right: 10px;
        }
        
        .wpapc-quick-replies {
            margin-top: 20px;
            padding: 15px;
            background: #f1f1f1;
            border-radius: 5px;
        }
        
        .wpapc-quick-replies button {
            margin-right: 10px;
            margin-bottom: 10px;
        }
    </style>
</div>