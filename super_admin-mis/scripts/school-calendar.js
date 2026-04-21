// school-calendar.js - School Calendar Management Script

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

// Calendar data storage
let calendarEvents = {};

// Detect if we're in super_admin or department-dean context
function getApiPath() {
    // Check if we can access super_admin-mis API
    return './api/get_school_year_events.php';
}

// Load events from API
async function loadCalendarEvents() {
    try {
        const response = await fetch(getApiPath());
        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }
        const text = await response.text();
        if (!text.trim()) {
            calendarEvents = {};
            updateUpcomingEvents();
            return;
        }
        const data = JSON.parse(text);
        
        // Handle both API formats: {status, data} or {status, data: {events}}
        const events = data.data?.events || data.data || [];
        
        calendarEvents = {};
        events.forEach(event => {
            const dateKey = event.date;
            if (!calendarEvents[dateKey]) {
                calendarEvents[dateKey] = [];
            }
            calendarEvents[dateKey].push(event);
        });
        
        console.log('Loaded calendar events:', Object.keys(calendarEvents).length, 'days with events');
        
        updateUpcomingEvents();
    } catch (error) {
        console.error('Error loading calendar events:', error.message);
        calendarEvents = {};
    }
}

// Update upcoming events list
function updateUpcomingEvents() {
    const container = document.getElementById('upcomingEventsList');
    if (!container) return;
    
    const today = new Date();
    const upcoming = [];
    
    Object.keys(calendarEvents).forEach(dateKey => {
        const eventDate = new Date(dateKey);
        if (eventDate >= today) {
            calendarEvents[dateKey].forEach(event => {
                upcoming.push({ ...event, dateObj: eventDate });
            });
        }
    });
    
    upcoming.sort((a, b) => a.dateObj - b.dateObj);
    
    if (upcoming.length === 0) {
        container.innerHTML = '<li class="item-subtitle" style="font-style: italic;">No upcoming events found.</li>';
    } else {
        container.innerHTML = upcoming.slice(0, 5).map(event => `
            <li class="data-item">
                <div class="item-info">
                    <span class="item-title">${event.title}</span>
                    <span class="item-subtitle">${event.date}</span>
                </div>
                <span class="status-badge ${event.status === 'Active' ? 'status-active' : 'status-inactive'}">${event.type}</span>
            </li>
        `).join('');
    }
}

// Render calendar with events
function renderCalendar(month, year) {
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    
    // Update header
    const monthYearDisplay = document.getElementById('currentMonthYear');
    if (monthYearDisplay) {
        monthYearDisplay.textContent = monthNames[month] + ' ' + year;
    }
    
    // Get calendar container
    const calendarGrid = document.getElementById('calendarGrid');
    if (!calendarGrid) return;
    
    // Clear existing cells
    calendarGrid.innerHTML = '';
    
    // Get first day of month and total days
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const today = new Date();
    const isCurrentMonth = today.getMonth() === month && today.getFullYear() === year;
    const todayDate = today.getDate();
    
    // Add empty cells for days before first of month
    for (let i = 0; i < firstDay; i++) {
        const cell = document.createElement('div');
        cell.className = 'calendar-cell inactive';
        calendarGrid.appendChild(cell);
    }
    
    // Add day cells
    for (let day = 1; day <= daysInMonth; day++) {
        const cell = document.createElement('div');
        cell.className = 'calendar-cell';
        
        const dateStr = year + '-' + String(month + 1).padStart(2, '0') + '-' + String(day).padStart(2, '0');
        const dayEvents = calendarEvents[dateStr] || [];
        
        // Check if it's today
        if (isCurrentMonth && day === todayDate) {
            cell.classList.add('today');
        }
        
        // Add event indicators
        if (dayEvents.length > 0) {
            cell.classList.add('has-events');
            cell.classList.add('event-' + dayEvents[0].type);
        }
        
        // Add day number
        const dayNum = document.createElement('span');
        dayNum.className = 'cell-num';
        dayNum.textContent = day;
        cell.appendChild(dayNum);
        
        // Add event dot indicators
        if (dayEvents.length > 0) {
            const eventDots = document.createElement('div');
            eventDots.className = 'event-dots';
            
            dayEvents.slice(0, 3).forEach(event => {
                const dot = document.createElement('span');
                dot.className = 'event-dot ' + event.type;
                dot.title = event.title + ' (' + event.type + ')';
                eventDots.appendChild(dot);
            });
            
            cell.appendChild(eventDots);
        }
        
        // Add click handler to show day details
        cell.addEventListener('click', function() {
            showDayDetails(dateStr, dayEvents);
        });
        
        calendarGrid.appendChild(cell);
    }
    
    // Add navigation button handlers - remove old handlers first to avoid duplicates
    const prevMonthBtn = document.getElementById('prevMonthBtn');
    const nextMonthBtn = document.getElementById('nextMonthBtn');
    const prevYearBtn = document.getElementById('prevYearBtn');
    const nextYearBtn = document.getElementById('nextYearBtn');
    const todayBtn = document.getElementById('todayBtn');
    
    // Clone and replace to remove old event listeners
    if (prevMonthBtn) {
        const newBtn = prevMonthBtn.cloneNode(true);
        prevMonthBtn.parentNode.replaceChild(newBtn, prevMonthBtn);
        newBtn.addEventListener('click', () => navigateCalendar(-1, 'month'));
    }
    if (nextMonthBtn) {
        const newBtn = nextMonthBtn.cloneNode(true);
        nextMonthBtn.parentNode.replaceChild(newBtn, nextMonthBtn);
        newBtn.addEventListener('click', () => navigateCalendar(1, 'month'));
    }
    if (prevYearBtn) {
        const newBtn = prevYearBtn.cloneNode(true);
        prevYearBtn.parentNode.replaceChild(newBtn, prevYearBtn);
        newBtn.addEventListener('click', () => navigateCalendar(-1, 'year'));
    }
    if (nextYearBtn) {
        const newBtn = nextYearBtn.cloneNode(true);
        nextYearBtn.parentNode.replaceChild(newBtn, nextYearBtn);
        newBtn.addEventListener('click', () => navigateCalendar(1, 'year'));
    }
    if (todayBtn) {
        const newBtn = todayBtn.cloneNode(true);
        todayBtn.parentNode.replaceChild(newBtn, todayBtn);
        newBtn.addEventListener('click', goToToday);
    }
    
    console.log('Calendar rendered:', monthNames[month], year, 'with', Object.keys(calendarEvents).length, 'event days');
}

// Show day details when clicking a day
function showDayDetails(dateStr, events) {
    const displayEl = document.getElementById('selectedDateDisplay');
    const eventsList = document.getElementById('selectedDayEventsList');
    
    const date = new Date(dateStr + 'T00:00:00');
    const displayDate = date.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
    
    if (displayEl) {
        displayEl.textContent = displayDate;
    }
    
    if (eventsList) {
        if (events && events.length > 0) {
            eventsList.innerHTML = events.map(event => `
                <div class="data-item">
                    <div class="item-info">
                        <span class="item-title">${event.title}</span>
                        <span class="item-subtitle">${event.type}</span>
                    </div>
                    <span class="status-badge ${event.status === 'Active' ? 'status-active' : 'status-inactive'}">${event.status}</span>
                </div>
            `).join('');
        } else {
            eventsList.innerHTML = '<li class="item-subtitle" style="font-style: italic;">No events scheduled for this day.</li>';
        }
    }
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
document.addEventListener('DOMContentLoaded', async function() {
    console.log('School Calendar: DOM loaded, initializing...');
    const today = new Date();
    currentMonth = today.getMonth();
    currentYear = today.getFullYear();
    selectedDate = today;
    
    // Load events first, then render calendar
    await loadCalendarEvents();
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
