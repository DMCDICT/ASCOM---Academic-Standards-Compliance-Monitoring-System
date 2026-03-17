<?php
require_once 'includes/db_connection.php';

echo "<h2>Setting up User Roles System</h2>";

if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
}

echo "<p style='color: green;'>✅ Database connected successfully</p>";

// Create user_roles table
echo "<h3>1. Creating user_roles table:</h3>";
$createTableQuery = "
CREATE TABLE IF NOT EXISTS user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role_name VARCHAR(50) NOT NULL,
    assigned_by VARCHAR(100),
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_role (user_id, role_name)
)
";

if ($conn->query($createTableQuery)) {
    echo "<p style='color: green;'>✅ user_roles table created successfully</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating user_roles table: " . $conn->error . "</p>";
}

// Check if table exists now
$checkTableQuery = "SHOW TABLES LIKE 'user_roles'";
$result = $conn->query($checkTableQuery);

if ($result && $result->num_rows > 0) {
    echo "<p style='color: green;'>✅ user_roles table exists and is ready</p>";
    
    // Show table structure
    echo "<h3>2. Table Structure:</h3>";
    $structureQuery = "DESCRIBE user_roles";
    $structureResult = $conn->query($structureQuery);
    
    if ($structureResult) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $structureResult->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Initialize with current roles
    echo "<h3>3. Initializing with current roles:</h3>";
    
    // Get all active users
    $usersQuery = "SELECT id, first_name, last_name, role_id FROM users WHERE is_active = 1";
    $usersResult = $conn->query($usersQuery);
    
    if ($usersResult) {
        $inserted = 0;
        $skipped = 0;
        
        while ($user = $usersResult->fetch_assoc()) {
            // Determine role name based on role_id
            $roleName = '';
            switch ($user['role_id']) {
                case 1: $roleName = 'super_admin'; break;
                case 2: $roleName = 'teacher'; break;
                case 3: $roleName = 'department_dean'; break;
                case 4: $roleName = 'teacher'; break; // Based on your database
                case 5: $roleName = 'quality_assurance'; break;
                default: $roleName = 'teacher'; // Default to teacher
            }
            
            if ($roleName) {
                // Check if this role already exists for this user
                $checkQuery = "SELECT id FROM user_roles WHERE user_id = ? AND role_name = ?";
                $checkStmt = $conn->prepare($checkQuery);
                $checkStmt->bind_param("is", $user['id'], $roleName);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();
                
                if ($checkResult->num_rows == 0) {
                    // Insert the role
                    $insertQuery = "INSERT INTO user_roles (user_id, role_name, assigned_by) VALUES (?, ?, 'system')";
                    $insertStmt = $conn->prepare($insertQuery);
                    $insertStmt->bind_param("is", $user['id'], $roleName);
                    
                    if ($insertStmt->execute()) {
                        $inserted++;
                        echo "<p style='color: green;'>✅ Added role '$roleName' for " . $user['first_name'] . " " . $user['last_name'] . "</p>";
                    } else {
                        echo "<p style='color: red;'>❌ Failed to add role for " . $user['first_name'] . " " . $user['last_name'] . ": " . $insertStmt->error . "</p>";
                    }
                } else {
                    $skipped++;
                    echo "<p style='color: orange;'>⚠️ Role '$roleName' already exists for " . $user['first_name'] . " " . $user['last_name'] . "</p>";
                }
            }
        }
        
        echo "<p><strong>Summary:</strong> $inserted roles added, $skipped roles skipped (already existed)</p>";
    }
    
    // Show final user_roles
    echo "<h3>4. Current User Roles:</h3>";
    $finalQuery = "
        SELECT 
            ur.user_id,
            u.first_name,
            u.last_name,
            ur.role_name,
            ur.assigned_at
        FROM user_roles ur
        JOIN users u ON ur.user_id = u.id
        WHERE ur.is_active = 1
        ORDER BY u.first_name, ur.role_name
    ";
    $finalResult = $conn->query($finalQuery);
    
    if ($finalResult) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>User ID</th><th>Name</th><th>Role</th><th>Assigned At</th></tr>";
        while ($row = $finalResult->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['user_id'] . "</td>";
            echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
            echo "<td>" . $row['role_name'] . "</td>";
            echo "<td>" . $row['assigned_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} else {
    echo "<p style='color: red;'>❌ user_roles table was not created successfully</p>";
}

$conn->close();
?>
