<?php
/**
 * REST API Handler Class
 * Provides REST API endpoints for external integrations
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_AI_Chat_Rest_API {
    
    /**
     * API namespace
     */
    private $namespace = 'ai-chat/v1';
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Conversations endpoints
        register_rest_route($this->namespace, '/conversations', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_conversations'],
                'permission_callback' => [$this, 'check_admin_permissions']
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'create_conversation'],
                'permission_callback' => [$this, 'check_user_permissions'],
                'args' => $this->get_conversation_args()
            ]
        ]);
        
        register_rest_route($this->namespace, '/conversations/(?P<id>\d+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_conversation'],
                'permission_callback' => [$this, 'check_conversation_permissions']
            ],
            [
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => [$this, 'delete_conversation'],
                'permission_callback' => [$this, 'check_admin_permissions']
            ]
        ]);
        
        // Requests endpoints
        register_rest_route($this->namespace, '/requests', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_requests'],
                'permission_callback' => [$this, 'check_requests_permissions']
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'create_request'],
                'permission_callback' => [$this, 'check_user_permissions'],
                'args' => $this->get_request_args()
            ]
        ]);
        
        register_rest_route($this->namespace, '/requests/(?P<id>\d+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_request'],
                'permission_callback' => [$this, 'check_request_permissions']
            ],
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_request'],
                'permission_callback' => [$this, 'check_request_edit_permissions'],
                'args' => $this->get_request_update_args()
            ]
        ]);
        
        // Vendors endpoints
        register_rest_route($this->namespace, '/vendors', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_vendors'],
                'permission_callback' => [$this, 'check_vendor_permissions']
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'create_vendor'],
                'permission_callback' => [$this, 'check_admin_permissions'],
                'args' => $this->get_vendor_args()
            ]
        ]);
        
        register_rest_route($this->namespace, '/vendors/(?P<id>\d+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_vendor'],
                'permission_callback' => [$this, 'check_vendor_permissions']
            ],
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_vendor'],
                'permission_callback' => [$this, 'check_admin_permissions']
            ]
        ]);
        
        // Analytics endpoints
        register_rest_route($this->namespace, '/analytics/conversations', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_conversation_analytics'],
            'permission_callback' => [$this, 'check_analytics_permissions']
        ]);
        
        register_rest_route($this->namespace, '/analytics/requests', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_request_analytics'],
            'permission_callback' => [$this, 'check_analytics_permissions']
        ]);
        
        // Product search endpoint
        register_rest_route($this->namespace, '/search', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'search_products'],
            'permission_callback' => [$this, 'check_search_permissions'],
            'args' => [
                'query' => [
                    'required' => true,
                    'type' => 'string',
                    'description' => 'Search query'
                ],
                'type' => [
                    'default' => 'text',
                    'enum' => ['text', 'image'],
                    'description' => 'Search type'
                ]
            ]
        ]);
        
        // Webhook endpoints
        register_rest_route($this->namespace, '/webhooks/vendor-response', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'handle_vendor_response_webhook'],
            'permission_callback' => [$this, 'check_webhook_permissions']
        ]);
        
        // Health check endpoint
        register_rest_route($this->namespace, '/health', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'health_check'],
            'permission_callback' => '__return_true'
        ]);
    }
    
    /**
     * Get conversations
     */
    public function get_conversations($request) {
        $params = $request->get_params();
        
        $args = [
            'limit' => isset($params['per_page']) ? intval($params['per_page']) : 20,
            'offset' => isset($params['page']) ? (intval($params['page']) - 1) * 20 : 0,
            'user_id' => isset($params['user_id']) ? intval($params['user_id']) : 0,
            'context' => isset($params['context']) ? sanitize_text_field($params['context']) : '',
            'date_from' => isset($params['date_from']) ? sanitize_text_field($params['date_from']) : '',
            'date_to' => isset($params['date_to']) ? sanitize_text_field($params['date_to']) : ''
        ];
        
        $db_manager = new WP_AI_Chat_Database_Manager();
        $conversations = $db_manager->get_conversations($args);
        
        return new WP_REST_Response([
            'data' => $conversations,
            'pagination' => [
                'page' => intval($params['page'] ?? 1),
                'per_page' => $args['limit'],
                'total' => $this->get_conversations_total($args)
            ]
        ], 200);
    }
    
    /**
     * Create conversation
     */
    public function create_conversation($request) {
        $params = $request->get_params();
        
        $db_manager = new WP_AI_Chat_Database_Manager();
        
        $conversation_id = $db_manager->insert_conversation([
            'user_id' => get_current_user_id(),
            'session_id' => sanitize_text_field($params['session_id'] ?? wp_generate_uuid4()),
            'user_message' => sanitize_textarea_field($params['user_message']),
            'ai_response' => sanitize_textarea_field($params['ai_response']),
            'context' => sanitize_text_field($params['context'] ?? 'general'),
            'ip_address' => $request->get_header('X-Forwarded-For') ?: $_SERVER['REMOTE_ADDR'],
            'user_agent' => $request->get_header('User-Agent')
        ]);
        
        if ($conversation_id) {
            return new WP_REST_Response([
                'id' => $conversation_id,
                'message' => 'Conversation created successfully'
            ], 201);
        }
        
        return new WP_Error('creation_failed', 'Failed to create conversation', ['status' => 500]);
    }
    
    /**
     * Get single conversation
     */
    public function get_conversation($request) {
        $id = $request['id'];
        
        $db_manager = new WP_AI_Chat_Database_Manager();
        $conversation = $db_manager->get_conversation($id);
        
        if (!$conversation) {
            return new WP_Error('not_found', 'Conversation not found', ['status' => 404]);
        }
        
        return new WP_REST_Response($conversation, 200);
    }
    
    /**
     * Get requests
     */
    public function get_requests($request) {
        $params = $request->get_params();
        
        $request_manager = new WP_AI_Chat_Request_Manager();
        
        $args = [
            'status' => isset($params['status']) ? sanitize_text_field($params['status']) : '',
            'category' => isset($params['category']) ? sanitize_text_field($params['category']) : '',
            'user_id' => isset($params['user_id']) ? intval($params['user_id']) : 0,
            'limit' => isset($params['per_page']) ? intval($params['per_page']) : 20,
            'offset' => isset($params['page']) ? (intval($params['page']) - 1) * 20 : 0
        ];
        
        // If not admin, only show user's own requests
        if (!current_user_can('manage_ai_chat_requests')) {
            $args['user_id'] = get_current_user_id();
        }
        
        $requests = $request_manager->get_requests($args);
        
        return new WP_REST_Response([
            'data' => $requests,
            'pagination' => [
                'page' => intval($params['page'] ?? 1),
                'per_page' => $args['limit'],
                'total' => $this->get_requests_total($args)
            ]
        ], 200);
    }
    
    /**
     * Create request
     */
    public function create_request($request) {
        $params = $request->get_params();
        
        $request_manager = new WP_AI_Chat_Request_Manager();
        
        try {
            $request_id = $request_manager->create_request([
                'user_id' => get_current_user_id(),
                'category' => sanitize_text_field($params['category']),
                'description' => sanitize_textarea_field($params['description']),
                'priority' => sanitize_text_field($params['priority'] ?? 'normal'),
                'customer_name' => sanitize_text_field($params['customer_name'] ?? ''),
                'customer_email' => sanitize_email($params['customer_email'] ?? ''),
                'customer_phone' => sanitize_text_field($params['customer_phone'] ?? ''),
                'image_url' => esc_url_raw($params['image_url'] ?? '')
            ]);
            
            return new WP_REST_Response([
                'id' => $request_id,
                'message' => 'Request created successfully'
            ], 201);
            
        } catch (Exception $e) {
            return new WP_Error('creation_failed', $e->getMessage(), ['status' => 400]);
        }
    }
    
    /**
     * Get single request
     */
    public function get_request($request) {
        $id = $request['id'];
        
        $request_manager = new WP_AI_Chat_Request_Manager();
        $product_request = $request_manager->get_request($id);
        
        if (!$product_request) {
            return new WP_Error('not_found', 'Request not found', ['status' => 404]);
        }
        
        return new WP_REST_Response($product_request, 200);
    }
    
    /**
     * Update request
     */
    public function update_request($request) {
        $id = $request['id'];
        $params = $request->get_params();
        
        $request_manager = new WP_AI_Chat_Request_Manager();
        
        $update_data = [];
        
        if (isset($params['status'])) {
            $update_data['status'] = sanitize_text_field($params['status']);
        }
        
        if (isset($params['priority'])) {
            $update_data['priority'] = sanitize_text_field($params['priority']);
        }
        
        if (isset($params['notes'])) {
            $update_data['notes'] = sanitize_textarea_field($params['notes']);
        }
        
        try {
            $result = $request_manager->update_request($id, $update_data);
            
            if ($result) {
                return new WP_REST_Response([
                    'message' => 'Request updated successfully'
                ], 200);
            } else {
                return new WP_Error('update_failed', 'Failed to update request', ['status' => 500]);
            }
            
        } catch (Exception $e) {
            return new WP_Error('update_error', $e->getMessage(), ['status' => 400]);
        }
    }
    
    /**
     * Get vendors
     */
    public function get_vendors($request) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_vendors';
        $vendors = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 'active'", ARRAY_A);
        
        return new WP_REST_Response($vendors, 200);
    }
    
    /**
     * Create vendor
     */
    public function create_vendor($request) {
        $params = $request->get_params();
        
        $db_manager = new WP_AI_Chat_Database_Manager();
        
        $vendor_id = $db_manager->insert_vendor([
            'name' => sanitize_text_field($params['name']),
            'email' => sanitize_email($params['email']),
            'company' => sanitize_text_field($params['company'] ?? ''),
            'phone' => sanitize_text_field($params['phone'] ?? ''),
            'description' => sanitize_textarea_field($params['description'] ?? ''),
            'status' => 'active'
        ]);
        
        if ($vendor_id) {
            // Add categories if provided
            if (isset($params['categories']) && is_array($params['categories'])) {
                foreach ($params['categories'] as $index => $category) {
                    $db_manager->add_vendor_category(
                        $vendor_id,
                        sanitize_text_field($category['name']),
                        sanitize_title($category['slug']),
                        $index === 0
                    );
                }
            }
            
            return new WP_REST_Response([
                'id' => $vendor_id,
                'message' => 'Vendor created successfully'
            ], 201);
        }
        
        return new WP_Error('creation_failed', 'Failed to create vendor', ['status' => 500]);
    }
    
    /**
     * Search products
     */
    public function search_products($request) {
        $params = $request->get_params();
        
        $product_matcher = new WP_AI_Chat_Product_Matcher();
        
        try {
            $products = $product_matcher->search_products(
                sanitize_text_field($params['query']),
                sanitize_text_field($params['type'] ?? 'text'),
                intval($params['limit'] ?? 10)
            );
            
            return new WP_REST_Response([
                'products' => $products,
                'total' => count($products),
                'query' => $params['query']
            ], 200);
            
        } catch (Exception $e) {
            return new WP_Error('search_failed', $e->getMessage(), ['status' => 500]);
        }
    }
    
    /**
     * Get conversation analytics
     */
    public function get_conversation_analytics($request) {
        $params = $request->get_params();
        $days = isset($params['days']) ? intval($params['days']) : 30;
        
        $db_manager = new WP_AI_Chat_Database_Manager();
        $stats = $db_manager->get_conversation_stats($days);
        
        return new WP_REST_Response($stats, 200);
    }
    
    /**
     * Get request analytics
     */
    public function get_request_analytics($request) {
        $params = $request->get_params();
        $days = isset($params['days']) ? intval($params['days']) : 30;
        
        $db_manager = new WP_AI_Chat_Database_Manager();
        $stats = $db_manager->get_request_stats($days);
        
        return new WP_REST_Response($stats, 200);
    }
    
    /**
     * Handle vendor response webhook
     */
    public function handle_vendor_response_webhook($request) {
        $params = $request->get_params();
        
        // Validate webhook signature if configured
        if (!$this->validate_webhook_signature($request)) {
            return new WP_Error('invalid_signature', 'Invalid webhook signature', ['status' => 401]);
        }
        
        // Process vendor response
        $vendor_id = intval($params['vendor_id']);
        $request_id = intval($params['request_id']);
        $response_message = sanitize_textarea_field($params['message']);
        
        // Update notification record
        global $wpdb;
        $notifications_table = $wpdb->prefix . 'ai_chat_vendor_notifications';
        
        $wpdb->update(
            $notifications_table,
            [
                'status' => 'responded',
                'responded_at' => current_time('mysql'),
                'response_message' => $response_message
            ],
            [
                'vendor_id' => $vendor_id,
                'request_id' => $request_id
            ],
            ['%s', '%s', '%s'],
            ['%d', '%d']
        );
        
        // Update request responses count
        $requests_table = $wpdb->prefix . 'ai_chat_requests';
        $wpdb->query($wpdb->prepare(
            "UPDATE $requests_table SET responses_received = responses_received + 1 WHERE id = %d",
            $request_id
        ));
        
        // Hook for vendor response
        do_action('ai_chat_vendor_responded', $vendor_id, $request_id, $response_message);
        
        return new WP_REST_Response([
            'message' => 'Vendor response processed successfully'
        ], 200);
    }
    
    /**
     * Health check endpoint
     */
    public function health_check($request) {
        $health_data = [
            'status' => 'ok',
            'timestamp' => current_time('mysql'),
            'version' => WP_AI_CHAT_VERSION,
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'checks' => []
        ];
        
        // Database connectivity check
        global $wpdb;
        try {
            $wpdb->get_var("SELECT 1");
            $health_data['checks']['database'] = 'ok';
        } catch (Exception $e) {
            $health_data['checks']['database'] = 'failed';
            $health_data['status'] = 'error';
        }
        
        // AI API check (if configured)
        $api_key = get_option('ai_chat_api_key');
        if (!empty($api_key)) {
            $ai_handler = new WP_AI_Chat_AI_Handler();
            try {
                // Simple test message
                $response = $ai_handler->process_message('test', 'health_check');
                $health_data['checks']['ai_api'] = !empty($response) ? 'ok' : 'warning';
            } catch (Exception $e) {
                $health_data['checks']['ai_api'] = 'failed';
            }
        } else {
            $health_data['checks']['ai_api'] = 'not_configured';
        }
        
        // File permissions check
        $upload_dir = wp_upload_dir();
        $ai_chat_dir = $upload_dir['basedir'] . '/ai-chat/';
        
        if (is_writable($ai_chat_dir)) {
            $health_data['checks']['file_permissions'] = 'ok';
        } else {
            $health_data['checks']['file_permissions'] = 'warning';
        }
        
        return new WP_REST_Response($health_data, 200);
    }
    
    /**
     * Check admin permissions
     */
    public function check_admin_permissions() {
        return current_user_can('manage_ai_chat');
    }
    
    /**
     * Check user permissions
     */
    public function check_user_permissions() {
        return is_user_logged_in() || get_option('ai_chat_show_for_guests', true);
    }
    
    /**
     * Check conversation permissions
     */
    public function check_conversation_permissions($request) {
        $id = $request['id'];
        
        if (current_user_can('manage_ai_chat')) {
            return true;
        }
        
        // Users can only access their own conversations
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_chat_conversations';
        
        $conversation = $wpdb->get_row($wpdb->prepare(
            "SELECT user_id FROM $table_name WHERE id = %d",
            $id
        ));
        
        return $conversation && $conversation->user_id == get_current_user_id();
    }
    
    /**
     * Check requests permissions
     */
    public function check_requests_permissions() {
        return current_user_can('manage_ai_chat_requests') || is_user_logged_in();
    }
    
    /**
     * Check request permissions
     */
    public function check_request_permissions($request) {
        $id = $request['id'];
        
        if (current_user_can('manage_ai_chat_requests')) {
            return true;
        }
        
        // Users can only access their own requests
        $request_manager = new WP_AI_Chat_Request_Manager();
        $product_request = $request_manager->get_request($id);
        
        return $product_request && $product_request['user_id'] == get_current_user_id();
    }
    
    /**
     * Check request edit permissions
     */
    public function check_request_edit_permissions($request) {
        return current_user_can('manage_ai_chat_requests');
    }
    
    /**
     * Check vendor permissions
     */
    public function check_vendor_permissions() {
        return current_user_can('manage_ai_chat_vendors') || current_user_can('respond_to_requests');
    }
    
    /**
     * Check analytics permissions
     */
    public function check_analytics_permissions() {
        return current_user_can('view_ai_chat_analytics');
    }
    
    /**
     * Check search permissions
     */
    public function check_search_permissions() {
        return true; // Allow public search
    }
    
    /**
     * Check webhook permissions
     */
    public function check_webhook_permissions($request) {
        // Implement webhook authentication logic
        $token = $request->get_header('X-Webhook-Token');
        $expected_token = get_option('ai_chat_webhook_token');
        
        return !empty($token) && !empty($expected_token) && hash_equals($expected_token, $token);
    }
    
    /**
     * Validate webhook signature
     */
    private function validate_webhook_signature($request) {
        $signature = $request->get_header('X-Webhook-Signature');
        $payload = $request->get_body();
        $secret = get_option('ai_chat_webhook_secret');
        
        if (empty($signature) || empty($secret)) {
            return false;
        }
        
        $expected_signature = 'sha256=' . hash_hmac('sha256', $payload, $secret);
        
        return hash_equals($expected_signature, $signature);
    }
    
    /**
     * Get conversation arguments
     */
    private function get_conversation_args() {
        return [
            'user_message' => [
                'required' => true,
                'type' => 'string',
                'description' => 'User message'
            ],
            'ai_response' => [
                'required' => true,
                'type' => 'string',
                'description' => 'AI response'
            ],
            'context' => [
                'default' => 'general',
                'type' => 'string',
                'description' => 'Conversation context'
            ],
            'session_id' => [
                'type' => 'string',
                'description' => 'Session identifier'
            ]
        ];
    }
    
    /**
     * Get request arguments
     */
    private function get_request_args() {
        return [
            'category' => [
                'required' => true,
                'type' => 'string',
                'description' => 'Product category'
            ],
            'description' => [
                'required' => true,
                'type' => 'string',
                'description' => 'Product description'
            ],
            'priority' => [
                'default' => 'normal',
                'enum' => ['low', 'normal', 'high'],
                'description' => 'Request priority'
            ],
            'customer_name' => [
                'type' => 'string',
                'description' => 'Customer name'
            ],
            'customer_email' => [
                'type' => 'string',
                'format' => 'email',
                'description' => 'Customer email'
            ],
            'customer_phone' => [
                'type' => 'string',
                'description' => 'Customer phone'
            ],
            'image_url' => [
                'type' => 'string',
                'format' => 'uri',
                'description' => 'Product image URL'
            ]
        ];
    }
    
    /**
     * Get request update arguments
     */
    private function get_request_update_args() {
        return [
            'status' => [
                'enum' => ['pending', 'processing', 'completed', 'cancelled'],
                'description' => 'Request status'
            ],
            'priority' => [
                'enum' => ['low', 'normal', 'high'],
                'description' => 'Request priority'
            ],
            'notes' => [
                'type' => 'string',
                'description' => 'Internal notes'
            ]
        ];
    }
    
    /**
     * Get vendor arguments
     */
    private function get_vendor_args() {
        return [
            'name' => [
                'required' => true,
                'type' => 'string',
                'description' => 'Vendor name'
            ],
            'email' => [
                'required' => true,
                'type' => 'string',
                'format' => 'email',
                'description' => 'Vendor email'
            ],
            'company' => [
                'type' => 'string',
                'description' => 'Company name'
            ],
            'phone' => [
                'type' => 'string',
                'description' => 'Phone number'
            ],
            'description' => [
                'type' => 'string',
                'description' => 'Vendor description'
            ],
            'categories' => [
                'type' => 'array',
                'description' => 'Vendor categories'
            ]
        ];
    }
    
    /**
     * Get total conversations count
     */
    private function get_conversations_total($args) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_chat_conversations';
        $where_clauses = ['1=1'];
        $where_values = [];
        
        if (!empty($args['user_id'])) {
            $where_clauses[] = 'user_id = %d';
            $where_values[] = $args['user_id'];
        }
        
        if (!empty($args['context'])) {
            $where_clauses[] = 'context = %s';
            $where_values[] = $args['context'];
        }
        
        $where_sql = implode(' AND ', $where_clauses);
        $sql = "SELECT COUNT(*) FROM $table_name WHERE $where_sql";
        
        if (!empty($where_values)) {
            return $wpdb->get_var($wpdb->prepare($sql, $where_values));
        }
        
        return $wpdb->get_var($sql);
    }
    
    /**
     * Get total requests count
     */
    private function get_requests_total($args) {
        $request_manager = new WP_AI_Chat_Request_Manager();
        $requests = $request_manager->get_requests(array_merge($args, ['limit' => -1]));
        return count($requests);
    }
}