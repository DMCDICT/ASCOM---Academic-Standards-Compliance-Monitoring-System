<?php
// debug_500_error.php
echo "<h2>Debug 500 Error in Add Term API</h2>";

// Test data
$test_data = [
    'title' => 'Test Term',
    'school_year_id' => 35, // Use the active school year ID
    'start_date' => '2025-08-15',
    'end_date' => '2025-12-15'
];

echo "<h3>Test Data:</h3>";
echo "<pre>" . json_encode($test_data, JSON_PRETTY_PRINT) . "</pre>";

echo "<h3>Testing API with Error Capture:</h3>";

// Test 1: Direct file inclusion with error capture
echo "<h4>Test 1: Direct File Inclusion</h4>";
echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";

// Capture output and errors
ob_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simulate the API call
$_SERVER['REQUEST_METHOD'] = 'POST';
$GLOBALS['jsonData'] = json_encode($test_data);

// Include the API file
try {
    include 'super_admin-mis/api/add_term.php';
} catch (Exception $e) {
    echo "Exception caught: " . $e->getMessage() . "<br>";
} catch (Error $e) {
    echo "Fatal error caught: " . $e->getMessage() . "<br>";
}

$output = ob_get_clean();
echo "Raw API Output:<br>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

// Try to decode as JSON
$json_response = json_decode($output, true);
if ($json_response) {
    echo "✅ Valid JSON response:<br>";
    echo "<pre>" . json_encode($json_response, JSON_PRETTY_PRINT) . "</pre>";
} else {
    echo "❌ Invalid JSON response. JSON error: " . json_last_error_msg() . "<br>";
    echo "This explains the 'Unexpected end of JSON input' error!<br>";
}

echo "</div>";

// Test 2: Check if the API file exists and is readable
echo "<h4>Test 2: File Check</h4>";
echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";

$api_file = 'super_admin-mis/api/add_term.php';
if (file_exists($api_file)) {
    echo "✅ API file exists<br>";
    if (is_readable($api_file)) {
        echo "✅ API file is readable<br>";
        
        // Check file size
        $file_size = filesize($api_file);
        echo "File size: " . $file_size . " bytes<br>";
        
        // Check first few lines for syntax
        $file_content = file_get_contents($api_file);
        if ($file_content !== false) {
            echo "✅ File content can be read<br>";
            
            // Check for basic PHP syntax
            if (strpos($file_content, '<?php') !== false) {
                echo "✅ File contains PHP opening tag<br>";
            } else {
                echo "❌ File missing PHP opening tag<br>";
            }
        } else {
            echo "❌ Cannot read file content<br>";
        }
    } else {
        echo "❌ API file is not readable<br>";
    }
} else {
    echo "❌ API file does not exist<br>";
}

echo "</div>";

// Test 3: Check database connection
echo "<h4>Test 3: Database Connection</h4>";
echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";

try {
    require_once 'super_admin-mis/includes/db_connection.php';
    if (isset($conn) && $conn->ping()) {
        echo "✅ Database connection is working<br>";
        
        // Test if we can query the school_terms table
        $test_query = $conn->query("SELECT COUNT(*) as count FROM school_terms");
        if ($test_query) {
            $count = $test_query->fetch_assoc()['count'];
            echo "✅ school_terms table is accessible. Current count: " . $count . "<br>";
        } else {
            echo "❌ Cannot query school_terms table: " . $conn->error . "<br>";
        }
    } else {
        echo "❌ Database connection failed<br>";
    }
} catch (Exception $e) {
    echo "❌ Database connection exception: " . $e->getMessage() . "<br>";
}

echo "</div>";

echo "<h3>Next Steps:</h3>";
echo "<p>Based on the output above, we can identify what's causing the 500 error and fix it.</p>";
echo "<p><a href='test_api_fix.php' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Test API Again</a></p>";
?>
