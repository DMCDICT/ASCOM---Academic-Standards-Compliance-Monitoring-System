<?php
// user-account-management.php
// Redesigned for ASCOM premium standards.
// Included by content.php

global $conn;

// Initialize variables
$totalUsers = 0;
$newAccounts = 0;
$users = [];

if (isset($conn) && !$conn->connect_error) {
    // Fetch Total Users count
    $userCountQuery = "SELECT COUNT(id) AS total_users FROM users";
    $userCountResult = $conn->query($userCountQuery);
    if ($userCountResult && $userCountResult->num_rows > 0) {
        $row = $userCountResult->fetch_assoc();
        $totalUsers = $row['total_users'];
        $userCountResult->free();
    }

    // Fetch New Accounts count (last 7 days)
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

    // Fetch Departments for mapping
    $deptCodes = [];
    $deptMapResult = $conn->query("SELECT id, department_code FROM departments");
    if ($deptMapResult) {
        while($row = $deptMapResult->fetch_assoc()) { 
            $deptCodes[$row['id']] = $row['department_code'];
        }
        $deptMapResult->free();
    }

    // Fetch Roles for mapping
    $roleNames = [];
    $roleMapResult = $conn->query("SELECT id, role_name FROM roles");
    if ($roleMapResult) {
        while($row = $roleMapResult->fetch_assoc()) { 
            $roleNames[$row['id']] = $row['role_name'];
        }
        $roleMapResult->free();
    }

    // Fetch Users
    $fetchUsersQuery = "SELECT id, employee_no, first_name, last_name, email, institutional_email, mobile_no, role, role_id, department_id, is_active FROM users ORDER BY id DESC";
    $fetchUsersResult = $conn->query($fetchUsersQuery);

    if ($fetchUsersResult) {
        while ($row = $fetchUsersResult->fetch_assoc()) {
            $fullName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            
            $roleId = $row['role_id'] ?? null;
            $roleDisplay = ($roleId && isset($roleNames[$roleId])) ? $roleNames[$roleId] : ($row['role'] ?? 'User');
            
            $deptId = $row['department_id'] ?? null;
            $deptCode = $deptId ? ($deptCodes[$deptId] ?? '-') : '-';
            
            $email = $row['email'] ?: ($row['institutional_email'] ?? 'N/A');
            $status = ($row['is_active'] == 1) ? 'Active' : 'Inactive';
            $statusClass = ($row['is_active'] == 1) ? 'active' : 'inactive';

            $users[] = [
                'employee_no' => $row['employee_no'],
                'full_name' => $fullName,
                'email' => $email,
                'mobile' => $row['mobile_no'] ?? '-',
                'role' => ucfirst($roleDisplay),
                'dept' => $deptCode,
                'status' => $status,
                'status_class' => $statusClass
            ];
        }
        $fetchUsersResult->free();
    }
}

// Greeting logic
$hour = (int) date('G');
if ($hour < 12) { $greeting = 'Good Morning'; }
elseif ($hour < 17) { $greeting = 'Good Afternoon'; }
else { $greeting = 'Good Evening'; }
?>

<div class="user-account-page-container">
    <!-- Greeting Section -->
    <div class="user-greeting">
        <div class="greeting-text">
            <h2><?php echo $greeting; ?>, Admin</h2>
            <p>Manage institutions accounts and access levels here.</p>
        </div>
        <div class="header-actions">
            <button class="activity-logs-btn" onclick="location.href='?page=activity-logs'">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                Activity Logs
            </button>
            <button class="add-user-btn" onclick="openAddUserModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Add User
            </button>
        </div>
    </div>

    <!-- Overview Section -->
    <div class="section-header">
        <div class="label-bar"></div>
        <div>
            <h3>Total Overview</h3>
            <p>Quick snapshot of user registration status</p>
        </div>
    </div>

    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </div>
            <div class="metric-content">
                <span class="metric-label">Total Users</span>
                <div class="metric-value"><?php echo number_format($totalUsers); ?></div>
                <span class="metric-subtext">Registered accounts</span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="19" y1="8" x2="19" y2="14"></line><line x1="16" y1="11" x2="22" y2="11"></line></svg>
            </div>
            <div class="metric-content">
                <span class="metric-label">New Accounts</span>
                <div class="metric-value"><?php echo number_format($newAccounts); ?></div>
                <span class="metric-subtext">Created this week</span>
            </div>
        </div>
    </div>

    <!-- User Registry Section -->
    <div class="section-header">
        <div class="label-bar"></div>
        <div>
            <h3>User Registry</h3>
            <p>Search and manage existing user accounts</p>
        </div>
    </div>

    <!-- Search Bar Container -->
    <div class="search-container">
        <div class="search-wrapper">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" placeholder="Search by name, email or employee ID..." id="userSearchInput" autocomplete="off">
            <div id="searchSuggestions" class="search-suggestions-panel"></div>
        </div>
        <button class="search-btn" onclick="refreshUserList()">Search</button>
    </div>

    <!-- Table Container -->
    <div class="table-container">
        <table class="registry-table" id="userTable">
            <thead>
                <tr>
                    <th>Emp No.</th>
                    <th>Full Name</th>
                    <th>Institutional Email</th>
                    <th>Role</th>
                    <th>Dept</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
                <?php if (empty($users)): ?>
                    <tr><td colspan="7" style="text-align: center; padding: 40px;">No users found.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['employee_no']); ?></td>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td><?php echo htmlspecialchars($user['dept']); ?></td>
                        <td>
                            <div class="status-pill">
                                <span class="status-dot <?php echo $user['status_class']; ?>"></span>
                                <?php echo $user['status']; ?>
                            </div>
                        </td>
                        <td>
                            <div class="action-btn-group">
                                <button class="table-edit-btn" onclick="window.openEditUserModal('<?php echo htmlspecialchars($user['employee_no']); ?>')">Edit</button>
                                <button class="table-delete-btn" onclick="window.openDeleteUserModal('<?php echo htmlspecialchars($user['employee_no']); ?>', '<?php echo addslashes(htmlspecialchars($user['full_name'])); ?>', '<?php echo addslashes(htmlspecialchars($user['email'])); ?>', '<?php echo addslashes(htmlspecialchars($user['role'])); ?>')">Delete</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="pagination" id="paginationControls"></div>
    </div>
</div>

<script>
    const currentUserEmployeeNo = '<?php echo $_SESSION['employee_no'] ?? ''; ?>';
</script>