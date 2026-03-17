console.log('🚀 GLOBAL.JS LOADING - CLEAN VERSION');

// IMMEDIATE sidebar state restoration - runs before DOM is ready
(function() {
    const savedState = localStorage.getItem('sidebarCollapsed');
    console.log('⚡ IMMEDIATE: Restoring sidebar state:', savedState);
    
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
    console.log('toggleSidebar function called');
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
        
        console.log('Sidebar toggled:', wasCollapsed, '->', isNowCollapsed);
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
    console.log('🎯 Navigation to:', page);
    
    // Save current sidebar state before navigation
    const sidebar = document.getElementById('sidebar');
    const isCollapsed = sidebar ? sidebar.classList.contains('collapsed') : false;
    console.log('💾 Saving sidebar state before navigation:', isCollapsed ? 'collapsed' : 'expanded');
    localStorage.setItem('sidebarCollapsed', isCollapsed);
    
    // Verify the state was saved
    const savedState = localStorage.getItem('sidebarCollapsed');
    console.log('✅ Verified saved state:', savedState);
    
    // Update active state before navigation
    updateActiveNavState(page);
    
    // Simple page navigation
    console.log('🚀 Navigating to:', `content.php?page=${page}`);
    window.location.href = `content.php?page=${page}`;
}

// Function to update active navigation state
function updateActiveNavState(activePage) {
    console.log('🎯 Updating active nav state for:', activePage);
    
    const navButtons = document.querySelectorAll('.nav-button');
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

// Make functions globally available
window.toggleSidebar = toggleSidebar;
window.handleSidebarNavigation = handleSidebarNavigation;
window.updateActiveNavState = updateActiveNavState;

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

// IMMEDIATE initialization - runs right after script loads
(function() {
    console.log('⚡ IMMEDIATE: Initializing navigation buttons');
    
    // Wait a bit for DOM to be ready
    setTimeout(() => {
        const navButtons = document.querySelectorAll('.nav-button');
        console.log(`⚡ IMMEDIATE: Found ${navButtons.length} navigation buttons`);
        
        navButtons.forEach((button, index) => {
            const page = button.getAttribute('data-page');
            if (page) {
                // Remove any existing handlers
                button.onclick = null;
                
                // Add click handler
                button.onclick = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('⚡ IMMEDIATE: Navigation button clicked:', page);
                    handleSidebarNavigation(page);
                };
                
                // Make sure button is clickable
                button.style.cursor = 'pointer';
                button.style.pointerEvents = 'auto';
                
                console.log(`⚡ IMMEDIATE: Button ${index + 1} initialized for page: ${page}`);
            }
        });
    }, 100);
})();

// Initialize sidebar state when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔄 DOM ready - initializing sidebar state');
    
    const sidebar = document.getElementById('sidebar');
    const savedState = localStorage.getItem('sidebarCollapsed');
    
    if (sidebar) {
        // Remove any existing collapsed class first
        sidebar.classList.remove('collapsed');
        
        console.log('🔍 Saved sidebar state from localStorage:', savedState);
        
        if (savedState === 'true') {
            sidebar.classList.add('collapsed');
            updateContentWrapperMargin(true);
            console.log('✅ Sidebar restored to COLLAPSED state');
        } else {
            sidebar.classList.remove('collapsed');
            updateContentWrapperMargin(false);
            console.log('✅ Sidebar restored to EXPANDED state');
        }
        
        // Verify the state was applied correctly
        const isActuallyCollapsed = sidebar.classList.contains('collapsed');
        console.log('🔍 Sidebar state verification:', isActuallyCollapsed ? 'COLLAPSED' : 'EXPANDED');
        
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
            console.log('🍔 Hamburger clicked - toggling sidebar');
            toggleSidebar();
        };
        
        // Make sure it's clickable
        hamburger.style.cursor = 'pointer';
        hamburger.style.pointerEvents = 'auto';
        console.log('✅ Hamburger button initialized');
    }
    
    // Initialize navigation buttons
    const navButtons = document.querySelectorAll('.nav-button');
    console.log(`🔍 Found ${navButtons.length} navigation buttons`);
    
    navButtons.forEach((button, index) => {
        const page = button.getAttribute('data-page');
        console.log(`🔍 Button ${index + 1}: data-page="${page}"`);
        
        if (page) {
            // Remove any existing click handlers
            button.onclick = null;
            
            // Add new click handler
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('🎯 Navigation button clicked:', page);
                handleSidebarNavigation(page);
            });
            
            // Make sure button is clickable
            button.style.cursor = 'pointer';
            button.style.pointerEvents = 'auto';
            
            console.log(`✅ Button ${index + 1} initialized for page: ${page}`);
        } else {
            console.log(`⚠️ Button ${index + 1} has no data-page attribute`);
        }
    });
    
    console.log('✅ Navigation buttons initialized');
    
    // Set initial active state based on current page
    const currentPage = new URLSearchParams(window.location.search).get('page') || 'dashboard';
    console.log('🎯 Setting initial active state for page:', currentPage);
    updateActiveNavState(currentPage);
});

// Initialize tooltips for sidebar buttons - Only for collapsed sidebar
function initializeTooltips() {
    console.log('🔧 Initializing tooltips for collapsed sidebar only');
    
    // Check if tooltips are already initialized to prevent duplicates
    if (window.tooltipsInitialized) {
        console.log('⚠️ Tooltips already initialized, skipping...');
        return;
    }
    
    const navButtons = document.querySelectorAll('.nav-button');
    console.log(`Found ${navButtons.length} nav buttons`);
    
    if (navButtons.length === 0) {
        console.log('❌ No nav buttons found! Trying again in 1 second...');
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
                
                console.log(`✅ Tooltip created for button ${index + 1}`);
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

console.log('✅ GLOBAL.JS LOADED SUCCESSFULLY');