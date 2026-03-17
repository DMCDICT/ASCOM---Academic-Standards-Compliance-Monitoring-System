<?php
// test_term_indicators.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Test Term Indicators in Calendar</h2>";

// Get current date
$current_date = date('Y-m-d');
echo "<p><strong>Current Date:</strong> " . $current_date . "</p>";

// Check if we have terms in the database
$terms_query = "SELECT st.id, st.title, st.start_date, st.end_date, st.status, sy.school_year_label 
                FROM school_terms st 
                JOIN school_years sy ON st.school_year_id = sy.id 
                ORDER BY st.start_date";
$terms_result = $conn->query($terms_query);

if ($terms_result && $terms_result->num_rows > 0) {
    echo "<h3>✅ Terms Found in Database:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>ID</th><th>Title</th><th>Start Date</th><th>End Date</th><th>Status</th><th>School Year</th><th>Current Status</th>";
    echo "</tr>";
    
    while ($term = $terms_result->fetch_assoc()) {
        $term_is_active = ($current_date >= $term['start_date'] && $current_date <= $term['end_date']);
        $current_status = $term_is_active ? 'Active' : 'Inactive';
        
        echo "<tr>";
        echo "<td>" . $term['id'] . "</td>";
        echo "<td>" . $term['title'] . "</td>";
        echo "<td>" . $term['start_date'] . "</td>";
        echo "<td>" . $term['end_date'] . "</td>";
        echo "<td>" . $term['status'] . "</td>";
        echo "<td>" . $term['school_year_label'] . "</td>";
        echo "<td style='color: " . ($term_is_active ? 'green' : 'red') . ";'>" . $current_status . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<h3>❌ No Terms Found</h3>";
    echo "<p>You need to create some terms first to see the indicators.</p>";
}

// Test the API endpoint
echo "<h3>Testing API Endpoint:</h3>";
echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/DataDrift/ASCOM%20Monitoring%20System/super_admin-mis/api/get_school_year_events.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> " . $http_code . "</p>";

if ($curl_error) {
    echo "<p style='color: red;'><strong>CURL Error:</strong> " . $curl_error . "</p>";
}

echo "<p><strong>API Response:</strong></p>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Try to parse as JSON
$json_response = json_decode($response, true);
if ($json_response) {
    echo "<p style='color: green;'><strong>✅ Valid JSON Response</strong></p>";
    
    if ($json_response['status'] === 'success' && isset($json_response['data'])) {
        $events = $json_response['data'];
        echo "<p><strong>Total Events:</strong> " . count($events) . "</p>";
        
        // Count different types of events
        $school_year_events = array_filter($events, function($event) {
            return strpos($event['type'], 'school_year') !== false;
        });
        
        $term_events = array_filter($events, function($event) {
            return strpos($event['type'], 'term') !== false;
        });
        
        echo "<p><strong>School Year Events:</strong> " . count($school_year_events) . "</p>";
        echo "<p><strong>Term Events:</strong> " . count($term_events) . "</p>";
        
        // Show sample term events
        if (count($term_events) > 0) {
            echo "<h4>Sample Term Events:</h4>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background-color: #f0f0f0;'>";
            echo "<th>ID</th><th>Title</th><th>Date</th><th>Type</th><th>School Year</th><th>Status</th>";
            echo "</tr>";
            
            $sample_terms = array_slice($term_events, 0, 5); // Show first 5
            foreach ($sample_terms as $event) {
                echo "<tr>";
                echo "<td>" . $event['id'] . "</td>";
                echo "<td>" . $event['title'] . "</td>";
                echo "<td>" . $event['date'] . "</td>";
                echo "<td>" . $event['type'] . "</td>";
                echo "<td>" . ($event['school_year_label'] ?? 'N/A') . "</td>";
                echo "<td>" . ($event['status'] ?? 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p style='color: red;'><strong>❌ API Error:</strong> " . ($json_response['message'] ?? 'Unknown error') . "</p>";
    }
} else {
    echo "<p style='color: red;'><strong>❌ Invalid JSON Response</strong></p>";
    echo "<p>JSON Error: " . json_last_error_msg() . "</p>";
}

echo "</div>";

echo "<h3>Visual Indicators Guide:</h3>";
echo "<div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px;'>";
echo "<h4>Calendar Event Colors:</h4>";
echo "<ul>";
echo "<li><span style='background-color: #4CAF50; color: white; padding: 2px 6px; border-radius: 3px;'>School Year Start</span> - Green with dark green border</li>";
echo "<li><span style='background-color: #f44336; color: white; padding: 2px 6px; border-radius: 3px;'>School Year End</span> - Red with dark red border</li>";
echo "<li><span style='background-color: #2196F3; color: white; padding: 2px 6px; border-radius: 3px; font-weight: bold;'>Term Start</span> - Blue with dark blue border (bold)</li>";
echo "<li><span style='background-color: #FF9800; color: white; padding: 2px 6px; border-radius: 3px; font-weight: bold;'>Term End</span> - Orange with dark orange border (bold)</li>";
echo "</ul>";
echo "<p><strong>Note:</strong> Term events are displayed with bold text and enhanced styling to make them more prominent.</p>";
echo "</div>";

echo "<h3>Next Steps:</h3>";
echo "<p><a href='super_admin-mis/content.php?page=school-calendar&v=10' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 View Calendar with Term Indicators</a></p>";

$conn->close();
?>
