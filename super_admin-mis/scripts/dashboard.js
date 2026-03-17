console.log('🚀 DASHBOARD.JS IS LOADING!');

/*
 * dashboard.js - Clean version with NO sidebar handling
 * Contains JavaScript specific to the dashboard.php content fragment.
 */

// Global function from dashboard.php's original script (called by onclick attribute in HTML)
function toggleViewAll(btn) {
    const cards = document.querySelectorAll("#departmentContainer .department-card");
    const isExpanded = btn.innerText === "View Less";
    cards.forEach((card, index) => {
        if (index >= 6) { // Hide/show cards beyond the initial 6
            card.classList.toggle("hidden", isExpanded);
        }
    });
    btn.innerText = isExpanded ? "View All" : "View Less";
}

window.addEventListener('DOMContentLoaded', () => {
    // This script will only run its DOMContentLoaded block if dashboard.php is the active page.
    if (window.location.search.includes('page=dashboard') || window.location.search === '') {
        console.log('🔄 Dashboard.js loaded - dashboard functionality only');
        
        // SIDEBAR STATE HANDLED BY MAIN GLOBAL.JS - NO INTERFERENCE
        console.log('✅ Dashboard: Letting main global.js handle sidebar state');
        
        // 'departments' and 'recentActivities' are global variables defined in dashboard.php's <script> block.
        // We ensure they are defined before trying to use them.
        const departmentsData = typeof departments !== 'undefined' ? departments : [];
        const recentActivitiesData = typeof recentActivities !== 'undefined' ? recentActivities : [];

        // Attach event listener for "View All" button
        const viewAllDepartmentsButton = document.getElementById('viewAllDepartmentsButton');
        if (viewAllDepartmentsButton) {
            viewAllDepartmentsButton.addEventListener('click', function() {
                // toggleViewAll is a global function defined in this file
                toggleViewAll(this); // 'this' refers to the button clicked
            });
        }

        console.log('✅ Dashboard.js initialization completed');
    }
});

// BACK TO TOP FUNCTIONALITY - Run independently
window.addEventListener('DOMContentLoaded', () => {
    console.log('🎯 BACK TO TOP: Starting independent back-to-top functionality');
    
    try {
        // Get current page from URL
        const urlParams = new URLSearchParams(window.location.search);
        const currentPage = urlParams.get('page') || 'dashboard';
        console.log('🎯 BACK TO TOP: Current page =', currentPage);
        
        // Exclude back-to-top button from specific pages
        const excludedPages = ['reference-requests', 'course-material-requests', 'my-requests', 'book-requests'];
        
        // Add back-to-top functionality for all pages except excluded ones
        if (!excludedPages.includes(currentPage)) {
            console.log('🎯 BACK TO TOP: Creating back-to-top button for page:', currentPage);
            
            // Create back to top button with icon and text
            const backToTopButton = document.createElement('button');
            backToTopButton.className = 'back-to-top';
            backToTopButton.setAttribute('aria-label', 'Back to top');
            
            // Create icon element
            const icon = document.createElement('img');
            icon.src = '../src/assets/icons/go-back-icon.png';
            icon.alt = 'Back to Top';
            icon.className = 'arrow';
            
            // Create text element
            const text = document.createElement('span');
            text.className = 'text';
            text.textContent = 'Back to Top';
            
            // Append icon and text to button
            backToTopButton.appendChild(icon);
            backToTopButton.appendChild(text);
            
            // Append button to body
            document.body.appendChild(backToTopButton);
            console.log('🎯 BACK TO TOP: Button created and appended to body');

            // Show/hide button based on scroll position
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    backToTopButton.classList.add('show');
                } else {
                    backToTopButton.classList.remove('show');
                }
            });

            // Scroll to top when clicked
            backToTopButton.addEventListener('click', function() {
                console.log('🎯 BACK TO TOP: Button clicked');
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
            
            // Back-to-top button ready - will show when scrolling down
            console.log('🎯 BACK TO TOP: Button ready - will show when scrolling down');
            
            console.log('🎯 BACK TO TOP: Functionality completed successfully');
        } else {
            console.log('🎯 BACK TO TOP: Not on dashboard page, skipping');
        }
    } catch (error) {
        console.error('❌ BACK TO TOP: Error in back-to-top functionality:', error);
        // Fallback: Create a simple back-to-top button
        const fallbackButton = document.createElement('button');
        fallbackButton.textContent = 'BACK TO TOP (FALLBACK)';
        fallbackButton.style.position = 'fixed';
        fallbackButton.style.bottom = '30px';
        fallbackButton.style.right = '30px';
        fallbackButton.style.background = 'red';
        fallbackButton.style.color = 'white';
        fallbackButton.style.padding = '10px';
        fallbackButton.style.zIndex = '9999';
        fallbackButton.onclick = () => window.scrollTo({ top: 0, behavior: 'smooth' });
        document.body.appendChild(fallbackButton);
        console.log('🎯 BACK TO TOP: Fallback button created');
    }
});