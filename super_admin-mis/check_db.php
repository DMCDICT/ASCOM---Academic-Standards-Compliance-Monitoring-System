<?php
require_once 'includes/db_connection.php';

echo "<h2>School Years in Database:</h2>";

$sql = "SELECT * FROM school_years ORDER BY year_start DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Year Start</th><th>Year End</th><th>Start Date</th><th>End Date</th><th>Is Active</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['year_start'] . "</td>";
        echo "<td>" . $row['year_end'] . "</td>";
        echo "<td>" . $row['start_date'] . "</td>";
        echo "<td>" . $row['end_date'] . "</td>";
        echo "<td>" . $row['is_active'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No school years found in database.";
}

$conn->close();
?>
