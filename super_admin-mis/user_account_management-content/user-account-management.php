<?php
// user-account-management.php
// Enable real database queries and remove all dummy data.

global $conn; // Database connection ($conn) is expected to be available globally from content.php

// Initialize variables for fetching data
$totalUsers = 0;
$newAccounts = 0;
$users = []; // Array to store fetched user data

// Ensure database connection is active before querying
if (isset($conn) && !$conn->connect_error) {
    // Fetch Total Users count
    $userCountQuery = "SELECT COUNT(id) AS total_users FROM users";
    $userCountResult = $conn->query($userCountQuery);
    if ($userCountResult && $userCountResult->num_rows > 0) {
        $row = $userCountResult->fetch_assoc();
        $totalUsers = $row['total_users'];
        $userCountResult->free();
    } else {
        error_log("Error fetching total users in user-account-management.php: " . $conn->error);
    }

    // Fetch New Accounts count (created in last 7 days, only if created_at exists)
    $createdAtColResult = $conn->query("SHOW COLUMNS FROM users LIKE 'created_at'");
    if ($createdAtColResult && $createdAtColResult->num_rows > 0) {
        $newAccountsQuery = "SELECT COUNT(id) AS new_users FROM users WHERE created_at >= CURDATE() - INTERVAL 7 DAY";
        $newAccountsResult = $conn->query($newAccountsQuery);
        if ($newAccountsResult && $newAccountsResult->num_rows > 0) {
            $row = $newAccountsResult->fetch_assoc();
            $newAccounts = $row['new_users'];
            $newAccountsResult->free();
        }
    }

    // Fetch all users for the table
    $fetchUsersQuery = "SELECT employee_no, first_name, middle_name, last_name, institutional_email, mobile_no, role_id, department_id, is_active, last_activity, online_status, last_login, last_logout FROM users ORDER BY id DESC";
    $fetchUsersResult = $conn->query($fetchUsersQuery);

    if ($fetchUsersResult) {
        $roleNames = [];
        $deptNames = [];
        $deptCodes = []; // Initialize $deptCodes

        $roleMapQuery = "SELECT id, role FROM roles";
        $roleMapResult = $conn->query($roleMapQuery);
        if ($roleMapResult) {
            while($row = $roleMapResult->fetch_assoc()) { $roleNames[$row['id']] = $row['role']; }
            $roleMapResult->free();
        }

        $deptMapQuery = "SELECT id, department_name, department_code FROM departments";
        $deptMapResult = $conn->query($deptMapQuery);
        if ($deptMapResult) {
            while($row = $deptMapResult->fetch_assoc()) { 
                $deptNames[$row['id']] = $row['department_name'];
                $deptCodes[$row['id']] = $row['department_code'];
            }
            $deptMapResult->free();
        }

        while ($row = $fetchUsersResult->fetch_assoc()) {
            $row['role_name'] = $roleNames[$row['role_id']] ?? 'Unknown Role';
            $row['department_name'] = ($row['department_id'] !== NULL) ? ($deptNames[$row['department_id']] ?? 'N/A') : 'N/A';
            $row['department_code'] = ($row['department_id'] !== NULL) ? ($deptCodes[$row['department_id']] ?? '') : '';
            $row['status'] = ($row['is_active'] == 1) ? 'Active' : 'Inactive';
            $users[] = $row;
        }
        $fetchUsersResult->free();
    }
}
?>

<div class="user-account-page-container"> 
    <div class="header-row">
        <h2 class="main-page-title" style="padding-left: 0px;">User Account Management</h2> 
        <div class="header-buttons">
            <button class="activity-button">Activity Logs</button>
        </div>
    </div>

    <div class="stats-container">
        <div class="stat-box">
            <div class="label-icon">
                <span>Total Users</span>
            </div>
            <div class="stat-amount"><?php echo htmlspecialchars($totalUsers); ?></div>
        </div>

        <div class="stat-box">
            <div class="label-icon">
                <span>New Accounts</span>
            </div>
            <div class="stat-amount"><?php echo htmlspecialchars($newAccounts); ?></div>
        </div>
    </div>

    <div class="search-filter-row">
        <div class="search-left">
            <div class="user-search-bar">
                <img src="../src/assets/icons/magnifier-icon.png" alt="Search" class="magnifier-icon">
                <input type="text" placeholder="Search Account Here" id="userSearchInput" autocomplete="off">
                <button type="button" id="clearSearchBtn" class="clear-search-btn" style="display: none;">&times;</button>
                <div id="searchSuggestions" class="search-suggestions-panel" style="display:none;"></div>
            </div>
            <button class="search-button">Search</button>
        </div>

    </div>

    <div class="user-table-section">
        <table id="userTable">
            <thead>
                <tr>
                    <th>Employee No.</th>
                    <th>Name</th>
                    <th>Institutional Email</th>
                    <th>Mobile Number</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
                <!-- Table will be populated by JavaScript -->
                <tr><td colspan="8" style="text-align: center;">Loading users...</td></tr>
            </tbody>
        </table>

        <div class="pagination" id="paginationControls"></div>
    </div>
</div>

<script>
    // Set current user's employee number for activity tracking
    const currentUserEmployeeNo = '<?php echo isset($_SESSION['employee_no']) ? $_SESSION['employee_no'] : ''; ?>';
</script>