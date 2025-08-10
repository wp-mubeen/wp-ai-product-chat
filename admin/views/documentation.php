<?php
/**
 * Documentation Page Template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>📚 AI Product Chat - Complete Documentation</h1>
    
    <!-- Navigation Tabs -->
    <div class="ai-chat-tabs">
        <button class="ai-chat-tab active" data-tab="quick-start">🚀 Quick Start</button>
        <button class="ai-chat-tab" data-tab="user-guide">👤 User Guide</button>
        <button class="ai-chat-tab" data-tab="shortcodes">🔧 Shortcodes</button>
        <button class="ai-chat-tab" data-tab="api-reference">🔗 API Reference</button>
        <button class="ai-chat-tab" data-tab="customization">🎨 Customization</button>
        <button class="ai-chat-tab" data-tab="troubleshooting">🔍 Troubleshooting</button>
    </div>

    <!-- Quick Start Tab -->
    <div class="ai-chat-tab-content active" id="quick-start">
        <div class="documentation-section">
            <h2>🚀 Quick Start Guide</h2>
            
            <div class="setup-timeline">
                <div class="timeline-step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3>Get OpenAI API Key</h3>
                        <p>Visit <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a> and create an API key.</p>
                        <div class="code-example">
                            <strong>API Key Format:</strong> <code>sk-proj-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx</code>
                        </div>
                    </div>
                </div>
                
                <div class="timeline-step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3>Configure Plugin Settings</h3>
                        <p>Go to <strong>AI Chat > Settings</strong> and enter your API key.</p>
                        <ul>
                            <li>✅ Enable AI Chat</li>
                            <li>🔑 Enter OpenAI API Key</li>
                            <li>🎯 Choose AI Model (GPT-3.5 Turbo recommended)</li>
                            <li>🎨 Customize widget appearance</li>
                        </ul>
                    </div>
                </div>
                
                <div class="timeline-step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3>Set Up Vendors (Optional)</h3>
                        <p>Go to <strong>AI Chat > Vendors</strong> to add vendors who can respond to product requests.</p>
                        <div class="vendor-setup">
                            <strong>Vendor Information:</strong>
                            <ul>
                                <li>Name & Email</li>
                                <li>Company & Contact Details</li>
                                <li>Product Categories</li>
                                <li>Notification Settings</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="timeline-step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h3>Test Your Setup</h3>
                        <p>Visit your frontend and test all chat patterns:</p>
                        <div class="test-checklist">
                            <label><input type="checkbox"> 🔍 "I can't find the product I want"</label>
                            <label><input type="checkbox"> 📦 "Order Support"</label>
                            <label><input type="checkbox"> ⚠️ "Problem with the site"</label>
                            <label><input type="checkbox"> 📷 Image upload functionality</label>
                            <label><input type="checkbox"> 📧 Email notifications</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="success-metrics">
                <h3>🎯 Success Indicators</h3>
                <div class="metrics-grid">
                    <div class="metric">
                        <strong>Chat Widget Appears</strong>
                        <p>Floating chat button visible on frontend</p>
                    </div>
                    <div class="metric">
                        <strong>AI Responses Work</strong>
                        <p>Intelligent replies to customer messages</p>
                    </div>
                    <div class="metric">
                        <strong>Product Search Active</strong>
                        <p>Image analysis and product matching</p>
                    </div>
                    <div class="metric">
                        <strong>Vendor Notifications</strong>
                        <p>Emails sent when products not found</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Guide Tab -->
    <div class="ai-chat-tab-content" id="user-guide">
        <div class="documentation-section">
            <h2>👤 Complete User Guide</h2>
            
            <div class="user-guide-section">
                <h3>🎯 How the Plugin Works</h3>
                <div class="workflow-diagram">
                    <div class="workflow-step">
                        <div class="workflow-icon">👤</div>
                        <h4>Customer Interaction</h4>
                        <p>Customer clicks chat widget and selects one of three patterns</p>
                    </div>
                    <div class="workflow-arrow">→</div>
                    <div class="workflow-step">
                        <div class="workflow-icon">🤖</div>
                        <h4>AI Processing</h4>
                        <p>AI analyzes the request and searches product database</p>
                    </div>
                    <div class="workflow-arrow">→</div>
                    <div class="workflow-step">
                        <div class="workflow-icon">🏪</div>
                        <h4>Vendor Notification</h4>
                        <p>If no products found, relevant vendors are contacted</p>
                    </div>
                </div>
            </div>
            
            <div class="user-guide-section">
                <h3>💬 Chat Patterns Explained</h3>
                
                <div class="pattern-explanation">
                    <h4>🔍 "I can't find the product I want"</h4>
                    <div class="pattern-flow">
                        <div class="flow-step">
                            <strong>Step 1:</strong> Customer chooses photo upload or text description
                        </div>
                        <div class="flow-step">
                            <strong>Step 2:</strong> AI searches your product database
                        </div>
                        <div class="flow-step">
                            <strong>Step 3:</strong> If products found, customer sees results
                        </div>
                        <div class="flow-step">
                            <strong>Step 4:</strong> If no products, AI suggests categories
                        </div>
                        <div class="flow-step">
                            <strong>Step 5:</strong> Customer confirms category
                        </div>
                        <div class="flow-step">
                            <strong>Step 6:</strong> All vendors in that category receive email notifications
                        </div>
                    </div>
                    
                    <div class="example-box">
                        <h5>📷 Image Analysis Example:</h5>
                        <p><strong>Customer uploads:</strong> Photo of wireless headphones</p>
                        <p><strong>AI recognizes:</strong> "Wireless Bluetooth headphones, black color"</p>
                        <p><strong>System searches:</strong> Your WooCommerce/EDD products</p>
                        <p><strong>If not found:</strong> Suggests "Electronics" category</p>
                        <p><strong>Result:</strong> Electronics vendors get notified with image and description</p>
                    </div>
                </div>
                
                <div class="pattern-explanation">
                    <h4>📦 "Order Support"</h4>
                    <div class="order-support-features">
                        <div class="feature">
                            <strong>Order Status Lookup:</strong> AI recognizes order numbers and provides status
                        </div>
                        <div class="feature">
                            <strong>WooCommerce Integration:</strong> Real-time order information display
                        </div>
                        <div class="feature">
                            <strong>Tracking Information:</strong> Shows shipping details when available
                        </div>
                        <div class="feature">
                            <strong>Smart Recognition:</strong> Understands various order number formats
                        </div>
                    </div>
                    
                    <div class="example-box">
                        <h5>📋 Order Support Example:</h5>
                        <p><strong>Customer types:</strong> "Where is my order #1234?"</p>
                        <p><strong>AI extracts:</strong> Order number 1234</p>
                        <p><strong>System checks:</strong> WooCommerce order database</p>
                        <p><strong>Response shows:</strong> Order status, tracking, estimated delivery</p>
                    </div>
                </div>
                
                <div class="pattern-explanation">
                    <h4>⚠️ "Problem with the site"</h4>
                    <div class="problem-categories">
                        <div class="category">
                            <strong>Login Issues:</strong> Password resets, account problems
                        </div>
                        <div class="category">
                            <strong>Payment Problems:</strong> Checkout errors, card issues
                        </div>
                        <div class="category">
                            <strong>Page Errors:</strong> 404s, loading problems
                        </div>
                        <div class="category">
                            <strong>Performance Issues:</strong> Slow loading, timeouts
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="user-guide-section">
                <h3>👥 User Roles & Permissions</h3>
                <div class="roles-table">
                    <div class="role">
                        <h4>👑 Administrator</h4>
                        <ul>
                            <li>✅ Full access to all features</li>
                            <li>✅ Configure settings</li>
                            <li>✅ Manage vendors</li>
                            <li>✅ View analytics</li>
                            <li>✅ Handle requests</li>
                        </ul>
                    </div>
                    <div class="role">
                        <h4>🛒 Shop Manager</h4>
                        <ul>
                            <li>✅ Manage product requests</li>
                            <li>✅ View analytics</li>
                            <li>❌ Configure settings</li>
                            <li>❌ Manage vendors</li>
                        </ul>
                    </div>
                    <div class="role">
                        <h4>🏪 Vendor</h4>
                        <ul>
                            <li>✅ Receive email notifications</li>
                            <li>✅ Respond to product requests</li>
                            <li>✅ Update vendor profile</li>
                            <li>❌ Access admin dashboard</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Shortcodes Tab -->
    <div class="ai-chat-tab-content" id="shortcodes">
        <div class="documentation-section">
            <h2>🔧 Shortcodes Reference</h2>
            
            <div class="shortcode-section">
                <h3>💬 Chat Widget Shortcode</h3>
                <div class="shortcode-example">
                    <div class="shortcode-code">
                        <strong>Basic Usage:</strong>
                        <code>[ai_chat_widget]</code>
                    </div>
                    <div class="shortcode-description">
                        <p>Displays the AI chat widget on any page or post. The widget will appear with your default settings.</p>
                    </div>
                </div>
                
                <div class="shortcode-example">
                    <div class="shortcode-code">
                        <strong>With Custom Parameters:</strong>
                        <code>[ai_chat_widget position="bottom-left" color="#ff6900" auto_open="true"]</code>
                    </div>
                    <div class="shortcode-attributes">
                        <h4>Available Attributes:</h4>
                        <div class="attributes-table">
                            <div class="attribute">
                                <strong>position</strong>
                                <span class="attribute-type">string</span>
                                <span class="attribute-default">bottom-right</span>
                                <div class="attribute-description">
                                    Widget position: bottom-right, bottom-left, middle-right, middle-left
                                </div>
                            </div>
                            <div class="attribute">
                                <strong>color</strong>
                                <span class="attribute-type">string</span>
                                <span class="attribute-default">#667eea</span>
                                <div class="attribute-description">
                                    Primary widget color (hex color code)
                                </div>
                            </div>
                            <div class="attribute">
                                <strong>auto_open</strong>
                                <span class="attribute-type">boolean</span>
                                <span class="attribute-default">false</span>
                                <div class="attribute-description">
                                    Auto-open chat for first-time visitors (true/false)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="usage-examples">
                    <h4>📋 Usage Examples:</h4>
                    <div class="example">
                        <strong>Landing Page:</strong>
                        <code>[ai_chat_widget auto_open="true" color="#28a745"]</code>
                        <p>Perfect for landing pages where you want to immediately engage visitors</p>
                    </div>
                    <div class="example">
                        <strong>Product Pages:</strong>
                        <code>[ai_chat_widget position="middle-right"]</code>
                        <p>Position widget where it's easily accessible without blocking content</p>
                    </div>
                    <div class="example">
                        <strong>Support Page:</strong>
                        <code>[ai_chat_widget color="#dc3545" auto_open="true"]</code>
                        <p>Use attention-grabbing color and auto-open for support pages</p>
                    </div>
                </div>
            </div>
            
            <div class="shortcode-section">
                <h3>📋 Customer Requests Shortcode</h3>
                <div class="shortcode-example">
                    <div class="shortcode-code">
                        <strong>Basic Usage:</strong>
                        <code>[ai_chat_customer_requests]</code>
                    </div>
                    <div class="shortcode-description">
                        <p>Displays a table of customer's product requests. Requires user to be logged in.</p>
                    </div>
                </div>
                
                <div class="shortcode-example">
                    <div class="shortcode-code">
                        <strong>With Parameters:</strong>
                        <code>[ai_chat_customer_requests per_page="5" show_pagination="false"]</code>
                    </div>
                    <div class="shortcode-attributes">
                        <h4>Available Attributes:</h4>
                        <div class="attributes-table">
                            <div class="attribute">
                                <strong>per_page</strong>
                                <span class="attribute-type">integer</span>
                                <span class="attribute-default">10</span>
                                <div class="attribute-description">
                                    Number of requests to show per page
                                </div>
                            </div>
                            <div class="attribute">
                                <strong>show_pagination</strong>
                                <span class="attribute-type">boolean</span>
                                <span class="attribute-default">true</span>
                                <div class="attribute-description">
                                    Show/hide pagination controls (true/false)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="shortcode-section">
                <h3>🏪 Vendor Response Shortcode</h3>
                <div class="shortcode-example">
                    <div class="shortcode-code">
                        <strong>Usage:</strong>
                        <code>[ai_chat_vendor_response]</code>
                    </div>
                    <div class="shortcode-description">
                        <p>Creates a vendor response form. Automatically used on vendor response pages.</p>
                        <div class="note">
                            <strong>Note:</strong> This shortcode is typically added to the auto-generated vendor response page and doesn't need manual implementation.
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="php-functions">
                <h3>🔧 PHP Functions for Developers</h3>
                
                <div class="function-example">
                    <div class="function-code">
                        <strong>Check if chat is enabled:</strong>
                        <pre><code>if (function_exists('ai_chat_is_enabled')) {
    if (ai_chat_is_enabled()) {
        // Chat is enabled
        echo 'AI Chat is active!';
    }
}</code></pre>
                    </div>
                </div>
                
                <div class="function-example">
                    <div class="function-code">
                        <strong>Get chat statistics:</strong>
                        <pre><code>if (function_exists('ai_chat_get_stats')) {
    $stats = ai_chat_get_stats(30); // Last 30 days
    echo 'Total conversations: ' . $stats['total_conversations'];
}</code></pre>
                    </div>
                </div>
                
                <div class="function-example">
                    <div class="function-code">
                        <strong>Add custom chat pattern:</strong>
                        <pre><code>add_filter('ai_chat_patterns', function($patterns) {
    $patterns['custom_support'] = [
        'title' => 'Custom Support',
        'icon' => '🎯',
        'description' => 'Get specialized help'
    ];
    return $patterns;
});</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- API Reference Tab -->
    <div class="ai-chat-tab-content" id="api-reference">
        <div class="documentation-section">
            <h2>🔗 REST API Reference</h2>
            
            <div class="api-intro">
                <p>The AI Product Chat plugin provides a comprehensive REST API for external integrations. All endpoints are available at:</p>
                <div class="api-base-url">
                    <strong>Base URL:</strong> <code><?php echo home_url('/wp-json/ai-chat/v1/'); ?></code>
                </div>
            </div>
            
            <div class="api-endpoint">
                <h3>💬 Conversations Endpoint</h3>
                <div class="endpoint-details">
                    <div class="endpoint-method">GET</div>
                    <div class="endpoint-url">/conversations</div>
                </div>
                <div class="endpoint-description">
                    <p>Retrieve chat conversations with filtering and pagination.</p>
                </div>
                
                <div class="api-example">
                    <h4>Example Request:</h4>
                    <pre><code>GET /wp-json/ai-chat/v1/conversations?per_page=10&context=product-search
Authorization: Bearer YOUR_API_TOKEN</code></pre>
                </div>
                
                <div class="api-parameters">
                    <h4>Query Parameters:</h4>
                    <div class="parameter">
                        <strong>per_page</strong> <span class="param-type">integer</span>
                        <p>Number of conversations per page (default: 20, max: 100)</p>
                    </div>
                    <div class="parameter">
                        <strong>page</strong> <span class="param-type">integer</span>
                        <p>Page number for pagination (default: 1)</p>
                    </div>
                    <div class="parameter">
                        <strong>context</strong> <span class="param-type">string</span>
                        <p>Filter by conversation context (product-search, order-support, site-problem)</p>
                    </div>
                    <div class="parameter">
                        <strong>user_id</strong> <span class="param-type">integer</span>
                        <p>Filter conversations by user ID</p>
                    </div>
                </div>
                
                <div class="api-response">
                    <h4>Example Response:</h4>
                    <pre><code>{
    "data": [
        {
            "id": 123,
            "user_id": 45,
            "user_message": "I'm looking for wireless headphones",
            "ai_response": "I can help you find wireless headphones...",
            "context": "product-search",
            "created_at": "2024-01-15T10:30:00Z"
        }
    ],
    "pagination": {
        "page": 1,
        "per_page": 20,
        "total": 156
    }
}</code></pre>
                </div>
            </div>
            
            <div class="api-endpoint">
                <h3>🔍 Product Requests Endpoint</h3>
                <div class="endpoint-details">
                    <div class="endpoint-method">GET</div>
                    <div class="endpoint-url">/requests</div>
                </div>
                
                <div class="api-example">
                    <h4>Example Request:</h4>
                    <pre><code>GET /wp-json/ai-chat/v1/requests?status=pending&category=electronics</code></pre>
                </div>
                
                <div class="endpoint-details">
                    <div class="endpoint-method">POST</div>
                    <div class="endpoint-url">/requests</div>
                </div>
                
                <div class="api-example">
                    <h4>Create New Request:</h4>
                    <pre><code>POST /wp-json/ai-chat/v1/requests
Content-Type: application/json

{
    "category": "electronics",
    "description": "Looking for noise-cancelling headphones",
    "customer_name": "John Doe",
    "customer_email": "john@example.com",
    "priority": "normal"
}</code></pre>
                </div>
            </div>
            
            <div class="api-endpoint">
                <h3>🏪 Vendors Endpoint</h3>
                <div class="endpoint-details">
                    <div class="endpoint-method">GET</div>
                    <div class="endpoint-url">/vendors</div>
                </div>
                <div class="endpoint-description">
                    <p>Retrieve list of active vendors.</p>
                </div>
            </div>
            
            <div class="api-endpoint">
                <h3>🔍 Product Search Endpoint</h3>
                <div class="endpoint-details">
                    <div class="endpoint-method">POST</div>
                    <div class="endpoint-url">/search</div>
                </div>
                
                <div class="api-example">
                    <h4>Search Products:</h4>
                    <pre><code>POST /wp-json/ai-chat/v1/search
Content-Type: application/json

{
    "query": "wireless bluetooth headphones",
    "type": "text",
    "limit": 10
}</code></pre>
                </div>
            </div>
            
            <div class="api-endpoint">
                <h3>📊 Analytics Endpoints</h3>
                <div class="endpoint-details">
                    <div class="endpoint-method">GET</div>
                    <div class="endpoint-url">/analytics/conversations</div>
                </div>
                <div class="endpoint-details">
                    <div class="endpoint-method">GET</div>
                    <div class="endpoint-url">/analytics/requests</div>
                </div>
                
                <div class="api-example">
                    <h4>Get Analytics:</h4>
                    <pre><code>GET /wp-json/ai-chat/v1/analytics/conversations?days=30</code></pre>
                </div>
            </div>
            
            <div class="api-authentication">
                <h3>🔐 Authentication</h3>
                <p>The API uses WordPress's built-in authentication system. You can use:</p>
                <ul>
                    <li><strong>Application Passwords:</strong> For external applications</li>
                    <li><strong>Cookie Authentication:</strong> For same-origin requests</li>
                    <li><strong>OAuth:</strong> With appropriate plugins</li>
                </ul>
                
                <div class="auth-example">
                    <h4>Using Application Password:</h4>
                    <pre><code>curl -X GET \
  '<?php echo home_url('/wp-json/ai-chat/v1/conversations'); ?>' \
  -H 'Authorization: Basic base64(username:app_password)'</code></pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Customization Tab -->
    <div class="ai-chat-tab-content" id="customization">
        <div class="documentation-section">
            <h2>🎨 Customization Guide</h2>
            
            <div class="customization-section">
                <h3>🎯 Widget Appearance</h3>
                
                <div class="custom-css-section">
                    <h4>Custom CSS Classes</h4>
                    <div class="css-reference">
                        <div class="css-class">
                            <strong>.ai-chat-widget</strong>
                            <p>Main widget container</p>
                        </div>
                        <div class="css-class">
                            <strong>.ai-chat-toggle</strong>
                            <p>Chat toggle button</p>
                        </div>
                        <div class="css-class">
                            <strong>.ai-chat-window</strong>
                            <p>Chat window container</p>
                        </div>
                        <div class="css-class">
                            <strong>.ai-pattern-buttons</strong>
                            <p>Default pattern buttons container</p>
                        </div>
                    </div>
                </div>
                
                <div class="css-examples">
                    <h4>📝 CSS Customization Examples</h4>
                    
                    <div class="css-example">
                        <h5>Change Widget Shadow:</h5>
                        <pre><code>.ai-chat-toggle {
    box-shadow: 0 8px 32px rgba(102, 126, 234, 0.4) !important;
}</code></pre>
                    </div>
                    
                    <div class="css-example">
                        <h5>Custom Animation:</h5>
                        <pre><code>.ai-chat-toggle:hover {
    transform: scale(1.1) rotate(5deg) !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
}</code></pre>
                    </div>
                    
                    <div class="css-example">
                        <h5>Branded Colors:</h5>
                        <pre><code>:root {
    --ai-chat-primary: #your-brand-color;
    --ai-chat-secondary: #your-secondary-color;
}

.ai-chat-toggle {
    background: linear-gradient(135deg, var(--ai-chat-primary), var(--ai-chat-secondary)) !important;
}</code></pre>
                    </div>
                    
                    <div class="css-example">
                        <h5>Mobile Responsiveness:</h5>
                        <pre><code>@media (max-width: 768px) {
    .ai-chat-widget {
        bottom: 10px !important;
        right: 10px !important;
    }
    
    .ai-chat-window {
        width: calc(100vw - 20px) !important;
        height: calc(100vh - 100px) !important;
    }
}</code></pre>
                    </div>
                </div>
            </div>
            
            <div class="customization-section">
                <h3>🔧 WordPress Hooks & Filters</h3>
                
                <div class="hook-example">
                    <h4>Modify Chat Patterns</h4>
                    <pre><code>// Add custom chat pattern
add_filter('ai_chat_default_patterns', function($patterns) {
    $patterns[] = [
        'id' => 'custom-billing',
        'title' => 'Billing Questions',
        'icon' => '💳',
        'description' => 'Questions about billing and payments'
    ];
    return $patterns;
});

// Remove default pattern
add_filter('ai_chat_default_patterns', function($patterns) {
    return array_filter($patterns, function($pattern) {
        return $pattern['id'] !== 'site-problem';
    });
});</code></pre>
                </div>
                
                <div class="hook-example">
                    <h4>Customize AI Responses</h4>
                    <pre><code>// Filter AI responses before sending
add_filter('ai_chat_ai_response', function($response, $message, $context) {
    if ($context === 'product-search') {
        $response .= "\n\nNeed more help? Contact our sales team!";
    }
    return $response;
}, 10, 3);

// Add custom system prompt
add_filter('ai_chat_system_prompt', function($prompt, $context) {
    if ($context === 'order-support') {
        return $prompt . " Always ask for order number if not provided.";
    }
    return $prompt;
}, 10, 2);</code></pre>
                </div>
                
                <div class="hook-example">
                    <h4>Custom Vendor Selection</h4>
                    <pre><code>// Filter vendors based on custom logic
add_filter('ai_chat_vendors_for_category', function($vendors, $category) {
    // Only notify premium vendors for high-value categories
    if ($category === 'luxury-items') {
        return array_filter($vendors, function($vendor) {
            return $vendor['premium'] === true;
        });
    }
    return $vendors;
}, 10, 2);</code></pre>
                </div>
                
                <div class="hook-example">
                    <h4>Custom Email Templates</h4>
                    <pre><code>// Override email template path
add_filter('ai_chat_email_template_path', function($path, $template_name) {
    $custom_path = get_template_directory() . '/ai-chat-emails/' . $template_name . '.php';
    if (file_exists($custom_path)) {
        return $custom_path;
    }
    return $path;
}, 10, 2);</code></pre>
                </div>
            </div>
            
            <div class="customization-section">
                <h3>🌍 Multi-language Support</h3>
                
                <div class="translation-info">
                    <h4>Translation Files</h4>
                    <p>The plugin is translation-ready. Create language files in:</p>
                    <code>/wp-content/languages/plugins/wp-ai-product-chat-{locale}.po</code>
                </div>
                
                <div class="translation-example">
                    <h4>Custom Translations</h4>
                    <pre><code>// Override specific translations
add_filter('gettext', function($translation, $text, $domain) {
    if ($domain === 'wp-ai-product-chat') {
        switch ($text) {
            case 'Hello! How can I help you today?':
                return __('¡Hola! ¿Cómo puedo ayudarte hoy?', 'your-theme');
            case "I can't find the product I want":
                return __('No puedo encontrar el producto que quiero', 'your-theme');
        }
    }
    return $translation;
}, 10, 3);</code></pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Troubleshooting Tab -->
    <div class="ai-chat-tab-content" id="troubleshooting">
        <div class="documentation-section">
            <h2>🔍 Troubleshooting Guide</h2>
            
            <div class="troubleshooting-section">
                <h3>❓ Common Issues & Solutions</h3>
                
                <div class="issue-solution">
                    <div class="issue">
                        <h4>🚫 Chat Widget Not Showing</h4>
                        <div class="issue-symptoms">
                            <strong>Symptoms:</strong> No chat button visible on frontend
                        </div>
                    </div>
                    <div class="solution">
                        <h5>✅ Solutions:</h5>
                        <ol>
                            <li><strong>Check Plugin Status:</strong> Ensure "Enable AI Chat" is checked in Settings</li>
                            <li><strong>Clear Cache:</strong> Clear any caching plugins and browser cache</li>
                            <li><strong>Theme Conflicts:</strong> Switch to default WordPress theme temporarily</li>
                            <li><strong>JavaScript Errors:</strong> Check browser console for errors</li>
                            <li><strong>Page Restrictions:</strong> Verify widget display settings</li>
                        </ol>
                        <div class="debug-code">
                            <strong>Debug Code:</strong>
                            <pre><code>// Add to functions.php temporarily
add_action('wp_footer', function() {
    if (function_exists('ai_chat_is_enabled')) {
        echo '<!-- AI Chat Debug: ' . (ai_chat_is_enabled() ? 'Enabled' : 'Disabled') . ' -->';
    }
});</code></pre>
                        </div>
                    </div>
                </div>
                
                <div class="issue-solution">
                    <div class="issue">
                        <h4>🤖 AI Not Responding</h4>
                        <div class="issue-symptoms">
                            <strong>Symptoms:</strong> Chat widget works but AI gives error messages
                        </div>
                    </div>
                    <div class="solution">
                        <h5>✅ Solutions:</h5>
                        <ol>
                            <li><strong>API Key:</strong> Verify OpenAI API key is correct and active</li>
                            <li><strong>API Credits:</strong> Check OpenAI account has sufficient credits</li>
                            <li><strong>Rate Limits:</strong> Ensure you're not hitting API rate limits</li>
                            <li><strong>Model Access:</strong> Verify your API key has access to selected model</li>
                            <li><strong>Network Issues:</strong> Check server can reach OpenAI API</li>
                        </ol>
                        <div class="test-api">
                            <strong>Test API Connection:</strong>
                            <p>Go to AI Chat > Settings and click "Test Connection" button</p>
                        </div>
                    </div>
                </div>
                
                <div class="issue-solution">
                    <div class="issue">
                        <h4>📧 Emails Not Sending</h4>
                        <div class="issue-symptoms">
                            <strong>Symptoms:</strong> Vendors not receiving notification emails
                        </div>
                    </div>
                    <div class="solution">
                        <h5>✅ Solutions:</h5>
                        <ol>
                            <li><strong>WordPress Mail:</strong> Test WordPress email functionality</li>
                            <li><strong>SMTP Setup:</strong> Configure SMTP plugin for reliable delivery</li>
                            <li><strong>Spam Folders:</strong> Check vendor spam/junk folders</li>
                            <li><strong>Email Settings:</strong> Verify notification settings are enabled</li>
                            <li><strong>Vendor Status:</strong> Ensure vendors are set to active</li>
                        </ol>
                        <div class="email-test">
                            <strong>Email Test:</strong>
                            <pre><code>// Test WordPress email
wp_mail('test@example.com', 'Test Subject', 'Test Message');</code></pre>
                        </div>
                    </div>
                </div>
                
                <div class="issue-solution">
                    <div class="issue">
                        <h4>🔍 Product Search Not Working</h4>
                        <div class="issue-symptoms">
                            <strong>Symptoms:</strong> No products found even when they exist
                        </div>
                    </div>
                    <div class="solution">
                        <h5>✅ Solutions:</h5>
                        <ol>
                            <li><strong>Product Visibility:</strong> Check WooCommerce product visibility settings</li>
                            <li><strong>Search Index:</strong> Products must be published and searchable</li>
                            <li><strong>Database Tables:</strong> Ensure plugin tables were created correctly</li>
                            <li><strong>Search Terms:</strong> Try simpler, more common product names</li>
                            <li><strong>Plugin Conflicts:</strong> Deactivate other search-related plugins</li>
                        </ol>
                    </div>
                </div>
            </div>
            
            <div class="troubleshooting-section">
                <h3>🔧 Debug Mode</h3>
                
                <div class="debug-info">
                    <p>Enable debug mode in AI Chat > Settings to get detailed error information:</p>
                    <ol>
                        <li>Go to <strong>AI Chat > Settings</strong></li>
                        <li>Check <strong>"Enable debug logging"</strong></li>
                        <li>Save settings</li>
                        <li>Reproduce the issue</li>
                        <li>Check WordPress error logs for detailed information</li>
                    </ol>
                </div>
                
                <div class="debug-locations">
                    <h4>📁 Log File Locations:</h4>
                    <ul>
                        <li><strong>WordPress Debug Log:</strong> <code>/wp-content/debug.log</code></li>
                        <li><strong>Server Error Log:</strong> <code>/var/log/apache2/error.log</code> or <code>/var/log/nginx/error.log</code></li>
                        <li><strong>Plugin Logs:</strong> <code>/wp-content/uploads/ai-chat/logs/</code></li>
                    </ul>
                </div>
            </div>
            
            <div class="troubleshooting-section">
                <h3>📊 System Requirements Check</h3>
                
                <div class="requirements-check">
                    <?php
                    $requirements = [
                        'WordPress Version' => [
                            'required' => '5.0',
                            'current' => get_bloginfo('version'),
                            'check' => version_compare(get_bloginfo('version'), '5.0', '>=')
                        ],
                        'PHP Version' => [
                            'required' => '7.4',
                            'current' => PHP_VERSION,
                            'check' => version_compare(PHP_VERSION, '7.4', '>=')
                        ],
                        'cURL Extension' => [
                            'required' => 'Enabled',
                            'current' => extension_loaded('curl') ? 'Enabled' : 'Disabled',
                            'check' => extension_loaded('curl')
                        ],
                        'JSON Extension' => [
                            'required' => 'Enabled',
                            'current' => extension_loaded('json') ? 'Enabled' : 'Disabled',
                            'check' => extension_loaded('json')
                        ],
                        'OpenSSL Extension' => [
                            'required' => 'Enabled',
                            'current' => extension_loaded('openssl') ? 'Enabled' : 'Disabled',
                            'check' => extension_loaded('openssl')
                        ]
                    ];
                    
                    foreach ($requirements as $name => $req):
                        $status_class = $req['check'] ? 'requirement-pass' : 'requirement-fail';
                        $status_icon = $req['check'] ? '✅' : '❌';
                    ?>
                    <div class="requirement-item <?php echo $status_class; ?>">
                        <div class="requirement-name">
                            <?php echo $status_icon; ?> <?php echo $name; ?>
                        </div>
                        <div class="requirement-details">
                            <strong>Required:</strong> <?php echo $req['required']; ?> | 
                            <strong>Current:</strong> <?php echo $req['current']; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="troubleshooting-section">
                <h3>🆘 Getting Support</h3>
                
                <div class="support-options">
                    <div class="support-option">
                        <h4>📚 Documentation</h4>
                        <p>Check this comprehensive documentation first - most issues are covered here.</p>
                    </div>
                    
                    <div class="support-option">
                        <h4>💬 Community Forum</h4>
                        <p>Join our community forum to get help from other users and developers.</p>
                    </div>
                    
                    <div class="support-option">
                        <h4>🐛 Bug Reports</h4>
                        <p>Found a bug? Report it on our GitHub repository with detailed information.</p>
                    </div>
                    
                    <div class="support-option">
                        <h4>💼 Premium Support</h4>
                        <p>Need priority support? Consider our premium support package for immediate assistance.</p>
                    </div>
                </div>
                
                <div class="support-info">
                    <h4>📋 Information to Include in Support Requests:</h4>
                    <ul>
                        <li>WordPress version and active theme</li>
                        <li>List of active plugins</li>
                        <li>PHP version and server information</li>
                        <li>Error messages or screenshots</li>
                        <li>Steps to reproduce the issue</li>
                        <li>Debug log excerpts (if applicable)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Documentation Styles */
.ai-chat-tabs {
    display: flex;
    border-bottom: 2px solid #f1f1f1;
    margin: 20px 0 0 0;
    flex-wrap: wrap;
}

.ai-chat-tab {
    padding: 12px 20px;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    color: #666;
    border-bottom: 2px solid transparent;
    transition: all 0.2s ease;
    flex: 1;
    min-width: 150px;
}

.ai-chat-tab:hover {
    color: #0073aa;
    background: #f9f9f9;
}

.ai-chat-tab.active {
    color: #0073aa;
    border-bottom-color: #0073aa;
}

.ai-chat-tab-content {
    display: none;
    padding: 30px 0;
}

.ai-chat-tab-content.active {
    display: block;
}

.documentation-section {
    max-width: 1000px;
}

/* Timeline Styles */
.setup-timeline {
    position: relative;
    margin: 30px 0;
}

.timeline-step {
    display: flex;
    align-items: flex-start;
    margin-bottom: 40px;
    position: relative;
}

.timeline-step:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 25px;
    top: 50px;
    width: 2px;
    height: 40px;
    background: #ddd;
}

.step-number {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 18px;
    margin-right: 25px;
    flex-shrink: 0;
}

.step-content {
    flex: 1;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #667eea;
}

.step-content h3 {
    margin: 0 0 10px 0;
    color: #333;
}

.step-content ul, .step-content ol {
    margin: 10px 0;
    padding-left: 20px;
}

/* Code Examples */
.code-example {
    background: #f1f1f1;
    padding: 15px;
    border-radius: 5px;
    margin: 10px 0;
    font-family: 'Courier New', monospace;
    border-left: 4px solid #0073aa;
}

/* Success Metrics Grid */
.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.metric {
    background: #fff;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.metric strong {
    display: block;
    color: #0073aa;
    margin-bottom: 10px;
    font-size: 16px;
}

/* Workflow Diagram */
.workflow-diagram {
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 30px 0;
    flex-wrap: wrap;
    gap: 20px;
}

.workflow-step {
    text-align: center;
    max-width: 200px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 2px solid #e9ecef;
}

.workflow-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.workflow-step h4 {
    margin: 0 0 10px 0;
    color: #333;
}

.workflow-arrow {
    font-size: 24px;
    color: #0073aa;
    font-weight: bold;
}

/* API Documentation */
.api-base-url {
    background: #e1f5fe;
    padding: 15px;
    border-radius: 5px;
    margin: 15px 0;
    font-family: monospace;
}

.api-endpoint {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin: 20px 0;
    overflow: hidden;
}

.endpoint-details {
    display: flex;
    align-items: center;
    background: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #ddd;
}

.endpoint-method {
    background: #28a745;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: bold;
    font-size: 12px;
    margin-right: 15px;
}

.endpoint-url {
    font-family: monospace;
    font-size: 16px;
    color: #333;
}

.endpoint-description {
    padding: 15px 20px;
}

.api-example {
    background: #f8f9fa;
    padding: 15px 20px;
    border-top: 1px solid #ddd;
}

.api-example pre {
    background: #2d3748;
    color: #e2e8f0;
    padding: 15px;
    border-radius: 5px;
    overflow-x: auto;
    margin: 10px 0;
}

.api-parameters {
    padding: 15px 20px;
    border-top: 1px solid #ddd;
}

.parameter {
    margin: 15px 0;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.parameter:last-child {
    border-bottom: none;
}

.param-type {
    background: #e1f5fe;
    color: #01579b;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 12px;
    margin-left: 10px;
}

/* Shortcode Documentation */
.shortcode-section {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin: 20px 0;
    overflow: hidden;
}

.shortcode-example {
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.shortcode-example:last-child {
    border-bottom: none;
}

.shortcode-code {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    font-family: monospace;
    margin: 10px 0;
}

.shortcode-code code {
    background: #e9ecef;
    padding: 2px 6px;
    border-radius: 3px;
    font-weight: bold;
}

.attributes-table {
    margin: 15px 0;
}

.attribute {
    display: grid;
    grid-template-columns: 150px 80px 100px 1fr;
    gap: 15px;
    align-items: start;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.attribute:last-child {
    border-bottom: none;
}

.attribute-type {
    background: #ffeaa7;
    color: #2d3436;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 11px;
    text-align: center;
}

.attribute-default {
    background: #ddd6fe;
    color: #5b21b6;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 11px;
    text-align: center;
    font-family: monospace;
}

/* Troubleshooting */
.issue-solution {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin: 20px 0;
    overflow: hidden;
}

.issue {
    background: #fff3cd;
    padding: 20px;
    border-bottom: 1px solid #ddd;
}

.issue h4 {
    margin: 0 0 10px 0;
    color: #856404;
}

.issue-symptoms {
    font-size: 14px;
    color: #666;
}

.solution {
    padding: 20px;
}

.solution h5 {
    color: #28a745;
    margin: 0 0 15px 0;
}

.solution ol {
    margin: 0 0 15px 0;
    padding-left: 20px;
}

.debug-code, .email-test {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-top: 15px;
}

.debug-code pre, .email-test pre {
    background: #2d3748;
    color: #e2e8f0;
    padding: 10px;
    border-radius: 4px;
    overflow-x: auto;
}

/* Requirements Check */
.requirement-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    margin: 10px 0;
    border-radius: 5px;
    border: 1px solid #ddd;
}

.requirement-pass {
    background: #d4edda;
    border-color: #c3e6cb;
}

.requirement-fail {
    background: #f8d7da;
    border-color: #f5c6cb;
}

.requirement-name {
    font-weight: bold;
}

.requirement-details {
    font-size: 14px;
    color: #666;
}

/* Support Options */
.support-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.support-option {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #0073aa;
}

.support-option h4 {
    margin: 0 0 10px 0;
    color: #0073aa;
}

/* Responsive Design */
@media (max-width: 768px) {
    .workflow-diagram {
        flex-direction: column;
    }
    
    .workflow-arrow {
        transform: rotate(90deg);
    }
    
    .metrics-grid {
        grid-template-columns: 1fr;
    }
    
    .attribute {
        grid-template-columns: 1fr;
        gap: 5px;
    }
    
    .ai-chat-tabs {
        flex-direction: column;
    }
    
    .ai-chat-tab {
        flex: none;
    }
}

/* Test Checklist */
.test-checklist {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin: 15px 0;
}

.test-checklist label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    padding: 8px;
    border-radius: 4px;
    transition: background 0.2s;
}

.test-checklist label:hover {
    background: #f0f8ff;
}

.test-checklist input[type="checkbox"] {
    margin: 0;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Tab switching functionality
    $('.ai-chat-tab').click(function() {
        var tabId = $(this).data('tab');
        
        // Remove active class from all tabs and contents
        $('.ai-chat-tab').removeClass('active');
        $('.ai-chat-tab-content').removeClass('active');
        
        // Add active class to clicked tab and corresponding content
        $(this).addClass('active');
        $('#' + tabId).addClass('active');
    });
    
    // Checklist functionality
    $('.test-checklist input[type="checkbox"]').change(function() {
        var checkedCount = $('.test-checklist input[type="checkbox"]:checked').length;
        var totalCount = $('.test-checklist input[type="checkbox"]').length;
        
        if (checkedCount === totalCount) {
            alert('🎉 Congratulations! You\'ve completed all setup tests. Your AI Chat is ready to go!');
        }
    });
    
    // Copy code functionality
    $(document).on('click', 'pre code, .code-example', function() {
        var text = $(this).text();
        navigator.clipboard.writeText(text).then(function() {
            // Show temporary tooltip or notification
            $('<div class="copy-notification">Copied to clipboard!</div>')
                .appendTo('body')
                .fadeIn()
                .delay(2000)
                .fadeOut(function() { $(this).remove(); });
        });
    });
});
</script>