<?php
// debug_super_admin_login_notice.php
// Debug script to identify notices on Super Admin login screen

echo "<h2>Super Admin Login Notice Debug</h2>";

// Test 1: Check error reporting settings
echo "<h3>Test 1: Error Reporting Settings</h3>";
echo "<p><strong>error_reporting():</strong> " . error_reporting() . "</p>";
echo "<p><strong>display_errors:</strong> " . ini_get('display_errors') . "</p>";
echo "<p><strong>display_startup_errors:</strong> " . ini_get('display_startup_errors') . "</p>";
echo "<p><strong>log_errors:</strong> " . ini_get('log_errors') . "</p>";

// Test 2: Check session configuration
echo "<h3>Test 2: Session Configuration</h3>";
echo "<p><strong>Session Name:</strong> " . session_name() . "</p>";
echo "<p><strong>Session Status:</strong> " . session_status() . "</p>";

// Test 3: Test Super Admin session config inclusion
echo "<h3>Test 3: Super Admin Session Config Test</h3>";
try {
    require_once 'super_admin_session_config.php';
    echo "<p style='color: green;'>✅ super_admin_session_config.php loaded successfully</p>";
    
    // Test session start
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
        echo "<p style='color: green;'>✅ Session started successfully</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Session already active</p>";
    }
    
    echo "<p><strong>Session Name After Config:</strong> " . session_name() . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error loading super_admin_session_config.php: " . $e->getMessage() . "</p>";
}

// Test 4: Check session variables
echo "<h3>Test 4: Session Variables</h3>";
echo "<p><strong>Session Variables:</strong></p>";
if (empty($_SESSION)) {
    echo "<p style='color: orange;'>⚠️ No session variables found</p>";
} else {
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
}

// Test 5: Test the exact logic from successful login
echo "<h3>Test 5: Successful Login Logic Test</h3>";
$user_role = $_SESSION['user_role'] ?? '';
$redirectUrl = 'super_admin-mis/content.php';
$roleMessage = 'Welcome, Super Admin!';

echo "<p><strong>user_role:</strong> " . ($user_role ?: 'empty') . "</p>";
echo "<p><strong>super_admin_logged_in:</strong> " . (isset($_SESSION['super_admin_logged_in']) ? 'set' : 'not set') . "</p>";

if ($user_role === 'super_admin' || isset($_SESSION['super_admin_logged_in'])) {
    $redirectUrl = 'super_admin-mis/content.php';
    $roleMessage = 'Welcome, Super Admin!';
    echo "<p style='color: green;'>✅ Super Admin condition met</p>";
} else {
    $redirectUrl = 'user_login.php';
    $roleMessage = 'Login Successful!';
    echo "<p style='color: orange;'>⚠️ Fallback to regular user condition</p>";
}

echo "<p><strong>redirectUrl:</strong> $redirectUrl</p>";
echo "<p><strong>roleMessage:</strong> $roleMessage</p>";

// Test 6: Check for any PHP warnings or notices
echo "<h3>Test 6: PHP Warnings/Notices Check</h3>";

// Test undefined variable access
$test_undefined = $undefined_variable ?? 'undefined variable test passed';
echo "<p><strong>Undefined Variable Test:</strong> $test_undefined</p>";

// Test array access
$test_array = [];
$test_array_access = $test_array['nonexistent'] ?? 'array access test passed';
echo "<p><strong>Array Access Test:</strong> $test_array_access</p>";

// Test 7: Check file paths
echo "<h3>Test 7: File Path Check</h3>";
$files = [
    'super_admin_session_config.php' => 'Super Admin session config',
    'super_admin_successful_login.php' => 'Super Admin successful login',
    'src/assets/animated_icons/check-animated-icon.gif' => 'Success icon'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ $file exists ($description)</p>";
    } else {
        echo "<p style='color: red;'>❌ $file missing ($description)</p>";
    }
}

// Test 8: Check for any output before headers
echo "<h3>Test 8: Output Before Headers Check</h3>";
echo "<p style='color: blue;'>ℹ️ This test checks if there's any output before headers</p>";

// Test 9: Simulate the successful login page
echo "<h3>Test 9: Simulate Successful Login Page</h3>";
echo "<p style='color: blue;'>ℹ️ Testing the exact HTML output...</p>";

// Suppress error reporting for clean display
error_reporting(0);
ini_set('display_errors', 0);

ob_start(); // Start output buffering to catch any notices

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Super Admin Login Successful</title>
  <style>
    body {
      background: #0C4B34;
      font-family: 'TT Interphases', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      color: white;
      text-align: center;
      flex-direction: column;
    }
    .popup-message {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 20px;
      padding: 40px;
      color: #000000;
      box-shadow: 0 4px 20px rgba(0,0,0,0.3);
      max-width: 600px;
      margin: auto;
    }
    img {
      width: 120px;
      height: auto;
    }
    h2 {
      margin: 20px 0 10px 0;
      color: #0C4B34;
    }
    p {
      color: #0C4B34;
      margin-bottom: 0;
    }
    .okay-btn {
      background: #739AFF;
      border: none;
      border-radius: 12px;
      padding: 12px 24px;
      font-size: 18px;
      cursor: pointer;
      margin-top: 20px;
      color: white;
      transition: background 0.3s ease;
    }
    .okay-btn:hover {
      background: #0bf;
    }
  </style>
</head>
<body>
  <div class="popup-message">
    <img src="src/assets/animated_icons/check-animated-icon.gif" alt="Success" />
    <h2><?php echo htmlspecialchars($roleMessage); ?></h2>
    <p>Redirecting to your dashboard...</p>
    <button class="okay-btn" onclick="window.location.href='<?php echo $redirectUrl; ?>'">Continue</button>
  </div>

  <script>
    // Auto-redirect after 2 seconds
    setTimeout(function() {
      window.location.href = '<?php echo $redirectUrl; ?>';
    }, 2000);
  </script>
</body>
</html>
<?php

$output = ob_get_clean(); // Get the buffered output

echo "<p style='color: green;'>✅ HTML output generated successfully</p>";
echo "<p><strong>Output Length:</strong> " . strlen($output) . " characters</p>";

// Check for any PHP tags or notices in the output
if (strpos($output, '<?php') !== false) {
    echo "<p style='color: red;'>❌ PHP tags found in output</p>";
} else {
    echo "<p style='color: green;'>✅ No PHP tags in output</p>";
}

if (strpos($output, 'Notice:') !== false) {
    echo "<p style='color: red;'>❌ PHP notices found in output</p>";
} else {
    echo "<p style='color: green;'>✅ No PHP notices in output</p>";
}

if (strpos($output, 'Warning:') !== false) {
    echo "<p style='color: red;'>❌ PHP warnings found in output</p>";
} else {
    echo "<p style='color: green;'>✅ No PHP warnings in output</p>";
}

if (strpos($output, 'Error:') !== false) {
    echo "<p style='color: red;'>❌ PHP errors found in output</p>";
} else {
    echo "<p style='color: green;'>✅ No PHP errors in output</p>";
}

// Test 10: Recommendations
echo "<h3>Test 10: Recommendations</h3>";
echo "<ol>";
echo "<li><strong>Check Browser Console:</strong> Open Developer Tools (F12) and check for any JavaScript errors</li>";
echo "<li><strong>Check Network Tab:</strong> Look for any failed requests or 404 errors</li>";
echo "<li><strong>Clear Browser Cache:</strong> Clear all cache and cookies</li>";
echo "<li><strong>Check File Permissions:</strong> Ensure all files have proper read permissions</li>";
echo "<li><strong>Test in Different Browser:</strong> Try in a different browser to isolate the issue</li>";
echo "</ol>";

echo "<h3>Test Links:</h3>";
echo "<p><a href='super_admin_successful_login.php' target='_blank'>Test Super Admin Successful Login</a></p>";
echo "<p><a href='index.php' target='_blank'>Super Admin Login Page</a></p>";

echo "<br><p><strong>Notice debug complete!</strong></p>";
?> 