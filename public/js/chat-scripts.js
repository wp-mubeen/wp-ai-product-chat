/**
 * AI Chat Widget JavaScript
 */

(function($) {
    'use strict';

    let chatWidget = null;
    let currentConversation = [];
    let isMinimized = false;
    let currentStep = 'initial';
    let selectedPattern = null;
    let uploadedImage = null;

    // Initialize chat when DOM is ready
    $(document).ready(function() {
        initializeChat();
    });

    function initializeChat() {
        chatWidget = new AIChatWidget();
        chatWidget.init();
    }

    class AIChatWidget {
        constructor() {
            this.elements = {
                widget: $('#ai-chat-widget'),
                toggle: $('#ai-chat-toggle'),
                window: $('#ai-chat-window'),
                messages: $('#ai-chat-messages'),
                input: $('#ai-message-input'),
                sendBtn: $('#ai-send-message'),
                loading: $('#ai-loading'),
                minimize: $('#ai-chat-minimize'),
                close: $('#ai-chat-close'),
                attachBtn: $('#ai-attach-image'),
                imageUpload: $('#ai-image-upload-section'),
                textInput: $('#ai-text-input-section'),
                imageInput: $('#ai-image-input'),
                uploadDrop: $('#ai-upload-drop'),
                imagePreview: $('#ai-image-preview'),
                previewImg: $('#ai-preview-img'),
                removeImage: $('#ai-remove-image'),
                cancelUpload: $('#ai-cancel-upload'),
                sendImage: $('#ai-send-image'),
                productModal: $('#ai-product-modal'),
                categoryModal: $('#ai-category-modal')
            };
        }

        init() {
            this.bindEvents();
            this.setupDragDrop();
            this.autoResizeTextarea();
        }

        bindEvents() {
            // Toggle chat window
            this.elements.toggle.on('click', () => this.toggleChat());
            
            // Window controls
            this.elements.minimize.on('click', () => this.minimizeChat());
            this.elements.close.on('click', () => this.closeChat());
            
            // Pattern buttons
            $(document).on('click', '.ai-pattern-btn', (e) => {
                const pattern = $(e.target).data('pattern');
                this.handlePatternSelection(pattern);
            });
            
            // Message input
            this.elements.input.on('keypress', (e) => {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });
            
            this.elements.sendBtn.on('click', () => this.sendMessage());
            
            // Image handling
            this.elements.attachBtn.on('click', () => this.showImageUpload());
            this.elements.cancelUpload.on('click', () => this.hideImageUpload());
            this.elements.uploadDrop.on('click', () => this.elements.imageInput.click());
            this.elements.imageInput.on('change', (e) => this.handleImageSelect(e));
            this.elements.removeImage.on('click', () => this.removeImage());
            this.elements.sendImage.on('click', () => this.sendImageMessage());
            
            // Modal controls
            $('.ai-modal-close').on('click', function() {
                $(this).closest('.ai-modal').hide();
            });
            
            // Product confirmation
            $('#ai-confirm-yes').on('click', () => this.handleProductConfirmation(true));
            $('#ai-confirm-no').on('click', () => this.handleProductConfirmation(false));
            
            // Category selection
            $(document).on('click', '.ai-category-suggestion', function() {
                $('.ai-category-suggestion').removeClass('selected');
                $(this).addClass('selected');
                $('#ai-custom-category').val($(this).text());
            });
            
            $('#ai-category-confirm').on('click', () => this.sendToVendors());
            $('#ai-category-cancel').on('click', () => this.elements.categoryModal.hide());
        }

        setupDragDrop() {
            let dragCounter = 0;

            this.elements.uploadDrop.on('dragenter', (e) => {
                e.preventDefault();
                dragCounter++;
                $(e.target).addClass('dragover');
            });

            this.elements.uploadDrop.on('dragleave', (e) => {
                dragCounter--;
                if (dragCounter === 0) {
                    $(e.target).removeClass('dragover');
                }
            });

            this.elements.uploadDrop.on('dragover', (e) => {
                e.preventDefault();
            });

            this.elements.uploadDrop.on('drop', (e) => {
                e.preventDefault();
                dragCounter = 0;
                $(e.target).removeClass('dragover');
                
                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0 && files[0].type.startsWith('image/')) {
                    this.handleImageFile(files[0]);
                }
            });
        }

        autoResizeTextarea() {
            this.elements.input.on('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        }

        toggleChat() {
            if (this.elements.window.is(':visible')) {
                this.closeChat();
            } else {
                this.openChat();
            }
        }

        openChat() {
            this.elements.window.show();
            this.elements.input.focus();
            $('#ai-chat-notification').hide();
        }

        closeChat() {
            this.elements.window.hide();
        }

        minimizeChat() {
            if (isMinimized) {
                this.elements.window.show();
                isMinimized = false;
            } else {
                this.elements.window.hide();
                isMinimized = true;
            }
        }

        handlePatternSelection(pattern) {
            selectedPattern = pattern;
            
            switch (pattern) {
                case 'product-search':
                    this.startProductSearch();
                    break;
                case 'order-support':
                    this.startOrderSupport();
                    break;
                case 'site-problem':
                    this.startSiteProblem();
                    break;
            }
        }

        startProductSearch() {
            const message = "I can't find the product I want";
            this.addUserMessage(message);
            
            setTimeout(() => {
                this.addAIMessage(
                    "I'll help you find the perfect product! You can either:" +
                    "<br><br>📷 <strong>Take a photo</strong> or upload an image of what you're looking for" +
                    "<br>📝 <strong>Describe the product</strong> in text" +
                    "<br><br>Which would you prefer?"
                );
                this.showProductSearchOptions();
            }, 500);
        }

        startOrderSupport() {
            const message = "Order Support";
            this.addUserMessage(message);
            
            setTimeout(() => {
                this.addAIMessage(
                    "I'm here to help with your order! Please provide:" +
                    "<br><br>• Your order number" +
                    "<br>• What specific help you need" +
                    "<br><br>You can also describe any issues you're experiencing."
                );
            }, 500);
        }

        startSiteProblem() {
            const message = "Problem with the site";
            this.addUserMessage(message);
            
            setTimeout(() => {
                this.addAIMessage(
                    "I'm sorry you're experiencing issues! Please describe:" +
                    "<br><br>• What problem you're encountering" +
                    "<br>• What page or feature isn't working" +
                    "<br>• Any error messages you see" +
                    "<br><br>I'll help resolve this quickly!"
                );
            }, 500);
        }

        showProductSearchOptions() {
            const optionsHtml = `
                <div class="ai-search-options" style="margin-top: 15px;">
                    <button class="ai-btn ai-btn-primary" id="ai-option-photo" style="margin-right: 10px;">
                        📷 Upload Photo
                    </button>
                    <button class="ai-btn ai-btn-secondary" id="ai-option-describe">
                        📝 Describe Product
                    </button>
                </div>
            `;
            
            this.elements.messages.find('.ai-message:last .ai-message-content').append(optionsHtml);
            
            $('#ai-option-photo').on('click', () => {
                this.showImageUpload();
                $('.ai-search-options').remove();
            });
            
            $('#ai-option-describe').on('click', () => {
                $('.ai-search-options').remove();
                this.addAIMessage("Great! Please describe the product you're looking for in as much detail as possible.");
                this.elements.input.attr('placeholder', 'Describe the product you want...');
                this.elements.input.focus();
            });
        }

        showImageUpload() {
            this.elements.textInput.hide();
            this.elements.imageUpload.show();
        }

        hideImageUpload() {
            this.elements.imageUpload.hide();
            this.elements.textInput.show();
            this.removeImage();
        }

        handleImageSelect(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                this.handleImageFile(file);
            }
        }

        handleImageFile(file) {
            if (file.size > 5 * 1024 * 1024) { // 5MB limit
                alert('Image size must be less than 5MB');
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                this.elements.previewImg.attr('src', e.target.result);
                this.elements.imagePreview.show();
                this.elements.uploadDrop.hide();
                this.elements.sendImage.prop('disabled', false);
                uploadedImage = file;
            };
            reader.readAsDataURL(file);
        }

        removeImage() {
            this.elements.imagePreview.hide();
            this.elements.uploadDrop.show();
            this.elements.sendImage.prop('disabled', true);
            this.elements.imageInput.val('');
            uploadedImage = null;
        }

        sendImageMessage() {
            if (!uploadedImage) return;

            this.showLoading(window.aiChatData.strings.processingImage);
            this.hideImageUpload();

            const formData = new FormData();
            formData.append('action', 'ai_upload_image');
            formData.append('nonce', window.aiChatData.nonce);
            formData.append('image', uploadedImage);
            formData.append('context', 'product-search');

            $.ajax({
                url: window.aiChatData.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    this.hideLoading();
                    
                    if (response.success) {
                        this.addUserMessage('📷 [Image uploaded]');
                        this.handleImageAnalysis(response.data);
                    } else {
                        this.addAIMessage('Sorry, there was an error processing your image. Please try again.');
                    }
                },
                error: () => {
                    this.hideLoading();
                    this.addAIMessage(window.aiChatData.strings.uploadError);
                }
            });

            this.removeImage();
        }

        handleImageAnalysis(data) {
            if (data.description) {
                this.addAIMessage(`I can see this is ${data.description}. Let me search for similar products...`);
                
                setTimeout(() => {
                    this.searchProducts(data.description, 'image');
                }, 1000);
            } else {
                this.addAIMessage("I couldn't analyze the image clearly. Could you describe what you're looking for?");
            }
        }

        sendMessage() {
            const message = this.elements.input.val().trim();
            if (!message) return;

            this.addUserMessage(message);
            this.elements.input.val('').css('height', 'auto');

            if (selectedPattern === 'product-search') {
                this.handleProductSearchMessage(message);
            } else {
                this.handleGeneralMessage(message);
            }
        }

        handleProductSearchMessage(message) {
            this.showLoading(window.aiChatData.strings.searchingProducts);
            
            setTimeout(() => {
                this.hideLoading();
                this.addAIMessage(`I understand you're looking for "${message}". Let me search for products that match your description...`);
                
                setTimeout(() => {
                    this.searchProducts(message, 'text');
                }, 1000);
            }, 1500);
        }

        handleGeneralMessage(message) {
            this.showLoading();
            
            const data = {
                action: 'ai_chat_message',
                nonce: window.aiChatData.nonce,
                message: message,
                context: selectedPattern || 'general',
                conversation: currentConversation
            };

            $.post(window.aiChatData.ajaxUrl, data, (response) => {
                this.hideLoading();
                
                if (response.success) {
                    this.addAIMessage(response.data.message);
                    currentConversation.push({
                        user: message,
                        ai: response.data.message,
                        timestamp: new Date()
                    });
                } else {
                    this.addAIMessage('I apologize, but I\'m having trouble processing your request. Please try again.');
                }
            }).fail(() => {
                this.hideLoading();
                this.addAIMessage(window.aiChatData.strings.networkError);
            });
        }

        searchProducts(query, type) {
            this.showLoading(window.aiChatData.strings.searchingProducts);
            
            const data = {
                action: 'ai_search_products',
                nonce: window.aiChatData.nonce,
                query: query,
                type: type
            };

            $.post(window.aiChatData.ajaxUrl, data, (response) => {
                this.hideLoading();
                
                if (response.success && response.data.products.length > 0) {
                    this.displayProductResults(response.data.products);
                } else {
                    this.handleNoProductsFound(query);
                }
            }).fail(() => {
                this.hideLoading();
                this.addAIMessage('Error searching for products. Please try again.');
            });
        }

        displayProductResults(products) {
            this.addAIMessage('I found these products that might match what you\'re looking for:');
            
            let productsHtml = '';
            products.slice(0, 3).forEach(product => {
                productsHtml += `
                    <div class="ai-product-result">
                        <img src="${product.image}" alt="${product.title}" class="ai-product-image">
                        <div class="ai-product-info">
                            <div class="ai-product-title">${product.title}</div>
                            <div class="ai-product-price">${product.price}</div>
                            <div class="ai-product-description">${product.description}</div>
                        </div>
                    </div>
                `;
            });
            
            $('#ai-product-results').html(productsHtml);
            this.elements.productModal.show();
        }

        handleNoProductsFound(query) {
            this.addAIMessage("I couldn't find any exact matches in our current inventory. Let me help you find the right product category to contact our vendors.");
            
            setTimeout(() => {
                this.suggestCategories(query);
            }, 1500);
        }

        handleProductConfirmation(found) {
            this.elements.productModal.hide();
            
            if (found) {
                this.addAIMessage("Wonderful! I'm glad I could help you find what you were looking for. Is there anything else I can assist you with?");
                this.resetChat();
            } else {
                this.addAIMessage("No problem! Let me help you find the right product category so I can contact vendors who might have what you need.");
                
                setTimeout(() => {
                    this.suggestCategories('');
                }, 1000);
            }
        }

        suggestCategories(query) {
            this.showLoading('Finding relevant categories...');
            
            const data = {
                action: 'ai_suggest_categories',
                nonce: window.aiChatData.nonce,
                query: query
            };

            $.post(window.aiChatData.ajaxUrl, data, (response) => {
                this.hideLoading();
                
                if (response.success) {
                    this.displayCategoryOptions(response.data.categories);
                } else {
                    this.addAIMessage('Please type the product category you think best describes what you\'re looking for.');
                    this.showManualCategoryInput();
                }
            }).fail(() => {
                this.hideLoading();
                this.showManualCategoryInput();
            });
        }

        displayCategoryOptions(categories) {
            let categoriesHtml = '';
            categories.forEach(category => {
                categoriesHtml += `
                    <button class="ai-category-suggestion">${category.name}</button>
                `;
            });
            
            $('#ai-category-suggestions').html(categoriesHtml);
            this.elements.categoryModal.show();
        }

        showManualCategoryInput() {
            $('#ai-category-suggestions').html('<p>Please specify the product category:</p>');
            this.elements.categoryModal.show();
        }

        sendToVendors() {
            const category = $('#ai-custom-category').val() || $('.ai-category-suggestion.selected').text();
            
            if (!category) {
                alert('Please select or enter a category.');
                return;
            }
            
            this.elements.categoryModal.hide();
            this.showLoading(window.aiChatData.strings.contactingVendors);
            
            const data = {
                action: 'ai_contact_vendors',
                nonce: window.aiChatData.nonce,
                category: category,
                description: currentConversation[currentConversation.length - 1]?.user || '',
                user_id: window.aiChatData.userId
            };

            $.post(window.aiChatData.ajaxUrl, data, (response) => {
                this.hideLoading();
                
                if (response.success) {
                    this.addAIMessage(
                        `Perfect! I've sent your product request to vendors in the "${category}" category. ` +
                        `They will review your request and contact you if they have matching products. ` +
                        `You can track your requests in your account dashboard.`
                    );
                } else {
                    this.addAIMessage('There was an error contacting vendors. Please try again later.');
                }
                
                this.resetChat();
            }).fail(() => {
                this.hideLoading();
                this.addAIMessage('Network error. Please try again.');
            });
        }

        addUserMessage(message) {
            const messageHtml = `
                <div class="user-message">
                    <div class="user-message-content">${message}</div>
                    <div class="user-message-time">${this.getCurrentTime()}</div>
                </div>
            `;
            
            this.elements.messages.append(messageHtml);
            this.scrollToBottom();
        }

        addAIMessage(message) {
            const messageHtml = `
                <div class="ai-message">
                    <div class="ai-message-content">${message}</div>
                    <div class="ai-message-time">${this.getCurrentTime()}</div>
                </div>
            `;
            
            this.elements.messages.append(messageHtml);
            this.scrollToBottom();
        }

        showLoading(text = 'AI is thinking...') {
            this.elements.loading.find('p').text(text);
            this.elements.loading.show();
            this.scrollToBottom();
        }

        hideLoading() {
            this.elements.loading.hide();
        }

        getCurrentTime() {
            return new Date().toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: false
            });
        }

        scrollToBottom() {
            setTimeout(() => {
                this.elements.messages.scrollTop(this.elements.messages[0].scrollHeight);
            }, 100);
        }

        resetChat() {
            selectedPattern = null;
            currentStep = 'initial';
            this.elements.input.attr('placeholder', 'Type your message...');
        }
    }

})(jQuery);