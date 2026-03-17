<?php
// test_add_term_functionality.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Test Add Term Functionality</h2>";

// Test 1: Check if school_terms table exists
echo "<h3>1. Database Table Check:</h3>";
$table_check = $conn->query("SHOW TABLES LIKE 'school_terms'");
if ($table_check->num_rows > 0) {
    echo "✅ school_terms table exists<br>";
    
    // Check table structure
    $structure = $conn->query("DESCRIBE school_terms");
    echo "<h4>Table Structure:</h4>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ school_terms table does not exist<br>";
}

// Test 2: Check if school_years table has data
echo "<h3>2. School Years Data Check:</h3>";
$school_years = $conn->query("SELECT id, school_year_label, status FROM school_years ORDER BY id DESC LIMIT 5");
if ($school_years && $school_years->num_rows > 0) {
    echo "✅ Found " . $school_years->num_rows . " school years<br>";
    echo "<h4>Available School Years:</h4>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>School Year</th><th>Status</th></tr>";
    while ($row = $school_years->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['school_year_label'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ No school years found<br>";
}

// Test 3: Check existing terms
echo "<h3>3. Existing Terms Check:</h3>";
$existing_terms = $conn->query("SELECT st.id, st.title, st.start_date, st.end_date, st.status, sy.school_year_label 
                                FROM school_terms st 
                                JOIN school_years sy ON st.school_year_id = sy.id 
                                ORDER BY st.id DESC LIMIT 5");
if ($existing_terms && $existing_terms->num_rows > 0) {
    echo "✅ Found " . $existing_terms->num_rows . " existing terms<br>";
    echo "<h4>Existing Terms:</h4>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Title</th><th>School Year</th><th>Start Date</th><th>End Date</th><th>Status</th></tr>";
    while ($row = $existing_terms->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['title'] . "</td>";
        echo "<td>" . $row['school_year_label'] . "</td>";
        echo "<td>" . $row['start_date'] . "</td>";
        echo "<td>" . $row['end_date'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "ℹ️ No existing terms found<br>";
}

// Test 4: Test API endpoint
echo "<h3>4. API Endpoint Test:</h3>";
echo "<p>Testing the add_term.php API endpoint...</p>";

// Get a school year ID for testing
$test_school_year = $conn->query("SELECT id FROM school_years WHERE status = 'Active' LIMIT 1");
if ($test_school_year && $test_school_year->num_rows > 0) {
    $school_year_id = $test_school_year->fetch_assoc()['id'];
    
    // Test data
    $test_data = [
        'title' => 'Test Term',
        'school_year_id' => $school_year_id,
        'start_date' => '2025-01-15',
        'end_date' => '2025-05-15'
    ];
    
    echo "<h4>Test Data:</h4>";
    echo "<pre>" . json_encode($test_data, JSON_PRETTY_PRINT) . "</pre>";
    
    // Simulate API call
    echo "<h4>API Response:</h4>";
    $api_url = 'super_admin-mis/api/add_term.php';
    
    // Create a test script to call the API
    $test_script = "<?php
    \$data = " . var_export($test_data, true) . ";
    \$json_data = json_encode(\$data);
    
    \$ch = curl_init();
    curl_setopt(\$ch, CURLOPT_URL, 'http://localhost/DataDrift/ASCOM%20Monitoring%20System/super_admin-mis/api/add_term.php');
    curl_setopt(\$ch, CURLOPT_POST, true);
    curl_setopt(\$ch, CURLOPT_POSTFIELDS, \$json_data);
    curl_setopt(\$ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
    
    \$response = curl_exec(\$ch);
    \$http_code = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
    curl_close(\$ch);
    
    echo 'HTTP Code: ' . \$http_code . '<br>';
    echo 'Response: ' . \$response . '<br>';
    ?>";
    
    file_put_contents('temp_api_test.php', $test_script);
    include 'temp_api_test.php';
    unlink('temp_api_test.php');
    
} else {
    echo "❌ No active school year found for testing<br>";
}

// Test 5: Manual insertion test
echo "<h3>5. Manual Database Insertion Test:</h3>";
$test_school_year = $conn->query("SELECT id FROM school_years WHERE status = 'Active' LIMIT 1");
if ($test_school_year && $test_school_year->num_rows > 0) {
    $school_year_id = $test_school_year->fetch_assoc()['id'];
    
    // Try to insert a test term
    $test_title = 'Manual Test Term';
    $test_start = '2025-02-01';
    $test_end = '2025-06-01';
    
    $insert_sql = "INSERT INTO school_terms (title, school_year_id, start_date, end_date, status) VALUES (?, ?, ?, ?, 'Inactive')";
    $stmt = $conn->prepare($insert_sql);
    
    if ($stmt) {
        $stmt->bind_param('siss', $test_title, $school_year_id, $test_start, $test_end);
        
        if ($stmt->execute()) {
            $new_term_id = $conn->insert_id;
            echo "✅ Successfully inserted test term with ID: " . $new_term_id . "<br>";
            
            // Clean up - delete the test term
            $delete_sql = "DELETE FROM school_terms WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param('i', $new_term_id);
            $delete_stmt->execute();
            echo "🧹 Cleaned up test term<br>";
            
        } else {
            echo "❌ Failed to insert test term: " . $stmt->error . "<br>";
        }
        $stmt->close();
    } else {
        echo "❌ Failed to prepare statement: " . $conn->error . "<br>";
    }
} else {
    echo "❌ No active school year found for testing<br>";
}

echo "<h3>6. Test the Modal:</h3>";
echo "<p><a href='super_admin-mis/content.php?page=school-calendar&v=5' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Test Add Term Modal</a></p>";

echo "<h3>7. Expected Workflow:</h3>";
echo "<ol>";
echo "<li>Click 'Add Term' button</li>";
echo "<li>Select Term Title (1st Semester, 2nd Semester, or Summer Semester)</li>";
echo "<li>Select School Year from the filtered dropdown</li>";
echo "<li>Date fields should become enabled</li>";
echo "<li>Select Start Date and End Date within the school year range</li>";
echo "<li>Click 'Save Term'</li>";
echo "<li>Should see success message and term added to database</li>";
echo "</ol>";

$conn->close();
?>
