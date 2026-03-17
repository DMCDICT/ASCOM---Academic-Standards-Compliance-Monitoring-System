// notifications.js - Notification system for super admin interface
class NotificationSystem {
    constructor() {
        this.notificationIcon = document.querySelector('.notification-icon');
        this.notificationDropdown = document.getElementById('notificationDropdown');
        this.notificationCount = document.querySelector('.notification-count');
        this.notifications = [];
        this.unreadCount = 0;
        
        console.log('🔔 Super Admin notification system elements:', {
            icon: !!this.notificationIcon,
            dropdown: !!this.notificationDropdown,
            count: !!this.notificationCount
        });
        
        // Debug: Log all elements with notification classes
        console.log('🔔 Super Admin all notification elements:', {
            allIcons: document.querySelectorAll('.notification-icon'),
            allDropdowns: document.querySelectorAll('[id*="notification"]'),
            allCounts: document.querySelectorAll('.notification-count')
        });
        
        this.init();
    }
    
    init() {
        this.loadNotifications();
        this.setupEventListeners();
        this.startAutoRefresh();
    }
    
    setupEventListeners() {
        if (!this.notificationIcon) return;
        
        // Document-level delegation (capture) - catches clicks inside the dropdown
        document.addEventListener('click', (e) => {
            if (!this.notificationDropdown || !this.notificationDropdown.contains(e.target)) return;
            if (this.notificationDropdown.style.display !== 'block') return;
            const target = e.target;
            const menuBtn = target.closest('.notification-menu-btn');
            if (menuBtn) {
                const item = menuBtn.closest('.notification-item');
                if (item?.dataset.id) {
                    toggleNotificationMenu(parseInt(item.dataset.id, 10));
                }
                return;
            }
            const menuItem = target.closest('.notification-menu-item');
            if (menuItem) {
                const menu = menuItem.closest('.notification-menu-dropdown');
                const id = menu?.id ? parseInt(menu.id.replace('menu-', ''), 10) : NaN;
                if (!isNaN(id)) {
                    if (menuItem.classList.contains('remove')) removeNotification(id);
                    else toggleNotificationRead(id);
                }
                return;
            }
            if (target.closest('.notification-toggle-read-btn')) {
                toggleAlreadyReadSection();
                return;
            }
            if (target.closest('.mark-all-read-btn')) {
                this.markAllAsRead();
                return;
            }
            const notifItem = target.closest('.notification-item');
            if (notifItem?.dataset.id && !target.closest('.notification-menu') && !target.closest('.notification-menu-dropdown')) {
                this.markAsRead(parseInt(notifItem.dataset.id, 10));
            }
        }, true);

        // Also attach to dropdown directly as backup
        if (this.notificationDropdown) {
            this.notificationDropdown.addEventListener('click', (e) => {
                e.stopPropagation();
                const target = e.target;
                const menuBtn = target.closest('.notification-menu-btn');
                if (menuBtn) {
                    const item = menuBtn.closest('.notification-item');
                    if (item && item.dataset.id) {
                        toggleNotificationMenu(parseInt(item.dataset.id, 10));
                    }
                    return;
                }
                const menuItem = target.closest('.notification-menu-item');
                if (menuItem) {
                    const menu = menuItem.closest('.notification-menu-dropdown');
                    const id = menu?.id ? parseInt(menu.id.replace('menu-', ''), 10) : NaN;
                    if (!isNaN(id)) {
                        if (menuItem.classList.contains('remove')) removeNotification(id);
                        else toggleNotificationRead(id);
                    }
                    return;
                }
                if (target.closest('.notification-toggle-read-btn')) {
                    toggleAlreadyReadSection();
                    return;
                }
                if (target.closest('.mark-all-read-btn')) {
                    this.markAllAsRead();
                    return;
                }
                const notifItem = target.closest('.notification-item');
                if (notifItem && notifItem.dataset.id && !target.closest('.notification-menu') && !target.closest('.notification-menu-dropdown')) {
                    this.markAsRead(parseInt(notifItem.dataset.id, 10));
                }
            }, true);
        }
        
        // Close when clicking outside icon and panel
        document.addEventListener('click', (e) => {
            const inIcon = this.notificationIcon && this.notificationIcon.contains(e.target);
            const inPanel = this.notificationDropdown && this.notificationDropdown.contains(e.target);
            if (!inIcon && !inPanel) {
                this.closeDropdown();
            }
            // Close three-dots menus when clicking outside them (including elsewhere in panel)
            const inMenu = e.target.closest('.notification-menu-dropdown, .notification-menu-btn');
            if (!inMenu) {
                document.querySelectorAll('.notification-menu-dropdown.show').forEach(m => m.classList.remove('show'));
            }
        });
        
    }
    
    async loadNotifications() {
        // Use server-rendered data first (loaded by content.php - no fetch needed)
        const initial = window.INITIAL_NOTIFICATIONS;
        if (initial && initial.success && Array.isArray(initial.data)) {
            this.notifications = initial.data;
            this.unreadCount = this.notifications.filter(n => !n.is_read).length;
            this.updateUI();
            return;
        }
        if (initial && !initial.success) {
            this.showErrorInDropdown(initial.error || 'Load failed');
            return;
        }
        // Fallback: try API fetch (for refresh)
        try {
            const apiUrl = './api/get_notifications.php?role=super_admin&limit=10';
            const response = await fetch(apiUrl, { credentials: 'same-origin' });
            if (!response.ok) throw new Error(`API ${response.status}`);
            const data = await response.json();
            if (data.success && Array.isArray(data.data)) {
                this.notifications = data.data.map(n => ({
                    ...n,
                    created_at_ts: n.created_at_ts || (n.created_at ? Math.floor(new Date(n.created_at).getTime() / 1000) : 0)
                }));
                this.unreadCount = this.notifications.filter(n => !n.is_read).length;
                this.updateUI();
            } else {
                this.showErrorInDropdown(data.error || 'Unknown error');
            }
        } catch (error) {
            this.showErrorInDropdown(error.message);
        }
    }

    showErrorInDropdown(message) {
        if (!this.notificationDropdown) return;
        this.notificationDropdown.innerHTML = `
            <h3>Notifications</h3>
            <div class="notification-empty" style="color: #c00;">Failed to load: ${message}</div>
        `;
    }
    
    updateUI() {
        if (!this.notificationDropdown) return;
        
        // Update notification count
        let displayCount = this.unreadCount;
        if (this.unreadCount > 99) {
            displayCount = '99+';
        }
        
        if (this.notificationCount) {
            this.notificationCount.textContent = displayCount;
            this.notificationCount.setAttribute('data-count', this.unreadCount);
            if (this.unreadCount > 0) {
                this.notificationCount.style.display = 'flex';
                this.notificationCount.classList.add('new');
                setTimeout(() => this.notificationCount.classList.remove('new'), 1000);
            } else {
                this.notificationCount.style.display = 'none';
            }
        }
        
        // Update dropdown content
        this.updateDropdownContent();
    }
    
    updateDropdownContent() {
        if (this.notifications.length === 0) {
            this.notificationDropdown.innerHTML = `
                <h3>Notifications</h3>
                <div class="notification-list"><div class="notification-empty">No new notifications</div></div>
                <div class="notification-actions"><button class="mark-all-read-btn" onclick="notificationSystem.markAllAsRead()">Mark All Read</button></div>
            `;
            return;
        }
        const unread = this.notifications.filter(n => !n.is_read);
        const read = this.notifications.filter(n => n.is_read);
        const renderItem = (n, showDot = false) => `
            <div class="notification-item ${showDot ? 'unread' : ''}" data-id="${n.id}">
                <div class="notification-content">
                    <div class="notification-title">${n.title || ''}</div>
                    <div class="notification-message">${n.message || ''}</div>
                    <div class="notification-meta">
                        <span class="notification-sender">${n.sender_name || ''} (${n.sender_role || ''})</span>
                        <span class="notification-time">${n.created_at || ''}</span>
                    </div>
                </div>
                <div class="notification-actions">
                    ${showDot ? '<div class="notification-dot"></div>' : ''}
                    <div class="notification-menu">
                        <button class="notification-menu-btn" onclick="event.stopPropagation(); toggleNotificationMenu(${n.id})">⋯</button>
                        <div class="notification-menu-dropdown" id="menu-${n.id}">
                            <button class="notification-menu-item ${showDot ? 'mark-read' : 'mark-unread'}" onclick="toggleNotificationRead(${n.id})">
                                <i>${showDot ? '📬' : '📧'}</i>
                                ${showDot ? 'Mark as Read' : 'Mark as Unread'}
                            </button>
                            <button class="notification-menu-item remove" onclick="removeNotification(${n.id})"><i>🗑️</i> Remove</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        const hasUnread = unread.length > 0;
        const hasRead = read.length > 0;
        let listHTML = '';
        if (hasUnread) {
            listHTML += `<div class="notification-section notification-unread-section"><div class="notification-section-title">Unread</div>${unread.map(n => renderItem(n, true)).join('')}</div>`;
            if (hasRead) {
                listHTML += `
                <div class="notification-toggle-read-wrap">
                    <button type="button" class="notification-toggle-read-btn" id="toggleReadBtn" onclick="toggleAlreadyReadSection()">View Already Read Notifications</button>
                </div>
                <div class="notification-section notification-read-section" id="notificationReadSection" style="display:none;">
                    <div class="notification-section-title">Already Read</div>
                    ${read.map(n => renderItem(n, false)).join('')}
                    <div class="notification-toggle-read-wrap">
                        <button type="button" class="notification-toggle-read-btn notification-hide-read-btn" onclick="toggleAlreadyReadSection()">Hide Already Read Notifications</button>
                    </div>
                </div>`;
            }
        } else if (hasRead) {
            listHTML += `<div class="notification-section notification-read-section" id="notificationReadSection"><div class="notification-section-title">Already Read</div>${read.map(n => renderItem(n, false)).join('')}</div>`;
        }
        this.notificationDropdown.innerHTML = `
            <h3>Notifications</h3>
            <div class="notification-list" id="notificationListContainer">${listHTML}</div>
            <div class="notification-actions">
                <button class="mark-all-read-btn" onclick="notificationSystem.markAllAsRead()">Mark All Read</button>
            </div>
        `;
        this.notificationDropdown.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', (e) => {
                if (!e.target.closest('.notification-menu') && !e.target.closest('.notification-menu-dropdown')) {
                    this.markAsRead(parseInt(item.dataset.id));
                }
            });
        });
    }
    
    async markAsRead(notificationId) {
        try {
            const base = (typeof NOTIFICATIONS_API_BASE !== 'undefined' ? NOTIFICATIONS_API_BASE : '') || 
                window.location.pathname.replace(/\/[^/]*$/, '').replace(/\/[^/]*$/, '');
            const apiUrl = (base ? base + '/api/' : './api/') + 'mark_notification_read.php';
            const response = await fetch(apiUrl, {
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
        if (this.notificationDropdown?.style.display === 'block') {
            this.closeDropdown();
        } else {
            this.openDropdown();
        }
    }
    
    openDropdown() {
        if (this.notificationDropdown) this.notificationDropdown.style.display = 'block';
    }
    
    closeDropdown() {
        if (this.notificationDropdown) this.notificationDropdown.style.display = 'none';
    }
    
    startAutoRefresh() {
        // Refresh notifications every 30 seconds
        setInterval(() => {
            this.loadNotifications();
        }, 30000);
    }
}

// Toggle visibility of "Already Read" section (when there are unread notifications)
function toggleAlreadyReadSection() {
    const section = document.getElementById('notificationReadSection');
    const viewBtn = document.getElementById('toggleReadBtn');
    if (!section) return;
    const isHidden = section.style.display === 'none' || !section.style.display;
    section.style.display = isHidden ? 'block' : 'none';
    const viewWrap = viewBtn ? viewBtn.closest('.notification-toggle-read-wrap') : null;
    if (viewWrap) viewWrap.style.display = isHidden ? 'none' : 'block';
}

// Global functions for notification menu
function toggleNotificationMenu(notificationId) {
    document.querySelectorAll('.notification-menu-dropdown').forEach(m => {
        if (m.id !== `menu-${notificationId}`) m.classList.remove('show');
    });
    const menu = document.getElementById(`menu-${notificationId}`);
    if (!menu) return;
    const btn = menu.closest('.notification-menu')?.querySelector('.notification-menu-btn');
    const isOpening = !menu.classList.contains('show');
    menu.classList.toggle('show');
    if (isOpening && btn) {
        requestAnimationFrame(() => {
            const rect = btn.getBoundingClientRect();
            menu.style.top = (rect.bottom + 4) + 'px';
            menu.style.left = (rect.right - menu.offsetWidth) + 'px';
            menu.style.right = 'auto';
        });
    }
}

async function toggleNotificationRead(notificationId) {
    try {
        const notification = window.notificationSystem.notifications.find(n => n.id === notificationId);
        if (!notification) return;
        
        const newReadStatus = !notification.is_read;
        const base = (typeof NOTIFICATIONS_API_BASE !== 'undefined' ? NOTIFICATIONS_API_BASE : '') || 
            window.location.pathname.replace(/\/[^/]*$/, '').replace(/\/[^/]*$/, '');
        const apiUrl = (base ? base + '/api/' : './api/') + 'mark_notification_read.php';
        const response = await fetch(apiUrl, {
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
        const base = (typeof NOTIFICATIONS_API_BASE !== 'undefined' ? NOTIFICATIONS_API_BASE : '') || 
            window.location.pathname.replace(/\/[^/]*$/, '').replace(/\/[^/]*$/, '');
        const apiUrl = (base ? base + '/api/' : './api/') + 'remove_notification.php';
        const response = await fetch(apiUrl, {
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
    window.toggleAlreadyReadSection = toggleAlreadyReadSection;
});

// Simple toggle function for onclick handler
function toggleNotificationDropdown() {
    const d = document.getElementById('notificationDropdown');
    if (d) d.style.display = d.style.display === 'block' ? 'none' : 'block';
}
