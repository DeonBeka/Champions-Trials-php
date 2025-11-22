/**
 * Volunteer Connect - Dashboard JavaScript
 * Handles dashboard interactions and dynamic content
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
});

/**
 * Initialize dashboard functionality
 */
function initializeDashboard() {
    setupTabNavigation();
    setupProfileEditing();
    setupActivityFeeds();
    setupNotifications();
    setupQuickActions();
    initializeCharts();
    setupAutoRefresh();
}

/**
 * Setup tab navigation with smooth transitions
 */
function setupTabNavigation() {
    const tabs = document.querySelectorAll('.tab');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('data-target') || 
                           this.textContent.toLowerCase().replace(' ', '') + 'Tab';
            
            // Switch active tab
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Switch content with animation
            tabContents.forEach(content => {
                if (content.id === targetId) {
                    content.style.display = 'block';
                    content.classList.add('fade-in');
                    
                    // Load dynamic content if needed
                    loadTabContent(targetId);
                } else {
                    content.style.display = 'none';
                    content.classList.remove('fade-in');
                }
            });
            
            // Update URL without reload
            const url = new URL(window.location);
            url.searchParams.set('tab', targetId.replace('Tab', ''));
            window.history.pushState({}, '', url);
        });
    });
    
    // Load tab from URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');
    if (activeTab) {
        const tabToActivate = document.querySelector(`[data-target="${activeTab}Tab"]`);
        if (tabToActivate) {
            tabToActivate.click();
        }
    }
}

/**
 * Load dynamic content for tabs
 */
function loadTabContent(tabId) {
    switch(tabId) {
        case 'notificationsTab':
            loadNotifications();
            break;
        case 'applicationsTab':
            loadApplications();
            break;
        case 'opportunitiesTab':
            loadOpportunities();
            break;
        case 'profileTab':
            loadProfileData();
            break;
    }
}

/**
 * Setup profile editing functionality
 */
function setupProfileEditing() {
    const editBtn = document.querySelector('#editProfileBtn');
    const saveBtn = document.querySelector('#saveProfileBtn');
    const cancelBtn = document.querySelector('#cancelProfileBtn');
    const profileForm = document.querySelector('#profileForm');
    
    if (editBtn) {
        editBtn.addEventListener('click', enableProfileEditing);
    }
    
    if (saveBtn) {
        saveBtn.addEventListener('click', saveProfile);
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', cancelProfileEdit);
    }
    
    if (profileForm) {
        profileForm.addEventListener('submit', handleProfileSubmit);
    }
}

/**
 * Enable profile editing
 */
function enableProfileEditing() {
    const fields = document.querySelectorAll('.profile-field');
    const editBtn = document.querySelector('#editProfileBtn');
    const saveCancelBtns = document.querySelector('#profileActions');
    
    fields.forEach(field => {
        if (field.tagName === 'INPUT' || field.tagName === 'TEXTAREA' || field.tagName === 'SELECT') {
            field.disabled = false;
            field.classList.add('editable');
        }
    });
    
    if (editBtn) editBtn.style.display = 'none';
    if (saveCancelBtns) saveCancelBtns.style.display = 'flex';
}

/**
 * Save profile via AJAX
 */
function saveProfile() {
    const form = document.querySelector('#profileForm');
    const formData = new FormData(form);
    const saveBtn = document.querySelector('#saveProfileBtn');
    
    // Show loading state
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    
    fetch('api/update-profile.php', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Profile updated successfully!', 'success');
            disableProfileEditing();
            
            // Update displayed values
            updateProfileDisplay(data.data);
        } else {
            showMessage(data.message || 'Failed to update profile', 'error');
        }
    })
    .catch(error => {
        console.error('Profile update error:', error);
        showMessage('A network error occurred. Please try again.', 'error');
    })
    .finally(() => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
    });
}

/**
 * Cancel profile editing
 */
function cancelProfileEdit() {
    const form = document.querySelector('#profileForm');
    const fields = form.querySelectorAll('.profile-field');
    
    // Reset to original values
    fields.forEach(field => {
        if (field.tagName === 'INPUT' || field.tagName === 'TEXTAREA' || field.tagName === 'SELECT') {
            field.disabled = true;
            field.classList.remove('editable');
        }
    });
    
    const editBtn = document.querySelector('#editProfileBtn');
    const saveCancelBtns = document.querySelector('#profileActions');
    
    if (editBtn) editBtn.style.display = 'inline-block';
    if (saveCancelBtns) saveCancelBtns.style.display = 'none';
}

/**
 * Disable profile editing
 */
function disableProfileEditing() {
    const fields = document.querySelectorAll('.profile-field');
    const editBtn = document.querySelector('#editProfileBtn');
    const saveCancelBtns = document.querySelector('#profileActions');
    
    fields.forEach(field => {
        if (field.tagName === 'INPUT' || field.tagName === 'TEXTAREA' || field.tagName === 'SELECT') {
            field.disabled = true;
            field.classList.remove('editable');
        }
    });
    
    if (editBtn) editBtn.style.display = 'inline-block';
    if (saveCancelBtns) saveCancelBtns.style.display = 'none';
}

/**
 * Update profile display with new data
 */
function updateProfileDisplay(data) {
    Object.keys(data).forEach(key => {
        const displayElement = document.querySelector(`[data-display="${key}"]`);
        if (displayElement) {
            displayElement.textContent = data[key];
        }
    });
}

/**
 * Handle profile form submission
 */
function handleProfileSubmit(e) {
    e.preventDefault();
    saveProfile();
}

/**
 * Setup activity feeds
 */
function setupActivityFeeds() {
    // Load recent activity
    loadRecentActivity();
    
    // Setup refresh button
    const refreshBtn = document.querySelector('#refreshActivity');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            loadRecentActivity().finally(() => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
            });
        });
    }
}

/**
 * Load recent activity
 */
function loadRecentActivity() {
    return fetch('api/recent-activity.php', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayActivity(data.activities);
        }
    })
    .catch(error => {
        console.error('Activity loading error:', error);
    });
}

/**
 * Display activity feed
 */
function displayActivity(activities) {
    const container = document.querySelector('#activityList');
    if (!container) return;
    
    if (activities.length === 0) {
        container.innerHTML = '<p class="no-activity">No recent activity</p>';
        return;
    }
    
    let html = '';
    activities.forEach(activity => {
        html += createActivityItem(activity);
    });
    
    container.innerHTML = html;
    
    // Animate new items
    container.querySelectorAll('.activity-item').forEach((item, index) => {
        setTimeout(() => {
            item.classList.add('fade-in');
        }, index * 100);
    });
}

/**
 * Create activity item HTML
 */
function createActivityItem(activity) {
    const icon = getActivityIcon(activity.type);
    const timeAgo = formatTimeAgo(activity.created_at);
    
    return `
        <div class="activity-item">
            <div class="activity-icon">
                <i class="fas fa-${icon}" style="color: ${getActivityColor(activity.type)}"></i>
            </div>
            <div class="activity-content">
                <h4>${activity.title}</h4>
                <p>${activity.description}</p>
                <span class="activity-time">${timeAgo}</span>
                ${activity.status ? `<span class="badge badge-${activity.status}">${activity.status}</span>` : ''}
            </div>
        </div>
    `;
}

/**
 * Get activity icon based on type
 */
function getActivityIcon(type) {
    const icons = {
        'application': 'paper-plane',
        'opportunity': 'plus-circle',
        'message': 'envelope',
        'profile_update': 'user-edit',
        'review': 'star',
        'accepted': 'check-circle',
        'rejected': 'times-circle'
    };
    return icons[type] || 'circle';
}

/**
 * Get activity color based on type
 */
function getActivityColor(type) {
    const colors = {
        'application': 'var(--primary-color)',
        'opportunity': 'var(--secondary-color)',
        'message': 'var(--accent-color)',
        'profile_update': 'var(--info-color)',
        'review': '#fbbf24',
        'accepted': 'var(--success-color)',
        'rejected': 'var(--danger-color)'
    };
    return colors[type] || '#6b7280';
}

/**
 * Setup notifications system
 */
function setupNotifications() {
    // Load notifications on page load
    loadNotifications();
    
    // Setup mark all as read button
    const markAllReadBtn = document.querySelector('#markAllRead');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', markAllNotificationsRead);
    }
}

/**
 * Load notifications
 */
function loadNotifications() {
    fetch('api/notifications.php', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayNotifications(data.notifications);
            updateNotificationBadge(data.unread_count);
        }
    })
    .catch(error => {
        console.error('Notifications loading error:', error);
    });
}

/**
 * Display notifications
 */
function displayNotifications(notifications) {
    const container = document.querySelector('#notificationsList');
    if (!container) return;
    
    if (notifications.length === 0) {
        container.innerHTML = '<p class="no-notifications">No notifications</p>';
        return;
    }
    
    let html = '';
    notifications.forEach(notification => {
        html += createNotificationItem(notification);
    });
    
    container.innerHTML = html;
}

/**
 * Create notification item HTML
 */
function createNotificationItem(notification) {
    const icon = getNotificationIcon(notification.type);
    const timeAgo = formatTimeAgo(notification.created_at);
    const unreadClass = notification.is_read ? '' : 'unread';
    
    return `
        <div class="notification-item ${unreadClass}" data-id="${notification.id}">
            <div class="notification-icon">
                <i class="fas fa-${icon}"></i>
            </div>
            <div class="notification-content">
                <h4>${notification.title}</h4>
                <p>${notification.message}</p>
                <span class="notification-time">${timeAgo}</span>
            </div>
            <div class="notification-actions">
                ${!notification.is_read ? `<button class="btn-link" onclick="markNotificationRead(${notification.id})">Mark as read</button>` : ''}
            </div>
        </div>
    `;
}

/**
 * Get notification icon
 */
function getNotificationIcon(type) {
    const icons = {
        'application': 'paper-plane',
        'message': 'envelope',
        'opportunity': 'plus-circle',
        'system': 'info-circle',
        'reminder': 'bell'
    };
    return icons[type] || 'bell';
}

/**
 * Update notification badge
 */
function updateNotificationBadge(count) {
    const badges = document.querySelectorAll('.notification-badge');
    badges.forEach(badge => {
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    });
}

/**
 * Mark notification as read
 */
function markNotificationRead(notificationId) {
    fetch('api/mark-notification-read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ id: notificationId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const notificationItem = document.querySelector(`[data-id="${notificationId}"]`);
            if (notificationItem) {
                notificationItem.classList.remove('unread');
                const actions = notificationItem.querySelector('.notification-actions');
                actions.innerHTML = '';
            }
            
            // Update badge
            const currentBadge = document.querySelector('.notification-badge');
            if (currentBadge) {
                const currentCount = parseInt(currentBadge.textContent) || 0;
                updateNotificationBadge(Math.max(0, currentCount - 1));
            }
        }
    })
    .catch(error => {
        console.error('Mark notification read error:', error);
    });
}

/**
 * Mark all notifications as read
 */
function markAllNotificationsRead() {
    fetch('api/mark-all-notifications-read.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelectorAll('.notification-item').forEach(item => {
                item.classList.remove('unread');
                const actions = item.querySelector('.notification-actions');
                if (actions) {
                    actions.innerHTML = '';
                }
            });
            
            updateNotificationBadge(0);
            showMessage('All notifications marked as read', 'success');
        }
    })
    .catch(error => {
        console.error('Mark all notifications read error:', error);
    });
}

/**
 * Setup quick actions
 */
function setupQuickActions() {
    // Quick apply buttons
    document.querySelectorAll('.quick-apply').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const opportunityId = this.dataset.opportunityId;
            quickApply(opportunityId);
        });
    });
    
    // Quick message buttons
    document.querySelectorAll('.quick-message').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.dataset.userId;
            openQuickMessage(userId);
        });
    });
}

/**
 * Quick apply to opportunity
 */
function quickApply(opportunityId) {
    if (!confirm('Are you sure you want to apply for this opportunity?')) {
        return;
    }
    
    const btn = document.querySelector(`[data-opportunity-id="${opportunityId}"]`);
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Applying...';
    
    fetch('api/quick-apply.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ opportunity_id: opportunityId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Application submitted successfully!', 'success');
            btn.innerHTML = '<i class="fas fa-check"></i> Applied';
            btn.classList.add('btn-success');
            btn.disabled = true;
        } else {
            showMessage(data.message || 'Failed to apply', 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Quick apply error:', error);
        showMessage('A network error occurred. Please try again.', 'error');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

/**
 * Open quick message modal
 */
function openQuickMessage(userId) {
    const modal = document.createElement('div');
    modal.className = 'modal active';
    modal.innerHTML = `
        <div class="modal-overlay" onclick="this.parentElement.remove()"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Send Message</h3>
                <button class="modal-close" onclick="this.closest('.modal').remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="quickMessageForm">
                <input type="hidden" name="receiver_id" value="${userId}">
                <div class="form-group">
                    <label class="form-label">Subject</label>
                    <input type="text" class="form-input" name="subject" placeholder="Message subject" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Message</label>
                    <textarea class="form-textarea" name="message" placeholder="Type your message here..." required></textarea>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">Send Message</button>
                    <button type="button" class="btn btn-outline" onclick="this.closest('.modal').remove()">Cancel</button>
                </div>
            </form>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Focus on subject input
    setTimeout(() => {
        modal.querySelector('input[name="subject"]').focus();
    }, 100);
    
    // Handle form submission
    modal.querySelector('#quickMessageForm').addEventListener('submit', function(e) {
        e.preventDefault();
        sendQuickMessage(this);
    });
}

/**
 * Send quick message
 */
function sendQuickMessage(form) {
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    
    fetch('api/send-message.php', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Message sent successfully!', 'success');
            form.closest('.modal').remove();
        } else {
            showMessage(data.message || 'Failed to send message', 'error');
        }
    })
    .catch(error => {
        console.error('Send message error:', error);
        showMessage('A network error occurred. Please try again.', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

/**
 * Initialize charts for dashboard statistics
 */
function initializeCharts() {
    // Only initialize if Chart.js is available and chart containers exist
    if (typeof Chart !== 'undefined') {
        initializeActivityChart();
        initializeSkillsChart();
    }
}

/**
 * Initialize activity chart
 */
function initializeActivityChart() {
    const canvas = document.getElementById('activityChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Applications',
                data: [3, 5, 2, 8, 4, 6, 3],
                borderColor: 'var(--primary-color)',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

/**
 * Initialize skills chart
 */
function initializeSkillsChart() {
    const canvas = document.getElementById('skillsChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Education', 'Healthcare', 'Environment', 'Community'],
            datasets: [{
                data: [30, 25, 20, 25],
                backgroundColor: [
                    'var(--primary-color)',
                    'var(--secondary-color)',
                    'var(--accent-color)',
                    'var(--danger-color)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

/**
 * Setup auto-refresh for dashboard data
 */
function setupAutoRefresh() {
    // Auto-refresh every 5 minutes
    setInterval(() => {
        refreshDashboardData();
    }, 5 * 60 * 1000);
    
    // Setup manual refresh
    const refreshBtn = document.querySelector('#refreshDashboard');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', refreshDashboardData);
    }
}

/**
 * Refresh dashboard data
 */
function refreshDashboardData() {
    const refreshBtn = document.querySelector('#refreshDashboard');
    if (refreshBtn) {
        refreshBtn.disabled = true;
        refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }
    
    // Load fresh data
    Promise.all([
        loadRecentActivity(),
        loadNotifications(),
        updateStats()
    ])
    .finally(() => {
        if (refreshBtn) {
            refreshBtn.disabled = false;
            refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
        }
    });
}

/**
 * Update dashboard statistics
 */
function updateStats() {
    return fetch('api/dashboard-stats.php', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateStatCards(data.stats);
        }
    })
    .catch(error => {
        console.error('Stats update error:', error);
    });
}

/**
 * Update stat cards with new data
 */
function updateStatCards(stats) {
    Object.keys(stats).forEach(key => {
        const element = document.querySelector(`[data-stat="${key}"]`);
        if (element) {
            const currentValue = parseInt(element.textContent) || 0;
            const newValue = parseInt(stats[key]) || 0;
            
            if (newValue !== currentValue) {
                // Animate the change
                animateValue(element, currentValue, newValue, 1000);
            }
        }
    });
}

/**
 * Animate numeric value change
 */
function animateValue(element, start, end, duration) {
    const startTime = performance.now();
    
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        const currentValue = Math.floor(start + (end - start) * progress);
        element.textContent = currentValue;
        
        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }
    
    requestAnimationFrame(update);
}

/**
 * Utility functions
 */
function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);
    
    if (minutes < 1) return 'just now';
    if (minutes < 60) return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
    if (hours < 24) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    if (days < 7) return `${days} day${days > 1 ? 's' : ''} ago`;
    
    return date.toLocaleDateString();
}

// Expose functions for global access
window.switchTab = function(tabName) {
    const tab = document.querySelector(`[data-target="${tabName}Tab"]`);
    if (tab) {
        tab.click();
    }
};

window.markNotificationRead = markNotificationRead;
window.quickApply = quickApply;
window.openQuickMessage = openQuickMessage;