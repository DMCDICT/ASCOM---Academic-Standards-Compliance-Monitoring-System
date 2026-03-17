<?php
session_start();

echo "<h1>Session Test</h1>";

echo "<h2>Current Session Data:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Session Status:</h2>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Status:</strong> " . session_status() . "</p>";
echo "<p><strong>Session Name:</strong> " . session_name() . "</p>";

echo "<h2>User Authentication Status:</h2>";
echo "<p><strong>admin_qa_logged_in:</strong> " . (isset($_SESSION['admin_qa_logged_in']) ? ($_SESSION['admin_qa_logged_in'] ? 'true' : 'false') : 'not set') . "</p>";
echo "<p><strong>user_id:</strong> " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set') . "</p>";
echo "<p><strong>teacher_logged_in:</strong> " . (isset($_SESSION['teacher_logged_in']) ? ($_SESSION['teacher_logged_in'] ? 'true' : 'false') : 'not set') . "</p>";

echo "<h2>Available Roles:</h2>";
if (isset($_SESSION['available_roles'])) {
    echo "<p><strong>Available roles:</strong> " . implode(', ', $_SESSION['available_roles']) . "</p>";
} else {
    echo "<p><strong>Available roles:</strong> not set</p>";
}

echo "<h2>Selected Role:</h2>";
if (isset($_SESSION['selected_role'])) {
    echo "<pre>";
    print_r($_SESSION['selected_role']);
    echo "</pre>";
} else {
    echo "<p><strong>Selected role:</strong> not set</p>";
}
?>
