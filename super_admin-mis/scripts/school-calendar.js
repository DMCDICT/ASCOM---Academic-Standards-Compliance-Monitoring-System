// school-calendar.js - School Calendar Management Script

// Make functions globally available
window.navigateCalendar = navigateCalendar;
window.goToToday = goToToday;
window.openAddTermModal = openAddTermModal;
window.closeAddTermModal = closeAddTermModal;
window.openAddSchoolYearModal = openAddSchoolYearModal;
window.closeAddSchoolYearModal = closeAddSchoolYearModal;
window.openAddHolidayModal = openAddHolidayModal;
window.closeAddHolidayModal = closeAddHolidayModal;
window.toggleCalendarView = toggleCalendarView;

// Calendar state
let currentMonth = new Date().getMonth();
let currentYear = new Date().getFullYear();
let selectedDate = null;

// Navigate calendar
function navigateCalendar(direction, type) {
    if (type === 'month') {
        currentMonth += direction;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        } else if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
    } else if (type === 'year') {
        currentYear += direction;
    }
    renderCalendar(currentMonth, currentYear);
}

// Go to today
function goToToday() {
    const today = new Date();
    currentMonth = today.getMonth();
    currentYear = today.getFullYear();
    selectedDate = today;
    renderCalendar(currentMonth, currentYear);
}

// Render calendar (placeholder - implement based on your needs)
function renderCalendar(month, year) {
    console.log('Rendering calendar:', month + 1, year);
}

// Open add term modal
function openAddTermModal() {
    const modal = document.getElementById('addTermModal');
    if (modal) modal.style.display = 'flex';
}

// Close add term modal
function closeAddTermModal() {
    const modal = document.getElementById('addTermModal');
    if (modal) modal.style.display = 'none';
}

// Open add school year modal
function openAddSchoolYearModal() {
    const modal = document.getElementById('addSchoolYearModal');
    if (modal) modal.style.display = 'flex';
}

// Close add school year modal
function closeAddSchoolYearModal() {
    const modal = document.getElementById('addSchoolYearModal');
    if (modal) modal.style.display = 'none';
}

// Open add holiday modal
function openAddHolidayModal() {
    const modal = document.getElementById('addHolidayModal');
    if (modal) modal.style.display = 'flex';
}

// Close add holiday modal
function closeAddHolidayModal() {
    const modal = document.getElementById('addHolidayModal');
    if (modal) modal.style.display = 'none';
}

// Toggle calendar view
function toggleCalendarView(view) {
    console.log('Switching to view:', view);
}

// Initialize calendar when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date();
    currentMonth = today.getMonth();
    currentYear = today.getFullYear();
    selectedDate = today;
    
    // Render initial calendar
    renderCalendar(currentMonth, currentYear);
});
