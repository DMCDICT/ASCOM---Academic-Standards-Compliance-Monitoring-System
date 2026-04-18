// session_manager.js - Session Management Script
// Handles session timeout, auto-refresh, and user activity tracking

(function() {
    'use strict';
    
    // Configuration
    const SESSION_TIMEOUT = 30 * 60 * 1000; // 30 minutes
    const HEARTBEAT_INTERVAL = 5 * 60 * 1000; // 5 minutes
    const WARNING_BEFORE = 2 * 60 * 1000; // 2 minutes before expiry
    
    let lastActivity = Date.now();
    let heartbeatInterval = null;
    let warningTimeout = null;
    let isPageVisible = true;
    
    // Track user activity
    function updateActivity() {
        lastActivity = Date.now();
    }
    
    // Initialize session manager
    function init() {
        // Track various user activities
        ['click', 'keypress', 'scroll', 'mousemove'].forEach(event => {
            document.addEventListener(event, updateActivity, { passive: true });
        });
        
        // Handle page visibility
        document.addEventListener('visibilitychange', handleVisibilityChange);
        
        // Start heartbeat
        startHeartbeat();
    }
    
    // Handle page visibility changes
    function handleVisibilityChange() {
        isPageVisible = !document.hidden;
        if (isPageVisible) {
            updateActivity();
        }
    }
    
    // Start heartbeat to keep session alive
    function startHeartbeat() {
        if (heartbeatInterval) clearInterval(heartbeatInterval);
        
        heartbeatInterval = setInterval(() => {
            if (isPageVisible) {
                try {
                    // Use fetch with keepalive for reliable heartbeat
                    fetch('../update_user_activity.php', {
                        method: 'POST',
                        keepalive: true,
                        headers: { 'Content-Type': 'application/json' }
                    }).catch(() => {});
                } catch (e) {
                    // Ignore errors
                }
            }
        }, HEARTBEAT_INTERVAL);
    }
    
    // Start session manager when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
