<?php
// modal_user_details.php
// This file is an HTML fragment, included by content.php.
?>

<div id="userDetailsModal" class="modal-overlay" style="display: none !important; visibility: hidden !important;" data-modal-type="user-details">
    <div class="modal-box">
        <div class="modal-header">
            <h2>User Account Details</h2>
            <span class="close-button" onclick="closeUserDetailsModal()">&times;</span>
        </div>
        
        <div id="userDetailsContent">
            <!-- User details will be populated here -->
        </div>
        
        <div class="form-actions">
            <button type="button" id="editFromDetailsBtn" onclick="editFromDetails()" class="edit-btn">EDIT</button>
            <button type="button" id="deleteFromDetailsBtn" onclick="deleteFromDetails()" class="delete-btn">DELETE</button>
        </div>
    </div>
</div> 