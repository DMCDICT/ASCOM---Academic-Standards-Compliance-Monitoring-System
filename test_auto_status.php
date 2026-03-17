<?php
// test_auto_status.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Test Automatic Term Status Assignment</h2>";

// Get current date
$current_date = date('Y-m-d');
echo "<p><strong>Current Date:</strong> " . $current_date . "</p>";

// Get an active school year for testing
$school_years = $conn->query("SELECT id, school_year_label FROM school_years WHERE status = 'Active' ORDER BY start_date DESC LIMIT 1");
if (!$school_years || $school_years->num_rows === 0) {
    echo "❌ No active school year found<br>";
    exit;
}

$school_year = $school_years->fetch_assoc();
echo "✅ Using school year: " . $school_year['school_year_label'] . " (ID: " . $school_year['id'] . ")<br>";

// Test different date scenarios
$test_scenarios = [
    [
        'title' => 'Past Term',
        'start_date' => '2024-01-01',
        'end_date' => '2024-06-30',
        'description' => 'Term that ended in the past'
    ],
    [
        'title' => 'Current Term',
        'start_date' => date('Y-m-d', strtotime('-30 days')),
        'end_date' => date('Y-m-d', strtotime('+30 days')),
        'description' => 'Term that includes current date'
    ],
    [
        'title' => 'Future Term',
        'start_date' => date('Y-m-d', strtotime('+60 days')),
        'end_date' => date('Y-m-d', strtotime('+120 days')),
        'description' => 'Term that starts in the future'
    ]
];

echo "<h3>Testing Different Date Scenarios:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background-color: #f0f0f0;'>";
echo "<th>Scenario</th><th>Title</th><th>Start Date</th><th>End Date</th><th>Description</th><th>Expected Status</th>";
echo "</tr>";

foreach ($test_scenarios as $scenario) {
    $is_active = ($current_date >= $scenario['start_date'] && $current_date <= $scenario['end_date']);
    $expected_status = $is_active ? 'Active' : 'Inactive';
    
    echo "<tr>";
    echo "<td>" . $scenario['title'] . "</td>";
    echo "<td>" . $scenario['title'] . "</td>";
    echo "<td>" . $scenario['start_date'] . "</td>";
    echo "<td>" . $scenario['end_date'] . "</td>";
    echo "<td>" . $scenario['description'] . "</td>";
    echo "<td style='color: " . ($expected_status === 'Active' ? 'green' : 'red') . ";'>" . $expected_status . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>Status Logic Explanation:</h3>";
echo "<div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>Formula:</strong> <code>current_date >= start_date AND current_date <= end_date</code></p>";
echo "<ul>";
echo "<li><strong>Active:</strong> Current date falls within or exactly on the term's date range</li>";
echo "<li><strong>Inactive:</strong> Current date is before the start date OR after the end date</li>";
echo "</ul>";
echo "<p><strong>Examples:</strong></p>";
echo "<ul>";
echo "<li>Current date: " . $current_date . "</li>";
echo "<li>Past term (2024-01-01 to 2024-06-30): <strong>Inactive</strong> (current date is after end date)</li>";
echo "<li>Current term (" . date('Y-m-d', strtotime('-30 days')) . " to " . date('Y-m-d', strtotime('+30 days')) . "): <strong>Active</strong> (current date is within range)</li>";
echo "<li>Future term (" . date('Y-m-d', strtotime('+60 days')) . " to " . date('Y-m-d', strtotime('+120 days')) . "): <strong>Inactive</strong> (current date is before start date)</li>";
echo "</ul>";
echo "</div>";

echo "<h3>Test the API:</h3>";
echo "<p>You can now test the Add Term functionality and see that terms are automatically assigned the correct status based on their date range.</p>";

echo "<h3>Next Steps:</h3>";
echo "<p><a href='test_api_fix.php' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Test Add Term API</a></p>";
echo "<p><a href='update_term_statuses.php' style='background-color: #FF9800; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔄 Update Existing Terms</a></p>";
echo "<p><a href='super_admin-mis/content.php?page=school-calendar&v=8' style='background-color: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Test Add Term Modal</a></p>";

$conn->close();
?>
