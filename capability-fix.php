<?php
/**
 * AI Chat Capability Fixer
 * 
 * Upload this file to wp-content/plugins/wp-ai-product-chat/
 * Visit: yoursite.com/wp-content/plugins/wp-ai-product-chat/capability-fix.php
 * This will add the required capabilities to administrator role
 */

// Security check and WordPress loading
if (!defined('ABSPATH')) {
    require_once('../../../wp-config.php');
}

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('Access denied. You must be an administrator.');
}

// Fix capabilities
if (isset($_POST['fix_capabilities'])) {
    $admin_role = get_role('administrator');
    
    if ($admin_role) {
        // Add all required capabilities
        $capabilities = [
            'manage_ai_chat',
            'manage_ai_chat_vendors', 
            'manage_ai_chat_requests',
            'manage_ai_chat_settings',
            'view_ai_chat_analytics'
        ];
        
        $added = [];
        foreach ($capabilities as $cap) {
            $admin_role->add_cap($cap);
            $added[] = $cap;
        }
        
        // Also add to editor role (optional)
        $editor_role = get_role('editor');
        if ($editor_role) {
            $editor_role->add_cap('manage_ai_chat_requests');
        }
        
        echo '<div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px;">
                <h3>✅ Capabilities Fixed Successfully!</h3>
                <p>Added the following capabilities to Administrator role:</p>
                <ul>';
        
        foreach ($added as $cap) {
            echo '<li>' . esc_html($cap) . '</li>';
        }
        
        echo '</ul>
                <p><strong>You can now access:</strong></p>
                <ul>
                    <li><a href="' . admin_url('admin.php?page=ai-chat-dashboard') . '">AI Chat Dashboard</a></li>
                    <li><a href="' . admin_url('admin.php?page=ai-chat-settings') . '">AI Chat Settings</a></li>
                    <li><a href="' . admin_url('admin.php?page=ai-chat-documentation') . '">AI Chat Documentation</a></li>
                </ul>
              </div>';
        
        // Delete this file after use for security
        @unlink(__FILE__);
        exit;
    } else {
        echo '<div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px;">
                <h3>❌ Error</h3>
                <p>Could not find administrator role. Please contact support.</p>
              </div>';
    }
}

// Check current capabilities
$current_user = wp_get_current_user();
$admin_role = get_role('administrator');
$has_caps = $admin_role && $admin_role->has_cap('manage_ai_chat');
?>

<!DOCTYPE html>
<html>
<head>
    <title>AI Chat - Capability Fix</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            max-width: 800px; 
            margin: 50px auto; 
            padding: 20px; 
            background: #f1f1f1;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .status { 
            padding: 15px; 
            border-radius: 5px; 
            margin: 20px 0; 
            border-left: 4px solid;
        }
        .status.error { 
            background: #f8d7da; 
            color: #721c24; 
            border-color: #dc3545;
        }
        .status.success { 
            background: #d4edda; 
            color: #155724; 
            border-color: #28a745;
        }
        .status.warning { 
            background: #fff3cd; 
            color: #856404; 
            border-color: #ffc107;
        }
        .button { 
            background: #0073aa; 
            color: white; 
            padding: 12px 24px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            font-size: 16px; 
            text-decoration: none;
            display: inline-block;
        }
        .button:hover { 
            background: #005a87; 
        }
        .button.danger {
            background: #dc3545;
        }
        .button.danger:hover {
            background: #c82333;
        }
        .capability-list {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .capability-list ul {
            margin: 0;
            padding-left: 20px;
        }
        h1 { color: #333; margin-bottom: 10px; }
        h2 { color: #0073aa; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 AI Chat - Capability Fix Tool</h1>
        
        <div class="status <?php echo $has_caps ? 'success' : 'error'; ?>">
            <?php if ($has_caps): ?>
                <h3>✅ Capabilities Already Present</h3>
                <p>Your administrator role already has AI Chat capabilities.</p>
                <p><a href="<?php echo admin_url('admin.php?page=ai-chat-settings'); ?>" class="button">Go to AI Chat Settings</a></p>
            <?php else: ?>
                <h3>❌ Missing Capabilities</h3>
                <p>Your administrator role is missing required AI Chat capabilities.</p>
            <?php endif; ?>
        </div>
        
        <h2>🔍 Current Status</h2>
        <div class="capability-list">
            <strong>User:</strong> <?php echo esc_html($current_user->display_name); ?> (ID: <?php echo $current_user->ID; ?>)<br>
            <strong>Role:</strong> <?php echo implode(', ', $current_user->roles); ?><br>
            <strong>Can manage options:</strong> <?php echo current_user_can('manage_options') ? '✅ Yes' : '❌ No'; ?><br>
            <strong>Has AI Chat caps:</strong> <?php echo $has_caps ? '✅ Yes' : '❌ No'; ?>
        </div>
        
        <?php if (!$has_caps): ?>
        <h2>🛠️ Fix Required Capabilities</h2>
        <p>This will add the following capabilities to the Administrator role:</p>
        <div class="capability-list">
            <ul>
                <li><strong>manage_ai_chat</strong> - Access AI Chat dashboard</li>
                <li><strong>manage_ai_chat_settings</strong> - Configure plugin settings</li>
                <li><strong>manage_ai_chat_vendors</strong> - Manage vendors</li>
                <li><strong>manage_ai_chat_requests</strong> - Handle product requests</li>
                <li><strong>view_ai_chat_analytics</strong> - View statistics</li>
            </ul>
        </div>
        
        <form method="post" onsubmit="return confirm('Add AI Chat capabilities to Administrator role?');">
            <input type="hidden" name="fix_capabilities" value="1">
            <button type="submit" class="button">🔧 Fix Capabilities Now</button>
        </form>
        <?php endif; ?>
        
        <h2>📋 Alternative Solutions</h2>
        <div class="status warning">
            <h4>If the fix above doesn't work:</h4>
            <ol>
                <li><strong>Deactivate and reactivate</strong> the AI Chat plugin</li>
                <li><strong>Add code to functions.php</strong> (see documentation)</li>
                <li><strong>Check WordPress user roles</strong> in Users > All Users</li>
                <li><strong>Contact your hosting provider</strong> if issues persist</li>
            </ol>
        </div>
        
        <div style="margin-top: 30px; padding: 15px; background: #e7f3ff; border-radius: 5px;">
            <h4>🔗 Quick Links:</h4>
            <a href="<?php echo admin_url('plugins.php'); ?>" class="button">« Back to Plugins</a>
            <a href="<?php echo admin_url(); ?>" class="button">WordPress Dashboard</a>
        </div>
    </div>
</body>
</html>