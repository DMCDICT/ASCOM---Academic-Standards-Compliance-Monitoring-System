<?php
// modal_add_department.php
// This file is an HTML fragment, included by dashboard.php.
// It expects $conn to be available globally from content.php (via dashboard.php inclusion).

// No PHP processing logic for display needed directly here, as it's handled by process_add_department.php via AJAX.

?>

<div id="addDepartmentModal" class="department-modal-overlay" style="display: none;">
    <div class="department-modal-box">
        <div class="modal-header">
            <h2>Add New Department</h2>
            <span class="close-button" onclick="closeAddDepartmentModal()">&times;</span>
        </div>
        <form id="addDepartmentForm" class="form-grid" onsubmit="handleDepartmentFormSubmit(event)">

            <div class="form-row">
                <div class="form-group" style="width: 250px;">
                    <label>Department Code</label>
                    <input type="text" name="department_code" id="modal_department_code" required autocomplete="off" oninput="checkFormValidity()">
                </div>
                <div class="form-group" style="width: 375px;">
                    <label>Full Name</label>
                    <input type="text" name="department_name" id="modal_department_name" required autocomplete="off" oninput="checkFormValidity()">
                </div>
            </div>

            <div class="form-group" style="width: 250px;">
                <label>Color Code</label>
                <div class="color-input-wrapper">
                    <input type="color" id="colorPicker" value="#4A7DFF">

                    <div id="colorSwatchDisplay" class="color-swatch-display" style="background-color: #4A7DFF;"></div>

                    <input type="text" name="color_code" id="colorHex" value="#4A7DFF" required pattern="^#([A-Fa-f0-9]{6})$" title="Enter a valid hex code like #123ABC" oninput="checkFormValidity()">
                    <button type="button" id="clearColorBtn" class="clear-input-btn" title="Clear color">
                        &times;
                    </button>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="cancel-btn" onclick="closeAddDepartmentModal()">CANCEL</button>
                <button type="submit" class="create-btn">CREATE</button>
            </div>
        </form>
    </div>
</div>

<div id="successModal" class="department-modal-overlay" style="display: none;">
    <div class="department-modal-box" style="width: 400px; text-align: center; animation: fadeIn 0.3s;">
        <img src="../src/assets/animated_icons/check-animated-icon.gif" alt="Success" style="width: 60px; height: 60px; margin-bottom: 15px;">
        <h2 style="color: green; margin-bottom: 10px;">Success!</h2>
        <p id="successMessage" style="font-size: 16px; margin-bottom: 20px;"></p>
        <button type="button" class="create-btn" onclick="closeSuccessModal()">OK</button>
    </div>
</div>