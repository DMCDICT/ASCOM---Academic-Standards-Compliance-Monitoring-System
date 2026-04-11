// session_manager.js
// Client-side session management for extended sessions

class SessionManager {
    constructor() {
        this.sessionCheckInterval = 2 * 60 * 1000; // Check every 2 minutes instead of 30 seconds
        this.lastActivity = Date.now();
        this.init();
    }

    init() {
        // Extend session on page load
        this.extendSession();
        
        // Set up periodic session extension (less frequent)
        setInterval(() => {
            // Only extend if there's been recent activity (within last 5 minutes)
            const timeSinceLastActivity = Date.now() - this.lastActivity;
            if (timeSinceLastActivity < 5 * 60 * 1000) { // 5 minutes
                this.extendSession();
            }
        }, this.sessionCheckInterval);
        
        // Extend session on user activity
        this.setupActivityListeners();
        
        // Handle tab/window close
        this.setupCloseListeners();
        
        // Handle page visibility changes
        this.setupVisibilityListeners();
    }

    async extendSession() {
        try {
            const response = await fetch('../extend_session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin' // Include cookies
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    // Session extended successfully - no console logging needed
                }
            }
        } catch (error) {
            console.error('Failed to extend session:', error);
        }
    }

    setupActivityListeners() {
        // Extend session on user activity
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
        
        events.forEach(event => {
            document.addEventListener(event, () => {
                this.lastActivity = Date.now();
                this.extendSession();
            }, { passive: true });
        });
    }

    setupCloseListeners() {
        // Handle tab/window close with aggressive detection
        let isPageVisible = true;
        let heartbeatInterval;
        let lastActivity = Date.now();
        let employeeNo = null;
        
        // Get employee number from server
        const getEmployeeNumber = async () => {
            try {
                const response = await fetch('../get_user_info.php');
                const data = await response.json();
                if (data.success && data.employee_no) {
                    employeeNo = data.employee_no;
                }
            } catch (error) {
            }
        };
        
        // Get employee number on page load
        getEmployeeNumber();
        
        // Function to send logout request
        const sendLogoutRequest = () => {
            
            // Try multiple methods to ensure logout request is sent
            try {
                // Method 1: sendBeacon (most reliable for tab close)
                const beaconData = new FormData();
                beaconData.append('logout_reason', 'tab_close');
                if (employeeNo) {
                    beaconData.append('employee_no', employeeNo);
                }
                navigator.sendBeacon('../logout_on_close.php', beaconData);
                
                // Method 2: fetch with keepalive
                fetch('../logout_on_close.php', {
                    method: 'POST',
                    keepalive: true,
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ 
                        logout_reason: 'tab_close',
                        employee_no: employeeNo
                    })
                
                // Method 3: synchronous XMLHttpRequest (fallback)
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '../logout_on_close.php', false); // synchronous
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.send(JSON.stringify({ 
                    logout_reason: 'tab_close',
                    employee_no: employeeNo
                }));
                
            } catch (error) {
            }
        };
        
        // Start heartbeat when page becomes visible
        const startHeartbeat = () => {
            if (heartbeatInterval) clearInterval(heartbeatInterval);
            
            heartbeatInterval = setInterval(async () => {
                if (isPageVisible) {
                    try {
                        const response = await fetch('../update_user_activity.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            credentials: 'same-origin'
                        });
                        
                        if (response.ok) {
                            lastActivity = Date.now();
                        }
                    } catch (error) {
                        sendLogoutRequest();
                    }
                }
            }, 5000); // More frequent heartbeat (every 5 seconds)
        };
        
        // Stop heartbeat when page becomes hidden
        const stopHeartbeat = () => {
            if (heartbeatInterval) {
                clearInterval(heartbeatInterval);
                heartbeatInterval = null;
            }
        };
        
        // Handle page visibility changes
        document.addEventListener('visibilitychange', () => {
            isPageVisible = document.visibilityState === 'visible';
            
            if (isPageVisible) {
                startHeartbeat();
            } else {
                stopHeartbeat();
                sendLogoutRequest();
            }
        });
        
        // Handle pagehide (more reliable than unload)
        window.addEventListener('pagehide', (event) => {
            sendLogoutRequest();
        });
        
        // Most aggressive approach: force redirect to logout on page unload
        window.addEventListener('beforeunload', (event) => {
            
            // Try to send logout request first
            sendLogoutRequest();
            
            // Force redirect to logout page as backup
            try {
                // This will force a redirect to logout page
                window.location.href = '../logout_handler.php?reason=tab_close';
            } catch (e) {
            }
        });
        
        // Handle unload (page unload)
        window.addEventListener('unload', (event) => {
            sendLogoutRequest();
        });
        
        // Additional fallback: check if page is still active every 10 seconds
        setInterval(() => {
            if (!isPageVisible) {
                const timeSinceLastActivity = Date.now() - lastActivity;
                if (timeSinceLastActivity > 10000) { // 10 seconds
                    sendLogoutRequest();
                }
            }
        }, 10000);
        
        // Start initial heartbeat
        startHeartbeat();
    }

    setupVisibilityListeners() {
        // Handle page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                // User returned to the tab, update last activity and extend session
                this.lastActivity = Date.now();
                this.extendSession();
            }
        });
    }

    // Method to manually extend session (can be called from other scripts)
    static extend() {
        if (window.sessionManager) {
            window.sessionManager.extendSession();
        }
    }
}

// Initialize session manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.sessionManager = new SessionManager();
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SessionManager;
} 