<?php
require_once 'teachers/includes/db_connection.php';

echo "<h2>Database Role Check</h2>";

try {
    // Check what roles exist
    $stmt = $pdo->query("SELECT DISTINCT role_name FROM user_roles ORDER BY role_name");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>All role names in database:</h3>";
    echo "<ul>";
    foreach ($roles as $role) {
        echo "<li>" . $role['role_name'] . "</li>";
    }
    echo "</ul>";
    
    // Check total count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM user_roles");
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Total role entries: " . $total['total'] . "</p>";
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
