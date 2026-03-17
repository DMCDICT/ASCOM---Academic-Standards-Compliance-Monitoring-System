<?php
// debug_filtering_issue.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Debug Filtering Issue</h2>";

// Get current school year
$current_year_sql = "SELECT school_year_label FROM school_years WHERE status = 'Active' ORDER BY start_date DESC LIMIT 1";
$current_result = $conn->query($current_year_sql);
$current_school_year = null;

if ($current_result && $current_result->num_rows > 0) {
    $current_row = $current_result->fetch_assoc();
    $current_school_year = $current_row['school_year_label'];
    echo "<p><strong>Current School Year (Active):</strong> " . $current_school_year . "</p>";
} else {
    echo "<p style='color: red;'>❌ No current school year found</p>";
    exit;
}

// Extract year from current school year
$current_year = intval(explode('-', $current_school_year)[0]);
$min_year = $current_year - 5; // 5 years behind

echo "<p><strong>Current Year (from label):</strong> " . $current_year . "</p>";
echo "<p><strong>Minimum Year (5 years behind):</strong> " . $min_year . "</p>";
echo "<p><strong>Expected range:</strong> " . $min_year . " to " . $current_year . "</p>";

// Get all school years
$sql_sy = "SELECT id, school_year_label, status, start_date, end_date FROM school_years ORDER BY school_year_label DESC";
$result_sy = $conn->query($sql_sy);

echo "<h3>Detailed Filtering Analysis:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background-color: #f0f0f0;'>";
echo "<th>School Year</th><th>Year Start</th><th>Min Year</th><th>Current Year</th><th>Condition 1</th><th>Condition 2</th><th>Include?</th><th>Reason</th>";
echo "</tr>";

$filtered_school_years = [];

while ($row = $result_sy->fetch_assoc()) {
    $year_label = $row['school_year_label'];
    $year_start = intval(explode('-', $year_label)[0]);
    
    // Test the exact conditions
    $condition1 = ($year_label === $current_school_year);
    $condition2 = ($year_start >= $min_year && $year_start <= $current_year);
    $include = $condition1 || $condition2;
    
    $reason = "";
    if ($condition1) {
        $reason = "Current school year";
    } elseif ($condition2) {
        $reason = "Within 5 years behind (not future)";
    } else {
        $reason = "Too old (>5 years behind) or future year";
    }
    
    if ($include) {
        $filtered_school_years[] = $row;
    }
    
    $include_color = $include ? 'green' : 'red';
    
    echo "<tr>";
    echo "<td>" . $year_label . "</td>";
    echo "<td>" . $year_start . "</td>";
    echo "<td>" . $min_year . "</td>";
    echo "<td>" . $current_year . "</td>";
    echo "<td>" . ($condition1 ? 'TRUE' : 'FALSE') . "</td>";
    echo "<td>" . ($condition2 ? 'TRUE' : 'FALSE') . "</td>";
    echo "<td style='color: " . $include_color . "; font-weight: bold;'>" . ($include ? 'YES' : 'NO') . "</td>";
    echo "<td>" . $reason . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>Final Filtered Results:</h3>";
echo "<p><strong>Total school years in database:</strong> " . $result_sy->num_rows . "</p>";
echo "<p><strong>Filtered school years for dropdown:</strong> " . count($filtered_school_years) . "</p>";

echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";
echo "<h4>Expected Dropdown:</h4>";
echo "<select style='width: 100%; padding: 5px;'>";
echo "<option value='' disabled selected>-- Select a School Year --</option>";

// Sort by year in descending order (newest first)
usort($filtered_school_years, function($a, $b) {
    $year_a = intval(explode('-', $a['school_year_label'])[0]);
    $year_b = intval(explode('-', $b['school_year_label'])[0]);
    return $year_b - $year_a;
});

foreach ($filtered_school_years as $sy) {
    // Check if this is the current school year based on date range
    $today = date('Y-m-d');
    $is_current = ($today >= $sy['start_date'] && $today <= $sy['end_date']) ? ' (Current)' : '';
    echo "<option value='" . $sy['id'] . "'>" . $sy['school_year_label'] . $is_current . "</option>";
}
echo "</select>";
echo "</div>";

echo "<h3>Summary:</h3>";
echo "<p>Based on current year <strong>" . $current_year . "</strong> and minimum year <strong>" . $min_year . "</strong>:</p>";
echo "<ul>";
echo "<li>✅ Current school year: " . $current_school_year . "</li>";
echo "<li>✅ Years from " . $min_year . " to " . $current_year . " (5 years behind)</li>";
echo "<li>❌ Years before " . $min_year . " (too old)</li>";
echo "<li>❌ Years after " . $current_year . " (future years)</li>";
echo "</ul>";

$conn->close();
?>
