<?php
// Account Checker for ASCOM Monitoring System
// Use this to see what accounts exist in your database

require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Current User Accounts</h2>";

try {
    $stmt = $conn->prepare("SELECT id, email, role, first_name, last_name, is_active, created_at FROM users ORDER BY role, email");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>ID</th><th>Email</th><th>Role</th><th>Name</th><th>Status</th><th>Created</th>";
        echo "</tr>";
        
        while ($row = $result->fetch_assoc()) {
            $status = $row['is_active'] ? "Active" : "Disabled";
            $statusColor = $row['is_active'] ? "green" : "red";
            
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['email']}</td>";
            echo "<td>{$row['role']}</td>";
            echo "<td>{$row['first_name']} {$row['last_name']}</td>";
            echo "<td style='color: $statusColor;'>{$status}</td>";
            echo "<td>{$row['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No users found in the database.</p>";
        echo "<p>Run the database_setup.sql script first to create sample accounts.</p>";
    }
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

$stmt->close();
$conn->close();
?>

<hr>

<h3>Quick Reference - Test Passwords:</h3>
<p>All sample accounts use: <strong>password123</strong></p>

<h3>Sample Accounts:</h3>
<ul>
    <li><strong>Department Dean:</strong> dean.cse@sccpag.edu.ph</li>
    <li><strong>Teacher 1:</strong> teacher1@sccpag.edu.ph</li>
    <li><strong>Teacher 2:</strong> teacher2@sccpag.edu.ph</li>
    <li><strong>Librarian:</strong> librarian@sccpag.edu.ph</li>
    <li><strong>Admin QA:</strong> qa.admin@sccpag.edu.ph</li>
</ul>

<h3>To Change Passwords:</h3>
<ol>
    <li>Use the <a href="generate_password.php">Password Generator</a></li>
    <li>Or run SQL commands directly in your database</li>
</ol> 