<?php
echo "<h2>MySQL Connection Test</h2>";

// Test 1: Basic MySQL connection
echo "<h3>Test 1: Basic MySQL Connection</h3>";
try {
    $mysqli = new mysqli('localhost', 'root', '', 'mysql');
    if ($mysqli->connect_error) {
        echo "❌ <strong>Connection failed:</strong> " . $mysqli->connect_error . "<br>";
    } else {
        echo "✅ <strong>MySQL connection successful!</strong><br>";
        echo "Server info: " . $mysqli->server_info . "<br>";
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "❌ <strong>Exception:</strong> " . $e->getMessage() . "<br>";
}

// Test 2: Check if ascom_db exists
echo "<h3>Test 2: Check Database</h3>";
try {
    $mysqli = new mysqli('localhost', 'root', '', '');
    if ($mysqli->connect_error) {
        echo "❌ <strong>Connection failed:</strong> " . $mysqli->connect_error . "<br>";
    } else {
        $result = $mysqli->query("SHOW DATABASES LIKE 'ascom_db'");
        if ($result && $result->num_rows > 0) {
            echo "✅ <strong>Database 'ascom_db' exists!</strong><br>";
        } else {
            echo "❌ <strong>Database 'ascom_db' does not exist!</strong><br>";
        }
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "❌ <strong>Exception:</strong> " . $e->getMessage() . "<br>";
}

// Test 3: PHP Info
echo "<h3>Test 3: PHP Configuration</h3>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . "<br>";
echo "PHP Version: " . phpversion() . "<br>";

// Test 4: Check if mysqli extension is loaded
echo "<h3>Test 4: Extensions</h3>";
if (extension_loaded('mysqli')) {
    echo "✅ <strong>mysqli extension is loaded</strong><br>";
} else {
    echo "❌ <strong>mysqli extension is NOT loaded</strong><br>";
}

if (extension_loaded('pdo_mysql')) {
    echo "✅ <strong>pdo_mysql extension is loaded</strong><br>";
} else {
    echo "❌ <strong>pdo_mysql extension is NOT loaded</strong><br>";
}

// Test 5: Try to access PhpMyAdmin config
echo "<h3>Test 5: PhpMyAdmin Config</h3>";
$config_file = 'D:/xampp/phpMyAdmin/config.inc.php';
if (file_exists($config_file)) {
    echo "✅ <strong>PhpMyAdmin config file exists</strong><br>";
    $config_content = file_get_contents($config_file);
    if (strpos($config_content, 'blowfish_secret') !== false) {
        echo "✅ <strong>Config file appears valid</strong><br>";
    } else {
        echo "❌ <strong>Config file may be corrupted</strong><br>";
    }
} else {
    echo "❌ <strong>PhpMyAdmin config file not found</strong><br>";
}

echo "<hr>";
echo "<h3>Quick Fix Suggestions:</h3>";
echo "<ol>";
echo "<li><strong>If MySQL connection fails:</strong> Check if MySQL is running on port 3306</li>";
echo "<li><strong>If database doesn't exist:</strong> Create the ascom_db database</li>";
echo "<li><strong>If extensions missing:</strong> Check php.ini configuration</li>";
echo "<li><strong>If config file issues:</strong> Recreate PhpMyAdmin config</li>";
echo "</ol>";
?> 