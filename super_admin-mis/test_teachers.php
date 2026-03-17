<?php
require_once 'includes/db_connection.php';

echo "<h2>Testing Teachers in Database</h2>";

// Check all teachers
$query = "
    SELECT 
        u.id,
        u.first_name,
        u.last_name,
        u.employee_no,
        u.department_id,
        u.role_id,
        d.department_code,
        d.department_name
    FROM 
        users u
    LEFT JOIN 
        departments d ON u.department_id = d.id
    WHERE 
        u.role_id = 4
        AND u.is_active = 1
    ORDER BY 
        d.department_code, u.last_name
";

$result = $conn->query($query);

if ($result) {
    echo "<h3>All Teachers:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Employee No</th><th>Department</th><th>Dept Code</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
        echo "<td>" . $row['employee_no'] . "</td>";
        echo "<td>" . $row['department_name'] . "</td>";
        echo "<td>" . $row['department_code'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error: " . $conn->error;
}

// Check departments
echo "<h3>All Departments:</h3>";
$deptQuery = "SELECT id, department_code, department_name FROM departments ORDER BY department_code";
$deptResult = $conn->query($deptQuery);

if ($deptResult) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Code</th><th>Name</th></tr>";
    
    while ($row = $deptResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['department_code'] . "</td>";
        echo "<td>" . $row['department_name'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
