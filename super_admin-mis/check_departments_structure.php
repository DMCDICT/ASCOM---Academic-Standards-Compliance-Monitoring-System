<?php
// Check departments table structure
require_once __DIR__ . '/includes/db_connection.php';

echo "<h2>Departments Table Structure Check</h2>";

// Show the actual structure
$result = $conn->query("DESCRIBE departments");
if ($result) {
    echo "<h3>Actual Departments Table Structure:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Error describing departments table: " . $conn->error . "<br>";
}

// Show current data
echo "<h3>Current Departments Data:</h3>";
$result = $conn->query("SELECT * FROM departments LIMIT 10");
if ($result) {
    echo "<table border='1'>";
    echo "<tr>";
    while ($row = $result->fetch_assoc()) {
        foreach ($row as $key => $value) {
            echo "<th>" . $key . "</th>";
        }
        break;
    }
    echo "</tr>";
    $result->data_seek(0);
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . ($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Error querying departments table: " . $conn->error . "<br>";
}

$conn->close();
?> 