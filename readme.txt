=== AI Product Chat ===
Contributors: yourname
Tags: ai, chat, product-search, customer-support, woocommerce, artificial-intelligence, chatbot, vendor-notifications
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Intelligent AI-powered chat widget that helps customers find products through image recognition and natural language processing.

== Description ==

**AI Product Chat** transforms your e-commerce store with an intelligent chat system that helps customers find exactly what they're looking for. Using advanced AI technology, the plugin can analyze product images, understand natural language queries, and automatically connect customers with relevant vendors.

= 🚀 Key Features =

**Smart Product Discovery**
* **Photo Search** - Customers can upload images of products they're looking for
* **AI Image Analysis** - Powered by GPT-4 Vision for accurate product identification
* **Natural Language Search** - Understands conversational product descriptions
* **Intelligent Matching** - Searches across WooCommerce, EDD, and custom post types

**Vendor Integration**
* **Automatic Vendor Notifications** - Alerts relevant vendors when products aren't found
* **Category-based Matching** - Routes requests to vendors by product category
* **Response Tracking** - Monitors vendor engagement and response rates
* **Email Templates** - Professional notification emails with customer details

**Customer Support Patterns**
* **"I can't find the product I want"** - Advanced product discovery workflow
* **"Order Support"** - Direct customer service assistance
* **"Problem with the site"** - Technical issue resolution

**Advanced Chat Interface**
* **Modern Responsive Design** - Works seamlessly on desktop and mobile
* **Drag & Drop Upload** - Easy image sharing with preview
* **Real-time Responses** - AJAX-powered for instant interactions
* **Conversation History** - Tracks customer interactions for analytics

= 🎯 Perfect For =

* **E-commerce Stores** using WooCommerce or Easy Digital Downloads
* **Marketplace Websites** with multiple vendors
* **Product Catalogs** with extensive inventories
* **Customer Support Teams** wanting AI assistance
* **Businesses** looking to improve product discoverability

= 🛠 Technical Features =

* **AI Integration** - OpenAI API support with fallback responses
* **Database Optimization** - Efficient storage and retrieval
* **Security First** - Nonce verification, sanitization, and rate limiting
* **GDPR Compliant** - Configurable data retention and privacy settings
* **Developer Friendly** - Hooks, filters, and extensible architecture

= 🚀 How It Works =

1. **Customer opens chat** → Sees three helpful options
2. **Selects "I can't find product"** → Can upload photo or describe item
3. **AI analyzes request** → Searches your product database
4. **Shows relevant products** → Customer confirms if found
5. **If not found** → System suggests categories and contacts vendors
6. **Vendors get notifications** → With customer details and response links
7. **Customer gets confirmation** → Can track requests in their account

= 🎨 Customization Options =

* **Widget Positioning** - Bottom-right, bottom-left, or custom placement
* **Color Schemes** - Match your brand colors
* **Custom Messages** - Personalize greeting and response templates
* **Page Restrictions** - Show/hide on specific pages or post types
* **User Role Control** - Configure access by user roles
* **Mobile Settings** - Responsive design with mobile-specific options

= 📊 Analytics & Reporting =

* **Conversation Tracking** - Monitor customer interactions
* **Vendor Performance** - Response rates and success metrics
* **Popular Categories** - Identify trending product requests
* **Success Rates** - Track how many requests result in sales
* **Usage Statistics** - Understand chat widget effectiveness

= 🔧 Requirements =

* WordPress 5.0 or higher
* PHP 7.4 or higher
* MySQL 5.6 or higher
* cURL and JSON PHP extensions
* OpenAI API key (for full AI functionality)

= 🚀 Quick Start =

1. **Install and activate** the plugin
2. **Configure AI settings** with your OpenAI API key
3. **Set up vendor accounts** and categories
4. **Customize widget appearance** and positioning
5. **Test the functionality** with sample conversations

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Go to Plugins > Add New
3. Search for "AI Product Chat"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin ZIP file
2. Go to Plugins > Add New > Upload Plugin
3. Choose the ZIP file and click "Install Now"
4. Activate the plugin after installation

= After Installation =

1. Go to **AI Chat > Settings** in your WordPress admin
2. Configure your OpenAI API key for full AI functionality
3. Set up vendor accounts in **AI Chat > Vendors**
4. Customize the widget appearance in **AI Chat > Display**
5. Test the chat widget on your frontend

== Frequently Asked Questions ==

= Do I need an OpenAI API key? =

While recommended for full AI functionality, the plugin includes fallback responses that work without an API key. However, for image analysis and intelligent responses, an OpenAI API key is required.

= Is this compatible with WooCommerce? =

Yes! The plugin is fully compatible with WooCommerce and can search your product catalog, analyze product images, and integrate with your existing store setup.

= Can customers upload images? =

Absolutely! Customers can upload product images, and the AI will analyze them to identify similar products in your store or suggest relevant categories.

= How do vendor notifications work? =

When a product isn't found, the system automatically identifies the relevant category and sends email notifications to all vendors registered for that category, including customer details and response links.

= Is it mobile responsive? =

Yes, the chat widget is fully responsive and works seamlessly on desktop, tablet, and mobile devices with touch-friendly interactions.

= Can I customize the appearance? =

Yes! You can customize colors, positioning, messages, and even add custom CSS to match your brand perfectly.

= Does it work with other plugins? =

The plugin is designed to be compatible with popular e-commerce plugins like WooCommerce and Easy Digital Downloads, plus it can search custom post types.

= How is customer data handled? =

The plugin is GDPR compliant with configurable data retention settings. You can control how long conversations are stored and provide data export/deletion options.

= Can I limit usage by user roles? =

Yes, you can configure which user roles can access the chat widget and set different permissions for various user types.

= What about rate limiting? =

The plugin includes built-in rate limiting to prevent abuse and manage API costs, with configurable limits per user.

== Screenshots ==

1. **Chat Widget Interface** - Modern, responsive chat widget with three default support patterns
2. **Image Upload Feature** - Drag and drop image upload with preview and AI analysis
3. **Product Search Results** - Intelligent product matching with relevance scoring
4. **Admin Dashboard** - Comprehensive analytics and conversation management
5. **Vendor Management** - Easy vendor setup with category assignments
6. **Settings Panel** - Extensive customization options and AI configuration
7. **Mobile Experience** - Fully responsive design optimized for mobile devices
8. **Email Notifications** - Professional vendor notification templates

== Changelog ==

= 1.0.0 =
* Initial release
* AI-powered product search with image recognition
* Vendor notification system with category matching
* Three default support patterns (product search, order support, site problems)
* WooCommerce and Easy Digital Downloads integration
* Responsive chat widget with drag & drop image upload
* Comprehensive admin dashboard with analytics
* GDPR compliance features
* Rate limiting and security measures
* Email notification templates for vendors and customers
* Conversation tracking and vendor performance metrics
* Customizable appearance and positioning options
* Mobile-responsive design
* REST API endpoints for extensibility
* Database optimization with cleanup routines
* Multi-language support ready (translation files)

== Upgrade Notice ==

= 1.0.0 =
Initial release of AI Product Chat. Install now to transform your customer experience with intelligent product discovery!

== Technical Details ==

= Database Tables Created =
* `wp_ai_chat_conversations` - Stores chat interactions
* `wp_ai_chat_requests` - Manages product requests
* `wp_ai_chat_vendors` - Vendor information and settings
* `wp_ai_chat_vendor_categories` - Vendor-category relationships
* `wp_ai_chat_vendor_notifications` - Notification tracking
* `wp_ai_chat_support_tickets` - Support ticket management

= Hooks & Filters =
* `ai_chat_should_load` - Control when chat widget loads
* `ai_chat_vendors_for_category` - Modify vendor selection
* `ai_chat_product_post_types` - Add custom post types for search
* `ai_chat_before_send_notification` - Customize vendor notifications
* `ai_chat_conversation_data` - Modify conversation logging

= Shortcodes =
* `[ai_chat_widget]` - Display chat widget anywhere
* `[ai_chat_customer_requests]` - Show customer request history
* `[ai_chat_vendor_response]` - Vendor response interface

= REST API Endpoints =
* `/wp-json/ai-chat/v1/conversations` - Conversation management
* `/wp-json/ai-chat/v1/requests` - Request handling
* `/wp-json/ai-chat/v1/vendors` - Vendor operations
* `/wp-json/ai-chat/v1/analytics` - Usage statistics

= Security Features =
* Nonce verification for all AJAX requests
* Input sanitization and validation
* Rate limiting to prevent abuse
* Capability checks for admin functions
* Secure file upload handling
* SQL injection protection

= Performance Optimizations =
* Database indexing for fast queries
* Lazy loading of chat widget
* Compressed and minified assets
* Efficient caching mechanisms
* Background processing for notifications

== Support ==

For support, feature requests, or bug reports, please visit:
* **Documentation**: [Plugin Documentation](https://your-site.com/docs)
* **Support Forum**: [WordPress Support](https://wordpress.org/support/plugin/ai-product-chat)
* **GitHub Issues**: [GitHub Repository](https://github.com/your-username/ai-product-chat)

== Privacy Policy ==

This plugin may collect and process the following data:
* Chat conversations and messages
* Uploaded images for product analysis
* User interaction analytics
* Email addresses for notifications
* IP addresses for rate limiting

All data collection is configurable and GDPR compliant. Users can request data export or deletion at any time.

== Credits ==

* **OpenAI API** for artificial intelligence capabilities
* **WordPress Team** for the excellent platform
* **jQuery Team** for JavaScript functionality
* **Font Awesome** for icons (if used)

== License ==

This plugin is licensed under the GPL v2 or later.

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.