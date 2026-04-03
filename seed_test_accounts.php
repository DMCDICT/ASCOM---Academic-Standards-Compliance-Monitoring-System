<?php
/**
 * Test Account Seeder for ASCOM Monitoring System
 * This script resets all user passwords to 'password123' for testing purposes.
 */

require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>ASCOM Test Account Seeder</h2>";

$new_password = 'password123';
$password_hash = password_hash($new_password, PASSWORD_BCRYPT);

try {
    // 1. Reset passwords in 'users' table
    echo "<h3>Updating 'users' table...</h3>";
    $stmt = $conn->prepare("UPDATE users SET password = ?");
    $stmt->bind_param("s", $password_hash);
    
    if ($stmt->execute()) {
        $affected = $stmt->affected_rows;
        echo "✅ Successfully updated {$affected} users in the 'users' table.<br>";
    } else {
        echo "❌ Failed to update users: " . $stmt->error . "<br>";
    }
    $stmt->close();

    // 2. Reset passwords in 'super_admin' table
    echo "<h3>Updating 'super_admin' table...</h3>";
    $stmt = $conn->prepare("UPDATE super_admin SET password = ?");
    $stmt->bind_param("s", $password_hash);
    
    if ($stmt->execute()) {
        $affected = $stmt->affected_rows;
        echo "✅ Successfully updated {$affected} admin(s) in the 'super_admin' table.<br>";
    } else {
        echo "❌ Failed to update super_admin: " . $stmt->error . "<br>";
    }
    $stmt->close();

    // 3. Display current accounts for reference
    echo "<h3>Available Test Accounts:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%; text-align: left;'>";
    echo "<tr style='background-color: #f0f0f0;'><th>Email/Username</th><th>Name</th><th>Role</th><th>Password</th></tr>";

    // Super Admins
    $res = $conn->query("SELECT email FROM super_admin");
    while ($row = $res->fetch_assoc()) {
        echo "<tr><td>{$row['email']}</td><td>Super Admin MIS</td><td>Super Admin</td><td><code>{$new_password}</code></td></tr>";
    }

    // Standard Users
    $res = $conn->query("SELECT institutional_email, first_name, last_name, role_id FROM users ORDER BY role_id");
    while ($row = $res->fetch_assoc()) {
        switch ((int)$row['role_id']) {
            case 1: $role_label = 'Admin - QA'; break;
            case 2: $role_label = 'Department Dean'; break;
            case 3: $role_label = 'Librarian'; break;
            case 4: $role_label = 'Teacher'; break;
            default: $role_label = 'Unknown';
        }
        
        echo "<tr>";
        echo "<td>{$row['institutional_email']}</td>";
        echo "<td>{$row['first_name']} {$row['last_name']}</td>";
        echo "<td>{$role_label}</td>";
        echo "<td><code>{$new_password}</code></td>";
        echo "</tr>";
    }
    echo "</table>";

} catch (Exception $e) {
    echo "❌ <strong>Error:</strong> " . $e->getMessage() . "<br>";
}

$conn->close();
?>
