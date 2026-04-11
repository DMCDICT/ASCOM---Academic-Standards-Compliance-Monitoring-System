// super_admin_session_manager.js
// Client-side session management specifically for Super Admin - COMPLETELY PASSIVE

class SuperAdminSessionManager {
    constructor() {
        // Super Admin has unlimited sessions - completely passive management
        this.init();
    }

    init() {
        // No session extension requests - Super Admin sessions are unlimited
        
        // Only handle tab/window close (but don't force logout)
        this.setupCloseListeners();
        
        // Handle page visibility changes (but don't make requests)
        this.setupVisibilityListeners();
    }

    setupCloseListeners() {
        // Handle tab/window close (but don't force logout for Super Admin)
        window.addEventListener('beforeunload', (event) => {
            // Super Admin can close tabs without being logged out
            // Only log the event, don't force logout
        });
    }

    setupVisibilityListeners() {
        // Handle page visibility changes (but don't make requests)
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                // User returned to the tab - just log it
            }
        });
    }

    // Method to manually extend session (can be called from other scripts)
    static extend() {
    }
}

// Initialize session manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.superAdminSessionManager = new SuperAdminSessionManager();
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SuperAdminSessionManager;
} 