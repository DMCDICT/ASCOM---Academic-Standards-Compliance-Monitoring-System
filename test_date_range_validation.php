<?php
// test_date_range_validation.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Date Range Validation Test</h2>";

// Test 1: Check existing school years
echo "<h3>1. Current School Years in Database:</h3>";
$school_years_query = "SELECT school_year_label, start_date, end_date FROM school_years ORDER BY start_date";
$school_years_result = $conn->query($school_years_query);

if ($school_years_result && $school_years_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>School Year</th><th>Start Date</th><th>End Date</th><th>Duration</th>";
    echo "</tr>";
    
    while ($row = $school_years_result->fetch_assoc()) {
        $start = new DateTime($row['start_date']);
        $end = new DateTime($row['end_date']);
        $duration = $start->diff($end)->days + 1;
        
        echo "<tr>";
        echo "<td>" . $row['school_year_label'] . "</td>";
        echo "<td>" . $row['start_date'] . "</td>";
        echo "<td>" . $row['end_date'] . "</td>";
        echo "<td>" . $duration . " days</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No school years found in database.</p>";
}

// Test 2: Check existing terms
echo "<h3>2. Current Terms in Database:</h3>";
$terms_query = "SELECT st.title, st.start_date, st.end_date, sy.school_year_label 
                FROM school_terms st 
                JOIN school_years sy ON st.school_year_id = sy.id 
                ORDER BY st.start_date";
$terms_result = $conn->query($terms_query);

if ($terms_result && $terms_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>Term</th><th>School Year</th><th>Start Date</th><th>End Date</th><th>Duration</th>";
    echo "</tr>";
    
    while ($row = $terms_result->fetch_assoc()) {
        $start = new DateTime($row['start_date']);
        $end = new DateTime($row['end_date']);
        $duration = $start->diff($end)->days + 1;
        
        echo "<tr>";
        echo "<td>" . $row['title'] . "</td>";
        echo "<td>" . $row['school_year_label'] . "</td>";
        echo "<td>" . $row['start_date'] . "</td>";
        echo "<td>" . $row['end_date'] . "</td>";
        echo "<td>" . $duration . " days</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No terms found in database.</p>";
}

// Test 3: Test overlapping scenarios
echo "<h3>3. Test Overlapping Scenarios:</h3>";

// Test school year overlap
echo "<h4>School Year Overlap Test:</h4>";
$test_school_year_data = [
    'school_year_label' => 'A.Y. 2025-2026',
    'start_date' => '2025-07-01',
    'end_date' => '2026-06-10',
    'status' => 'Active'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/DataDrift/ASCOM%20Monitoring%20System/super_admin-mis/api/add_school_year.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_school_year_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<div style='background-color: #f9f9f9; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Test Data:</strong> " . $test_school_year_data['school_year_label'] . " (" . $test_school_year_data['start_date'] . " - " . $test_school_year_data['end_date'] . ")<br>";
echo "<strong>HTTP Code:</strong> " . $http_code . "<br>";
echo "<strong>Response:</strong> " . htmlspecialchars($response);
echo "</div>";

// Test term overlap
echo "<h4>Term Overlap Test:</h4>";
$test_term_data = [
    'title' => '1st Semester',
    'school_year_id' => 1, // Assuming school year ID 1 exists
    'start_date' => '2025-07-01',
    'end_date' => '2025-12-15'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/DataDrift/ASCOM%20Monitoring%20System/super_admin-mis/api/add_term.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_term_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<div style='background-color: #f9f9f9; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Test Data:</strong> " . $test_term_data['title'] . " (" . $test_term_data['start_date'] . " - " . $test_term_data['end_date'] . ")<br>";
echo "<strong>HTTP Code:</strong> " . $http_code . "<br>";
echo "<strong>Response:</strong> " . htmlspecialchars($response);
echo "</div>";

echo "<h3>4. Validation Rules:</h3>";
echo "<div style='background-color: #e8f5e8; padding: 15px; border-radius: 5px;'>";
echo "<h4>School Year Validation:</h4>";
echo "<ul>";
echo "<li>Cannot have overlapping date ranges with existing school years</li>";
echo "<li>Start date must be before end date</li>";
echo "<li>Shows specific conflicting school years in error message</li>";
echo "</ul>";

echo "<h4>Term Validation:</h4>";
echo "<ul>";
echo "<li>Cannot have overlapping date ranges with existing terms in the same school year</li>";
echo "<li>Start date must be before end date</li>";
echo "<li>Shows specific conflicting terms in error message</li>";
echo "</ul>";
echo "</div>";

echo "<h3>5. Next Steps:</h3>";
echo "<p><a href='super_admin-mis/content.php?page=school-calendar&v=10' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Test Date Range Validation in Calendar</a></p>";

$conn->close();
?>
