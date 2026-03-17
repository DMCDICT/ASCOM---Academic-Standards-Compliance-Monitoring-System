<?php
require_once 'teachers/includes/db_connection.php';

echo "<h2>Fix User ID 25 Roles</h2>";

try {
    // First, show current roles
    echo "<h3>Current Roles for User ID 25:</h3>";
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
        echo "<p>Name: " . $roles[0]['first_name'] . " " . $roles[0]['last_name'] . "</p>";
        echo "<ul>";
        foreach ($roles as $role) {
            echo "<li>" . $role['role_name'] . "</li>";
        }
        echo "</ul>";
        
        // Remove the dean role
        echo "<h3>Removing dean role...</h3>";
        $deleteStmt = $pdo->prepare("
            DELETE FROM user_roles 
            WHERE user_id = 25 AND role_name = 'dean'
        ");
        $deleteStmt->execute();
        $deletedRows = $deleteStmt->rowCount();
        
        if ($deletedRows > 0) {
            echo "<p>✅ Successfully removed dean role from User ID 25</p>";
            
            // Show updated roles
            echo "<h3>Updated Roles for User ID 25:</h3>";
            $stmt->execute();
            $updatedRoles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($updatedRoles) > 0) {
                echo "<ul>";
                foreach ($updatedRoles as $role) {
                    echo "<li>" . $role['role_name'] . "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>No roles found</p>";
            }
            
        } else {
            echo "<p>❌ No dean role found to remove</p>";
        }
        
    } else {
        echo "<p>❌ No roles found for User ID 25</p>";
    }
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
