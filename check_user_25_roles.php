<?php
require_once 'teachers/includes/db_connection.php';

echo "<h2>User ID 25 Role Check</h2>";

try {
    // Check all roles for User ID 25
    $stmt = $pdo->prepare("
        SELECT ur.role_name, u.first_name, u.last_name
        FROM user_roles ur
        JOIN users u ON ur.user_id = u.id
        WHERE ur.user_id = 25
        ORDER BY ur.role_name
    ");
    $stmt->execute();
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($roles) > 0) {
        echo "<h3>User Information:</h3>";
        echo "<p>Name: " . $roles[0]['first_name'] . " " . $roles[0]['last_name'] . "</p>";
        
        echo "<h3>All Roles for User ID 25:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Role Name</th></tr>";
        foreach ($roles as $role) {
            echo "<tr><td>" . $role['role_name'] . "</td></tr>";
        }
        echo "</table>";
        
        echo "<h3>Role Analysis:</h3>";
        $roleNames = array_column($roles, 'role_name');
        echo "<p>Total roles: " . count($roles) . "</p>";
        echo "<p>Roles: " . implode(', ', $roleNames) . "</p>";
        
        if (in_array('teacher', $roleNames)) {
            echo "<p>✅ Has teacher role</p>";
        } else {
            echo "<p>❌ Missing teacher role</p>";
        }
        
        $otherRoles = array_diff($roleNames, ['teacher']);
        if (count($otherRoles) > 0) {
            echo "<p>⚠️ Has additional roles: " . implode(', ', $otherRoles) . "</p>";
        } else {
            echo "<p>✅ Only has teacher role</p>";
        }
        
    } else {
        echo "<p>❌ No roles found for User ID 25</p>";
    }
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
