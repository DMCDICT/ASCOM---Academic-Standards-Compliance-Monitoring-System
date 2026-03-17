<?php
// Database connection for ASCOM Monitoring System
$servername = "localhost";
$username = "root";
$password = "";
$database = "ascom_db";

global $conn;
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>