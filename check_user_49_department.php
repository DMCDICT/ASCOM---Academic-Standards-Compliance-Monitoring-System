<?php
// Check what department user ID 49 is actually assigned to
$conn = new mysqli("localhost", "root", "", "ascom_db");

echo "<h1>User ID 49 Department Check</h1>";

// Check user 49's department assignment
$stmt = $conn->prepare("
    SELECT u.id, u.first_name, u.last_name, u.department_id,
           d.id as dept_id, d.department_code, d.department_name, d.color_code
    FROM users u
    LEFT JOIN departments d ON u.department_id = d.id
    WHERE u.id = 49
");

$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    echo "<h2>User 49 Information:</h2>";
    echo "<p><strong>Name:</strong> " . $user['first_name'] . " " . $user['last_name'] . "</p>";
    echo "<p><strong>Department ID in users table:</strong> " . $user['department_id'] . "</p>";
    echo "<p><strong>Department Code:</strong> " . $user['department_code'] . "</p>";
    echo "<p><strong>Department Name:</strong> " . $user['department_name'] . "</p>";
    echo "<p><strong>Department Color:</strong> " . $user['color_code'] . "</p>";
} else {
    echo "<p style='color: red;'>User 49 not found!</p>";
}

// Show all departments
echo "<h2>All Departments:</h2>";
$stmt2 = $conn->prepare("SELECT id, department_code, department_name, color_code FROM departments");
$stmt2->execute();
$result2 = $stmt2->get_result();

echo "<table border='1'>";
echo "<tr><th>ID</th><th>Code</th><th>Name</th><th>Color</th></tr>";
while ($dept = $result2->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $dept['id'] . "</td>";
    echo "<td>" . $dept['department_code'] . "</td>";
    echo "<td>" . $dept['department_name'] . "</td>";
    echo "<td>" . $dept['color_code'] . "</td>";
    echo "</tr>";
}
echo "</table>";

$conn->close();
?>
