<?php
// test_complete_add_term_workflow.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Complete Add Term Workflow Test</h2>";

// Step 1: Check prerequisites
echo "<h3>Step 1: Prerequisites Check</h3>";

// Check if school_years table has data
$school_years = $conn->query("SELECT id, school_year_label, status, start_date, end_date FROM school_years WHERE status = 'Active' ORDER BY start_date DESC LIMIT 1");
if ($school_years && $school_years->num_rows > 0) {
    $active_school_year = $school_years->fetch_assoc();
    echo "✅ Active school year found: " . $active_school_year['school_year_label'] . "<br>";
    echo "   - ID: " . $active_school_year['id'] . "<br>";
    echo "   - Date range: " . $active_school_year['start_date'] . " to " . $active_school_year['end_date'] . "<br>";
} else {
    echo "❌ No active school year found<br>";
    exit;
}

// Check if school_terms table exists
$terms_table = $conn->query("SHOW TABLES LIKE 'school_terms'");
if ($terms_table->num_rows > 0) {
    echo "✅ school_terms table exists<br>";
} else {
    echo "❌ school_terms table does not exist<br>";
    exit;
}

// Step 2: Test data preparation
echo "<h3>Step 2: Test Data Preparation</h3>";
$test_data = [
    'title' => '1st Semester',
    'school_year_id' => $active_school_year['id'],
    'start_date' => '2025-08-15',
    'end_date' => '2025-12-15'
];

echo "Test data prepared:<br>";
echo "<pre>" . json_encode($test_data, JSON_PRETTY_PRINT) . "</pre>";

// Step 3: Validate test data
echo "<h3>Step 3: Data Validation</h3>";

// Check if dates are within school year range
$start_date = new DateTime($test_data['start_date']);
$end_date = new DateTime($test_data['end_date']);
$school_start = new DateTime($active_school_year['start_date']);
$school_end = new DateTime($active_school_year['end_date']);

if ($start_date >= $school_start && $end_date <= $school_end) {
    echo "✅ Test dates are within school year range<br>";
} else {
    echo "❌ Test dates are outside school year range<br>";
    echo "   School year: " . $active_school_year['start_date'] . " to " . $active_school_year['end_date'] . "<br>";
    echo "   Test dates: " . $test_data['start_date'] . " to " . $test_data['end_date'] . "<br>";
}

if ($start_date < $end_date) {
    echo "✅ Start date is before end date<br>";
} else {
    echo "❌ Start date is not before end date<br>";
}

// Step 4: Check for existing duplicate
echo "<h3>Step 4: Duplicate Check</h3>";
$duplicate_check = $conn->prepare("SELECT id FROM school_terms WHERE title = ? AND school_year_id = ?");
$duplicate_check->bind_param('si', $test_data['title'], $test_data['school_year_id']);
$duplicate_check->execute();
$duplicate_result = $duplicate_check->get_result();

if ($duplicate_result->num_rows > 0) {
    echo "⚠️ Duplicate term already exists for this school year<br>";
    echo "   - Title: " . $test_data['title'] . "<br>";
    echo "   - School Year: " . $active_school_year['school_year_label'] . "<br>";
} else {
    echo "✅ No duplicate found - safe to proceed<br>";
}
$duplicate_check->close();

// Step 5: Simulate API call
echo "<h3>Step 5: API Simulation</h3>";

// Create a temporary test script
$api_test_script = "<?php
require_once 'super_admin-mis/includes/db_connection.php';

// Simulate the add_term.php logic
\$test_data = " . var_export($test_data, true) . ";

// Validation
if (empty(\$test_data['title']) || empty(\$test_data['school_year_id']) || empty(\$test_data['start_date']) || empty(\$test_data['end_date'])) {
    echo '❌ Validation failed: Missing required fields<br>';
    exit;
}

// Date validation
if (strtotime(\$test_data['start_date']) > strtotime(\$test_data['end_date'])) {
    echo '❌ Validation failed: Start date after end date<br>';
    exit;
}

// Insert into database
\$sql = 'INSERT INTO school_terms (title, school_year_id, start_date, end_date, status) VALUES (?, ?, ?, ?, \"Inactive\")';
\$stmt = \$conn->prepare(\$sql);

if (\$stmt) {
    \$stmt->bind_param('siss', \$test_data['title'], \$test_data['school_year_id'], \$test_data['start_date'], \$test_data['end_date']);
    
    if (\$stmt->execute()) {
        \$new_term_id = \$conn->insert_id;
        echo '✅ Term added successfully!<br>';
        echo '   - New Term ID: ' . \$new_term_id . '<br>';
        echo '   - Title: ' . \$test_data['title'] . '<br>';
        echo '   - School Year ID: ' . \$test_data['school_year_id'] . '<br>';
        echo '   - Start Date: ' . \$test_data['start_date'] . '<br>';
        echo '   - End Date: ' . \$test_data['end_date'] . '<br>';
        
        // Clean up - delete the test term
        \$delete_sql = 'DELETE FROM school_terms WHERE id = ?';
        \$delete_stmt = \$conn->prepare(\$delete_sql);
        \$delete_stmt->bind_param('i', \$new_term_id);
        \$delete_stmt->execute();
        echo '🧹 Test term cleaned up<br>';
        
    } else {
        echo '❌ Database error: ' . \$stmt->error . '<br>';
    }
    \$stmt->close();
} else {
    echo '❌ Prepare statement failed: ' . \$conn->error . '<br>';
}
?>";

file_put_contents('temp_api_simulation.php', $api_test_script);
include 'temp_api_simulation.php';
unlink('temp_api_simulation.php');

// Step 6: Test the actual API endpoint
echo "<h3>Step 6: Real API Endpoint Test</h3>";

// Create a test script to call the actual API
$real_api_test = "<?php
\$test_data = " . var_export($test_data, true) . ";
\$json_data = json_encode(\$test_data);

echo 'Sending to API:<br>';
echo '<pre>' . \$json_data . '</pre><br>';

\$ch = curl_init();
curl_setopt(\$ch, CURLOPT_URL, 'http://localhost/DataDrift/ASCOM%20Monitoring%20System/super_admin-mis/api/add_term.php');
curl_setopt(\$ch, CURLOPT_POST, true);
curl_setopt(\$ch, CURLOPT_POSTFIELDS, \$json_data);
curl_setopt(\$ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt(\$ch, CURLOPT_TIMEOUT, 30);

\$response = curl_exec(\$ch);
\$http_code = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
\$curl_error = curl_error(\$ch);
curl_close(\$ch);

echo 'HTTP Response Code: ' . \$http_code . '<br>';
if (\$curl_error) {
    echo 'CURL Error: ' . \$curl_error . '<br>';
}
echo 'API Response: ' . \$response . '<br>';

if (\$http_code == 200) {
    \$response_data = json_decode(\$response, true);
    if (\$response_data && isset(\$response_data['status'])) {
        if (\$response_data['status'] === 'success') {
            echo '✅ API call successful!<br>';
            echo 'Message: ' . \$response_data['message'] . '<br>';
        } else {
            echo '❌ API returned error<br>';
            echo 'Message: ' . \$response_data['message'] . '<br>';
        }
    } else {
        echo '❌ Invalid JSON response<br>';
    }
} else {
    echo '❌ HTTP error: ' . \$http_code . '<br>';
}
?>";

file_put_contents('temp_real_api_test.php', $real_api_test);
include 'temp_real_api_test.php';
unlink('temp_real_api_test.php');

// Step 7: Verify current terms in database
echo "<h3>Step 7: Current Terms in Database</h3>";
$current_terms = $conn->query("SELECT st.id, st.title, st.start_date, st.end_date, st.status, sy.school_year_label 
                               FROM school_terms st 
                               JOIN school_years sy ON st.school_year_id = sy.id 
                               ORDER BY st.id DESC LIMIT 10");

if ($current_terms && $current_terms->num_rows > 0) {
    echo "Current terms in database:<br>";
    echo "<table border='1' style='border-collapse: collapse; margin-top: 10px;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>ID</th><th>Title</th><th>School Year</th><th>Start Date</th><th>End Date</th><th>Status</th>";
    echo "</tr>";
    while ($row = $current_terms->fetch_assoc()) {
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
    echo "ℹ️ No terms currently in database<br>";
}

echo "<h3>Step 8: Test the Modal Interface</h3>";
echo "<p><a href='super_admin-mis/content.php?page=school-calendar&v=6' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Test Add Term Modal Interface</a></p>";

echo "<h3>Step 9: Expected User Workflow</h3>";
echo "<ol>";
echo "<li>Click 'Add Term' button</li>";
echo "<li>Select '1st Semester' from Term Title dropdown</li>";
echo "<li>Select '" . $active_school_year['school_year_label'] . "' from School Year dropdown</li>";
echo "<li>Date fields should become enabled</li>";
echo "<li>Select Start Date: " . $test_data['start_date'] . "</li>";
echo "<li>Select End Date: " . $test_data['end_date'] . "</li>";
echo "<li>Click 'Save Term'</li>";
echo "<li>Should see success message</li>";
echo "<li>Term should appear in database</li>";
echo "</ol>";

$conn->close();
?>
