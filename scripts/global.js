// global.js - Global navigation and tooltip functionality
console.log('🌐 GLOBAL.JS: Starting initialization...');

// Sidebar toggle functionality
function toggleSidebar() {
    console.log('🔄 Toggle sidebar called');
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.classList.toggle('collapsed');
        const isCollapsed = sidebar.classList.contains('collapsed');
        console.log(`📱 Sidebar ${isCollapsed ? 'collapsed' : 'expanded'}`);
        
        // Update navigation links with sidebar state
        updateNavigationLinks(isCollapsed ? 'collapsed' : 'expanded');
        
        // Store sidebar state in localStorage
        localStorage.setItem('sidebarState', isCollapsed ? 'collapsed' : 'expanded');
    }
}

// Handle sidebar navigation
function handleSidebarNavigation(event) {
    console.log('🧭 Sidebar navigation called');
    const target = event.target.closest('.nav-button');
    if (target) {
        const page = target.getAttribute('data-page');
        if (page) {
            console.log(`📄 Navigating to: ${page}`);
            // Navigation will be handled by the href attribute
        }
    }
}

// Update active navigation state
function updateActiveNavState(activePage) {
    console.log(`🎯 Updating active nav state for: ${activePage}`);
    const navButtons = document.querySelectorAll('.nav-button[data-page]');
    
    navButtons.forEach(button => {
        const page = button.getAttribute('data-page');
        if (page === activePage) {
            button.classList.add('active');
            console.log(`✅ Set active state for: ${page}`);
        } else {
            button.classList.remove('active');
        }
    });
}

// Test function for debugging
window.testNavigation = function() {
    console.log('🧪 Testing navigation buttons...');
    const navButtons = document.querySelectorAll('.nav-button');
    console.log(`Found ${navButtons.length} buttons`);
    
    navButtons.forEach((button, index) => {
        const page = button.getAttribute('data-page');
        const hasClickHandler = button.onclick !== null;
        console.log(`Button ${index + 1}: page="${page}", hasClickHandler=${hasClickHandler}`);
    });
};

// Function to update all navigation links with new sidebar state
function updateNavigationLinks(sidebarState) {
    console.log('🔄 Updating navigation links with sidebar state:', sidebarState);
    
    const navButtons = document.querySelectorAll('.nav-button[data-page]');
    navButtons.forEach(button => {
        const page = button.getAttribute('data-page');
        if (page) {
            const newHref = `content.php?page=${page}&sidebar=${sidebarState}`;
            button.href = newHref;
            console.log(`✅ Updated ${page} link: ${newHref}`);
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
    console.log('openAddUserModal called');
    const modal = document.getElementById('addUserModal');
    if (modal) {
        modal.style.display = 'flex';
        console.log('Modal opened');
    } else {
        console.error('Modal not found');
        alert('Modal not available');
    }
}

// Make it globally available
window.openAddUserModal = openAddUserModal;

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('🌐 GLOBAL.JS: DOM loaded, initializing...');
    
    // Get current page from URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('page') || 'dashboard';
    console.log(`📄 Current page: ${currentPage}`);
    
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
            console.log(`📱 Restored sidebar state: ${savedSidebarState}`);
        }
    }
    
    // Initialize tooltips
    function initializeTooltips() {
        console.log('🔧 Initializing tooltips...');
        
        // Check if already initialized
        if (window.tooltipsInitialized) {
            console.log('🔧 Tooltips already initialized, skipping');
            return;
        }
        
        const navButtons = document.querySelectorAll('.nav-button');
        if (navButtons.length === 0) {
            console.log('🔧 No nav buttons found, retrying...');
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
            console.log(`✅ Setting up tooltip for button ${index + 1}: "${tooltipText}"`);
            
            button.addEventListener('mouseenter', function() {
                const sidebar = document.getElementById('sidebar');
                const isCollapsed = sidebar.classList.contains('collapsed');
                
                console.log(`🖱️ HOVER ENTER: Button ${index + 1} (sidebar ${isCollapsed ? 'collapsed' : 'expanded'})`);
                
                // Only show tooltips when sidebar is collapsed
                if (isCollapsed) {
                    // Clear any existing timeouts
                    if (tooltipTimeout) clearTimeout(tooltipTimeout);
                    if (hideTimeout) clearTimeout(hideTimeout);
                    
                    // Clean up any existing tooltip
                    cleanupTooltip();
                    
                    console.log(`🚀 Creating tooltip for button ${index + 1}: "${tooltipText}"`);
                    
                    // Get button position
                    const buttonRect = button.getBoundingClientRect();
                    console.log(`📍 Button position: left=${buttonRect.left}, top=${buttonRect.top}, width=${buttonRect.width}, height=${buttonRect.height}`);
                    
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
                            console.log(`✅ Tooltip shown for button ${index + 1}`);
                        }
                    }, 100);
                }
            });
            
            button.addEventListener('mouseleave', function() {
                console.log(`🖱️ HOVER LEAVE: Button ${index + 1}`);
                
                if (currentButtonIndex === index) {
                    hideTimeout = setTimeout(() => {
                        cleanupTooltip();
                        console.log(`✅ Tooltip removed for button ${index + 1}`);
                    }, 100);
                }
            });
        });
        
        console.log('✅ Tooltips initialized for collapsed sidebar only');
    }
    
    // Initialize tooltips
        setTimeout(initializeTooltips, 1000);
    
    console.log('Navigation initialization complete');
}); 
