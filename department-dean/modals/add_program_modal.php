<?php
// add_program_modal.php
// This file is an HTML fragment, included by content.php.
// It expects $conn to be available globally from content.php (via dashboard.php inclusion).

// No PHP processing logic for display needed directly here, as it's handled by process_add_program.php via AJAX.

?>

<div id="addProgramModal" class="department-modal-overlay" style="display: none;">
    <div class="department-modal-box">
        <div class="modal-header">
            <h2>Add New Program</h2>
            <span class="close-button" onclick="closeAddProgramModal()">&times;</span>
        </div>
        <form id="addProgramForm" class="form-grid">

            <div class="form-row">
                <div class="form-group" style="width: 250px;">
                    <label>Program Code</label>
                    <input type="text" name="program_code" id="modal_program_code" required autocomplete="off">
                </div>
                <div class="form-group" style="width: 375px;">
                    <label>Program Name</label>
                    <input type="text" name="program_name" id="modal_program_name" required autocomplete="off">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group" style="width: 625px;">
                    <label>Major (Optional)</label>
                    <input type="text" name="major" id="modal_program_major" placeholder="e.g., Software Engineering, Network Administration" autocomplete="off">
                    <small style="color: #666; font-size: 12px; margin-top: 4px; display: block;">Specify the major/specialization if applicable</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="cancel-btn" onclick="closeAddProgramModal()">CANCEL</button>
                <button type="submit" class="create-btn">CREATE</button>
            </div>
        </form>
    </div>
</div>

<div id="successModal" class="department-modal-overlay" style="display: none;">
    <div class="department-modal-box" style="width: 400px; text-align: center; animation: fadeIn 0.3s;">
        <img id="modalIcon" src="/DataDrift/ASCOM%20Monitoring%20System/src/assets/animated_icons/check-animated-icon.gif" alt="Icon" style="width: 64px; height: 64px; margin-bottom: 8px;">
        <h2 id="modalTitle" style="color: green; margin-bottom: 10px;">Success!</h2>
        <p id="successMessage" style="font-size: 16px; margin-bottom: 20px;"></p>
        <button type="button" class="create-btn" onclick="closeSuccessModal()">OK</button>
    </div>
</div>
