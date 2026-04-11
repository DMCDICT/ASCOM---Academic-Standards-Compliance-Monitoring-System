/*
 * school-calendar.js
 * FINAL VERSION with Add Term and Add School Year modals
 */

// Global variables and functions remain the same
let currentMonth = new Date().getMonth();
let currentYear = new Date().getFullYear();
let selectedDate = new Date();
let schoolYearEvents = []; // Store school year events

// Make functions globally available for AJAX navigation
window.renderCalendar = renderCalendar;
window.navigateCalendar = navigateCalendar;
window.handleDayClick = handleDayClick;
window.currentMonth = currentMonth;
window.currentYear = currentYear;

function renderCalendar(month, year) {
    const calendarGrid = document.getElementById('calendarGrid');
    const currentMonthYearHeader = document.getElementById('currentMonthYear');
    if (!calendarGrid || !currentMonthYearHeader) { console.error("Calendar elements not found."); return; }
    calendarGrid.innerHTML = '';
    const firstDayOfMonth = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    currentMonthYearHeader.textContent = `${monthNames[month]} ${year}`;
    const today = new Date();
    const currentDay = today.getDate();
    const currentMonthActual = today.getMonth();
    const currentYearActual = today.getFullYear();
    for (let i = 0; i < firstDayOfMonth; i++) {
        const emptyCell = document.createElement('div');
        emptyCell.classList.add('calendar-day-cell', 'inactive');
        calendarGrid.appendChild(emptyCell);
    }
    for (let day = 1; day <= daysInMonth; day++) {
        const dayCell = document.createElement('div');
        dayCell.classList.add('calendar-day-cell');
        // Add data attributes for easy selection
        dayCell.setAttribute('data-day', day);
        dayCell.setAttribute('data-month', month);
        dayCell.setAttribute('data-year', year);
        
        if (day === currentDay && month === currentMonthActual && year === currentYearActual) {
            dayCell.classList.add('current-day');
        }
        if (selectedDate && day === selectedDate.getDate() && month === selectedDate.getMonth() && year === selectedDate.getFullYear()) {
            dayCell.classList.add('selected-day');
        }
        
        const dayNumber = document.createElement('div');
        dayNumber.classList.add('calendar-day-number');
        dayNumber.textContent = day;
        dayCell.appendChild(dayNumber);
        
        // Check for events on this date
        const currentDate = new Date(year, month, day);
        const eventsForDay = getEventsForDate(currentDate);
        
        if (eventsForDay.length > 0) {
            eventsForDay.forEach(event => {
                const eventIndicator = document.createElement('div');
                eventIndicator.classList.add('calendar-event-indicator');
                eventIndicator.setAttribute('data-event-id', event.id);
                eventIndicator.setAttribute('data-event-type', event.type);
                
                // Set different colors and styles for different event types - matching management options
                if (event.type === 'school_year_start') {
                    eventIndicator.style.backgroundColor = '#4CAF50'; // Green (Add School Year)
                    eventIndicator.style.borderLeft = '4px solid #2E7D32';
                } else if (event.type === 'school_year_end') {
                    eventIndicator.style.backgroundColor = '#4CAF50'; // Green (Add School Year)
                    eventIndicator.style.borderLeft = '4px solid #2E7D32';
                } else if (event.type === 'term_start') {
                    eventIndicator.style.backgroundColor = '#A99F30'; // Yellow (Add Term) - matches New Account button
                    eventIndicator.style.borderLeft = '4px solid #8B7D1A';
                    eventIndicator.style.fontWeight = 'bold';
                    eventIndicator.style.color = 'white'; // White text for better contrast
                } else if (event.type === 'term_end') {
                    eventIndicator.style.backgroundColor = '#A99F30'; // Yellow (Add Term) - matches New Account button
                    eventIndicator.style.borderLeft = '4px solid #8B7D1A';
                    eventIndicator.style.fontWeight = 'bold';
                    eventIndicator.style.color = 'white'; // White text for better contrast
                }
                
                // Set the full text content
                eventIndicator.textContent = event.title;
                
                // Add tooltip with full event details
                let tooltipText = event.title;
                if (event.type.includes('term')) {
                    tooltipText += `\nSchool Year: ${event.school_year_label || 'N/A'}`;
                    tooltipText += `\nStatus: ${event.status || 'N/A'}`;
                }
                eventIndicator.title = tooltipText;
                
                dayCell.appendChild(eventIndicator);
            });
        }
        
        dayCell.addEventListener('click', () => {
            if (!dayCell.classList.contains('inactive')) {
                handleDayClick(day, month, year);
                showDayDetails(day, month, year);
            }
        });
        calendarGrid.appendChild(dayCell);
    }
}

function navigateCalendar(direction, unit) {
    if (unit === 'month') {
        currentMonth += direction;
        if (currentMonth < 0) { currentMonth = 11; currentYear--; }
        else if (currentMonth > 11) { currentMonth = 0; currentYear++; }
    } else if (unit === 'year') {
        currentYear += direction;
    }
    // Re-render calendar with current events
    renderCalendar(currentMonth, currentYear);
}

function handleDayClick(day, month, year) {
    // Remove previous selection
    const prevSelected = document.querySelector('.calendar-day-cell.selected-day');
    if (prevSelected) { 
        prevSelected.classList.remove('selected-day'); 
    }
    
    // Add selection to clicked day
    const newSelected = document.querySelector(`.calendar-day-cell[data-day="${day}"][data-month="${month}"][data-year="${year}"]`);
    if (newSelected) { 
        newSelected.classList.add('selected-day'); 
    }
    
    // Update selected date
    selectedDate = new Date(year, month, day);
}

// Function to load school year events
async function loadSchoolYearEvents() {
    try {
        const response = await fetch('api/get_school_year_events.php');
        const data = await response.json();
        
        if (data.status === 'success') {
            schoolYearEvents = data.data;
        } else {
            console.error('Failed to load school year events:', data.message);
        }
    } catch (error) {
        console.error('Error loading school year events:', error);
    }
}

// Function to get events for a specific date
function getEventsForDate(date) {
    // Format date as YYYY-MM-DD without timezone issues
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const dateString = `${year}-${month}-${day}`;
    
    const events = schoolYearEvents.filter(event => event.date === dateString);
    return events;
}

// Function to show day details modal
function showDayDetails(day, month, year) {
    const date = new Date(year, month, day);
    const events = getEventsForDate(date);
    
    // Format the date for display
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const formattedDate = date.toLocaleDateString('en-US', options);
    
    // Update modal title
    const titleElement = document.getElementById('dayDetailsTitle');
    if (titleElement) {
        titleElement.textContent = formattedDate;
    }
    
    // Get modal elements
    const eventsList = document.getElementById('dayEventsList');
    const noEventsMessage = document.getElementById('noEventsMessage');
    
    if (events.length > 0) {
        // Show events list, hide no events message
        noEventsMessage.style.display = 'none';
        eventsList.style.display = 'block';
        
        // Clear previous events
        eventsList.innerHTML = '';
        
        // Add each event to the list
        events.forEach(event => {
            // Determine styling based on event type - matching calendar colors
            let borderColor, backgroundColor, eventTypeText;
            
            if (event.type === 'school_year_start') {
                borderColor = '#4CAF50'; // Green (Add School Year)
                backgroundColor = '#f1f8e9';
                eventTypeText = 'School Year Start';
            } else if (event.type === 'school_year_end') {
                borderColor = '#4CAF50'; // Green (Add School Year)
                backgroundColor = '#f1f8e9';
                eventTypeText = 'School Year End';
            } else if (event.type === 'term_start') {
                borderColor = '#A99F30'; // Yellow (Add Term) - matches New Account button
                backgroundColor = '#f8f6e8';
                eventTypeText = 'Term Start';
            } else if (event.type === 'term_end') {
                borderColor = '#A99F30'; // Yellow (Add Term) - matches New Account button
                backgroundColor = '#f8f6e8';
                eventTypeText = 'Term End';
            }
            
            const eventItem = document.createElement('div');
            eventItem.style.cssText = `
                padding: 15px;
                margin-bottom: 10px;
                border-radius: 8px;
                border-left: 4px solid ${borderColor};
                background-color: ${backgroundColor};
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                position: relative;
            `;
            
            // Add a small color indicator dot
            const colorDot = document.createElement('div');
            colorDot.style.cssText = `
                position: absolute;
                top: 15px;
                right: 15px;
                width: 12px;
                height: 12px;
                border-radius: 50%;
                background-color: ${borderColor};
                border: 2px solid white;
                box-shadow: 0 1px 3px rgba(0,0,0,0.2);
            `;
            eventItem.appendChild(colorDot);
            
            const eventTitle = document.createElement('h3');
            eventTitle.textContent = event.title;
            eventTitle.style.cssText = `
                margin: 0 0 8px 0;
                font-size: 16px;
                font-weight: bold;
                color: #333;
            `;
            
            const eventType = document.createElement('p');
            eventType.textContent = eventTypeText;
            eventType.style.cssText = `
                margin: 0;
                font-size: 14px;
                color: #666;
                font-style: italic;
            `;
            
            // Add school year info for term events
            if (event.type.includes('term') && event.school_year_label) {
                const schoolYearInfo = document.createElement('p');
                schoolYearInfo.textContent = `School Year: ${event.school_year_label}`;
                schoolYearInfo.style.cssText = `
                    margin: 5px 0 0 0;
                    font-size: 12px;
                    color: #666;
                `;
                eventItem.appendChild(schoolYearInfo);
            }
            
            const eventStatus = document.createElement('p');
            eventStatus.textContent = `Status: ${event.is_active ? 'Active' : 'Inactive'}`;
            eventStatus.style.cssText = `
                margin: 5px 0 0 0;
                font-size: 12px;
                color: ${event.is_active ? borderColor : '#666'};
                font-weight: bold;
            `;
            
            eventItem.appendChild(eventTitle);
            eventItem.appendChild(eventType);
            eventItem.appendChild(eventStatus);
            eventsList.appendChild(eventItem);
        });
    } else {
        // Show no events message, hide events list
        eventsList.style.display = 'none';
        noEventsMessage.style.display = 'block';
    }
    
    // Show the modal
    const modal = document.getElementById('dayDetailsModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}


// --- MAIN EXECUTION ---
window.addEventListener('DOMContentLoaded', () => {
    if (window.location.search.includes('page=school-calendar')) {
        
        // --- Get All Buttons ---
        const prevMonthBtn = document.getElementById('prevMonthBtn');
        const nextMonthBtn = document.getElementById('nextMonthBtn');
        const prevYearBtn = document.getElementById('prevYearBtn');
        const nextYearBtn = document.getElementById('nextYearBtn');
        const todayBtn = document.getElementById('todayBtn');
        const addTermBtn = document.getElementById('addTermBtn');
        const addSchoolYearOptionBtn = document.getElementById('addSchoolYearOptionBtn'); // UPDATED
        const addHolidayBtn = document.getElementById('addHolidayBtn');
        const scheduleMaintenanceBtn = document.getElementById('scheduleMaintenanceBtn');
        const addCustomEventBtn = document.getElementById('addCustomEventBtn');

        // --- Image Swap Hover Listeners ---
        document.querySelectorAll('.calendar-nav-icon').forEach(iconImg => {
            const defaultSrc = iconImg.dataset.defaultSrc;
            const hoverSrc = iconImg.dataset.hoverSrc;
            if (defaultSrc && hoverSrc) {
                const img = new Image();
                img.src = hoverSrc;
                iconImg.addEventListener('mouseover', () => iconImg.src = hoverSrc);
                iconImg.addEventListener('mouseout', () => iconImg.src = defaultSrc);
            }
        });

        // --- Calendar Navigation Listeners ---
        if (prevMonthBtn) prevMonthBtn.addEventListener('click', () => navigateCalendar(-1, 'month'));
        if (nextMonthBtn) nextMonthBtn.addEventListener('click', () => navigateCalendar(1, 'month'));
        if (prevYearBtn) prevYearBtn.addEventListener('click', () => navigateCalendar(-1, 'year'));
        if (nextYearBtn) nextYearBtn.addEventListener('click', () => navigateCalendar(1, 'year'));
        if (todayBtn) {
            todayBtn.addEventListener('click', () => {
                const today = new Date();
                currentMonth = today.getMonth();
                currentYear = today.getFullYear();
                selectedDate = today;
                // Load school year events and render calendar
        loadSchoolYearEvents().then(() => {
            // Check if we have events and navigate to the first event's month if needed
            if (schoolYearEvents.length > 0) {
                const firstEvent = schoolYearEvents[0];
                const eventDate = new Date(firstEvent.date);
                
                // Navigate to the month of the first event if it's different from current
                if (eventDate.getMonth() !== currentMonth || eventDate.getFullYear() !== currentYear) {
                    currentMonth = eventDate.getMonth();
                    currentYear = eventDate.getFullYear();
                }
            }
            renderCalendar(currentMonth, currentYear);
        }).catch(error => {
            console.error('Error in automatic event loading:', error);
            // Still render calendar even if events fail to load
            renderCalendar(currentMonth, currentYear);
        });
            });
        }

        // --- Get All Modals ---
        const addTermModal = document.getElementById('addTermModal');
        const addSchoolYearModal = document.getElementById('addSchoolYearModal'); // NEW
        const successModal = document.getElementById('successModal');
        
        // Debug modal elements
            addTermModal: !!addTermModal,
            addSchoolYearModal: !!addSchoolYearModal,
            successModal: !!successModal,
            addTermBtn: !!addTermBtn,
            addSchoolYearOptionBtn: !!addSchoolYearOptionBtn,
            addHolidayBtn: !!addHolidayBtn,
            scheduleMaintenanceBtn: !!scheduleMaintenanceBtn
        });

        // --- ADD SCHOOL YEAR MODAL LOGIC (NEW) ---
        if (addSchoolYearModal && addSchoolYearOptionBtn) {
            const form = document.getElementById('addSchoolYearForm');
            const saveBtn = addSchoolYearModal.querySelector('.form-btn-save');
            const requiredInputs = form.querySelectorAll('input[required], select[required]');

            const updateSchoolYearLabel = () => {
                const startYear = document.getElementById('syStartYear').value;
                const endYear = document.getElementById('syEndYear').value;
                if (startYear && endYear) {
                    document.getElementById('schoolYearLabel').value = `A.Y. ${startYear} - ${endYear}`;
                }
            };
            
            const validate = () => {
                let isValid = true;
                requiredInputs.forEach(i => { if (i.value.trim() === '') isValid = false; });
                
                // Additional validation for school year
                const startYear = parseInt(document.getElementById('syStartYear').value);
                const endYear = parseInt(document.getElementById('syEndYear').value);
                
                if (endYear < startYear) {
                    isValid = false;
                    document.getElementById('syEndYear').style.borderColor = '#e74c3c';
                } else {
                    document.getElementById('syEndYear').style.borderColor = '';
                }
                
                saveBtn.disabled = !isValid;
            };

            const openModal = () => { 
                form.reset(); 
                
                // Set default values for month/day inputs (August 1st and May 31st)
                const currentYear = new Date().getFullYear();
                document.getElementById('syStartYear').value = currentYear;
                document.getElementById('syEndYear').value = currentYear + 1;
                
                // Set date picker constraints
                document.getElementById('syStartMonthDay').min = `${currentYear}-01-01`;
                document.getElementById('syStartMonthDay').max = `${currentYear}-12-31`;
                document.getElementById('syEndMonthDay').min = `${currentYear + 1}-01-01`;
                document.getElementById('syEndMonthDay').max = `${currentYear + 1}-12-31`;
                
                document.getElementById('syStartMonthDay').value = `${currentYear}-08-01`;
                document.getElementById('syEndMonthDay').value = `${currentYear + 1}-05-31`;
                
                // Update school year label
                updateSchoolYearLabel();
                
                validate(); 
                addSchoolYearModal.style.display = 'flex';
                // Disable body scroll
                document.body.style.overflow = 'hidden';
            };
            const closeModal = () => { 
                addSchoolYearModal.style.display = 'none';
                // Re-enable body scroll
                document.body.style.overflow = '';
            };

            addSchoolYearOptionBtn.addEventListener('click', openModal);
            addSchoolYearModal.querySelector('.close-button').addEventListener('click', closeModal);
            addSchoolYearModal.querySelector('.form-btn-cancel').addEventListener('click', closeModal);

            form.addEventListener('input', validate);
            
            // Update school year label when end year changes
            document.getElementById('syEndYear').addEventListener('input', updateSchoolYearLabel);
            
            // Update end year and school year label when start year changes
            document.getElementById('syStartYear').addEventListener('change', function() {
                const startYear = parseInt(this.value);
                if (startYear) {
                    const endYear = startYear + 1;
                    document.getElementById('syEndYear').value = endYear;
                    
                    // Update date picker constraints
                    document.getElementById('syStartMonthDay').min = `${startYear}-01-01`;
                    document.getElementById('syStartMonthDay').max = `${startYear}-12-31`;
                    document.getElementById('syEndMonthDay').min = `${endYear}-01-01`;
                    document.getElementById('syEndMonthDay').max = `${endYear}-12-31`;
                    
                    // Update month/day inputs
                    const startMonthDay = document.getElementById('syStartMonthDay').value;
                    if (startMonthDay) {
                        const parts = startMonthDay.split('-');
                        document.getElementById('syStartMonthDay').value = `${startYear}-${parts[1]}-${parts[2]}`;
                    }
                    
                    const endMonthDay = document.getElementById('syEndMonthDay').value;
                    if (endMonthDay) {
                        const parts = endMonthDay.split('-');
                        document.getElementById('syEndMonthDay').value = `${endYear}-${parts[1]}-${parts[2]}`;
                    }
                    
                    // Update school year label
                    updateSchoolYearLabel();
                }
            });
            
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                if (saveBtn.disabled) return;
                
                // Combine year and month/day inputs to create full dates
                const startYear = document.getElementById('syStartYear').value;
                const endYear = document.getElementById('syEndYear').value;
                const startMonthDay = document.getElementById('syStartMonthDay').value;
                const endMonthDay = document.getElementById('syEndMonthDay').value;
                
                // Extract month and day from the date inputs
                const startDateParts = startMonthDay.split('-');
                const endDateParts = endMonthDay.split('-');
                
                const startDate = `${startYear}-${startDateParts[1]}-${startDateParts[2]}`;
                const endDate = `${endYear}-${endDateParts[1]}-${endDateParts[2]}`;
                
                // Determine status automatically based on current date and school year date range
                const currentDate = new Date();
                const startDateObj = new Date(startDate);
                const endDateObj = new Date(endDate);
                let status = 'Inactive';
                
                // Check if current date falls within the school year date range (inclusive)
                if (currentDate >= startDateObj && currentDate <= endDateObj) {
                    status = 'Active';
                }
                
                const formData = {
                    school_year_label: document.getElementById('schoolYearLabel').value,
                    start_date: startDate,
                    end_date: endDate,
                    status: status,
                };

                
                fetch('api/add_school_year.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error(`HTTP error! status: ${res.status}`);
                    }
                    return res.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Invalid JSON response:', text);
                            throw new Error('Server returned invalid response format');
                        }
                    });
                })
                .then(data => {
                    if (data.status === 'success') {
                        closeModal();
                        // Show success modal
                        const successModal = document.getElementById('successModal');
                        successModal.querySelector('#successMessageText').textContent = data.message;
                        successModal.style.display = 'flex';
                        // Disable body scroll for success modal
                        document.body.style.overflow = 'hidden';
                        successModal.querySelector('#successOkBtn').onclick = () => {
                            // Reload events and re-render calendar instead of full page reload
                            loadSchoolYearEvents().then(() => {
                                renderCalendar(currentMonth, currentYear);
                            });
                            successModal.style.display = 'none';
                            // Re-enable body scroll
                            document.body.style.overflow = '';
                        };
                    } else {
                        // Show error modal
                        const errorModal = document.getElementById('errorModal');
                        if (errorModal) {
                            const errorMessageText = errorModal.querySelector('#errorMessageText');
                            if (errorMessageText) {
                                errorMessageText.textContent = data.message || 'An error occurred while saving the school year.';
                            }
                            errorModal.style.display = 'flex';
                            // Disable body scroll for error modal
                            document.body.style.overflow = 'hidden';
                            
                            const errorOkBtn = errorModal.querySelector('#errorOkBtn');
                            if (errorOkBtn) {
                                errorOkBtn.onclick = () => {
                                    errorModal.style.display = 'none';
                                    // Re-enable body scroll
                                    document.body.style.overflow = '';
                                };
                            }
                        } else {
                            console.error('Error modal not found!');
                        }
                    }
                }).catch(error => {
                    console.error('Fetch Error:', error);
                    console.error('Error details:', error.message);
                    // Show error modal
                    const errorModal = document.getElementById('errorModal');
                    if (errorModal) {
                        const errorMessageText = errorModal.querySelector('#errorMessageText');
                        if (errorMessageText) {
                            // Provide more specific error message based on the error type
                            let errorMessage = 'A network error occurred. Please try again.';
                            if (error.message.includes('Server returned invalid response format')) {
                                errorMessage = 'Server error: Invalid response format. Please contact administrator.';
                            } else if (error.message.includes('HTTP error')) {
                                errorMessage = 'Server error: ' + error.message;
                            }
                            errorMessageText.textContent = errorMessage;
                        }
                        errorModal.style.display = 'flex';
                        // Disable body scroll for error modal
                        document.body.style.overflow = 'hidden';
                        
                        const errorOkBtn = errorModal.querySelector('#errorOkBtn');
                        if (errorOkBtn) {
                            errorOkBtn.onclick = () => {
                                errorModal.style.display = 'none';
                                // Re-enable body scroll
                                document.body.style.overflow = '';
                            };
                        }
                    } else {
                        console.error('Error modal not found for network error!');
                    }
                });
            });
        }

        // --- ADD TERM MODAL LOGIC ---
        if (addTermModal && addTermBtn) {
            const addTermForm = document.getElementById('addTermForm');
            const saveTermBtn = addTermModal.querySelector('.form-btn-save');
            const requiredInputs = addTermForm.querySelectorAll('input[required], select[required]');
            const closeModalBtn = addTermModal.querySelector('.close-button');
            const cancelModalBtn = addTermModal.querySelector('.form-btn-cancel');

            const validateForm = () => {
                let isValid = true;
                const termTitle = document.getElementById('termTitle').value;
                const schoolYearSelect = document.getElementById('schoolYearId').value;
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;
                
                // Check if both dropdowns are selected
                const bothDropdownsSelected = termTitle && schoolYearSelect;
                
                // Enable/disable date fields based on dropdown selection
                const startDateField = document.getElementById('startDate');
                const endDateField = document.getElementById('endDate');
                
                if (bothDropdownsSelected) {
                    startDateField.disabled = false;
                    endDateField.disabled = false;
                    startDateField.style.opacity = '1';
                    endDateField.style.opacity = '1';
                    startDateField.style.cursor = 'pointer';
                    endDateField.style.cursor = 'pointer';
                } else {
                    startDateField.disabled = true;
                    endDateField.disabled = true;
                    startDateField.style.opacity = '0.5';
                    endDateField.style.opacity = '0.5';
                    startDateField.style.cursor = 'not-allowed';
                    endDateField.style.cursor = 'not-allowed';
                    // Clear date values when disabled
                    startDateField.value = '';
                    endDateField.value = '';
                }
                
                // Check if all required fields are filled
                requiredInputs.forEach(input => { 
                    if (input.value.trim() === '') isValid = false; 
                });
                
                // Additional validation for dates (only if both dropdowns are selected)
                if (bothDropdownsSelected && startDate && endDate) {
                    if (new Date(startDate) >= new Date(endDate)) {
                        isValid = false;
                        endDateField.style.borderColor = '#e74c3c';
                    } else {
                        endDateField.style.borderColor = '';
                    }
                }
                
                // Validate against school year date range
                if (schoolYearSelect && startDate && endDate) {
                    const selectedOption = document.getElementById('schoolYearId').options[document.getElementById('schoolYearId').selectedIndex];
                    const schoolYearStart = selectedOption.dataset.start;
                    const schoolYearEnd = selectedOption.dataset.end;
                    
                    if (schoolYearStart && schoolYearEnd) {
                        if (new Date(startDate) < new Date(schoolYearStart) || new Date(endDate) > new Date(schoolYearEnd)) {
                            isValid = false;
                            startDateField.style.borderColor = '#e74c3c';
                            endDateField.style.borderColor = '#e74c3c';
                        } else {
                            startDateField.style.borderColor = '';
                            endDateField.style.borderColor = '';
                        }
                    }
                }
                
                saveTermBtn.disabled = !isValid;
            };
            const openAddTermModal = () => { 
                addTermForm.reset(); 
                
                // Initially disable date fields
                const startDateField = document.getElementById('startDate');
                const endDateField = document.getElementById('endDate');
                startDateField.disabled = true;
                endDateField.disabled = true;
                startDateField.style.opacity = '0.5';
                endDateField.style.opacity = '0.5';
                startDateField.style.cursor = 'not-allowed';
                endDateField.style.cursor = 'not-allowed';
                
                validateForm(); 
                addTermModal.style.display = 'flex';
                // Disable body scroll
                document.body.style.overflow = 'hidden';
            };
            const closeAddTermModal = () => { 
                addTermModal.style.display = 'none';
                // Re-enable body scroll
                document.body.style.overflow = '';
            };
            
            // Add event listener for term title selection
            document.getElementById('termTitle').addEventListener('change', function() {
                validateForm();
            });
            
            // Add event listener for school year selection to set date constraints
            document.getElementById('schoolYearId').addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const startDate = selectedOption.dataset.start;
                const endDate = selectedOption.dataset.end;
                
                if (startDate && endDate) {
                    document.getElementById('startDate').min = startDate;
                    document.getElementById('startDate').max = endDate;
                    document.getElementById('endDate').min = startDate;
                    document.getElementById('endDate').max = endDate;
                }
                
                validateForm();
            });
            
            addTermForm.addEventListener('input', validateForm);
            addTermBtn.addEventListener('click', openAddTermModal);
            closeModalBtn.addEventListener('click', closeAddTermModal);
            cancelModalBtn.addEventListener('click', closeAddTermModal);
            
            addTermForm.addEventListener('submit', (event) => {
                event.preventDefault();
                if (saveTermBtn.disabled) return;
                const formData = {
                    school_year_id: document.getElementById('schoolYearId').value,
                    title: document.getElementById('termTitle').value,
                    start_date: document.getElementById('startDate').value,
                    end_date: document.getElementById('endDate').value
                };
                fetch('api/add_term.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        closeAddTermModal();
                        // Show success modal
                        const successModal = document.getElementById('successModal');
                        successModal.querySelector('#successMessageText').textContent = data.message;
                        successModal.style.display = 'flex';
                        // Disable body scroll for success modal
                        document.body.style.overflow = 'hidden';
                        successModal.querySelector('#successOkBtn').onclick = () => {
                            // Reload events and re-render calendar instead of full page reload
                            loadSchoolYearEvents().then(() => {
                                renderCalendar(currentMonth, currentYear);
                            });
                            successModal.style.display = 'none';
                            // Re-enable body scroll
                            document.body.style.overflow = '';
                        };
                    } else {
                        // Show error modal
                        const errorModal = document.getElementById('errorModal');
                        errorModal.querySelector('#errorMessageText').textContent = data.message || 'An error occurred while saving the term.';
                        errorModal.style.display = 'flex';
                        // Disable body scroll for error modal
                        document.body.style.overflow = 'hidden';
                        errorModal.querySelector('#errorOkBtn').onclick = () => {
                            errorModal.style.display = 'none';
                            // Re-enable body scroll
                            document.body.style.overflow = '';
                        };
                    }
                }).catch(error => {
                    console.error('Fetch Error:', error);
                    // Show error modal
                    const errorModal = document.getElementById('errorModal');
                    errorModal.querySelector('#errorMessageText').textContent = 'A network error occurred. Please try again.';
                    errorModal.style.display = 'flex';
                    // Disable body scroll for error modal
                    document.body.style.overflow = 'hidden';
                    errorModal.querySelector('#errorOkBtn').onclick = () => {
                        errorModal.style.display = 'none';
                        // Re-enable body scroll
                        document.body.style.overflow = '';
                    };
                });
            });
        }

        // --- ADD CUSTOM EVENT MODAL LOGIC ---
        const addCustomEventModal = document.getElementById('addCustomEventModal');
        if (addCustomEventModal && addCustomEventBtn) {
            const form = document.getElementById('addCustomEventForm');
            const saveBtn = addCustomEventModal.querySelector('.form-btn-save');
            const cancelBtn = addCustomEventModal.querySelector('.form-btn-cancel');
            const closeBtn = addCustomEventModal.querySelector('.close-button');
            const repeatSelect = document.getElementById('eventRepeat');
            const customRecurrenceOptions = document.getElementById('customRecurrenceOptions');
            const colorInput = document.getElementById('eventColor');
            const colorHexInput = document.getElementById('eventColorHex');
            const requiredInputs = form.querySelectorAll('input[required], select[required]');

            // Open modal
            addCustomEventBtn.addEventListener('click', () => {
                form.reset();
                customRecurrenceOptions.style.display = 'none';
                saveBtn.disabled = true;
                addCustomEventModal.style.display = 'flex';
            });
            // Close modal
            function closeModal() { addCustomEventModal.style.display = 'none'; }
            closeBtn.addEventListener('click', closeModal);
            cancelBtn.addEventListener('click', closeModal);

            // Show/hide custom recurrence
            repeatSelect.addEventListener('change', () => {
                if (repeatSelect.value === 'custom') {
                    customRecurrenceOptions.style.display = 'block';
                } else {
                    customRecurrenceOptions.style.display = 'none';
                }
            });

            // Color picker and hex input sync
            function isValidHex(hex) {
                return /^#([0-9A-Fa-f]{6})$/.test(hex);
            }
            colorInput.addEventListener('input', () => {
                colorHexInput.value = colorInput.value;
            });
            colorHexInput.addEventListener('input', () => {
                let val = colorHexInput.value.trim();
                if (val.startsWith('#') && isValidHex(val)) {
                    colorInput.value = val;
                } else {
                    // Try to parse color name
                    const temp = document.createElement('div');
                    temp.style.color = val;
                    document.body.appendChild(temp);
                    const computed = getComputedStyle(temp).color;
                    document.body.removeChild(temp);
                    if (computed && computed !== 'rgb(0, 0, 0)' && computed !== 'rgba(0, 0, 0, 0)') {
                        // Convert rgb to hex
                        const rgb = computed.match(/\d+/g);
                        if (rgb && rgb.length >= 3) {
                            const hex = '#' + rgb.slice(0,3).map(x => (+x).toString(16).padStart(2, '0')).join('');
                            colorInput.value = hex;
                        }
                    }
                }
            });

            // All Day switch logic: disable End Date/Time and sync values when checked
            const allDaySwitch = document.getElementById('allDaySwitch');
            const startDateInput = document.getElementById('eventStartDate');
            const endDateInput = document.getElementById('eventEndDate');
            const startTimeInput = document.getElementById('eventStartTime');
            const endTimeInput = document.getElementById('eventEndTime');
            if (allDaySwitch && startDateInput && endDateInput && startTimeInput && endTimeInput) {
                allDaySwitch.addEventListener('change', function() {
                    if (allDaySwitch.checked) {
                        endDateInput.value = startDateInput.value;
                        endTimeInput.value = startTimeInput.value;
                        endDateInput.disabled = true;
                        endTimeInput.disabled = true;
                    } else {
                        endDateInput.disabled = false;
                        endTimeInput.disabled = false;
                    }
                });
                // Also update if start date/time changes while All Day is checked
                startDateInput.addEventListener('input', function() {
                    if (allDaySwitch.checked) {
                        endDateInput.value = startDateInput.value;
                    }
                });
                startTimeInput.addEventListener('input', function() {
                    if (allDaySwitch.checked) {
                        endTimeInput.value = startTimeInput.value;
                    }
                });
            }

            // Enable save button if required fields are filled
            function validate() {
                let isValid = true;
                requiredInputs.forEach(i => { if (i.value.trim() === '') isValid = false; });
                saveBtn.disabled = !isValid;
            }
            form.addEventListener('input', validate);
            form.addEventListener('change', validate);

            // Prevent form submit for now (implement backend as needed)
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                if (saveBtn.disabled) return;
                // TODO: Implement backend submission
                closeModal();
            });
        }

        // --- Custom Recurrence Day Toggle Buttons ---
        document.querySelectorAll('.custom-days .day-btn').forEach(btn => {
          btn.addEventListener('click', function() {
            btn.classList.toggle('selected');
            // Update hidden input with selected days
            const selected = Array.from(document.querySelectorAll('.custom-days .day-btn.selected'))
              .map(b => b.dataset.day)
              .join(',');
            const hiddenInput = document.getElementById('customDaysSelected');
            if (hiddenInput) hiddenInput.value = selected;
          });
        });

        // --- Success Modal Logic ---
        if(successModal) {
            const successOkBtn = successModal.querySelector('#successOkBtn');
            if (successOkBtn) {
                successOkBtn.onclick = () => successModal.style.display = 'none';
            }
        }
        
        // --- ADD HOLIDAY MODAL LOGIC ---
        const addHolidayModal = document.getElementById('addHolidayModal');
        let addHolidayModalInitialized = false; // Flag to prevent auto-opening
            modal: !!addHolidayModal,
            button: !!addHolidayBtn
        });
        
        // Ensure modal is hidden on page load with multiple approaches
        if (addHolidayModal) {
            
            // Force hide using CSS class approach
            addHolidayModal.classList.remove('show');
            
            // Also ensure inline styles don't interfere
            addHolidayModal.style.display = 'none';
            addHolidayModal.style.visibility = 'hidden';
            addHolidayModal.style.opacity = '0';
            addHolidayModal.style.zIndex = '-1';
            
            
            // Add MutationObserver to prevent external scripts from showing the modal
            const modalObserver = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                        const target = mutation.target;
                        if (target === addHolidayModal && !addHolidayModalInitialized) {
                            target.style.display = 'none';
                            target.style.visibility = 'hidden';
                            target.style.opacity = '0';
                            target.style.zIndex = '-1';
                            target.classList.remove('show');
                        }
                    }
                });
            });
            
            modalObserver.observe(addHolidayModal, {
                attributes: true,
                attributeFilter: ['style', 'class']
            });
            
            
            // Double-check after a brief delay
            setTimeout(() => {
                if (addHolidayModal.classList.contains('show')) {
                    addHolidayModal.classList.remove('show');
                }
            }, 100);
        }
        
        if (addHolidayModal && addHolidayBtn) {
            const addHolidayForm = document.getElementById('addHolidayForm');
            const saveHolidayBtn = addHolidayModal.querySelector('.form-btn-save');
            const requiredHolidayInputs = addHolidayForm.querySelectorAll('input[required], select[required]');
            const holidayAllDayCheckbox = document.getElementById('holidayAllDay');

            const validateHolidayForm = () => {
                let isValid = true;
                requiredHolidayInputs.forEach(input => { 
                    if (input.value.trim() === '') isValid = false; 
                });
                saveHolidayBtn.disabled = !isValid;
            };

            const openAddHolidayModal = () => { 
                
                // Only allow opening if properly initialized
                if (!addHolidayModalInitialized) {
                    return;
                }
                
                // Double-check modal state before opening
                if (addHolidayModal.classList.contains('show')) {
                    return;
                }
                
                addHolidayForm.reset(); 
                validateHolidayForm(); 
                
                // Show the modal using CSS class
                addHolidayModal.classList.add('show');
                
                // Disable body scroll
                document.body.style.overflow = 'hidden';
            };
            
            const closeAddHolidayModal = () => { 
                
                // Hide the modal using CSS class
                addHolidayModal.classList.remove('show');
                
                // Re-enable body scroll
                document.body.style.overflow = '';
            };

            // All Day switch functionality
            if (holidayAllDayCheckbox) {
                holidayAllDayCheckbox.addEventListener('change', function() {
                    const isAllDay = this.checked;
                    const startDateInput = document.getElementById('holidayStartDate');
                    const endDateInput = document.getElementById('holidayEndDate');
                    
                    
                    if (isAllDay) {
                        // When All Day is enabled, set end date to match start date and disable it
                        if (startDateInput.value) {
                            endDateInput.value = startDateInput.value;
                        }
                        endDateInput.disabled = true;
                        endDateInput.style.opacity = '0.6';
                        endDateInput.style.cursor = 'not-allowed';
                    } else {
                        // When All Day is disabled, re-enable end date
                        endDateInput.disabled = false;
                        endDateInput.style.opacity = '1';
                        endDateInput.style.cursor = 'pointer';
                    }
                });
            }

            // Also update if start date changes while All Day is checked
            const startDateInput = document.getElementById('holidayStartDate');
            if (startDateInput) {
                startDateInput.addEventListener('change', function() {
                    if (holidayAllDayCheckbox && holidayAllDayCheckbox.checked) {
                        const endDateInput = document.getElementById('holidayEndDate');
                        if (endDateInput) {
                            endDateInput.value = this.value;
                        }
                    }
                });
            }

            // Simple button click handler
            addHolidayBtn.addEventListener('click', () => {
                addHolidayModalInitialized = true;
                openAddHolidayModal();
            });
            
            // Ensure close functionality works properly
            const closeBtn = addHolidayModal.querySelector('.close-button');
            const cancelBtn = addHolidayModal.querySelector('.form-btn-cancel');
            
            if (closeBtn) {
                closeBtn.addEventListener('click', closeAddHolidayModal);
            } else {
                console.error('❌ Add Holiday modal close button not found');
            }
            
            if (cancelBtn) {
                cancelBtn.addEventListener('click', closeAddHolidayModal);
            } else {
                console.error('❌ Add Holiday modal cancel button not found');
            }

            addHolidayForm.addEventListener('input', validateHolidayForm);
            addHolidayForm.addEventListener('submit', (event) => {
                event.preventDefault();
                if (saveHolidayBtn.disabled) return;
                
                // TODO: Implement backend submission for holidays
                    title: document.getElementById('holidayTitle').value,
                    startDate: document.getElementById('holidayStartDate').value,
                    endDate: document.getElementById('holidayEndDate').value,
                    allDay: holidayAllDayCheckbox ? holidayAllDayCheckbox.checked : false,
                    description: document.getElementById('holidayDescription').value
                });
                
                closeAddHolidayModal();
                successModal.querySelector('#successMessageText').textContent = 'Holiday added successfully!';
                successModal.style.display = 'flex';
            });
        }

        // --- SCHEDULE MAINTENANCE MODAL LOGIC ---
        const scheduleMaintenanceModal = document.getElementById('scheduleMaintenanceModal');
        if (scheduleMaintenanceModal && scheduleMaintenanceBtn) {
            const scheduleMaintenanceForm = document.getElementById('scheduleMaintenanceForm');
            const saveMaintenanceBtn = scheduleMaintenanceModal.querySelector('.form-btn-save');
            const requiredMaintenanceInputs = scheduleMaintenanceForm.querySelectorAll('input[required], select[required], textarea[required]');

            const validateMaintenanceForm = () => {
                let isValid = true;
                requiredMaintenanceInputs.forEach(input => { 
                    if (input.value.trim() === '') isValid = false; 
                });
                saveMaintenanceBtn.disabled = !isValid;
            };

            const openScheduleMaintenanceModal = () => { 
                scheduleMaintenanceForm.reset(); 
                validateMaintenanceForm(); 
                scheduleMaintenanceModal.style.display = 'flex'; 
            };
            const closeScheduleMaintenanceModal = () => { 
                scheduleMaintenanceModal.style.display = 'none';
                // Re-enable body scroll
                document.body.style.overflow = '';
            };

            scheduleMaintenanceBtn.addEventListener('click', openScheduleMaintenanceModal);
            scheduleMaintenanceModal.querySelector('.close-button').addEventListener('click', closeScheduleMaintenanceModal);
            scheduleMaintenanceModal.querySelector('.form-btn-cancel').addEventListener('click', closeScheduleMaintenanceModal);

            scheduleMaintenanceForm.addEventListener('input', validateMaintenanceForm);
            scheduleMaintenanceForm.addEventListener('submit', (event) => {
                event.preventDefault();
                if (saveMaintenanceBtn.disabled) return;
                
                // TODO: Implement backend submission for maintenance
                    title: document.getElementById('maintenanceTitle').value,
                    startDate: document.getElementById('maintenanceStartDate').value,
                    endDate: document.getElementById('maintenanceEndDate').value,
                    startTime: document.getElementById('maintenanceStartTime').value,
                    endTime: document.getElementById('maintenanceEndTime').value,
                    description: document.getElementById('maintenanceDescription').value
                });
                
                closeScheduleMaintenanceModal();
                successModal.querySelector('#successMessageText').textContent = 'Maintenance scheduled successfully!';
                successModal.style.display = 'flex';
            });
        }
        
        // --- GLOBAL CLICK HANDLER FOR SCHOOL CALENDAR MODALS ---
        // Only handle school calendar modals, not other modals
        const errorModal = document.getElementById('errorModal');
        const dayDetailsModal = document.getElementById('dayDetailsModal');
        const schoolCalendarModals = [successModal, errorModal, addSchoolYearModal, addTermModal, addHolidayModal, scheduleMaintenanceModal, dayDetailsModal].filter(Boolean);
        
        window.addEventListener('click', (event) => {
            // Only handle clicks on school calendar modals
            if (schoolCalendarModals.includes(event.target)) {
                if (event.target === successModal) {
                    // Check if the OK button has a reload function attached
                    const successOkBtn = successModal.querySelector('#successOkBtn');
                    if (successOkBtn && successOkBtn.onclick && successOkBtn.onclick.toString().includes('location.reload')) {
                        location.reload();
                    } else {
                        successModal.style.display = 'none';
                        // Re-enable body scroll
                        document.body.style.overflow = '';
                    }
                } else if (event.target === errorModal) {
                    errorModal.style.display = 'none';
                    // Re-enable body scroll
                    document.body.style.overflow = '';
                } else if (event.target === addSchoolYearModal) {
                    addSchoolYearModal.style.display = 'none';
                    // Re-enable body scroll
                    document.body.style.overflow = '';
                } else if (event.target === addTermModal) {
                    addTermModal.style.display = 'none';
                    // Re-enable body scroll
                    document.body.style.overflow = '';
                } else if (event.target === addHolidayModal) {
                    addHolidayModal.classList.remove('show');
                    // Re-enable body scroll
                    document.body.style.overflow = '';
                } else if (event.target === scheduleMaintenanceModal) {
                    scheduleMaintenanceModal.style.display = 'none';
                    // Re-enable body scroll
                    document.body.style.overflow = '';
                } else if (event.target === dayDetailsModal) {
                    dayDetailsModal.style.display = 'none';
                    // Re-enable body scroll
                    document.body.style.overflow = '';
                }
            }
        });
        
        // Day Details Modal Close Button
        const dayDetailsCloseBtn = document.getElementById('dayDetailsCloseBtn');
        if (dayDetailsCloseBtn) {
            dayDetailsCloseBtn.addEventListener('click', () => {
                const dayDetailsModal = document.getElementById('dayDetailsModal');
                if (dayDetailsModal) {
                    dayDetailsModal.style.display = 'none';
                    document.body.style.overflow = '';
                }
            });
        }
        
        renderCalendar(currentMonth, currentYear);
        

        
        // Check if modals are loaded after a delay (for AJAX loading)
        setTimeout(() => {
            const delayedAddTermModal = document.getElementById('addTermModal');
            const delayedAddSchoolYearModal = document.getElementById('addSchoolYearModal');
            const delayedAddTermBtn = document.getElementById('addTermBtn');
            const delayedAddSchoolYearOptionBtn = document.getElementById('addSchoolYearOptionBtn');
            
                addTermModal: !!delayedAddTermModal,
                addSchoolYearModal: !!delayedAddSchoolYearModal,
                addTermBtn: !!delayedAddTermBtn,
                addSchoolYearOptionBtn: !!delayedAddSchoolYearOptionBtn
            });
            
            // If modals weren't found initially but are found now, set them up
            if (!addTermModal && delayedAddTermModal && delayedAddTermBtn) {
                // Add the modal setup logic here if needed
            }
            
            if (!addSchoolYearModal && delayedAddSchoolYearModal && delayedAddSchoolYearOptionBtn) {
                // Add the modal setup logic here if needed
            }
            
            // Fallback: If events haven't loaded yet, try loading them
            if (schoolYearEvents.length === 0) {
                loadSchoolYearEvents().then(() => {
                    if (schoolYearEvents.length > 0) {
                        renderCalendar(currentMonth, currentYear);
                    }
                });
            }
        }, 1000);
    }
});