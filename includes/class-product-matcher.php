<?php
/**
 * Product Matcher Class
 * Handles product search and matching logic
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_AI_Chat_Product_Matcher {
    
    private $search_engines;
    
    public function __construct() {
        $this->search_engines = [
            'woocommerce' => class_exists('WooCommerce'),
            'edd' => class_exists('Easy_Digital_Downloads'),
            'custom' => true
        ];
    }
    
    /**
     * Search for products based on query
     */
    public function search_products($query, $type = 'text', $limit = 10) {
        $products = [];
        
        // Search in different product sources
        if ($this->search_engines['woocommerce']) {
            $wc_products = $this->search_woocommerce_products($query, $limit);
            $products = array_merge($products, $wc_products);
        }
        
        if ($this->search_engines['edd']) {
            $edd_products = $this->search_edd_products($query, $limit);
            $products = array_merge($products, $edd_products);
        }
        
        // Search custom product posts
        $custom_products = $this->search_custom_products($query, $limit);
        $products = array_merge($products, $custom_products);
        
        // Remove duplicates and limit results
        $products = $this->deduplicate_products($products);
        $products = array_slice($products, 0, $limit);
        
        // Score and sort products by relevance
        $products = $this->score_products($products, $query);
        
        return $products;
    }
    
    /**
     * Search WooCommerce products
     */
    private function search_woocommerce_products($query, $limit) {
        if (!$this->search_engines['woocommerce']) {
            return [];
        }
        
        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            's' => $query,
            'meta_query' => [
                [
                    'key' => '_visibility',
                    'value' => ['hidden', 'search'],
                    'compare' => 'NOT IN'
                ]
            ]
        ];
        
        $products_query = new WP_Query($args);
        $products = [];
        
        if ($products_query->have_posts()) {
            while ($products_query->have_posts()) {
                $products_query->the_post();
                global $product;
                
                if (!$product || !$product->is_visible()) {
                    continue;
                }
                
                $image_id = $product->get_image_id();
                $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : wc_placeholder_img_src();
                
                $products[] = [
                    'id' => $product->get_id(),
                    'title' => $product->get_name(),
                    'description' => wp_trim_words($product->get_short_description() ?: $product->get_description(), 20),
                    'price' => $product->get_price_html(),
                    'image' => $image_url,
                    'url' => $product->get_permalink(),
                    'type' => 'woocommerce',
                    'in_stock' => $product->is_in_stock(),
                    'categories' => $this->get_product_categories($product->get_id(), 'product_cat'),
                    'tags' => $this->get_product_tags($product->get_id(), 'product_tag'),
                    'sku' => $product->get_sku()
                ];
            }
            wp_reset_postdata();
        }
        
        return $products;
    }
    
    /**
     * Search Easy Digital Downloads products
     */
    private function search_edd_products($query, $limit) {
        if (!$this->search_engines['edd']) {
            return [];
        }
        
        $args = [
            'post_type' => 'download',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            's' => $query
        ];
        
        $products_query = new WP_Query($args);
        $products = [];
        
        if ($products_query->have_posts()) {
            while ($products_query->have_posts()) {
                $products_query->the_post();
                
                $download_id = get_the_ID();
                $price = edd_get_download_price($download_id);
                $image_url = get_the_post_thumbnail_url($download_id, 'thumbnail') ?: EDD_PLUGIN_URL . 'assets/images/no-image.png';
                
                $products[] = [
                    'id' => $download_id,
                    'title' => get_the_title(),
                    'description' => wp_trim_words(get_the_excerpt() ?: get_the_content(), 20),
                    'price' => edd_currency_filter(edd_format_amount($price)),
                    'image' => $image_url,
                    'url' => get_permalink(),
                    'type' => 'edd',
                    'in_stock' => true, // EDD products are usually always available
                    'categories' => $this->get_product_categories($download_id, 'download_category'),
                    'tags' => $this->get_product_tags($download_id, 'download_tag')
                ];
            }
            wp_reset_postdata();
        }
        
        return $products;
    }
    
    /**
     * Search custom products (generic post type)
     */
    private function search_custom_products($query, $limit) {
        // Search in custom post types that might represent products
        $custom_post_types = apply_filters('ai_chat_product_post_types', ['product', 'item', 'listing']);
        
        $products = [];
        
        foreach ($custom_post_types as $post_type) {
            if (!post_type_exists($post_type)) {
                continue;
            }
            
            $args = [
                'post_type' => $post_type,
                'post_status' => 'publish',
                'posts_per_page' => $limit,
                's' => $query,
                'meta_query' => [
                    'relation' => 'OR',
                    [
                        'key' => '_product_visibility',
                        'value' => 'visible',
                        'compare' => '='
                    ],
                    [
                        'key' => '_product_visibility',
                        'compare' => 'NOT EXISTS'
                    ]
                ]
            ];
            
            $products_query = new WP_Query($args);
            
            if ($products_query->have_posts()) {
                while ($products_query->have_posts()) {
                    $products_query->the_post();
                    
                    $product_id = get_the_ID();
                    $price = get_post_meta($product_id, '_price', true) ?: get_post_meta($product_id, 'price', true);
                    $currency = get_option('ai_chat_currency_symbol', '$');
                    
                    $products[] = [
                        'id' => $product_id,
                        'title' => get_the_title(),
                        'description' => wp_trim_words(get_the_excerpt() ?: get_the_content(), 20),
                        'price' => $price ? $currency . $price : 'Price not available',
                        'image' => get_the_post_thumbnail_url($product_id, 'thumbnail') ?: WP_AI_CHAT_PLUGIN_URL . 'assets/images/no-image.png',
                        'url' => get_permalink(),
                        'type' => $post_type,
                        'in_stock' => $this->check_custom_product_stock($product_id),
                        'categories' => $this->get_product_categories($product_id),
                        'tags' => $this->get_product_tags($product_id)
                    ];
                }
                wp_reset_postdata();
            }
        }
        
        return $products;
    }
    
    /**
     * Get product categories
     */
    private function get_product_categories($product_id, $taxonomy = 'category') {
        $terms = get_the_terms($product_id, $taxonomy);
        
        if (!$terms || is_wp_error($terms)) {
            return [];
        }
        
        return array_map(function($term) {
            return [
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug
            ];
        }, $terms);
    }
    
    /**
     * Get product tags
     */
    private function get_product_tags($product_id, $taxonomy = 'post_tag') {
        $terms = get_the_terms($product_id, $taxonomy);
        
        if (!$terms || is_wp_error($terms)) {
            return [];
        }
        
        return array_map(function($term) {
            return [
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug
            ];
        }, $terms);
    }
    
    /**
     * Check custom product stock
     */
    private function check_custom_product_stock($product_id) {
        $stock_status = get_post_meta($product_id, '_stock_status', true);
        $stock_quantity = get_post_meta($product_id, '_stock', true);
        
        if ($stock_status === 'outofstock') {
            return false;
        }
        
        if ($stock_quantity !== '' && intval($stock_quantity) <= 0) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Remove duplicate products
     */
    private function deduplicate_products($products) {
        $unique_products = [];
        $seen_titles = [];
        
        foreach ($products as $product) {
            $title_key = strtolower(trim($product['title']));
            
            if (!in_array($title_key, $seen_titles)) {
                $unique_products[] = $product;
                $seen_titles[] = $title_key;
            }
        }
        
        return $unique_products;
    }
    
    /**
     * Score products by relevance
     */
    private function score_products($products, $query) {
        $query_terms = array_map('strtolower', explode(' ', $query));
        
        foreach ($products as &$product) {
            $score = 0;
            $title = strtolower($product['title']);
            $description = strtolower($product['description']);
            
            // Title exact match
            if (strpos($title, strtolower($query)) !== false) {
                $score += 100;
            }
            
            // Title word matches
            foreach ($query_terms as $term) {
                if (strpos($title, $term) !== false) {
                    $score += 50;
                }
                if (strpos($description, $term) !== false) {
                    $score += 25;
                }
            }
            
            // Category matches
            foreach ($product['categories'] as $category) {
                $category_name = strtolower($category['name']);
                foreach ($query_terms as $term) {
                    if (strpos($category_name, $term) !== false) {
                        $score += 30;
                    }
                }
            }
            
            // Tag matches
            foreach ($product['tags'] as $tag) {
                $tag_name = strtolower($tag['name']);
                foreach ($query_terms as $term) {
                    if (strpos($tag_name, $term) !== false) {
                        $score += 20;
                    }
                }
            }
            
            // Stock bonus
            if ($product['in_stock']) {
                $score += 10;
            }
            
            $product['relevance_score'] = $score;
        }
        
        // Sort by relevance score (highest first)
        usort($products, function($a, $b) {
            return $b['relevance_score'] - $a['relevance_score'];
        });
        
        return $products;
    }
    
    /**
     * Search products by image similarity (future enhancement)
     */
    public function search_by_image($image_path, $limit = 10) {
        // This is a placeholder for future image-based search functionality
        // Could integrate with Google Vision API, AWS Rekognition, or custom ML models
        
        // For now, return empty array
        return [];
    }
    
    /**
     * Get similar products based on a product ID
     */
    public function get_similar_products($product_id, $limit = 5) {
        $products = [];
        
        // Get the original product's categories and tags
        $categories = $this->get_product_categories($product_id);
        $tags = $this->get_product_tags($product_id);
        
        if (empty($categories) && empty($tags)) {
            return [];
        }
        
        // Build query for similar products
        $tax_query = ['relation' => 'OR'];
        
        // Add category conditions
        if (!empty($categories)) {
            $category_ids = array_column($categories, 'id');
            $tax_query[] = [
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $category_ids,
                'operator' => 'IN'
            ];
        }
        
        // Add tag conditions
        if (!empty($tags)) {
            $tag_ids = array_column($tags, 'id');
            $tax_query[] = [
                'taxonomy' => 'product_tag',
                'field' => 'term_id',
                'terms' => $tag_ids,
                'operator' => 'IN'
            ];
        }
        
        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => $limit + 1, // +1 to exclude current product
            'post__not_in' => [$product_id],
            'tax_query' => $tax_query
        ];
        
        $similar_query = new WP_Query($args);
        
        if ($similar_query->have_posts()) {
            while ($similar_query->have_posts()) {
                $similar_query->the_post();
                
                if ($this->search_engines['woocommerce']) {
                    global $product;
                    
                    if (!$product || !$product->is_visible()) {
                        continue;
                    }
                    
                    $image_id = $product->get_image_id();
                    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : wc_placeholder_img_src();
                    
                    $products[] = [
                        'id' => $product->get_id(),
                        'title' => $product->get_name(),
                        'description' => wp_trim_words($product->get_short_description() ?: $product->get_description(), 15),
                        'price' => $product->get_price_html(),
                        'image' => $image_url,
                        'url' => $product->get_permalink(),
                        'type' => 'woocommerce'
                    ];
                }
            }
            wp_reset_postdata();
        }
        
        return array_slice($products, 0, $limit);
    }
}