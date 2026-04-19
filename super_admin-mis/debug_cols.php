<?php
include 'super_admin-mis/includes/db_connection.php';
$columns_check = $conn->query("DESCRIBE school_years");
while ($row = $columns_check->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
