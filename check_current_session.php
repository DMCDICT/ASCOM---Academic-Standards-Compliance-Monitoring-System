<?php
session_start();

echo "<h1>Current Session Check</h1>";

echo "<h2>All Session Variables:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Session Status:</h2>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Status:</strong> " . session_status() . "</p>";
echo "<p><strong>Session Name:</strong> " . session_name() . "</p>";

echo "<h2>Key Variables:</h2>";
echo "<p><strong>admin_qa_logged_in:</strong> " . (isset($_SESSION['admin_qa_logged_in']) ? ($_SESSION['admin_qa_logged_in'] ? 'true' : 'false') : 'not set') . "</p>";
echo "<p><strong>teacher_logged_in:</strong> " . (isset($_SESSION['teacher_logged_in']) ? ($_SESSION['teacher_logged_in'] ? 'true' : 'false') : 'not set') . "</p>";
echo "<p><strong>user_id:</strong> " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set') . "</p>";
echo "<p><strong>dean_logged_in:</strong> " . (isset($_SESSION['dean_logged_in']) ? ($_SESSION['dean_logged_in'] ? 'true' : 'false') : 'not set') . "</p>";
echo "<p><strong>librarian_logged_in:</strong> " . (isset($_SESSION['librarian_logged_in']) ? ($_SESSION['librarian_logged_in'] ? 'true' : 'false') : 'not set') . "</p>";
?>
