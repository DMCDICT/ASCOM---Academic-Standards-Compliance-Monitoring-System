// school-calendar.js - School Calendar Management Script

// Make functions globally available immediately
window.navigateCalendar = navigateCalendar;
window.goToToday = goToToday;
window.openAddTermModal = openAddTermModal;
window.closeAddTermModal = closeAddTermModal;
window.openAddSchoolYearModal = openAddSchoolYearModal;
window.closeAddSchoolYearModal = closeAddSchoolYearModal;
window.openAddHolidayModal = openAddHolidayModal;
window.closeAddHolidayModal = closeAddHolidayModal;
window.openScheduleMaintenanceModal = openScheduleMaintenanceModal;
window.closeScheduleMaintenanceModal = closeScheduleMaintenanceModal;
window.openAddCustomEventModal = openAddCustomEventModal;
window.closeAddCustomEventModal = closeAddCustomEventModal;
window.closeDayDetailsModal = closeDayDetailsModal;
window.toggleCalendarView = toggleCalendarView;

// Define and immediately attach form handlers to window
window.submitAddTermForm = submitAddTermForm;
window.submitAddSchoolYearForm = submitAddSchoolYearForm;
window.submitAddHolidayForm = submitAddHolidayForm;

// Force define function for school year form
window.handleSaveSchoolYear = function() {
    console.log('handleSaveSchoolYear called');
    // Get form values
    var schoolYearLabel = document.getElementById('schoolYearLabel')?.value;
    var syStartYear = document.getElementById('syStartYear')?.value;
    var syEndYear = document.getElementById('syEndYear')?.value;
    var syStartMonthDay = document.getElementById('syStartMonthDay')?.value;
    var syEndMonthDay = document.getElementById('syEndMonthDay')?.value;
    
    alert('Values: ' + JSON.stringify({schoolYearLabel, syStartYear, syEndYear, syStartMonthDay, syEndMonthDay}));
};

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
// Open schedule maintenance modal
function openScheduleMaintenanceModal() {
    const modal = document.getElementById('scheduleMaintenanceModal');
    if (modal) modal.style.display = 'flex';
}

// Close schedule maintenance modal
function closeScheduleMaintenanceModal() {
    const modal = document.getElementById('scheduleMaintenanceModal');
    if (modal) modal.style.display = 'none';
}

// Open add custom event modal
function openAddCustomEventModal() {
    const modal = document.getElementById('addCustomEventModal');
    if (modal) modal.style.display = 'flex';
}

// Close add custom event modal
function closeAddCustomEventModal() {
    const modal = document.getElementById('addCustomEventModal');
    if (modal) modal.style.display = 'none';
}

// Close day details modal
function closeDayDetailsModal() {
    const modal = document.getElementById('dayDetailsModal');
    if (modal) modal.style.display = 'none';
}

// Toggle calendar view
function toggleCalendarView(view) {
    console.log('Switching to view:', view);
}

// Submit add term form
async function submitAddTermForm(e) {
    if (e) e.preventDefault();
    
    const form = document.getElementById('addTermForm');
    if (!form) return;
    
    const title = document.getElementById('termTitle')?.value;
    const schoolYearId = document.getElementById('schoolYearId')?.value;
    const startDate = document.getElementById('startDate')?.value;
    const endDate = document.getElementById('endDate')?.value;
    
    // Check if school year dropdown has valid selection
    if (!schoolYearId || schoolYearId === '') {
        alert('Please select a School Year first.');
        return;
    }
    
    if (!title || !startDate || !endDate) {
        alert('Please fill out all fields.');
        return;
    }
    
    // Validate dates
    if (new Date(startDate) > new Date(endDate)) {
        alert('Start date cannot be after end date.');
        return;
    }
    
    try {
        const response = await fetch('./api/add_term.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title, school_year_id: parseInt(schoolYearId), start_date: startDate, end_date: endDate })
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            alert(data.message);
            closeAddTermModal();
            form.reset();
            // Enable save button
            const saveBtn = form.querySelector('button[type="submit"]');
            if (saveBtn) saveBtn.disabled = true;
            // Refresh calendar if needed
            if (typeof loadCalendarEvents === 'function') loadCalendarEvents();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

// Submit add school year form
async function submitAddSchoolYearForm(e) {
    if (e) {
        e.preventDefault();
    }
    console.log('submitAddSchoolYearForm called');
    
    const form = document.getElementById('addSchoolYearForm');
    if (!form) {
        alert('Form not found');
        return;
    }
    
    const schoolYearLabel = document.getElementById('schoolYearLabel')?.value;
    const syStartYear = document.getElementById('syStartYear')?.value;
    const syEndYear = document.getElementById('syEndYear')?.value;
    const syStartMonthDay = document.getElementById('syStartMonthDay')?.value;
    const syEndMonthDay = document.getElementById('syEndMonthDay')?.value;
    
    if (!schoolYearLabel || !syStartYear || !syEndYear || !syStartMonthDay || !syEndMonthDay) {
        alert('Please fill out all fields.');
        return;
    }
    
    // Construct the full dates (e.g., 2025-08-01)
    let startDate, endDate;
    try {
        const startParts = syStartMonthDay.split('-');
        const endParts = syEndMonthDay.split('-');
        startDate = syStartYear + '-' + startParts[1] + '-' + startParts[2];
        endDate = syEndYear + '-' + endParts[1] + '-' + endParts[2];
    } catch (err) {
        alert('Error parsing dates: ' + err.message);
        return;
    }
    
    try {
        const response = await fetch('./api/add_school_year.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                school_year_label: schoolYearLabel, 
                year_start: parseInt(syStartYear), 
                year_end: parseInt(syEndYear),
                start_date: startDate,
                end_date: endDate,
                status: 'Inactive'
            })
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            alert(data.message);
            closeAddSchoolYearModal();
            form.reset();
            if (window.location.search.includes('page=school-calendar')) {
                window.location.reload();
            }
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

// Submit add holiday form
async function submitAddHolidayForm(e) {
    if (e) e.preventDefault();
    
    const form = document.getElementById('addHolidayForm');
    if (!form) return;
    
    const title = document.getElementById('holidayTitle')?.value;
    const startDate = document.getElementById('holidayStartDate')?.value;
    const endDate = document.getElementById('holidayEndDate')?.value;
    const description = document.getElementById('holidayDescription')?.value;
    const allDay = document.getElementById('holidayAllDay')?.checked;
    
    if (!title || !startDate || !endDate) {
        alert('Please fill out all fields.');
        return;
    }
    
    // Check if API exists
    try {
        const response = await fetch('./api/add_holiday.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                title, 
                start_date: startDate, 
                end_date: endDate,
                description,
                all_day: allDay
            })
        });
        
        if (!response.ok) throw new Error('API not found');
        
        const data = await response.json();
        
        if (data.status === 'success') {
            alert(data.message);
            closeAddHolidayModal();
            form.reset();
            if (typeof loadCalendarEvents === 'function') loadCalendarEvents();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Holiday API not available. This feature is not yet implemented.');
    }
}

// Initialize calendar when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date();
    currentMonth = today.getMonth();
    currentYear = today.getFullYear();
    selectedDate = today;
    
    // Render initial calendar
    renderCalendar(currentMonth, currentYear);
    
    // Attach form submit handlers
    const addTermForm = document.getElementById('addTermForm');
    if (addTermForm) {
        addTermForm.addEventListener('submit', submitAddTermForm);
        
        // Enable/disable save button based on form validity
        const termTitle = document.getElementById('termTitle');
        const schoolYearId = document.getElementById('schoolYearId');
        const startDate = document.getElementById('startDate');
        const endDate = document.getElementById('endDate');
        const saveBtn = addTermForm.querySelector('button[type="submit"]');
        
        const validateTermForm = () => {
            if (saveBtn) {
                saveBtn.disabled = !(termTitle?.value && schoolYearId?.value && startDate?.value && endDate?.value);
            }
        };
        
        [termTitle, schoolYearId, startDate, endDate].forEach(el => {
            el?.addEventListener('change', validateTermForm);
            el?.addEventListener('input', validateTermForm);
        });
    }
    
    const addSchoolYearForm = document.getElementById('addSchoolYearForm');
    if (addSchoolYearForm) {
        // Auto-fill school year label and end year based on start year
        const syStartYear = document.getElementById('syStartYear');
        const syEndYear = document.getElementById('syEndYear');
        const syStartMonthDay = document.getElementById('syStartMonthDay');
        const schoolYearLabel = document.getElementById('schoolYearLabel');
        const saveSchoolYearBtn = document.getElementById('saveSchoolYearBtn');
        
        // Add click handler for the save button
        if (saveSchoolYearBtn) {
            saveSchoolYearBtn.addEventListener('click', function(e) {
                e.preventDefault();
                submitAddSchoolYearForm(e);
            });
        }
        
        if (syStartYear && syEndYear && schoolYearLabel) {
            syStartYear.addEventListener('change', function() {
                const startYr = parseInt(this.value);
                if (startYr) {
                    syEndYear.value = startYr + 1;
                    schoolYearLabel.value = startYr + '-' + (startYr + 1);
                }
            });
            
            syStartYear.addEventListener('input', function() {
                const startYr = parseInt(this.value);
                if (startYr && this.value.length >= 4) {
                    syEndYear.value = startYr + 1;
                    schoolYearLabel.value = startYr + '-' + (startYr + 1);
                }
            });
        }
        
        // Validate school year form
        const validateSchoolYearForm = () => {
            if (saveSchoolYearBtn) {
                saveSchoolYearBtn.disabled = !(
                    schoolYearLabel?.value && 
                    syStartYear?.value && 
                    syEndYear?.value && 
                    syStartMonthDay?.value
                );
            }
        };
        
        [schoolYearLabel, syStartYear, syEndYear, syStartMonthDay].forEach(el => {
            el?.addEventListener('change', validateSchoolYearForm);
            el?.addEventListener('input', validateSchoolYearForm);
        });
    }
    
    const addHolidayForm = document.getElementById('addHolidayForm');
    if (addHolidayForm) {
        addHolidayForm.addEventListener('submit', submitAddHolidayForm);
    }
});
