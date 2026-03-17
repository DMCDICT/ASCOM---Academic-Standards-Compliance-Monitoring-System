<?php
// test_school_year_filtering.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Test School Year Filtering Logic</h2>";

// Get current school year
$current_year_sql = "SELECT school_year_label FROM school_years WHERE status = 'Active' ORDER BY start_date DESC LIMIT 1";
$current_result = $conn->query($current_year_sql);
$current_school_year = null;

if ($current_result && $current_result->num_rows > 0) {
    $current_row = $current_result->fetch_assoc();
    $current_school_year = $current_row['school_year_label'];
    echo "<p><strong>Current School Year:</strong> " . $current_school_year . "</p>";
} else {
    echo "<p style='color: red;'>❌ No current school year found</p>";
    echo "<p>Will show all school years without filtering.</p>";
    $current_school_year = null;
    $current_year = null;
    $min_year = null;
}

    // Extract year from current school year (e.g., "A.Y. 2025 - 2026" -> 2025)
    if ($current_school_year && !empty($current_school_year)) {
        // Handle the "A.Y. YYYY - YYYY" format
        if (preg_match('/(\d{4})/', $current_school_year, $matches)) {
            $current_year = intval($matches[1]);
        } else {
            $current_year = intval(explode('-', $current_school_year)[0]);
        }
        $min_year = $current_year - 5; // 5 years behind
    
    echo "<p><strong>Current Year:</strong> " . $current_year . "</p>";
    echo "<p><strong>Minimum Year (5 years behind):</strong> " . $min_year . "</p>";
} else {
    echo "<p><strong>No filtering will be applied - showing all school years</strong></p>";
}

// Get all school years
$sql_sy = "SELECT id, school_year_label, status, start_date, end_date FROM school_years ORDER BY school_year_label DESC";
$result_sy = $conn->query($sql_sy);

echo "<h3>All School Years in Database:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background-color: #f0f0f0;'><th>ID</th><th>School Year</th><th>Status</th><th>Start Year</th><th>Include?</th><th>Reason</th></tr>";

$all_school_years = [];
$filtered_school_years = [];

while ($row = $result_sy->fetch_assoc()) {
    $all_school_years[] = $row;
    $year_label = $row['school_year_label'];
    // Extract year from school year label (e.g., "A.Y. 2025 - 2026" -> 2025)
    // Handle the "A.Y. YYYY - YYYY" format
    if (preg_match('/(\d{4})/', $year_label, $matches)) {
        $year_start = intval($matches[1]);
    } else {
        $year_start = intval(explode('-', $year_label)[0]);
    }
    
    // Determine if this year should be included
    $include = false;
    $reason = "";
    
    if ($current_school_year && !empty($current_school_year) && isset($min_year)) {
        if ($year_label === $current_school_year) {
            $include = true;
            $reason = "Current school year";
        } elseif ($year_start >= $min_year && $year_start <= $current_year) {
            $include = true;
            $reason = "Within 5 years behind (not future)";
        } else {
            $include = false;
            $reason = "Too old (>5 years behind) or future year";
        }
    } else {
        // No filtering - include all
        $include = true;
        $reason = "No filtering applied";
    }
    
    if ($include) {
        $filtered_school_years[] = $row;
    }
    
    $status_color = $row['status'] === 'Active' ? 'green' : 'gray';
    $include_color = $include ? 'green' : 'red';
    
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $year_label . "</td>";
    echo "<td style='color: " . $status_color . ";'>" . $row['status'] . "</td>";
    echo "<td>" . $year_start . "</td>";
    echo "<td style='color: " . $include_color . ";'>" . ($include ? 'YES' : 'NO') . "</td>";
    echo "<td>" . $reason . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>Filtered School Years (What will show in dropdown):</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background-color: #f0f0f0;'><th>School Year</th><th>Status</th><th>Start Date</th><th>End Date</th></tr>";

foreach ($filtered_school_years as $sy) {
    // Check if this is the current school year based on date range
    $today = date('Y-m-d');
    $is_current = ($today >= $sy['start_date'] && $today <= $sy['end_date']) ? ' (Current)' : '';
    $status_color = $sy['status'] === 'Active' ? 'green' : 'gray';
    
    echo "<tr>";
    echo "<td><strong>" . $sy['school_year_label'] . $is_current . "</strong></td>";
    echo "<td style='color: " . $status_color . ";'>" . $sy['status'] . "</td>";
    echo "<td>" . $sy['start_date'] . "</td>";
    echo "<td>" . $sy['end_date'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>Expected Dropdown Order:</h3>";
echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";
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
echo "<p><strong>Total school years in database:</strong> " . count($all_school_years) . "</p>";
echo "<p><strong>Filtered school years for dropdown:</strong> " . count($filtered_school_years) . "</p>";
echo "<p><strong>Years excluded:</strong> " . (count($all_school_years) - count($filtered_school_years)) . "</p>";

echo "<h3>Next Steps:</h3>";
echo "<p><a href='super_admin-mis/content.php?page=school-calendar' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Test School Calendar</a></p>";

$conn->close();
?>
