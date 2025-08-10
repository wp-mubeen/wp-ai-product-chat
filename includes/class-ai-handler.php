<?php
/**
 * AI Handler Class
 * Handles AI interactions, image analysis, and natural language processing
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_AI_Chat_AI_Handler {
    
    private $api_key;
    private $api_endpoint;
    private $model;
    
    public function __construct() {
        $this->api_key = get_option('ai_chat_api_key', '');
        $this->api_endpoint = get_option('ai_chat_api_endpoint', 'https://api.openai.com/v1/chat/completions');
        $this->model = get_option('ai_chat_model', 'gpt-3.5-turbo');
    }
    
    /**
     * Process chat message with AI
     */
    public function process_message($message, $context = 'general', $conversation = []) {
        if (empty($this->api_key)) {
            return $this->get_fallback_response($message, $context);
        }
        
        $system_prompt = $this->build_system_prompt($context);
        $conversation_history = $this->format_conversation_history($conversation);
        
        $messages = [
            [
                'role' => 'system',
                'content' => $system_prompt
            ]
        ];
        
        // Add conversation history
        foreach ($conversation_history as $hist) {
            $messages[] = $hist;
        }
        
        // Add current message
        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];
        
        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => 500,
            'temperature' => 0.7,
            'top_p' => 1,
            'frequency_penalty' => 0,
            'presence_penalty' => 0
        ];
        
        $response = $this->make_api_request($payload);
        
        if ($response && isset($response['choices'][0]['message']['content'])) {
            return trim($response['choices'][0]['message']['content']);
        }
        
        return $this->get_fallback_response($message, $context);
    }
    
    /**
     * Analyze uploaded image
     */
    public function analyze_image($image_path, $image_url) {
        if (empty($this->api_key)) {
            return $this->get_fallback_image_analysis($image_path);
        }
        
        // For GPT-4 Vision or similar models
        if (strpos($this->model, 'gpt-4') !== false || strpos($this->model, 'vision') !== false) {
            return $this->analyze_image_with_vision($image_url);
        }
        
        // Fallback to basic image analysis
        return $this->get_fallback_image_analysis($image_path);
    }
    
    /**
     * Analyze image using GPT-4 Vision
     */
    private function analyze_image_with_vision($image_url) {
        $base64_image = $this->encode_image($image_url);
        
        if (!$base64_image) {
            throw new Exception('Failed to encode image');
        }
        
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a product identification expert. Analyze the image and provide a detailed description of the product, potential categories, and key features that would help in product search.'
            ],
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'Please analyze this product image and provide: 1) A detailed description, 2) Potential product categories, 3) Key identifying features'
                    ],
                    [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => $image_url
                        ]
                    ]
                ]
            ]
        ];
        
        $payload = [
            'model' => 'gpt-4-vision-preview',
            'messages' => $messages,
            'max_tokens' => 300
        ];
        
        $response = $this->make_api_request($payload);
        
        if ($response && isset($response['choices'][0]['message']['content'])) {
            $analysis_text = $response['choices'][0]['message']['content'];
            return $this->parse_image_analysis($analysis_text);
        }
        
        throw new Exception('Failed to analyze image with AI');
    }
    
    /**
     * Suggest product categories based on query
     */
    public function suggest_categories($query) {
        if (empty($this->api_key)) {
            return $this->get_default_categories_for_query($query);
        }
        
        $system_prompt = "You are a product categorization expert. Based on the user's product description, suggest the most relevant product categories. Return only a JSON array of category objects with 'name' and 'slug' properties. Maximum 5 categories.";
        
        $messages = [
            [
                'role' => 'system',
                'content' => $system_prompt
            ],
            [
                'role' => 'user',
                'content' => "Suggest product categories for: " . $query
            ]
        ];
        
        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => 200,
            'temperature' => 0.3
        ];
        
        $response = $this->make_api_request($payload);
        
        if ($response && isset($response['choices'][0]['message']['content'])) {
            $categories_json = trim($response['choices'][0]['message']['content']);
            $categories = json_decode($categories_json, true);
            
            if (is_array($categories)) {
                return $categories;
            }
        }
        
        return $this->get_default_categories_for_query($query);
    }
    
    /**
     * Build system prompt based on context
     */
    private function build_system_prompt($context) {
        $base_prompt = "You are a helpful AI assistant for an e-commerce website. You help customers find products, resolve issues, and provide support. Be friendly, helpful, and concise in your responses.";
        
        switch ($context) {
            case 'product-search':
                return $base_prompt . " Focus on helping the user find products by understanding their needs and guiding them through the search process. If you cannot find specific products, help them identify the right category to contact vendors.";
                
            case 'order-support':
                return $base_prompt . " Focus on helping with order-related issues like tracking, returns, modifications, and delivery problems. Ask for order numbers when relevant and provide clear next steps.";
                
            case 'site-problem':
                return $base_prompt . " Focus on troubleshooting website issues, technical problems, and user experience issues. Provide clear troubleshooting steps and escalation paths when needed.";
                
            default:
                return $base_prompt . " Provide general customer support and try to understand what the customer needs help with.";
        }
    }
    
    /**
     * Format conversation history for API
     */
    private function format_conversation_history($conversation) {
        $formatted = [];
        
        foreach ($conversation as $exchange) {
            if (isset($exchange['user'])) {
                $formatted[] = [
                    'role' => 'user',
                    'content' => $exchange['user']
                ];
            }
            
            if (isset($exchange['ai'])) {
                $formatted[] = [
                    'role' => 'assistant',
                    'content' => $exchange['ai']
                ];
            }
        }
        
        return $formatted;
    }
    
    /**
     * Make API request to AI service
     */
    private function make_api_request($payload) {
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->api_key
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->api_endpoint,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            error_log('AI API cURL Error: ' . $error);
            return false;
        }
        
        if ($http_code !== 200) {
            error_log('AI API HTTP Error: ' . $http_code . ' - ' . $response);
            return false;
        }
        
        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('AI API JSON Error: ' . json_last_error_msg());
            return false;
        }
        
        return $decoded;
    }
    
    /**
     * Encode image to base64
     */
    private function encode_image($image_url) {
        $image_data = file_get_contents($image_url);
        
        if ($image_data === false) {
            return false;
        }
        
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->buffer($image_data);
        
        return 'data:' . $mime_type . ';base64,' . base64_encode($image_data);
    }
    
    /**
     * Parse image analysis response
     */
    private function parse_image_analysis($analysis_text) {
        // Extract key information from the analysis
        $description = $analysis_text;
        $categories = [];
        $confidence = 0.8;
        
        // Try to extract categories if mentioned
        if (preg_match_all('/category|categories?:\s*([^.]+)/i', $analysis_text, $matches)) {
            foreach ($matches[1] as $match) {
                $cats = explode(',', $match);
                foreach ($cats as $cat) {
                    $categories[] = trim($cat);
                }
            }
        }
        
        return [
            'description' => $description,
            'categories' => array_slice(array_unique($categories), 0, 5),
            'confidence' => $confidence
        ];
    }
    
    /**
     * Get fallback response when AI is not available
     */
    private function get_fallback_response($message, $context) {
        $responses = [
            'product-search' => [
                'I understand you\'re looking for a product. Could you provide more details about what you need?',
                'Let me help you find that product. Can you describe it in more detail?',
                'I\'ll help you search for that item. What specific features are you looking for?'
            ],
            'order-support' => [
                'I\'d be happy to help with your order. Could you please provide your order number?',
                'Let me assist you with your order issue. What specifically do you need help with?',
                'I\'m here to help with your order. Please share more details about the problem.'
            ],
            'site-problem' => [
                'I\'m sorry you\'re experiencing issues. Can you describe what problem you\'re encountering?',
                'Let me help resolve this issue. What specific error or problem are you seeing?',
                'I\'ll help troubleshoot this problem. Can you provide more details about what\'s not working?'
            ]
        ];
        
        $context_responses = $responses[$context] ?? [
            'Thank you for your message. How can I help you today?',
            'I\'m here to assist you. Could you provide more details about what you need?',
            'How can I help you with your inquiry?'
        ];
        
        return $context_responses[array_rand($context_responses)];
    }
    
    /**
     * Get fallback image analysis
     */
    private function get_fallback_image_analysis($image_path) {
        // Basic image analysis using WordPress functions
        $image_data = wp_get_attachment_metadata(attachment_url_to_postid($image_path));
        
        return [
            'description' => 'Product image uploaded - please describe what you\'re looking for',
            'categories' => ['general'],
            'confidence' => 0.3
        ];
    }
    
    /**
     * Get default categories for query
     */
    private function get_default_categories_for_query($query) {
        $keyword_mapping = [
            'phone|mobile|smartphone|iphone|android' => [
                ['name' => 'Electronics', 'slug' => 'electronics'],
                ['name' => 'Mobile Phones', 'slug' => 'mobile-phones']
            ],
            'laptop|computer|pc|desktop' => [
                ['name' => 'Electronics', 'slug' => 'electronics'],
                ['name' => 'Computers', 'slug' => 'computers']
            ],
            'clothes|shirt|dress|pants|clothing' => [
                ['name' => 'Clothing & Fashion', 'slug' => 'clothing-fashion'],
                ['name' => 'Apparel', 'slug' => 'apparel']
            ],
            'shoes|sneakers|boots|footwear' => [
                ['name' => 'Clothing & Fashion', 'slug' => 'clothing-fashion'],
                ['name' => 'Footwear', 'slug' => 'footwear']
            ],
            'book|novel|magazine|reading' => [
                ['name' => 'Books & Media', 'slug' => 'books-media'],
                ['name' => 'Literature', 'slug' => 'literature']
            ]
        ];
        
        $query_lower = strtolower($query);
        
        foreach ($keyword_mapping as $pattern => $categories) {
            if (preg_match('/' . $pattern . '/i', $query_lower)) {
                return $categories;
            }
        }
        
        // Default categories
        return [
            ['name' => 'General', 'slug' => 'general'],
            ['name' => 'Electronics', 'slug' => 'electronics'],
            ['name' => 'Clothing & Fashion', 'slug' => 'clothing-fashion']
        ];
    }
}