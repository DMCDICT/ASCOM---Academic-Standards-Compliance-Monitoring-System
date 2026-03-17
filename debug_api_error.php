<?php
// debug_api_error.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Debug API Error</h2>";

// Step 1: Test database connection
echo "<h3>1. Database Connection Test:</h3>";
if ($conn) {
    echo "<p style='color: green;'>✅ Database connection successful</p>";
} else {
    echo "<p style='color: red;'>❌ Database connection failed</p>";
    exit;
}

// Step 2: Check if school_years table exists
echo "<h3>2. School Years Table Test:</h3>";
$table_check = $conn->query("SHOW TABLES LIKE 'school_years'");
if ($table_check && $table_check->num_rows > 0) {
    echo "<p style='color: green;'>✅ school_years table exists</p>";
} else {
    echo "<p style='color: red;'>❌ school_years table does not exist</p>";
    exit;
}

// Step 3: Check table structure
echo "<h3>3. Table Structure Test:</h3>";
$structure = $conn->query("DESCRIBE school_years");
if ($structure) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Failed to get table structure: " . $conn->error . "</p>";
}

// Step 4: Test the exact query from the API
echo "<h3>4. Testing API Query:</h3>";
$sql = "SELECT id, year_start, year_end, start_date, end_date, status FROM school_years ORDER BY year_start DESC";
$result = $conn->query($sql);

if (!$result) {
    echo "<p style='color: red;'>❌ Query failed: " . $conn->error . "</p>";
} else {
    echo "<p style='color: green;'>✅ Query successful!</p>";
    echo "<p>Found " . $result->num_rows . " school years</p>";
    
    if ($result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'><th>ID</th><th>Year Start</th><th>Year End</th><th>Start Date</th><th>End Date</th><th>Status</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['year_start'] . "</td>";
            echo "<td>" . $row['year_end'] . "</td>";
            echo "<td>" . $row['start_date'] . "</td>";
            echo "<td>" . $row['end_date'] . "</td>";
            echo "<td>" . $row['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

// Step 5: Simulate the API logic
echo "<h3>5. Simulating API Logic:</h3>";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $events = [];
    while ($row = $result->fetch_assoc()) {
        // Determine if this school year is currently active based on actual dates
        $current_date = date('Y-m-d');
        $is_currently_active = ($current_date >= $row['start_date'] && $current_date <= $row['end_date']);
        
        // Add start date event
        $events[] = [
            'id' => 'sy_start_' . $row['id'],
            'title' => 'A.Y. ' . $row['year_start'] . '-' . $row['year_end'] . ' Starts',
            'date' => $row['start_date'],
            'type' => 'school_year_start',
            'school_year_id' => $row['id'],
            'is_active' => $is_currently_active ? 1 : 0
        ];
        
        // Add end date event
        $events[] = [
            'id' => 'sy_end_' . $row['id'],
            'title' => 'A.Y. ' . $row['year_start'] . '-' . $row['year_end'] . ' Ends',
            'date' => $row['end_date'],
            'type' => 'school_year_end',
            'school_year_id' => $row['id'],
            'is_active' => $is_currently_active ? 1 : 0
        ];
    }
    
    echo "<p style='color: green;'>✅ Successfully created " . count($events) . " events</p>";
    echo "<pre>" . json_encode($events, JSON_PRETTY_PRINT) . "</pre>";
} else {
    echo "<p style='color: red;'>❌ No school years found or query failed</p>";
}

// Step 6: Test the actual API endpoint
echo "<h3>6. Testing Actual API Endpoint:</h3>";
echo "<p><a href='super_admin-mis/api/get_school_year_events.php' target='_blank' style='background-color: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔗 Test API Endpoint</a></p>";

$conn->close();
?>
