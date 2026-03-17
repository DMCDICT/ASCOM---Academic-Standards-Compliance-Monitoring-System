<?php
require_once 'includes/db_connection.php';

echo "<h2>Adding Title Column to Users Table</h2>";

try {
    // Check if title column exists
    $checkQuery = "SHOW COLUMNS FROM users LIKE 'title'";
    $result = $conn->query($checkQuery);
    
    if ($result->num_rows === 0) {
        echo "<p style='color: red;'>❌ Title column does not exist. Adding it...</p>";
        
        $addQuery = "ALTER TABLE users ADD COLUMN title VARCHAR(50) DEFAULT NULL AFTER last_name";
        if ($conn->query($addQuery)) {
            echo "<p style='color: green;'>✅ Title column added successfully!</p>";
        } else {
            echo "<p style='color: red;'>❌ Error adding title column: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: green;'>✅ Title column already exists</p>";
    }
    
    // Show the table structure
    echo "<h3>Current Users Table Structure:</h3>";
    $structureQuery = "DESCRIBE users";
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
    } else {
        echo "<p style='color: red;'>❌ Error describing users table: " . $conn->error . "</p>";
    }
    
    // Test the API endpoint
    echo "<h3>Testing Dean Assignment API:</h3>";
    
    // Get a department and teacher for testing
    $deptQuery = "SELECT id, department_code, department_name FROM departments LIMIT 1";
    $deptResult = $conn->query($deptQuery);
    
    if ($deptResult->num_rows > 0) {
        $dept = $deptResult->fetch_assoc();
        echo "<p>✅ Found department: " . $dept['department_name'] . " (" . $dept['department_code'] . ")</p>";
        
        // Get a teacher from this department
        $teacherQuery = "
            SELECT u.id, u.first_name, u.last_name, u.employee_no 
            FROM users u 
            WHERE u.department_id = ? AND u.role_id = 4 AND u.is_active = 1 
            LIMIT 1
        ";
        $teacherStmt = $conn->prepare($teacherQuery);
        $teacherStmt->bind_param("i", $dept['id']);
        $teacherStmt->execute();
        $teacherResult = $teacherStmt->get_result();
        
        if ($teacherResult->num_rows > 0) {
            $teacher = $teacherResult->fetch_assoc();
            echo "<p>✅ Found teacher: " . $teacher['first_name'] . " " . $teacher['last_name'] . " (ID: " . $teacher['id'] . ")</p>";
            
            // Test the API directly
            echo "<h4>Testing API call...</h4>";
            
            $testData = [
                'department_code' => $dept['department_code'],
                'teacher_id' => $teacher['id']
            ];
            
            echo "<p>Test data: " . json_encode($testData) . "</p>";
            
            // Simulate the API call
            $apiUrl = "http://localhost/DataDrift/ASCOM%20Monitoring%20System/super_admin-mis/api/assign_department_dean.php";
            
            // Create a test POST request
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            echo "<p>HTTP Code: " . $httpCode . "</p>";
            if ($curlError) {
                echo "<p style='color: red;'>cURL Error: " . htmlspecialchars($curlError) . "</p>";
            }
            echo "<p>Response: " . htmlspecialchars($response) . "</p>";
            
            if ($httpCode === 200) {
                $responseData = json_decode($response, true);
                if ($responseData && isset($responseData['success'])) {
                    if ($responseData['success']) {
                        echo "<p style='color: green;'>✅ API test successful!</p>";
                    } else {
                        echo "<p style='color: red;'>❌ API test failed: " . $responseData['message'] . "</p>";
                    }
                } else {
                    echo "<p style='color: red;'>❌ Invalid JSON response</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ HTTP error: " . $httpCode . "</p>";
            }
            
        } else {
            echo "<p style='color: red;'>❌ No teachers found in department " . $dept['department_name'] . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ No departments found</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?>
