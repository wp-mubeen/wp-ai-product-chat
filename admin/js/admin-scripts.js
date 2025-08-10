(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Dashboard Statistics Chart
        if ($('#wpapc-stats-chart').length) {
            initDashboardChart();
        }
        
        // DataTables for lists
        if ($('.wpapc-data-table').length) {
            $('.wpapc-data-table').DataTable({
                "pageLength": 25,
                "order": [[0, "desc"]],
                "responsive": true
            });
        }
        
        // Handle ticket status updates
        $('.wpapc-ticket-status').on('change', function() {
            const ticketId = $(this).data('ticket-id');
            const newStatus = $(this).val();
            
            updateTicketStatus(ticketId, newStatus);
        });
        
        // Handle request approval/rejection
        $('.wpapc-approve-request').on('click', function(e) {
            e.preventDefault();
            const requestId = $(this).data('request-id');
            handleRequestAction(requestId, 'approve');
        });
        
        $('.wpapc-reject-request').on('click', function(e) {
            e.preventDefault();
            const requestId = $(this).data('request-id');
            handleRequestAction(requestId, 'reject');
        });
        
        // Export functionality
        $('#export-requests').on('click', function() {
            exportData('requests');
        });
        
        $('#export-tickets').on('click', function() {
            exportData('tickets');
        });
        
        // Settings validation
        $('#wpapc-settings-form').on('submit', function(e) {
            if (!validateSettings()) {
                e.preventDefault();
                return false;
            }
        });
        
        // API key test
        $('#test-api-key').on('click', function() {
            testAPIKey();
        });
        
        // Live chat monitoring
        if ($('#wpapc-live-sessions').length) {
            setInterval(refreshLiveSessions, 5000);
        }
        
        // Notification dismissal
        $('.wpapc-dismiss-notice').on('click', function() {
            const noticeId = $(this).data('notice-id');
            dismissNotice(noticeId);
        });
        
        // Bulk actions
        $('#wpapc-bulk-action').on('change', function() {
            const action = $(this).val();
            if (action) {
                performBulkAction(action);
            }
        });
        
        // Quick reply templates
        $('.wpapc-quick-reply').on('click', function() {
            const template = $(this).data('template');
            insertQuickReply(template);
        });
        
        // Statistics date range
        $('#stats-date-range').on('change', function() {
            updateStatistics($(this).val());
        });
        
    });
    
    function initDashboardChart() {
        $.ajax({
            url: wpapc_admin.ajax_url,
            method: 'POST',
            data: {
                action: 'wpapc_get_dashboard_stats',
                nonce: wpapc_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    renderChart(response.data);
                }
            }
        });
    }
    
    function renderChart(data) {
        const ctx = document.getElementById('wpapc-stats-chart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Chat Sessions',
                    data: data.sessions,
                    borderColor: 'rgb(102, 126, 234)',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Product Requests',
                    data: data.requests,
                    borderColor: 'rgb(118, 75, 162)',
                    backgroundColor: 'rgba(118, 75, 162, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Chat Activity - Last 30 Days'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    function updateTicketStatus(ticketId, status) {
        $.ajax({
            url: wpapc_admin.ajax_url,
            method: 'POST',
            data: {
                action: 'wpapc_update_ticket_status',
                ticket_id: ticketId,
                status: status,
                nonce: wpapc_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Ticket status updated successfully', 'success');
                } else {
                    showNotification('Failed to update ticket status', 'error');
                }
            }
        });
    }
    
    function handleRequestAction(requestId, action) {
        if (!confirm('Are you sure you want to ' + action + ' this request?')) {
            return;
        }
        
        $.ajax({
            url: wpapc_admin.ajax_url,
            method: 'POST',
            data: {
                action: 'wpapc_handle_request',
                request_id: requestId,
                request_action: action,
                nonce: wpapc_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Request ' + action + 'd successfully', 'success');
                    location.reload();
                }
            }
        });
    }
    
    function exportData(type) {
        window.location.href = wpapc_admin.ajax_url + 
            '?action=wpapc_export_' + type + 
            '&nonce=' + wpapc_admin.nonce;
    }
    
    function validateSettings() {
        const apiKey = $('#wpapc_openai_api_key').val();
        
        if (apiKey && !apiKey.startsWith('sk-')) {
            alert('Invalid OpenAI API key format');
            return false;
        }
        
        const requestDuration = parseInt($('#wpapc_request_duration').val());
        const vendorDeadline = parseInt($('#wpapc_vendor_deadline').val());
        
        if (vendorDeadline >= requestDuration) {
            alert('Vendor deadline must be less than total request duration');
            return false;
        }
        
        return true;
    }
    
    function testAPIKey() {
        const apiKey = $('#wpapc_openai_api_key').val();
        
        if (!apiKey) {
            alert('Please enter an API key first');
            return;
        }
        
        $('#test-api-key').prop('disabled', true).text('Testing...');
        
        $.ajax({
            url: wpapc_admin.ajax_url,
            method: 'POST',
            data: {
                action: 'wpapc_test_api_key',
                api_key: apiKey,
                nonce: wpapc_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotification('API key is valid!', 'success');
                } else {
                    showNotification('API key is invalid or not working', 'error');
                }
            },
            complete: function() {
                $('#test-api-key').prop('disabled', false).text('Test API Key');
            }
        });
    }
    
    function refreshLiveSessions() {
        $.ajax({
            url: wpapc_admin.ajax_url,
            method: 'POST',
            data: {
                action: 'wpapc_get_live_sessions',
                nonce: wpapc_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateLiveSessionsDisplay(response.data);
                }
            }
        });
    }
    
    function updateLiveSessionsDisplay(sessions) {
        const container = $('#wpapc-live-sessions');
        container.empty();
        
        if (sessions.length === 0) {
            container.html('<p>No active chat sessions</p>');
            return;
        }
        
        sessions.forEach(function(session) {
            const sessionHtml = `
                <div class="wpapc-session-item">
                    <span class="session-user">${session.user_name}</span>
                    <span class="session-status">${session.status}</span>
                    <span class="session-duration">${session.duration}</span>
                    <button class="button button-small view-session" data-session-id="${session.id}">View</button>
                </div>
            `;
            container.append(sessionHtml);
        });
    }
    
    function dismissNotice(noticeId) {
        $.ajax({
            url: wpapc_admin.ajax_url,
            method: 'POST',
            data: {
                action: 'wpapc_dismiss_notice',
                notice_id: noticeId,
                nonce: wpapc_admin.nonce
            }
        });
        
        $('#notice-' + noticeId).fadeOut();
    }
    
    function performBulkAction(action) {
        const selected = $('.wpapc-bulk-select:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (selected.length === 0) {
            alert('Please select at least one item');
            return;
        }
        
        if (!confirm('Are you sure you want to perform this action on ' + selected.length + ' items?')) {
            return;
        }
        
        $.ajax({
            url: wpapc_admin.ajax_url,
            method: 'POST',
            data: {
                action: 'wpapc_bulk_action',
                bulk_action: action,
                items: selected,
                nonce: wpapc_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Bulk action completed successfully', 'success');
                    location.reload();
                }
            }
        });
    }
    
    function insertQuickReply(template) {
        const replyField = $('#ticket-reply');
        replyField.val(replyField.val() + template);
        replyField.focus();
    }
    
    function updateStatistics(range) {
        $.ajax({
            url: wpapc_admin.ajax_url,
            method: 'POST',
            data: {
                action: 'wpapc_update_statistics',
                range: range,
                nonce: wpapc_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateStatisticsDisplay(response.data);
                }
            }
        });
    }
    
    function updateStatisticsDisplay(stats) {
        $('#total-sessions').text(stats.sessions);
        $('#total-requests').text(stats.requests);
        $('#total-tickets').text(stats.tickets);
        $('#conversion-rate').text(stats.conversion + '%');
    }
    
    function showNotification(message, type) {
        const notification = $('<div>')
            .addClass('notice notice-' + type + ' is-dismissible')
            .html('<p>' + message + '</p>');
        
        $('.wrap h1').after(notification);
        
        setTimeout(function() {
            notification.fadeOut();
        }, 3000);
    }
    
})(jQuery);