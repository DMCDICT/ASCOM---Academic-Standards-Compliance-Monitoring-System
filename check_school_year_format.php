<?php
// check_school_year_format.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Check School Year Format</h2>";

// Get all school years with their exact format
$sql = "SELECT id, school_year_label, status, start_date, end_date FROM school_years ORDER BY id DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>ID</th><th>School Year Label (Exact)</th><th>Length</th><th>Status</th><th>Start Date</th><th>End Date</th>";
    echo "</tr>";
    
    while ($row = $result->fetch_assoc()) {
        $label = $row['school_year_label'];
        $length = strlen($label);
        
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td><code>" . htmlspecialchars($label) . "</code></td>";
        echo "<td>" . $length . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['start_date'] . "</td>";
        echo "<td>" . $row['end_date'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test the filtering logic with the actual data
    echo "<h3>Testing Filtering Logic:</h3>";
    
    $current_year_sql = "SELECT school_year_label FROM school_years WHERE status = 'Active' ORDER BY start_date DESC LIMIT 1";
    $current_result = $conn->query($current_year_sql);
    
    if ($current_result && $current_result->num_rows > 0) {
        $current_row = $current_result->fetch_assoc();
        $current_school_year = $current_row['school_year_label'];
        
        echo "<p><strong>Current School Year:</strong> <code>" . htmlspecialchars($current_school_year) . "</code></p>";
        
        // Extract year from current school year
        $current_year = intval(explode('-', $current_school_year)[0]);
        $min_year = $current_year - 5;
        
        echo "<p><strong>Current Year:</strong> " . $current_year . "</p>";
        echo "<p><strong>Min Year:</strong> " . $min_year . "</p>";
        
        // Reset result pointer
        $result->data_seek(0);
        
        echo "<h4>Filtering Results:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>School Year</th><th>Year Start</th><th>Include?</th><th>Reason</th>";
        echo "</tr>";
        
        while ($row = $result->fetch_assoc()) {
            $year_label = $row['school_year_label'];
            $year_start = intval(explode('-', $year_label)[0]);
            
            $condition1 = ($year_label === $current_school_year);
            $condition2 = ($year_start >= $min_year && $year_start <= $current_year);
            $include = $condition1 || $condition2;
            
            $reason = "";
            if ($condition1) {
                $reason = "Current school year";
            } elseif ($condition2) {
                $reason = "Within 5 years behind";
            } else {
                $reason = "Too old or future";
            }
            
            $color = $include ? 'green' : 'red';
            
            echo "<tr>";
            echo "<td>" . htmlspecialchars($year_label) . "</td>";
            echo "<td>" . $year_start . "</td>";
            echo "<td style='color: " . $color . "; font-weight: bold;'>" . ($include ? 'YES' : 'NO') . "</td>";
            echo "<td>" . $reason . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p>No school years found in database.</p>";
}

$conn->close();
?>
