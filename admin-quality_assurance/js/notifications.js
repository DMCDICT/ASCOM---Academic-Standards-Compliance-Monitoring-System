// notifications.js - Notification system for department dean interface
class NotificationSystem {
    constructor() {
        this.notificationIcon = document.querySelector('.notification-icon');
        this.notificationDropdown = document.getElementById('notificationDropdown');
        this.notificationCount = document.querySelector('.notification-count');
        this.notifications = [];
        this.unreadCount = 0;
        
            icon: !!this.notificationIcon,
            dropdown: !!this.notificationDropdown,
            count: !!this.notificationCount
        });
        
        this.init();
    }
    
    init() {
        this.loadNotifications();
        this.setupEventListeners();
        this.startAutoRefresh();
    }
    
    setupEventListeners() {
        if (!this.notificationIcon) {
            console.error('🔔 Notification icon not found!');
            return;
        }
        
        
        // Toggle notification dropdown
        this.notificationIcon.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleDropdown();
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.notificationIcon.contains(e.target)) {
                this.closeDropdown();
            }
        });
    }
    
    async loadNotifications() {
        try {
            const response = await fetch('../api/get_notifications.php?role=dean&limit=10');
            const data = await response.json();
            
            
            if (data.success) {
                this.notifications = data.data;
                this.unreadCount = this.notifications.filter(n => !n.is_read).length;
                this.updateUI();
            } else {
                console.error('🔔 Failed to load notifications:', data.error);
            }
        } catch (error) {
            console.error('🔔 Error loading notifications:', error);
        }
    }
    
    updateUI() {
        // Update notification count
        let displayCount = this.unreadCount;
        if (this.unreadCount > 99) {
            displayCount = '99+';
        }
        
        this.notificationCount.textContent = displayCount;
        this.notificationCount.setAttribute('data-count', this.unreadCount);
        
        if (this.unreadCount > 0) {
            this.notificationCount.style.display = 'flex';
            // Add animation class for new notifications
            this.notificationCount.classList.add('new');
            setTimeout(() => {
                this.notificationCount.classList.remove('new');
            }, 1000);
        } else {
            this.notificationCount.style.display = 'none';
        }
        
        // Update dropdown content
        this.updateDropdownContent();
    }
    
    updateDropdownContent() {
        
        if (this.notifications.length === 0) {
            this.notificationDropdown.innerHTML = `
                <h3>Notifications</h3>
                <div class="notification-empty">No new notifications</div>
            `;
            return;
        }
        
        const notificationsHTML = this.notifications.map(notification => `
            <div class="notification-item ${!notification.is_read ? 'unread' : ''}" data-id="${notification.id}">
                <div class="notification-content">
                    <div class="notification-title">${notification.title}</div>
                    <div class="notification-message">${notification.message}</div>
                    <div class="notification-meta">
                        <span class="notification-sender">${notification.sender_name} (${notification.sender_role})</span>
                        <span class="notification-time">${notification.created_at}</span>
                    </div>
                </div>
                <div class="notification-actions">
                    ${!notification.is_read ? '<div class="notification-dot"></div>' : ''}
                    <div class="notification-menu">
                        <button class="notification-menu-btn" onclick="event.stopPropagation(); toggleNotificationMenu(${notification.id})">⋯</button>
                        <div class="notification-menu-dropdown" id="menu-${notification.id}">
                            <button class="notification-menu-item ${notification.is_read ? 'mark-unread' : 'mark-read'}" onclick="toggleNotificationRead(${notification.id})">
                                <i>${notification.is_read ? '📧' : '📬'}</i>
                                ${notification.is_read ? 'Mark as Unread' : 'Mark as Read'}
                            </button>
                            <button class="notification-menu-item remove" onclick="removeNotification(${notification.id})">
                                <i>🗑️</i>
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
        
        this.notificationDropdown.innerHTML = `
            <h3>Notifications</h3>
            <div class="notification-list">
                ${notificationsHTML}
            </div>
            <div class="notification-actions">
                <button class="mark-all-read-btn" onclick="notificationSystem.markAllAsRead()">Mark All Read</button>
            </div>
        `;
        
        // Add click handlers for individual notifications
        this.notificationDropdown.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', (e) => {
                const notificationId = parseInt(item.dataset.id);
                this.markAsRead(notificationId);
            });
        });
    }
    
    async markAsRead(notificationId) {
        try {
            const response = await fetch('../api/mark_notification_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ notification_id: notificationId })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Update local state
                const notification = this.notifications.find(n => n.id === notificationId);
                if (notification) {
                    notification.is_read = true;
                    this.unreadCount = this.notifications.filter(n => !n.is_read).length;
                    this.updateUI();
                }
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }
    
    async markAllAsRead() {
        try {
            const unreadNotifications = this.notifications.filter(n => !n.is_read);
            
            for (const notification of unreadNotifications) {
                await this.markAsRead(notification.id);
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
        }
    }
    
    toggleDropdown() {
        
        if (this.notificationDropdown.style.display === 'block') {
            this.closeDropdown();
        } else {
            this.openDropdown();
        }
    }
    
    openDropdown() {
        this.notificationDropdown.style.display = 'block';
    }
    
    closeDropdown() {
        this.notificationDropdown.style.display = 'none';
    }
    
    startAutoRefresh() {
        // Refresh notifications every 30 seconds
        setInterval(() => {
            this.loadNotifications();
        }, 30000);
    }
}

// Global functions for notification menu
function toggleNotificationMenu(notificationId) {
    // Close all other menus first
    document.querySelectorAll('.notification-menu-dropdown').forEach(menu => {
        if (menu.id !== `menu-${notificationId}`) {
            menu.classList.remove('show');
        }
    });
    
    // Toggle current menu
    const menu = document.getElementById(`menu-${notificationId}`);
    if (menu) {
        menu.classList.toggle('show');
    }
}

async function toggleNotificationRead(notificationId) {
    try {
        const notification = window.notificationSystem.notifications.find(n => n.id === notificationId);
        if (!notification) return;
        
        const newReadStatus = !notification.is_read;
        const response = await fetch('../api/mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                notification_id: notificationId,
                mark_as_read: newReadStatus
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update local state
            notification.is_read = newReadStatus;
            window.notificationSystem.unreadCount = window.notificationSystem.notifications.filter(n => !n.is_read).length;
            window.notificationSystem.updateUI();
            
            // Close menu
            const menu = document.getElementById(`menu-${notificationId}`);
            if (menu) {
                menu.classList.remove('show');
            }
        }
    } catch (error) {
        console.error('Error toggling notification read status:', error);
    }
}

async function removeNotification(notificationId) {
    if (!confirm('Are you sure you want to remove this notification?')) {
        return;
    }
    
    try {
        const response = await fetch('../api/remove_notification.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ notification_id: notificationId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Remove from local state
            window.notificationSystem.notifications = window.notificationSystem.notifications.filter(n => n.id !== notificationId);
            window.notificationSystem.unreadCount = window.notificationSystem.notifications.filter(n => !n.is_read).length;
            window.notificationSystem.updateUI();
        } else {
            alert('Failed to remove notification: ' + (data.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error removing notification:', error);
        alert('Failed to remove notification');
    }
}

// Initialize notification system when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.notificationSystem = new NotificationSystem();
    
    // Add a fallback click handler for testing
    setTimeout(() => {
        const notificationIcon = document.querySelector('.notification-icon');
        if (notificationIcon) {
            notificationIcon.addEventListener('click', (e) => {
                const dropdown = document.getElementById('notificationDropdown');
                if (dropdown) {
                    if (dropdown.style.display === 'block') {
                        dropdown.style.display = 'none';
                    } else {
                        dropdown.style.display = 'block';
                    }
                }
            });
        }
    }, 1000);
});
