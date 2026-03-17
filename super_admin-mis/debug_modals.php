<?php
// debug_modals.php
// Debug script to test modal functionality

// Include the main content structure
require_once './includes/db_connection.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Modal Debug</title>
    <link rel="stylesheet" href="./styles/global.css">
    <link rel="stylesheet" href="./styles/modals.css">
    <link rel="stylesheet" href="./styles/user-account-management.css">
</head>
<body>
    <div class="content-wrapper">
        <div class="main-content">
            <h1>Modal Debug Page</h1>
            
                <button onclick="testOpenUserDetails()">Test Open User Details Modal</button>
    <button onclick="testOpenStatusInfo()">Test Open Status Info Modal</button>
    <button onclick="testOpenEditUser()">Test Open Edit User Modal</button>
    <button onclick="testOpenDeleteUser()">Test Open Delete User Modal</button>
    <button onclick="testRefreshFunction()">Test Refresh Function</button>
            
            <div id="debugOutput"></div>
        </div>
    </div>

    <!-- Include the modals -->
    <?php include './modal_user_details.php'; ?>
    <?php include './modal_edit_user.php'; ?>
    <?php include './modal_delete_user.php'; ?>

    <script>
        // Debug function to test modal opening
        function testOpenUserDetails() {
            console.log('Testing openUserDetailsModal...');
            try {
                // Test with a sample employee number
                openUserDetailsModal('123456');
            } catch (error) {
                console.error('Error opening user details modal:', error);
                document.getElementById('debugOutput').innerHTML += '<p style="color: red;">Error: ' + error.message + '</p>';
            }
        }
        
        function testOpenStatusInfo() {
            console.log('Testing showStatusInfo...');
            try {
                showStatusInfo();
            } catch (error) {
                console.error('Error opening status info modal:', error);
                document.getElementById('debugOutput').innerHTML += '<p style="color: red;">Error: ' + error.message + '</p>';
            }
        }
        
        function testOpenEditUser() {
            console.log('Testing openEditUserModal...');
            try {
                openEditUserModal('123456');
            } catch (error) {
                console.error('Error opening edit user modal:', error);
                document.getElementById('debugOutput').innerHTML += '<p style="color: red;">Error: ' + error.message + '</p>';
            }
        }
        
        function testOpenDeleteUser() {
            console.log('Testing openDeleteUserModal...');
            try {
                openDeleteUserModal('123456');
            } catch (error) {
                console.error('Error opening delete user modal:', error);
                document.getElementById('debugOutput').innerHTML += '<p style="color: red;">Error: ' + error.message + '</p>';
            }
        }
        
        function testRefreshFunction() {
            console.log('Testing manualRefreshUserList...');
            try {
                manualRefreshUserList();
                document.getElementById('debugOutput').innerHTML += '<p style="color: green;">✅ Refresh function called successfully</p>';
            } catch (error) {
                console.error('Error calling refresh function:', error);
                document.getElementById('debugOutput').innerHTML += '<p style="color: red;">Error: ' + error.message + '</p>';
            }
        }
        
        // Check if functions exist
        window.addEventListener('DOMContentLoaded', function() {
            console.log('Checking if modal functions exist...');
            
            const functions = [
                'openUserDetailsModal',
                'closeUserDetailsModal', 
                'showStatusInfo',
                'closeStatusInfoModal',
                'openEditUserModal',
                'closeEditUserModal',
                'openDeleteUserModal',
                'closeDeleteUserModal',
                'manualRefreshUserList',
                'refreshUserList',
                'filterUsers',
                'renderTable'
            ];
            
            functions.forEach(funcName => {
                if (typeof window[funcName] === 'function') {
                    console.log('✅ ' + funcName + ' exists');
                    document.getElementById('debugOutput').innerHTML += '<p style="color: green;">✅ ' + funcName + ' exists</p>';
                } else {
                    console.log('❌ ' + funcName + ' does not exist');
                    document.getElementById('debugOutput').innerHTML += '<p style="color: red;">❌ ' + funcName + ' does not exist</p>';
                }
            });
            
            // Check if modals exist in DOM
            const modals = [
                'userDetailsModal',
                'statusInfoModal',
                'editUserModal',
                'deleteUserModal'
            ];
            
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (modal) {
                    console.log('✅ ' + modalId + ' exists in DOM');
                    document.getElementById('debugOutput').innerHTML += '<p style="color: green;">✅ ' + modalId + ' exists in DOM</p>';
                } else {
                    console.log('❌ ' + modalId + ' does not exist in DOM');
                    document.getElementById('debugOutput').innerHTML += '<p style="color: red;">❌ ' + modalId + ' does not exist in DOM</p>';
                }
            });
        });
    </script>
    
    <!-- Include the JavaScript files -->
    <script src="./scripts/user-account-management.js"></script>
</body>
</html> 