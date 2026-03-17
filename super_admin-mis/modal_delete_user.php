<?php
// modal_delete_user.php
// This file is an HTML fragment, included by content.php.
?>

<div id="deleteUserModal" class="modal-overlay" style="display: none; align-items: center; justify-content: center;">
    <div class="modal-box" style="width: 450px; text-align: center; animation: fadeIn 0.3s; background: #fff; border-radius: 18px; box-shadow: 0 8px 32px rgba(0,0,0,0.18); padding: 32px 24px 24px 24px; position: relative; display: flex; flex-direction: column; align-items: center;">
        <div style="display: flex; justify-content: center; align-items: center; width: 100%;">
            <img src="../src/assets/animated_icons/warning-animated-icon.gif" alt="Warning" style="width: 90px; height: 90px; margin: 0 auto 18px auto; display: block;" />
        </div>
        <h2 style="color: #fbc02d; margin-bottom: 12px; font-size: 1.6em;">Delete User Account</h2>
        <p id="deleteUserMessage" style="font-family: 'TT Interphases', sans-serif; margin-bottom: 24px; color: #222; font-size: 1.1em; line-height: 1.5;">
            Are you sure you want to delete this user account? This action cannot be undone.
        </p>
        <div id="deleteUserDetails" style="background: #f5f5f5; padding: 16px; border-radius: 8px; margin-bottom: 24px; text-align: left; width: 100%;">
            <p style="margin: 0 0 8px 0; font-weight: bold; color: #333;">User Details:</p>
            <p id="deleteUserName" style="margin: 0 0 4px 0; color: #666;"></p>
            <p id="deleteUserEmail" style="margin: 0 0 4px 0; color: #666;"></p>
            <p id="deleteUserRole" style="margin: 0; color: #666;"></p>
        </div>
        <div style="display: flex; gap: 12px; justify-content: center; width: 100%;">
            <button type="button" class="cancel-btn" style="flex: 1; max-width: 120px;" onclick="closeDeleteUserModal()">CANCEL</button>
            <button type="button" class="delete-btn" id="confirmDeleteBtn" style="flex: 1; max-width: 120px; background: #d32f2f; color: #fff; border: none; border-radius: 8px; padding: 10px 16px; font-size: 1em; font-weight: 600; cursor: pointer;" onclick="confirmDeleteUser()">DELETE</button>
        </div>
    </div>
</div>

<div id="deleteUserSuccessModal" class="modal-overlay" style="display: none; align-items: center; justify-content: center;">
    <div class="modal-box" style="width: 400px; text-align: center; animation: fadeIn 0.3s; background: #fff; border-radius: 18px; box-shadow: 0 8px 32px rgba(0,0,0,0.18); padding: 32px 24px 24px 24px; position: relative; display: flex; flex-direction: column; align-items: center;">
        <div style="display: flex; justify-content: center; align-items: center; width: 100%;">
            <img src="../src/assets/animated_icons/check-animated-icon.gif" alt="Success" style="width: 100px; height: 100px; margin: 0 auto 18px auto; display: block;" />
        </div>
        <h2 style="color: #43a047; margin-bottom: 12px; font-size: 1.6em;">Success!</h2>
        <p id="deleteUserSuccessMessage" style="font-family: 'TT Interphases', sans-serif; margin-bottom: 24px; color: #222; font-size: 1.1em; line-height: 1.5;"></p>
        <button type="button" class="create-btn" style="margin: 0 auto; display: block; background: #43a047; color: #fff; border: none; border-radius: 8px; padding: 10px 32px; font-size: 1.1em; font-weight: 600; box-shadow: 0 2px 8px rgba(67,160,71,0.08);" onclick="closeDeleteUserSuccessModal()">OK</button>
    </div>
</div>

<div id="deleteUserErrorModal" class="modal-overlay" style="display: none; align-items: center; justify-content: center;">
    <div class="modal-box" style="width: 400px; text-align: center; animation: fadeIn 0.3s; background: #fff; border-radius: 18px; box-shadow: 0 8px 32px rgba(0,0,0,0.18); padding: 32px 24px 24px 24px; position: relative; display: flex; flex-direction: column; align-items: center;">
        <div style="display: flex; justify-content: center; align-items: center; width: 100%;">
            <img src="../src/assets/animated_icons/error2-animated-icon.gif" alt="Error" style="width: 90px; height: 90px; margin: 0 auto 18px auto; display: block;" />
        </div>
        <h2 id="deleteUserErrorHeading" style="color: #d32f2f; margin-bottom: 12px; font-size: 1.6em;">Error!</h2>
        <p id="deleteUserErrorMessage" style="font-family: 'TT Interphases', sans-serif; margin-bottom: 24px; color: #222; font-size: 1.1em; line-height: 1.5;"></p>
        <button type="button" class="create-btn error-btn" id="deleteUserErrorBtn" style="margin: 0 auto; display: block; background: #d32f2f; color: #fff; border: none; border-radius: 8px; padding: 10px 32px; font-size: 1.1em; font-weight: 600; box-shadow: 0 2px 8px rgba(211,47,47,0.08);" onclick="closeDeleteUserErrorModal()">OK</button>
    </div>
</div> 