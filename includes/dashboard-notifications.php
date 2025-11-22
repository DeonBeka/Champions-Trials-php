<?php
/**
 * Dashboard Notifications Tab Content
 */

// Only allow access if included from dashboard
if (!defined('VOLUNTEER_CONNECT')) {
    exit('Direct access denied');
}
?>

<div class="card">
    <div class="card-header">
        <h3>Notifications</h3>
        <div style="display: flex; gap: 0.5rem;">
            <?php if (!empty($notifications)): ?>
                <button class="btn btn-outline btn-small" id="markAllRead" onclick="markAllNotificationsRead()">
                    <i class="fas fa-check-double"></i> Mark All Read
                </button>
            <?php endif; ?>
            <button class="btn btn-outline btn-small" onclick="loadNotifications()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>
    
    <div id="notificationsList">
        <?php if (empty($notifications)): ?>
            <div style="text-align: center; padding: 2rem;">
                <i class="fas fa-bell-slash" style="font-size: 3rem; color: #6b7280; margin-bottom: 1rem;"></i>
                <h4>No Notifications</h4>
                <p style="color: #6b7280;">You're all caught up! Check back here for updates on your applications and messages.</p>
            </div>
        <?php else: ?>
            <div class="notifications-list">
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?>" data-id="<?php echo $notification['id']; ?>">
                        <div class="notification-icon">
                            <i class="fas fa-<?php echo getNotificationIcon($notification['type']); ?>"></i>
                        </div>
                        <div class="notification-content">
                            <h4><?php echo htmlspecialchars($notification['title']); ?></h4>
                            <p><?php echo htmlspecialchars($notification['message']); ?></p>
                            <span class="notification-time"><?php echo Utils::timeAgo($notification['created_at']); ?></span>
                        </div>
                        <div class="notification-actions">
                            <?php if (!$notification['is_read']): ?>
                                <button class="btn-link" onclick="markNotificationRead(<?php echo $notification['id']; ?>)">
                                    Mark as read
                                </button>
                            <?php endif; ?>
                            <button class="btn-link" onclick="deleteNotification(<?php echo $notification['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Notification Settings -->
<div class="card">
    <div class="card-header">
        <h4>Notification Settings</h4>
    </div>
    
    <form id="notificationSettingsForm" method="POST" action="api/update-notification-settings.php">
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="email_notifications" checked>
                <span class="checkmark"></span>
                Email notifications for new applications
            </label>
        </div>
        
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="message_notifications" checked>
                <span class="checkmark"></span>
                In-app notifications for new messages
            </label>
        </div>
        
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="opportunity_notifications" checked>
                <span class="checkmark"></span>
                Notifications for opportunity updates
            </label>
        </div>
        
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="marketing_notifications">
                <span class="checkmark"></span>
                Marketing and feature updates
            </label>
        </div>
        
        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
</div>

<!-- Notification Statistics -->
<div class="card">
    <div class="card-header">
        <h4>Notification Summary</h4>
    </div>
    
    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-number"><?php echo count($notifications); ?></div>
            <div class="stat-label">Total Notifications</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">
                <?php 
                $unreadCount = count(array_filter($notifications, fn($n) => !$n['is_read']));
                echo $unreadCount; 
                ?>
            </div>
            <div class="stat-label">Unread</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">
                <?php 
                $applicationCount = count(array_filter($notifications, fn($n) => $n['type'] === 'application'));
                echo $applicationCount; 
                ?>
            </div>
            <div class="stat-label">Applications</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">
                <?php 
                $messageCount = count(array_filter($notifications, fn($n) => $n['type'] === 'message'));
                echo $messageCount; 
                ?>
            </div>
            <div class="stat-label">Messages</div>
        </div>
    </div>
</div>

<style>
.notification-item {
    display: flex;
    align-items: start;
    gap: 1rem;
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
    transition: var(--transition);
}

.notification-item:hover {
    background: var(--light-color);
}

.notification-item.unread {
    background: #f0f9ff;
    border-left: 4px solid var(--primary-color);
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--light-color);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: var(--primary-color);
}

.notification-content {
    flex: 1;
}

.notification-content h4 {
    margin-bottom: 0.25rem;
    font-size: 1rem;
}

.notification-content p {
    color: #6b7280;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.notification-time {
    color: #9ca3af;
    font-size: 0.75rem;
}

.notification-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    align-items: flex-end;
}

.btn-link {
    background: none;
    border: none;
    color: var(--primary-color);
    font-size: 0.875rem;
    cursor: pointer;
    text-decoration: none;
}

.btn-link:hover {
    text-decoration: underline;
}

.notifications-list {
    max-height: 400px;
    overflow-y: auto;
}
</style>

<?php
// Helper function to get notification icon
function getNotificationIcon($type) {
    $icons = [
        'application' => 'paper-plane',
        'message' => 'envelope',
        'opportunity' => 'bullhorn',
        'system' => 'info-circle',
        'reminder' => 'bell'
    ];
    return $icons[$type] ?? 'bell';
}
?>

<script>
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
                actions.innerHTML = '<button class="btn-link" onclick="deleteNotification(' + notificationId + ')"><i class="fas fa-trash"></i></button>';
            }
            
            // Update badge
            const currentBadge = document.querySelector('.notification-badge');
            if (currentBadge) {
                const currentCount = parseInt(currentBadge.textContent) || 0;
                updateNotificationBadge(Math.max(0, currentCount - 1));
            }
        } else {
            showMessage(data.message || 'Failed to mark notification as read', 'error');
        }
    })
    .catch(error => {
        showMessage('A network error occurred. Please try again.', 'error');
    });
}

function markAllNotificationsRead() {
    if (!confirm('Mark all notifications as read?')) {
        return;
    }
    
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
                    actions.innerHTML = '<button class="btn-link" onclick="deleteNotification(' + item.dataset.id + ')"><i class="fas fa-trash"></i></button>';
                }
            });
            
            updateNotificationBadge(0);
            showMessage('All notifications marked as read', 'success');
        } else {
            showMessage(data.message || 'Failed to mark all notifications as read', 'error');
        }
    })
    .catch(error => {
        showMessage('A network error occurred. Please try again.', 'error');
    });
}

function deleteNotification(notificationId) {
    if (!confirm('Delete this notification?')) {
        return;
    }
    
    fetch('api/delete-notification.php', {
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
                notificationItem.remove();
            }
            
            showMessage('Notification deleted', 'success');
        } else {
            showMessage(data.message || 'Failed to delete notification', 'error');
        }
    })
    .catch(error => {
        showMessage('A network error occurred. Please try again.', 'error');
    });
}

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

// Handle notification settings form
document.getElementById('notificationSettingsForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Notification settings saved successfully', 'success');
        } else {
            showMessage(data.message || 'Failed to save settings', 'error');
        }
    })
    .catch(error => {
        showMessage('A network error occurred. Please try again.', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});
</script>