<?php
// Script to update school year active status based on current year
require_once 'includes/db_connection.php';

echo "<h2>Updating School Year Active Status</h2>";

try {
    // Get current year
    $current_year = date('Y');
    echo "<p>Current year: $current_year</p>";
    
    // Get all school years
    $sql = "SELECT id, year_start, year_end, is_active FROM school_years ORDER BY year_start DESC";
    $result = $conn->query($sql);
    
    if (!$result) {
        echo "<p style='color: red;'>Error: " . $conn->error . "</p>";
        exit;
    }
    
    echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
    echo "<tr><th>ID</th><th>Year Start</th><th>Year End</th><th>Old Status</th><th>New Status</th><th>Action</th></tr>";
    
    $updated_count = 0;
    
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $year_start = $row['year_start'];
        $year_end = $row['year_end'];
        $old_status = $row['is_active'];
        
        // Calculate correct status
        $is_currently_active = ($current_year >= $year_start && $current_year <= $year_end);
        $new_status = $is_currently_active ? 1 : 0;
        
        $old_status_text = $old_status ? 'Active' : 'Inactive';
        $new_status_text = $new_status ? 'Active' : 'Inactive';
        
        echo "<tr>";
        echo "<td>$id</td>";
        echo "<td>$year_start</td>";
        echo "<td>$year_end</td>";
        echo "<td>$old_status_text ($old_status)</td>";
        echo "<td>$new_status_text ($new_status)</td>";
        
        if ($old_status != $new_status) {
            // Update the database
            $update_sql = "UPDATE school_years SET is_active = ? WHERE id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param('ii', $new_status, $id);
            
            if ($stmt->execute()) {
                echo "<td style='color: green;'>Updated</td>";
                $updated_count++;
            } else {
                echo "<td style='color: red;'>Error: " . $stmt->error . "</td>";
            }
            $stmt->close();
        } else {
            echo "<td style='color: blue;'>No change needed</td>";
        }
        
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<p style='color: green; font-weight: bold;'>Updated $updated_count school year(s)</p>";
    echo "<p><a href='javascript:history.back()'>Go Back</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?>
