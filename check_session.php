<?php
session_start();
echo "<h1>Current Session State</h1>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Dean Logged In Status:</h2>";
echo "dean_logged_in: " . ($_SESSION['dean_logged_in'] ?? 'NOT_SET') . "<br>";
echo "is_authenticated: " . ($_SESSION['is_authenticated'] ?? 'NOT_SET') . "<br>";
echo "user_id: " . ($_SESSION['user_id'] ?? 'NOT_SET') . "<br>";
echo "selected_role: " . (isset($_SESSION['selected_role']) ? 'SET' : 'NOT_SET') . "<br>";

if (isset($_SESSION['selected_role'])) {
    echo "<h3>Selected Role Details:</h3>";
    echo "<pre>";
    print_r($_SESSION['selected_role']);
    echo "</pre>";
}

echo "<p><a href='department-dean/content.php'>Go to Department Dean Dashboard</a></p>";
?>
