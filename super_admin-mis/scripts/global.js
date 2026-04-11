
// IMMEDIATE sidebar state restoration - runs before DOM is ready
(function() {
    const savedState = localStorage.getItem('sidebarCollapsed');
    
    // Apply the state immediately to prevent any flash
    if (savedState === 'true') {
        document.documentElement.style.setProperty('--sidebar-width', '115px');
        document.documentElement.style.setProperty('--content-margin', '115px');
    } else {
        document.documentElement.style.setProperty('--sidebar-width', '298px');
        document.documentElement.style.setProperty('--content-margin', '298px');
    }
})();

// Simple sidebar toggle
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        const wasCollapsed = sidebar.classList.contains('collapsed');
        sidebar.classList.toggle('collapsed');
        const isNowCollapsed = sidebar.classList.contains('collapsed');
        
        // Save state to localStorage
        localStorage.setItem('sidebarCollapsed', isNowCollapsed);
        
        // Update content wrapper margin
        updateContentWrapperMargin(isNowCollapsed);
        
        // Reinitialize tooltips when sidebar state changes
        initializeTooltips();
        
    }
}

// Function to update content wrapper margin
function updateContentWrapperMargin(isCollapsed) {
    const contentWrapper = document.querySelector('.content-wrapper');
    if (contentWrapper) {
        if (isCollapsed) {
            contentWrapper.style.marginLeft = '115px';
        } else {
            contentWrapper.style.marginLeft = '298px';
        }
    }
}

// Navigation function - SIMPLE AND RELIABLE
function handleSidebarNavigation(page) {
    
    // Save current sidebar state before navigation
    const sidebar = document.getElementById('sidebar');
    const isCollapsed = sidebar ? sidebar.classList.contains('collapsed') : false;
    localStorage.setItem('sidebarCollapsed', isCollapsed);
    
    // Verify the state was saved
    const savedState = localStorage.getItem('sidebarCollapsed');
    
    // Update active state before navigation
    updateActiveNavState(page);
    
    // Simple page navigation
    window.location.href = `content.php?page=${page}`;
}

// Function to update active navigation state
function updateActiveNavState(activePage) {
    
    const navButtons = document.querySelectorAll('.nav-button');
    navButtons.forEach(button => {
        const page = button.getAttribute('data-page');
        if (page === activePage) {
            button.classList.add('active');
        } else {
            button.classList.remove('active');
        }
    });
}

// Make functions globally available
window.toggleSidebar = toggleSidebar;
window.handleSidebarNavigation = handleSidebarNavigation;
window.updateActiveNavState = updateActiveNavState;

// Test function for debugging
window.testNavigation = function() {
    const navButtons = document.querySelectorAll('.nav-button');
    
    navButtons.forEach((button, index) => {
        const page = button.getAttribute('data-page');
        const hasClickHandler = button.onclick !== null;
    });
};

// IMMEDIATE initialization - runs right after script loads
(function() {
    
    // Wait a bit for DOM to be ready
    setTimeout(() => {
        const navButtons = document.querySelectorAll('.nav-button');
        
        navButtons.forEach((button, index) => {
            const page = button.getAttribute('data-page');
            if (page) {
                // Remove any existing handlers
                button.onclick = null;
                
                // Add click handler
                button.onclick = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    handleSidebarNavigation(page);
                };
                
                // Make sure button is clickable
                button.style.cursor = 'pointer';
                button.style.pointerEvents = 'auto';
                
            }
        });
    }, 100);
})();

// Initialize sidebar state when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    
    const sidebar = document.getElementById('sidebar');
    const savedState = localStorage.getItem('sidebarCollapsed');
    
    if (sidebar) {
        // Remove any existing collapsed class first
        sidebar.classList.remove('collapsed');
        
        
        if (savedState === 'true') {
            sidebar.classList.add('collapsed');
            updateContentWrapperMargin(true);
        } else {
            sidebar.classList.remove('collapsed');
            updateContentWrapperMargin(false);
        }
        
        // Verify the state was applied correctly
        const isActuallyCollapsed = sidebar.classList.contains('collapsed');
        
        // Initialize tooltips for collapsed sidebar
        initializeTooltips();
    }
    
    // Initialize hamburger button
    const hamburger = document.querySelector('.hamburger');
    if (hamburger) {
        // Remove any existing handlers
        hamburger.onclick = null;
        
        // Simple click handler
        hamburger.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleSidebar();
        };
        
        // Make sure it's clickable
        hamburger.style.cursor = 'pointer';
        hamburger.style.pointerEvents = 'auto';
    }
    
    // Initialize navigation buttons
    const navButtons = document.querySelectorAll('.nav-button');
    
    navButtons.forEach((button, index) => {
        const page = button.getAttribute('data-page');
        
        if (page) {
            // Remove any existing click handlers
            button.onclick = null;
            
            // Add new click handler
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                handleSidebarNavigation(page);
            });
            
            // Make sure button is clickable
            button.style.cursor = 'pointer';
            button.style.pointerEvents = 'auto';
            
        } else {
        }
    });
    
    
    // Set initial active state based on current page
    const currentPage = new URLSearchParams(window.location.search).get('page') || 'dashboard';
    updateActiveNavState(currentPage);
});

// Initialize tooltips for sidebar buttons - Only for collapsed sidebar
function initializeTooltips() {
    
    // Check if tooltips are already initialized to prevent duplicates
    if (window.tooltipsInitialized) {
        return;
    }
    
    const navButtons = document.querySelectorAll('.nav-button');
    
    if (navButtons.length === 0) {
        setTimeout(initializeTooltips, 1000);
        return;
    }
    
    // Mark as initialized
    window.tooltipsInitialized = true;
    
    // Create a global tooltip container
    let globalTooltip = null;
    let currentButtonIndex = -1;
    let tooltipTimeout = null;
    let hideTimeout = null;
    
    // Function to clean up tooltip
    function cleanupTooltip() {
        if (globalTooltip) {
            globalTooltip.remove();
            globalTooltip = null;
            currentButtonIndex = -1;
        }
        if (tooltipTimeout) {
            clearTimeout(tooltipTimeout);
            tooltipTimeout = null;
        }
        if (hideTimeout) {
            clearTimeout(hideTimeout);
            hideTimeout = null;
        }
    }
    
    navButtons.forEach(function(button, index) {
        // Get tooltip text from the visible button text (not from removed tooltip spans)
        const tooltipText = button.querySelector('span:not(.nav-icon-wrapper)')?.textContent?.trim() || 'Unknown';
        
        button.addEventListener('mouseenter', function() {
            const sidebar = document.getElementById('sidebar');
            const isCollapsed = sidebar.classList.contains('collapsed');
            
            
            // Only show tooltips when sidebar is collapsed
            if (isCollapsed) {
                // Clear any existing timeouts
                if (tooltipTimeout) clearTimeout(tooltipTimeout);
                if (hideTimeout) clearTimeout(hideTimeout);
                
                // Clean up any existing tooltip
                cleanupTooltip();
                
                
                // Get button position
                const buttonRect = button.getBoundingClientRect();
                
                // Create tooltip element
                globalTooltip = document.createElement('div');
                globalTooltip.id = 'stable-tooltip';
                globalTooltip.innerHTML = `
                    <div style="
                        position: absolute;
                        left: 100%;
                        top: 50%;
                        transform: translateY(-50%);
                        background: #222;
                        color: #fff;
                        padding: 7px 16px;
                        border-radius: 18px;
                        font-size: 15px;
                        font-weight: 500;
                        white-space: nowrap;
                        box-shadow: 0 4px 16px rgba(0,0,0,0.18);
                        z-index: 1001;
                        margin-left: 15px;
                    ">
                        ${tooltipText}
                    </div>
                `;
                
                globalTooltip.style.cssText = `
                    position: fixed !important;
                    left: ${buttonRect.right + 20}px !important;
                    top: ${buttonRect.top + buttonRect.height / 2}px !important;
                    z-index: 1001 !important;
                    pointer-events: none !important;
                `;
                
                document.body.appendChild(globalTooltip);
                currentButtonIndex = index;
                
            }
        });
        
        button.addEventListener('mouseleave', function() {
            
            if (currentButtonIndex === index) {
                hideTimeout = setTimeout(() => {
                    cleanupTooltip();
                }, 100);
            }
        });
    });
    
}

// Active/Inactive status function (for user management)
function getActiveInactiveStatus(userData) {
    const now = new Date();
    let lastActivity = null;
    
    if (userData.last_activity) {
        lastActivity = new Date(userData.last_activity);
    }
    
    if (!lastActivity) {
        return 'Inactive';
    }
    
    const diffMinutes = Math.floor((now - lastActivity) / (1000 * 60));
    
    if (diffMinutes <= 5) {
        return 'Active';
    } else if (diffMinutes <= 30) {
        return 'Away';
    } else {
        return 'Inactive';
    }
}

// Make function globally available
window.getActiveInactiveStatus = getActiveInactiveStatus;

