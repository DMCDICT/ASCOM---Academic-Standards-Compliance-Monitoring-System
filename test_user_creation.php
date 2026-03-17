<?php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Testing User Creation</h2>";

// Test 1: Check if users table has the required columns
echo "<h3>1. Checking users table structure...</h3>";
$result = $conn->query("DESCRIBE users");
if ($result) {
    $columns = [];
    while($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
    }
    
    $required_columns = ['employee_no', 'institutional_email', 'role_id'];
    $missing_columns = [];
    foreach ($required_columns as $col) {
        if (!in_array($col, $columns)) {
            $missing_columns[] = $col;
        }
    }
    
    if (empty($missing_columns)) {
        echo "✅ All required columns exist<br>";
    } else {
        echo "❌ Missing columns: " . implode(', ', $missing_columns) . "<br>";
    }
} else {
    echo "❌ Error checking table structure: " . $conn->error . "<br>";
}

// Test 2: Check if roles table exists and has data
echo "<h3>2. Checking roles table...</h3>";
$roles_result = $conn->query("SELECT * FROM roles");
if ($roles_result) {
    echo "✅ Roles table exists with " . $roles_result->num_rows . " roles<br>";
    while($row = $roles_result->fetch_assoc()) {
        echo "- Role ID: " . $row['id'] . ", Name: " . $row['role'] . "<br>";
    }
} else {
    echo "❌ Error checking roles table: " . $conn->error . "<br>";
}

// Test 3: Check if departments table exists and has data
echo "<h3>3. Checking departments table...</h3>";
$dept_result = $conn->query("SELECT * FROM departments");
if ($dept_result) {
    echo "✅ Departments table exists with " . $dept_result->num_rows . " departments<br>";
    while($row = $dept_result->fetch_assoc()) {
        echo "- Dept ID: " . $row['id'] . ", Code: " . $row['department_code'] . ", Name: " . $row['department_name'] . "<br>";
    }
} else {
    echo "❌ Error checking departments table: " . $conn->error . "<br>";
}

// Test 4: Try to create a test user
echo "<h3>4. Testing user creation...</h3>";
$test_data = [
    'employee_no' => '123456',
    'first_name' => 'Test',
    'middle_name' => '',
    'last_name' => 'User',
    'title' => 'Mr.',
    'institutional_email' => 'test.user@sccpag.edu.ph',
    'mobile_no' => '09123456789',
    'password' => 'password123',
    'role_id' => '4',
    'department_id' => '1'
];

// Simulate the process_add_user.php logic
$employee_no = $test_data['employee_no'];
$first_name = $test_data['first_name'];
$middle_name = $test_data['middle_name'];
$last_name = $test_data['last_name'];
$name_prefix = $test_data['title'];
$institutional_email = $test_data['institutional_email'];
$mobile_no = $test_data['mobile_no'];
$password = $test_data['password'];
$role_id = $test_data['role_id'];
$department_id = $test_data['department_id'];
$created_by = 'Super Admin MIS';

// Check if user already exists
$check_stmt = $conn->prepare("SELECT id FROM users WHERE employee_no = ? OR institutional_email = ?");
if ($check_stmt) {
    $check_stmt->bind_param("ss", $employee_no, $institutional_email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo "❌ Test user already exists<br>";
    } else {
        echo "✅ No duplicate found, proceeding with creation...<br>";
        
        // Try to insert
        $insert_stmt = $conn->prepare("
            INSERT INTO users 
            (employee_no, first_name, middle_name, last_name, name_prefix, institutional_email, mobile_no, password, role_id, department_id, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if ($insert_stmt) {
            $insert_stmt->bind_param("ssssssssiss",
                $employee_no, $first_name, $middle_name, $last_name, $name_prefix, 
                $institutional_email, $mobile_no, $password, $role_id, $department_id, $created_by
            );
            
            if ($insert_stmt->execute()) {
                echo "✅ Test user created successfully!<br>";
                echo "- User ID: " . $conn->insert_id . "<br>";
                
                // Clean up - delete the test user
                $delete_stmt = $conn->prepare("DELETE FROM users WHERE employee_no = ?");
                if ($delete_stmt) {
                    $delete_stmt->bind_param("s", $employee_no);
                    $delete_stmt->execute();
                    echo "✅ Test user cleaned up<br>";
                }
            } else {
                echo "❌ Failed to create test user: " . $insert_stmt->error . "<br>";
            }
            $insert_stmt->close();
        } else {
            echo "❌ Failed to prepare insert statement: " . $conn->error . "<br>";
        }
    }
    $check_stmt->close();
} else {
    echo "❌ Failed to prepare check statement: " . $conn->error . "<br>";
}

$conn->close();
echo "<h3>Test completed!</h3>";
?> 