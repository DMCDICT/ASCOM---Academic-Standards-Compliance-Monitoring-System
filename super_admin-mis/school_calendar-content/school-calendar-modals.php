<?php
// school-calendar-modals.php
// Ensure we have the school years data for dropdowns
if (!isset($school_years_for_dropdown) || empty($school_years_for_dropdown)) {
    // Fetch school years if not already loaded
    $school_years_for_dropdown = [];
    $check_sy = $conn->query("SHOW TABLES LIKE 'school_years'");
    if ($check_sy->num_rows > 0) {
    $cols_raw = $conn->query("DESCRIBE school_years");
    if ($cols_raw) {
        $cols = [];
        while ($r = $cols_raw->fetch_assoc()) { $cols[] = $r['Field']; }
        
        $has_label = in_array('school_year_label', $cols);
        $has_status = in_array('status', $cols);
        $has_active = in_array('is_active', $cols);

        $label_field = $has_label ? "school_year_label" : "CONCAT(year_start, '-', year_end)";
        $status_field = $has_status ? "status" : ($has_active ? "CASE WHEN is_active = 1 THEN 'Active' ELSE 'Inactive' END" : "'Active'");
        
        $sql_sy = "SELECT id, $label_field as school_year_label, $status_field as status, start_date, end_date FROM school_years ORDER BY $label_field DESC";
        $result_sy = $conn->query($sql_sy);
        if ($result_sy && $result_sy->num_rows > 0) {
            while($row = $result_sy->fetch_assoc()) {
                $school_years_for_dropdown[] = $row;
            }
        }
    }

    }
    
    // If still empty, provide default data
    if (empty($school_years_for_dropdown)) {
        $school_years_for_dropdown = [
            [
                'id' => 1,
                'school_year_label' => '2023-2024',
                'status' => 'Active',
                'start_date' => '2023-08-01',
                'end_date' => '2024-05-31',
            ]
        ];
    }
}
?>

<!-- Add Term Modal -->
<div id="addTermModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Term</h3>
            <button type="button" class="close-button" onclick="closeAddTermModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="addTermForm">
                <div class="form-group">
                    <label for="termTitle">Term Title</label>
                    <select id="termTitle" name="termTitle" required>
                        <option value="" disabled selected>-- Select a Term --</option>
                        <option value="1st Semester">1st Semester</option>
                        <option value="2nd Semester">2nd Semester</option>
                        <option value="Summer Semester">Summer Semester</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="schoolYearId">School Year</label>
                    <select id="schoolYearId" name="schoolYearId" required>
                        <option value="" disabled selected>-- Select a School Year --</option>
                        <?php 
                        if (isset($school_years_for_dropdown) && !empty($school_years_for_dropdown)) {
                            foreach ($school_years_for_dropdown as $sy): 
                            $today = date('Y-m-d');
                            $is_current = ($today >= $sy['start_date'] && $today <= $sy['end_date']) ? ' (Current)' : '';
                        ?>
                        <option value="<?php echo htmlspecialchars($sy['id']); ?>" data-start="<?php echo htmlspecialchars($sy['start_date']); ?>" data-end="<?php echo htmlspecialchars($sy['end_date']); ?>">
                            <?php echo htmlspecialchars($sy['school_year_label'] . $is_current); ?>
                        </option>
                        <?php endforeach;
                        } else {
                            echo '<option value="" disabled>No school years available</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="startDate">Start Date</label>
                        <input type="date" id="startDate" name="startDate" required>
                    </div>
                    <div class="form-group">
                        <label for="endDate">End Date</label>
                        <input type="date" id="endDate" name="endDate" required>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="form-btn-cancel" onclick="closeAddTermModal()">Cancel</button>
                    <button type="submit" class="form-btn-save" disabled>Save Term</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add School Year Modal -->
<div id="addSchoolYearModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New School Year</h3>
            <button type="button" class="close-button" onclick="closeAddSchoolYearModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="addSchoolYearForm">
                <div class="form-group">
                    <label for="schoolYearLabel">School Year Label</label>
                    <input type="text" id="schoolYearLabel" name="schoolYearLabel" placeholder="Auto-filled from start year" autocomplete="off" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="syStartYear">Start Year</label>
                        <input type="number" id="syStartYear" name="syStartYear" min="2000" max="2100" placeholder="e.g., 2025" required onchange="autoFillEndYear()">
                    </div>
                    <div class="form-group">
                        <label for="syEndYear">End Year</label>
                        <input type="number" id="syEndYear" name="syEndYear" min="2000" max="2100" placeholder="Auto-filled" readonly>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="syStartMonthDay">Start Date</label>
                        <input type="date" id="syStartMonthDay" name="syStartMonthDay" required>
                    </div>
                    <div class="form-group">
                        <label for="syEndMonthDay">End Date</label>
                        <input type="date" id="syEndMonthDay" name="syEndMonthDay" required>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="form-btn-cancel" onclick="closeAddSchoolYearModal()">Cancel</button>
                    <button type="button" class="form-btn-save" id="saveSchoolYearBtn" onclick="doSaveSchoolYear()">Save School Year</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Manage Terms Modal -->
<div id="manageTermsModal" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h3>Manage All Terms</h3>
            <button type="button" class="close-button" onclick="closeManageTermsModal()">&times;</button>
        </div>
        <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
            <div id="termsListContainer">
                <p style="text-align: center; color: #666;">Loading terms...</p>
            </div>
        </div>
        <div class="modal-footer" style="padding: 16px 24px; border-top: 1px solid rgba(12,75,52,0.12);">
            <button type="button" class="form-btn-cancel" onclick="closeManageTermsModal()">Close</button>
        </div>
    </div>
</div>

<style>
.modal-footer {
    display: flex;
    justify-content: flex-end;
}
.term-card {
    background: #fff;
    border: 1px solid rgba(12,75,52,0.12);
    border-radius: 12px;
    padding: 14px;
    margin-bottom: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.term-card:hover {
    border-color: rgba(12,75,52,0.25);
    box-shadow: 0 4px 12px rgba(12,75,52,0.08);
}
.term-info {
    flex: 1;
}
.term-title {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
}
.term-dates {
    font-size: 12px;
    color: rgba(17,24,39,0.6);
    margin-top: 4px;
}
.term-school-year {
    font-size: 11px;
    color: rgba(17,24,39,0.5);
    margin-top: 2px;
}
.term-actions {
    display: flex;
    gap: 8px;
}
.btn-edit-term {
    background: #0C4B34;
    color: white;
    border: none;
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}
.btn-delete-term {
    background: #dc3545;
    color: white;
    border: none;
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}
html[data-theme="dark"] .term-card {
    background: #252525;
    border-color: #333;
}
html[data-theme="dark"] .term-title {
    color: #e0e0e0;
}
html[data-theme="dark"] .term-info {
    color: #ccc;
}
</style>

<script>
function openManageTermsModal() {
    const modal = document.getElementById('manageTermsModal');
    if (modal) {
        modal.style.display = 'flex';
        loadAllTerms();
    }
}

function closeManageTermsModal() {
    const modal = document.getElementById('manageTermsModal');
    if (modal) modal.style.display = 'none';
}

// Make function globally available
window.openManageTermsModal = openManageTermsModal;
window.closeManageTermsModal = closeManageTermsModal;

async function loadAllTerms() {
    const container = document.getElementById('termsListContainer');
    if (!container) return;
    
    try {
        const response = await fetch('./api/get_all_terms.php');
        const data = await response.json();
        
        if (data.status === 'success' && data.terms && data.terms.length > 0) {
            let html = '';
            data.terms.forEach(term => {
                const statusClass = term.status === 'Active' ? 'status-active' : 'status-inactive';
                html += `
                    <div class="term-card">
                        <div class="term-info">
                            <div class="term-title">${term.title}</div>
                            <div class="term-dates">${term.start_date} to ${term.end_date}</div>
                            <div class="term-school-year">School Year: ${term.school_year_label}</div>
                            <span class="status-badge ${statusClass}">${term.status}</span>
                        </div>
                        <div class="term-actions">
                            <button class="btn-edit-term" onclick="openEditTermModal(${term.id}, '${term.title}', '${term.start_date}', '${term.end_date}', ${term.school_year_id})">Edit</button>
                            <button class="btn-delete-term" onclick="deleteTerm(${term.id})">Delete</button>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        } else {
            container.innerHTML = '<p style="text-align: center; color: #666;">No terms found. Add a school year first, then create terms.</p>';
        }
    } catch (error) {
        container.innerHTML = '<p style="text-align: center; color: red;">Error loading terms: ' + error.message + '</p>';
    }
}

async function deleteTerm(termId) {
    if (!confirm('Are you sure you want to delete this term?')) return;
    
    try {
        const response = await fetch('./api/delete_term.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ term_id: termId })
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            alert('Term deleted successfully');
            loadAllTerms(); // Reload the list
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

function openEditTermModal(termId, title, startDate, endDate, schoolYearId) {
    alert('Edit term: ' + title + ' (ID: ' + termId + ') - This feature is coming soon!');
}
</script>

<script>
function autoFillEndYear() {
    const startYear = document.getElementById('syStartYear')?.value;
    const endYearField = document.getElementById('syEndYear');
    const labelField = document.getElementById('schoolYearLabel');
    
    if (startYear && endYearField) {
        const endYear = parseInt(startYear) + 1;
        endYearField.value = endYear;
        if (labelField) {
            labelField.value = startYear + '-' + endYear;
        }
    }
}

async function doSaveSchoolYear() {
    const schoolYearLabel = document.getElementById('schoolYearLabel')?.value;
    const syStartYear = document.getElementById('syStartYear')?.value;
    const syEndYear = document.getElementById('syEndYear')?.value;
    const syStartMonthDay = document.getElementById('syStartMonthDay')?.value;
    const syEndMonthDay = document.getElementById('syEndMonthDay')?.value;
    
    // Debug - show what's missing
    let missing = [];
    if (!schoolYearLabel) missing.push('Label');
    if (!syStartYear) missing.push('Start Year');
    if (!syEndYear) missing.push('End Year');
    if (!syStartMonthDay) missing.push('Start Date');
    if (!syEndMonthDay) missing.push('End Date');
    
    if (missing.length > 0) {
        alert('Missing fields: ' + missing.join(', '));
        return;
    }
    
    // Construct full dates
    const startParts = syStartMonthDay.split('-');
    const endParts = syEndMonthDay.split('-');
    const startDate = syStartYear + '-' + startParts[1] + '-' + startParts[2];
    const endDate = syEndYear + '-' + endParts[1] + '-' + endParts[2];
    
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
            document.getElementById('addSchoolYearForm').reset();
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}
</script>

<!-- Success Modal -->
<div id="successModal" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 400px; min-height: 280px; text-align: center; background-color: #FFFFFF; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3); margin: 0; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
        <div class="modal-header" style="text-align: center; margin-bottom: 20px; display: block;">
            <img src="../src/assets/animated_icons/check-animated-icon.gif" alt="Success" style="width: 80px; height: 80px; margin: 0 auto 20px; display: block;">
            <h2 style="color: #28a745; margin-bottom: 0; display: block; width: 100%;">Success!</h2>
        </div>
        <div class="modal-body" style="text-align: center; margin-bottom: 20px;">
            <p id="successMessageText" style="font-size: 16px; line-height: 1.5; color: #333; margin-bottom: 0;">
                Operation completed successfully!
            </p>
        </div>
        <div class="modal-actions" style="text-align: center;">
            <button type="button" id="successOkBtn" class="create-btn" style="min-width: 120px;">
                OK
            </button>
        </div>
    </div>
</div>

<script>
function closeSuccessModal() {
    document.getElementById('successModal').style.display = 'none';
}
document.getElementById('successOkBtn')?.addEventListener('click', closeSuccessModal);
</script>

<!-- Error Modal -->
<div id="errorModal" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 400px; min-height: 280px; text-align: center; background-color: #FFFFFF; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3); margin: 0; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
        <div class="modal-header" style="text-align: center; margin-bottom: 20px; display: block;">
            <img src="../src/assets/animated_icons/error2-animated-icon.gif" alt="Error" style="width: 80px; height: 80px; margin: 0 auto 20px; display: block;">
            <h2 style="color: #dc3545; margin-bottom: 0; display: block; width: 100%;">Error!</h2>
        </div>
        <div class="modal-body" style="text-align: center; margin-bottom: 20px;">
            <p id="errorMessageText" style="font-size: 16px; line-height: 1.5; color: #333; margin-bottom: 0;">
                An error occurred. Please try again.
            </p>
        </div>
        <div class="modal-actions" style="text-align: center;">
            <button type="button" id="errorOkBtn" class="cancel-btn" style="min-width: 120px;">
                TRY AGAIN
            </button>
        </div>
    </div>
</div>



<!-- Add Holiday Modal -->
<div id="addHolidayModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Holiday</h3>
            <button type="button" class="close-button" onclick="closeAddHolidayModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="addHolidayForm">
                <div class="form-group">
                    <label for="holidayTitle">Holiday Title</label>
                    <input type="text" id="holidayTitle" name="holidayTitle" placeholder="e.g., Independence Day" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="holidayStartDate">Start Date</label>
                        <input type="date" id="holidayStartDate" name="holidayStartDate" required>
                    </div>
                    <div class="form-group">
                        <label for="holidayEndDate">End Date</label>
                        <input type="date" id="holidayEndDate" name="holidayEndDate" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="switch-label">
                        <input type="checkbox" id="holidayAllDay" name="holidayAllDay">
                        <span class="switch-text">All Day Event</span>
                    </label>
                </div>
                <div class="form-group">
                    <label for="holidayDescription">Description (Optional)</label>
                    <textarea id="holidayDescription" name="holidayDescription" rows="3" placeholder="Brief description of the holiday..."></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="form-btn-cancel" onclick="closeAddHolidayModal()">Cancel</button>
                    <button type="submit" class="form-btn-save" disabled>Save Holiday</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Schedule Maintenance Modal -->
<div id="scheduleMaintenanceModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Schedule Maintenance</h3>
            <button type="button" class="close-button" onclick="closeScheduleMaintenanceModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="scheduleMaintenanceForm">
                <div class="form-group">
                    <label for="maintenanceTitle">Maintenance Title</label>
                    <input type="text" id="maintenanceTitle" name="maintenanceTitle" placeholder="e.g., System Maintenance" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="maintenanceStartDate">Start Date</label>
                        <input type="date" id="maintenanceStartDate" name="maintenanceStartDate" required>
                    </div>
                    <div class="form-group">
                        <label for="maintenanceEndDate">End Date</label>
                        <input type="date" id="maintenanceEndDate" name="maintenanceEndDate" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="maintenanceStartTime">Start Time</label>
                        <input type="time" id="maintenanceStartTime" name="maintenanceStartTime" required>
                    </div>
                    <div class="form-group">
                        <label for="maintenanceEndTime">End Time</label>
                        <input type="time" id="maintenanceEndTime" name="maintenanceEndTime" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="maintenanceDescription">Description</label>
                    <textarea id="maintenanceDescription" name="maintenanceDescription" rows="4" placeholder="Detailed description of the maintenance work..." required></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="form-btn-cancel" onclick="closeScheduleMaintenanceModal()">Cancel</button>
                    <button type="submit" class="form-btn-save" disabled>Schedule Maintenance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Ensure the modal text is not corrupted by any other scripts
function closeInvalidActiveYearModal() {
    const modal = document.getElementById('invalidActiveYearModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Prevent any script from modifying the modal text
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('invalidActiveYearModal');
    if (modal) {
        const textElement = modal.querySelector('p');
        if (textElement) {
            // Set the correct text and prevent modification
            textElement.textContent = 'You cannot set a school year that starts before the current year as "Active". Please select "Inactive" for past years.';
            
            // Prevent any script from modifying this text
            Object.defineProperty(textElement, 'textContent', {
                get: function() {
                    return 'You cannot set a school year that starts before the current year as "Active". Please select "Inactive" for past years.';
                },
                set: function() {
                    // Do nothing - prevent modification
                }
            });
        }
    }
});

// Prevent the modal from being shown automatically
window.addEventListener('load', function() {
    const modal = document.getElementById('invalidActiveYearModal');
    if (modal) {
        modal.style.display = 'none';
    }
});
</script>

<!-- Day Details Modal -->
<div id="dayDetailsModal" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 600px; min-height: 400px; text-align: center; background-color: #FFFFFF; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3); margin: 0; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); display: flex; flex-direction: column;">
        <div class="modal-header" style="text-align: center; margin-bottom: 20px; display: block; flex-shrink: 0;">
            <h2 id="dayDetailsTitle" style="color: #333; margin-bottom: 0; display: block; width: 100%; font-size: 24px; font-weight: bold;">Day Details</h2>
        </div>
        <div class="modal-body" style="text-align: left; margin-bottom: 20px; flex: 1; overflow-y: auto;">
            <div id="dayEventsList" style="max-height: none;">
                <!-- Events will be populated here -->
            </div>
            <div id="noEventsMessage" style="text-align: center; color: #888; font-style: italic; padding: 20px;">
                No events scheduled for this day.
            </div>
        </div>
        <div class="modal-actions" style="text-align: center; flex-shrink: 0; margin-top: auto;">
            <button type="button" id="dayDetailsCloseBtn" class="cancel-btn" style="min-width: 120px;" onclick="closeDayDetailsModal()">
                CLOSE
            </button>
        </div>
    </div>
</div>