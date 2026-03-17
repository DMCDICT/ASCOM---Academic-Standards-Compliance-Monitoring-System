<?php
// school-calendar-modals.php
// Ensure we have the school years data for dropdowns
if (!isset($school_years_for_dropdown) || empty($school_years_for_dropdown)) {
    // Fetch school years if not already loaded
    $school_years_for_dropdown = [];
    $check_sy = $conn->query("SHOW TABLES LIKE 'school_years'");
    if ($check_sy->num_rows > 0) {
        $columns_check = $conn->query("DESCRIBE school_years");
        $columns = [];
        while ($row = $columns_check->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        if (in_array('school_year_label', $columns)) {
            $sql_sy = "SELECT id, school_year_label, status, start_date, end_date FROM school_years ORDER BY school_year_label DESC";
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
<div id="addTermModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Term</h3>
            <span class="close-button">&times;</span>
        </div>
        <div class="modal-body">
            <form id="addTermForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="termTitle">Term Title</label>
                        <div class="custom-select-wrapper">
                            <select id="termTitle" name="termTitle" required>
                                <option value="" disabled selected>-- Select a Term --</option>
                                <option value="1st Semester">1st Semester</option>
                                <option value="2nd Semester">2nd Semester</option>
                                <option value="Summer Semester">Summer Semester</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="schoolYearId">School Year</label>
                        <div class="custom-select-wrapper">
                            <select id="schoolYearId" name="schoolYearId" required>
                                <option value="" disabled selected>-- Select a School Year --</option>
                                <?php 
                                // Ensure we have school years data
                                if (isset($school_years_for_dropdown) && !empty($school_years_for_dropdown)) {
                                                    foreach ($school_years_for_dropdown as $sy): 
                    // Check if this is the current school year based on date range
                    $today = date('Y-m-d');
                    $is_current = ($today >= $sy['start_date'] && $today <= $sy['end_date']) ? ' (Current)' : '';
                ?>
                    <option value="<?php echo htmlspecialchars($sy['id']); ?>" data-start="<?php echo htmlspecialchars($sy['start_date']); ?>" data-end="<?php echo htmlspecialchars($sy['end_date']); ?>">
                        <?php echo htmlspecialchars($sy['school_year_label'] . $is_current); ?>
                    </option>
                <?php endforeach;
                                } else {
                                    // Fallback if no school years are available
                                    echo '<option value="" disabled>No school years available</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
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
                    <button type="button" class="form-btn-cancel">Cancel</button>
                    <button type="submit" class="form-btn-save" disabled>Save Term</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add School Year Modal -->
<div id="addSchoolYearModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New School Year</h3>
            <span class="close-button">&times;</span>
        </div>
        <div class="modal-body">
            <form id="addSchoolYearForm">
                <div class="form-group">
                    <label for="schoolYearLabel">School Year Label</label>
                    <input type="text" id="schoolYearLabel" name="schoolYearLabel" placeholder="A.Y. 2025 - 2026" autocomplete="off" required readonly>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="syStartYear">Start Year</label>
                        <input type="number" id="syStartYear" name="syStartYear" min="2000" max="2100" placeholder="2025" required>
                    </div>
                    <div class="form-group">
                        <label for="syEndYear">End Year</label>
                        <input type="number" id="syEndYear" name="syEndYear" min="2000" max="2100" placeholder="2026" required readonly>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="syStartMonthDay">Start Month & Day</label>
                        <input type="date" id="syStartMonthDay" name="syStartMonthDay" required>
                    </div>
                    <div class="form-group">
                        <label for="syEndMonthDay">End Month & Day</label>
                        <input type="date" id="syEndMonthDay" name="syEndMonthDay" required>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="form-btn-cancel">Cancel</button>
                    <button type="submit" class="form-btn-save" disabled>Save School Year</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="modal-overlay">
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

<!-- Error Modal -->
<div id="errorModal" class="modal-overlay">
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
<div id="addHolidayModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Holiday</h3>
            <span class="close-button">&times;</span>
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
                    <div class="all-day-row">
                        <label class="switch">
                            <input type="checkbox" id="holidayAllDay" name="holidayAllDay">
                            <span class="slider"></span>
                        </label>
                        <span>All Day</span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="holidayDescription">Description (Optional)</label>
                    <textarea id="holidayDescription" name="holidayDescription" rows="3" placeholder="Brief description of the holiday..."></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="form-btn-cancel">Cancel</button>
                    <button type="submit" class="form-btn-save" disabled>Save Holiday</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Schedule Maintenance Modal -->
<div id="scheduleMaintenanceModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Schedule Maintenance</h3>
            <span class="close-button">&times;</span>
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
                    <button type="button" class="form-btn-cancel">Cancel</button>
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
<div id="dayDetailsModal" class="modal-overlay">
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
            <button type="button" id="dayDetailsCloseBtn" class="cancel-btn" style="min-width: 120px;">
                CLOSE
            </button>
        </div>
    </div>
</div>