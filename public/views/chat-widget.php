<?php
/**
 * Chat Widget Template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="ai-chat-widget" class="ai-chat-widget">
    <!-- Chat Toggle Button -->
    <div id="ai-chat-toggle" class="ai-chat-toggle">
        <span class="ai-chat-icon">💬</span>
        <span class="ai-chat-notification" id="ai-chat-notification" style="display: none;">1</span>
    </div>

    <!-- Chat Window -->
    <div id="ai-chat-window" class="ai-chat-window" style="display: none;">
        <!-- Chat Header -->
        <div class="ai-chat-header">
            <h3><?php _e('AI Assistant', 'wp-ai-product-chat'); ?></h3>
            <button id="ai-chat-minimize" class="ai-chat-minimize">−</button>
            <button id="ai-chat-close" class="ai-chat-close">×</button>
        </div>

        <!-- Chat Messages -->
        <div id="ai-chat-messages" class="ai-chat-messages">
            <!-- Welcome Message -->
            <div class="ai-message">
                <div class="ai-message-content">
                    <p><?php _e('Hello! How can I help you today?', 'wp-ai-product-chat'); ?></p>
                    
                    <!-- Default Pattern Buttons -->
                    <div class="ai-pattern-buttons">
                        <button class="ai-pattern-btn" data-pattern="product-search">
                            🔍 <?php _e("I can't find the product I want", 'wp-ai-product-chat'); ?>
                        </button>
                        <button class="ai-pattern-btn" data-pattern="order-support">
                            📦 <?php _e('Order Support', 'wp-ai-product-chat'); ?>
                        </button>
                        <button class="ai-pattern-btn" data-pattern="site-problem">
                            ⚠️ <?php _e('Problem with the site', 'wp-ai-product-chat'); ?>
                        </button>
                    </div>
                </div>
                <div class="ai-message-time"><?php echo current_time('H:i'); ?></div>
            </div>
        </div>

        <!-- Chat Input Area -->
        <div class="ai-chat-input-area">
            <!-- Image Upload Section -->
            <div id="ai-image-upload-section" class="ai-image-upload-section" style="display: none;">
                <div class="ai-upload-info">
                    <p><?php _e('Upload an image of the product you\'re looking for:', 'wp-ai-product-chat'); ?></p>
                </div>
                <div class="ai-upload-area">
                    <input type="file" id="ai-image-input" accept="image/*" style="display: none;">
                    <div id="ai-upload-drop" class="ai-upload-drop">
                        <span class="ai-upload-icon">📷</span>
                        <p><?php _e('Click to upload or drag and drop an image', 'wp-ai-product-chat'); ?></p>
                    </div>
                    <div id="ai-image-preview" class="ai-image-preview" style="display: none;">
                        <img id="ai-preview-img" src="" alt="Preview">
                        <button id="ai-remove-image" class="ai-remove-image">×</button>
                    </div>
                </div>
                <div class="ai-upload-actions">
                    <button id="ai-cancel-upload" class="ai-btn ai-btn-secondary">
                        <?php _e('Cancel', 'wp-ai-product-chat'); ?>
                    </button>
                    <button id="ai-send-image" class="ai-btn ai-btn-primary" disabled>
                        <?php _e('Analyze Image', 'wp-ai-product-chat'); ?>
                    </button>
                </div>
            </div>

            <!-- Text Input -->
            <div id="ai-text-input-section" class="ai-text-input-section">
                <div class="ai-input-group">
                    <textarea id="ai-message-input" class="ai-message-input" 
                              placeholder="<?php _e('Type your message...', 'wp-ai-product-chat'); ?>" 
                              rows="1"></textarea>
                    <div class="ai-input-actions">
                        <button id="ai-attach-image" class="ai-attach-btn" title="<?php _e('Attach Image', 'wp-ai-product-chat'); ?>">
                            📷
                        </button>
                        <button id="ai-send-message" class="ai-send-btn" title="<?php _e('Send Message', 'wp-ai-product-chat'); ?>">
                            ➤
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div id="ai-loading" class="ai-loading" style="display: none;">
                <div class="ai-typing-indicator">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <p><?php _e('AI is thinking...', 'wp-ai-product-chat'); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Product Confirmation Modal -->
<div id="ai-product-modal" class="ai-modal" style="display: none;">
    <div class="ai-modal-content">
        <div class="ai-modal-header">
            <h3><?php _e('Product Found', 'wp-ai-product-chat'); ?></h3>
            <button class="ai-modal-close">×</button>
        </div>
        <div class="ai-modal-body">
            <div id="ai-product-results"></div>
            <div class="ai-confirmation-question">
                <p><?php _e('Did you find the product you were looking for?', 'wp-ai-product-chat'); ?></p>
                <div class="ai-confirmation-buttons">
                    <button id="ai-confirm-yes" class="ai-btn ai-btn-success">
                        <?php _e('Yes', 'wp-ai-product-chat'); ?>
                    </button>
                    <button id="ai-confirm-no" class="ai-btn ai-btn-warning">
                        <?php _e('No', 'wp-ai-product-chat'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Selection Modal -->
<div id="ai-category-modal" class="ai-modal" style="display: none;">
    <div class="ai-modal-content">
        <div class="ai-modal-header">
            <h3><?php _e('Select Product Category', 'wp-ai-product-chat'); ?></h3>
        </div>
        <div class="ai-modal-body">
            <p><?php _e('Please confirm or select the correct product category:', 'wp-ai-product-chat'); ?></p>
            <div id="ai-category-suggestions"></div>
            <div class="ai-category-input">
                <label for="ai-custom-category"><?php _e('Or type your own category:', 'wp-ai-product-chat'); ?></label>
                <input type="text" id="ai-custom-category" class="ai-input" placeholder="<?php _e('Enter category name...', 'wp-ai-product-chat'); ?>">
            </div>
            <div class="ai-category-actions">
                <button id="ai-category-cancel" class="ai-btn ai-btn-secondary">
                    <?php _e('Cancel', 'wp-ai-product-chat'); ?>
                </button>
                <button id="ai-category-confirm" class="ai-btn ai-btn-primary">
                    <?php _e('Send to Vendors', 'wp-ai-product-chat'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
// Pass PHP variables to JavaScript
window.aiChatData = {
    ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
    nonce: '<?php echo wp_create_nonce('ai_chat_nonce'); ?>',
    userId: <?php echo get_current_user_id(); ?>,
    isLoggedIn: <?php echo is_user_logged_in() ? 'true' : 'false'; ?>,
    strings: {
        uploadError: '<?php _e('Error uploading image. Please try again.', 'wp-ai-product-chat'); ?>',
        networkError: '<?php _e('Network error. Please check your connection.', 'wp-ai-product-chat'); ?>',
        processingImage: '<?php _e('Analyzing image...', 'wp-ai-product-chat'); ?>',
        searchingProducts: '<?php _e('Searching for products...', 'wp-ai-product-chat'); ?>',
        contactingVendors: '<?php _e('Contacting vendors...', 'wp-ai-product-chat'); ?>'
    }
};
</script>