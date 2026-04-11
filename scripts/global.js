// global.js - Global navigation and tooltip functionality

// Sidebar toggle functionality
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.classList.toggle('collapsed');
        const isCollapsed = sidebar.classList.contains('collapsed');
        
        // Update navigation links with sidebar state
        updateNavigationLinks(isCollapsed ? 'collapsed' : 'expanded');
        
        // Store sidebar state in localStorage
        localStorage.setItem('sidebarState', isCollapsed ? 'collapsed' : 'expanded');
    }
}

// Handle sidebar navigation
function handleSidebarNavigation(event) {
    const target = event.target.closest('.nav-button');
    if (target) {
        const page = target.getAttribute('data-page');
        if (page) {
            // Navigation will be handled by the href attribute
        }
    }
}

// Update active navigation state
function updateActiveNavState(activePage) {
    const navButtons = document.querySelectorAll('.nav-button[data-page]');
    
    navButtons.forEach(button => {
        const page = button.getAttribute('data-page');
        if (page === activePage) {
            button.classList.add('active');
        } else {
            button.classList.remove('active');
        }
    });
}

// Test function for debugging
window.testNavigation = function() {
    const navButtons = document.querySelectorAll('.nav-button');
    
    navButtons.forEach((button, index) => {
        const page = button.getAttribute('data-page');
        const hasClickHandler = button.onclick !== null;
    });
};

// Function to update all navigation links with new sidebar state
function updateNavigationLinks(sidebarState) {
    
    const navButtons = document.querySelectorAll('.nav-button[data-page]');
    navButtons.forEach(button => {
        const page = button.getAttribute('data-page');
        if (page) {
            const newHref = `content.php?page=${page}&sidebar=${sidebarState}`;
            button.href = newHref;
        }
    });
}

// Make functions globally available
window.toggleSidebar = toggleSidebar;
window.handleSidebarNavigation = handleSidebarNavigation;
window.updateActiveNavState = updateActiveNavState;
window.updateNavigationLinks = updateNavigationLinks;

// Simple modal function
function openAddUserModal() {
    const modal = document.getElementById('addUserModal');
    if (modal) {
        modal.style.display = 'flex';
    } else {
        console.error('Modal not found');
        alert('Modal not available');
    }
}

// Make it globally available
window.openAddUserModal = openAddUserModal;

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    
    // Get current page from URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('page') || 'dashboard';
    
    // Update active navigation state
    updateActiveNavState(currentPage);
    
    // Get sidebar state from localStorage
    const savedSidebarState = localStorage.getItem('sidebarState');
    if (savedSidebarState) {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            if (savedSidebarState === 'collapsed') {
                sidebar.classList.add('collapsed');
            } else {
                sidebar.classList.remove('collapsed');
            }
        }
    }
    
    // Initialize tooltips
    function initializeTooltips() {
        
        // Check if already initialized
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
                    globalTooltip.innerHTML = `
                        <div style="
                            position: absolute;
                            left: -8px;
                            top: 50%;
                            transform: translateY(-50%);
                            width: 0;
                            height: 0;
                            border-top: 8px solid transparent;
                            border-bottom: 8px solid transparent;
                            border-right: 8px solid #333;
                        "></div>
                            ${tooltipText}
                    `;
                    
                    // Apply styles
                    globalTooltip.style.cssText = `
                        position: fixed;
                        left: ${buttonRect.right + 20}px;
                        top: ${buttonRect.top + buttonRect.height / 2}px;
                        transform: translateY(-50%);
                        background: #333;
                        color: white;
                        padding: 8px 12px;
                        border-radius: 6px;
                        font-size: 12px;
                        font-weight: 500;
                        white-space: nowrap;
                        opacity: 0;
                        visibility: visible;
                        display: block;
                        pointer-events: none;
                        z-index: 9999;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                        font-family: 'TT Interphases', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                        transition: opacity 0.2s ease;
                    `;
                    
                    document.body.appendChild(globalTooltip);
                    currentButtonIndex = index;
                    
                    // Show tooltip
                    tooltipTimeout = setTimeout(() => {
                        if (globalTooltip && currentButtonIndex === index) {
                            globalTooltip.style.opacity = '1';
                        }
                    }, 100);
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
    
    // Initialize tooltips
        setTimeout(initializeTooltips, 1000);
    
}); 
