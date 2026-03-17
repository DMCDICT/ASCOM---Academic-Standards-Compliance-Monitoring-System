<?php
// debug_department_creation.php
// Comprehensive test to debug department creation issues

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Department Creation Debug Test</h1>";
echo "<style>
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; font-weight: bold; }
    table { border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

// Step 1: Test database connection
echo "<h2>Step 1: Database Connection Test</h2>";
try {
    require_once __DIR__ . '/includes/db_connection.php';
    echo "<p class='success'>✅ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Step 2: Check if departments table exists
echo "<h2>Step 2: Departments Table Check</h2>";
$table_check = $conn->query("SHOW TABLES LIKE 'departments'");
if ($table_check->num_rows === 0) {
    echo "<p class='warning'>⚠️ Departments table does not exist - creating it...</p>";
    
    $create_table = "CREATE TABLE departments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        department_code VARCHAR(10) UNIQUE NOT NULL,
        department_name VARCHAR(100) NOT NULL,
        color_code VARCHAR(7) DEFAULT '#4A7DFF',
        created_by VARCHAR(100) DEFAULT 'Super Admin MIS',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($create_table)) {
        echo "<p class='success'>✅ Departments table created successfully</p>";
    } else {
        echo "<p class='error'>❌ Failed to create departments table: " . $conn->error . "</p>";
        exit;
    }
} else {
    echo "<p class='success'>✅ Departments table exists</p>";
}

// Step 3: Check table structure
echo "<h2>Step 3: Table Structure Check</h2>";
$result = $conn->query("DESCRIBE departments");
if ($result) {
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
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
    echo "<p class='error'>❌ Could not describe table: " . $conn->error . "</p>";
}

// Step 4: Check current departments
echo "<h2>Step 4: Current Departments</h2>";
$result = $conn->query("SELECT * FROM departments ORDER BY id DESC LIMIT 5");
if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Code</th><th>Name</th><th>Color</th><th>Created By</th><th>Created At</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['department_code'] . "</td>";
        echo "<td>" . $row['department_name'] . "</td>";
        echo "<td>" . $row['color_code'] . "</td>";
        echo "<td>" . $row['created_by'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='info'>ℹ️ No departments found in database</p>";
}

// Step 5: Test department insertion
echo "<h2>Step 5: Test Department Insertion</h2>";
$test_code = "TEST" . rand(100, 999);
$test_name = "Test Department " . rand(1, 100);
$test_color = "#FF0000";

echo "<p class='info'>Testing with:</p>";
echo "<ul>";
echo "<li>Code: " . $test_code . "</li>";
echo "<li>Name: " . $test_name . "</li>";
echo "<li>Color: " . $test_color . "</li>";
echo "</ul>";

$stmt = $conn->prepare("INSERT INTO departments (department_code, department_name, color_code, created_by) VALUES (?, ?, ?, ?)");
if ($stmt) {
    $created_by = 'Debug Test Script';
    $stmt->bind_param("ssss", $test_code, $test_name, $test_color, $created_by);
    
    if ($stmt->execute()) {
        $new_id = $conn->insert_id;
        echo "<p class='success'>✅ Test department created successfully! ID: " . $new_id . "</p>";
        
        // Verify the insertion
        $verify_stmt = $conn->prepare("SELECT * FROM departments WHERE id = ?");
        if ($verify_stmt) {
            $verify_stmt->bind_param("i", $new_id);
            $verify_stmt->execute();
            $verify_result = $verify_stmt->get_result();
            if ($verify_result->num_rows > 0) {
                $verify_row = $verify_result->fetch_assoc();
                echo "<p class='success'>✅ Verification successful - department found in database:</p>";
                echo "<ul>";
                echo "<li>ID: " . $verify_row['id'] . "</li>";
                echo "<li>Code: " . $verify_row['department_code'] . "</li>";
                echo "<li>Name: " . $verify_row['department_name'] . "</li>";
                echo "<li>Color: " . $verify_row['color_code'] . "</li>";
                echo "<li>Created By: " . $verify_row['created_by'] . "</li>";
                echo "</ul>";
            } else {
                echo "<p class='error'>❌ Verification failed - department not found after insertion</p>";
            }
            $verify_stmt->close();
        }
        
        // Clean up - delete the test department
        $delete_stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
        if ($delete_stmt) {
            $delete_stmt->bind_param("i", $new_id);
            if ($delete_stmt->execute()) {
                echo "<p class='success'>✅ Test department cleaned up</p>";
            } else {
                echo "<p class='error'>❌ Failed to clean up test department: " . $delete_stmt->error . "</p>";
            }
            $delete_stmt->close();
        }
    } else {
        echo "<p class='error'>❌ Failed to create test department: " . $stmt->error . "</p>";
    }
    $stmt->close();
} else {
    echo "<p class='error'>❌ Could not prepare insert statement: " . $conn->error . "</p>";
}

// Step 6: Test duplicate prevention
echo "<h2>Step 6: Duplicate Prevention Test</h2>";
$duplicate_code = "DUPLICATE";
$duplicate_name = "Duplicate Test Department";

// Insert first department
$stmt1 = $conn->prepare("INSERT INTO departments (department_code, department_name, color_code, created_by) VALUES (?, ?, ?, ?)");
if ($stmt1) {
    $created_by = 'Debug Test Script';
    $stmt1->bind_param("ssss", $duplicate_code, $duplicate_name, $test_color, $created_by);
    
    if ($stmt1->execute()) {
        $first_id = $conn->insert_id;
        echo "<p class='success'>✅ First department created with ID: " . $first_id . "</p>";
        
        // Try to insert duplicate
        $stmt2 = $conn->prepare("INSERT INTO departments (department_code, department_name, color_code, created_by) VALUES (?, ?, ?, ?)");
        if ($stmt2) {
            $stmt2->bind_param("ssss", $duplicate_code, $duplicate_name, $test_color, $created_by);
            
            if ($stmt2->execute()) {
                echo "<p class='error'>❌ Duplicate prevention failed - duplicate was inserted</p>";
            } else {
                echo "<p class='success'>✅ Duplicate prevention working - error: " . $stmt2->error . "</p>";
            }
            $stmt2->close();
        }
        
        // Clean up
        $delete_stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
        if ($delete_stmt) {
            $delete_stmt->bind_param("i", $first_id);
            $delete_stmt->execute();
            $delete_stmt->close();
        }
    } else {
        echo "<p class='error'>❌ Failed to create first department: " . $stmt1->error . "</p>";
    }
    $stmt1->close();
}

// Step 7: Test process_add_department.php simulation
echo "<h2>Step 7: Simulate process_add_department.php</h2>";

// Simulate POST data
$_POST['department_code'] = "SIM" . rand(100, 999);
$_POST['department_name'] = "Simulated Department " . rand(1, 100);
$_POST['color_code'] = "#00FF00";

echo "<p class='info'>Simulating POST data:</p>";
echo "<ul>";
echo "<li>department_code: " . $_POST['department_code'] . "</li>";
echo "<li>department_name: " . $_POST['department_name'] . "</li>";
echo "<li>color_code: " . $_POST['color_code'] . "</li>";
echo "</ul>";

// Simulate the validation logic
$department_code = trim($_POST['department_code'] ?? '');
$department_name = trim($_POST['department_name'] ?? '');
$color_code = trim($_POST['color_code'] ?? '#4A7DFF');

echo "<p class='info'>Validation results:</p>";

if (empty($department_code) || empty($department_name)) {
    echo "<p class='error'>❌ Validation failed: Department code and name are required</p>";
} elseif (strlen($department_code) < 2 || strlen($department_code) > 10) {
    echo "<p class='error'>❌ Validation failed: Department code must be between 2 and 10 characters</p>";
} elseif (strlen($department_name) < 3 || strlen($department_name) > 100) {
    echo "<p class='error'>❌ Validation failed: Department name must be between 3 and 100 characters</p>";
} else {
    echo "<p class='success'>✅ Validation passed</p>";
    
    // Check for duplicate
    $stmt_check = $conn->prepare("SELECT id FROM departments WHERE department_code = ?");
    if ($stmt_check) {
        $stmt_check->bind_param("s", $department_code);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            echo "<p class='error'>❌ Duplicate check failed: Department code already exists</p>";
        } else {
            echo "<p class='success'>✅ Duplicate check passed</p>";
            
            // Insert the department
            $stmt_insert = $conn->prepare("INSERT INTO departments (department_code, department_name, color_code, created_by) VALUES (?, ?, ?, ?)");
            if ($stmt_insert) {
                $created_by = 'Debug Test Script';
                $stmt_insert->bind_param("ssss", $department_code, $department_name, $color_code, $created_by);
                
                if ($stmt_insert->execute()) {
                    $new_department_id = $conn->insert_id;
                    echo "<p class='success'>✅ Simulated department created successfully! ID: " . $new_department_id . "</p>";
                    
                    // Clean up
                    $delete_stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
                    if ($delete_stmt) {
                        $delete_stmt->bind_param("i", $new_department_id);
                        $delete_stmt->execute();
                        $delete_stmt->close();
                        echo "<p class='success'>✅ Simulated department cleaned up</p>";
                    }
                } else {
                    echo "<p class='error'>❌ Failed to create simulated department: " . $stmt_insert->error . "</p>";
                }
                $stmt_insert->close();
            } else {
                echo "<p class='error'>❌ Could not prepare insert statement: " . $conn->error . "</p>";
            }
        }
        $stmt_check->close();
    } else {
        echo "<p class='error'>❌ Could not prepare duplicate check statement: " . $conn->error . "</p>";
    }
}

$conn->close();
echo "<h2>🎯 Debug Test Completed!</h2>";
echo "<p class='info'>All tests completed. Check the results above to identify any issues.</p>";
?> 