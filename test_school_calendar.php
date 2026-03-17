<?php
// test_school_calendar.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>School Calendar Test</h2>";

// Test database connection
if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
} else {
    echo "<p style='color: green;'>✅ Database connection successful</p>";
}

// Test school_years table
echo "<h3>Testing school_years table...</h3>";
$check_sy = $conn->query("SHOW TABLES LIKE 'school_years'");
if ($check_sy->num_rows === 0) {
    echo "<p style='color: orange;'>⚠️ school_years table does not exist</p>";
    
    // Create the table
    $create_sy = "CREATE TABLE school_years (
        id INT PRIMARY KEY AUTO_INCREMENT,
        school_year_label VARCHAR(50) UNIQUE NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        status ENUM('Active', 'Inactive') DEFAULT 'Inactive',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($create_sy)) {
        echo "<p style='color: green;'>✅ Created school_years table</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to create school_years table: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ school_years table exists</p>";
    
    // Check structure
    $columns = $conn->query("DESCRIBE school_years");
    echo "<p>Columns in school_years table:</p><ul>";
    while ($row = $columns->fetch_assoc()) {
        echo "<li>" . $row['Field'] . " - " . $row['Type'] . "</li>";
    }
    echo "</ul>";
}

// Test school_terms table
echo "<h3>Testing school_terms table...</h3>";
$check_st = $conn->query("SHOW TABLES LIKE 'school_terms'");
if ($check_st->num_rows === 0) {
    echo "<p style='color: orange;'>⚠️ school_terms table does not exist</p>";
    
    // Create the table
    $create_st = "CREATE TABLE school_terms (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(100) NOT NULL,
        school_year_id INT NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (school_year_id) REFERENCES school_years(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($create_st)) {
        echo "<p style='color: green;'>✅ Created school_terms table</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to create school_terms table: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ school_terms table exists</p>";
}

// Test inserting sample data
echo "<h3>Testing data insertion...</h3>";
$check_data = $conn->query("SELECT COUNT(*) as count FROM school_years");
$row = $check_data->fetch_assoc();
if ($row['count'] == 0) {
    // Insert sample school year
    $insert_sy = "INSERT INTO school_years (school_year_label, start_date, end_date, status) VALUES ('2023-2024', '2023-08-01', '2024-05-31', 'Active')";
    if ($conn->query($insert_sy)) {
        echo "<p style='color: green;'>✅ Inserted sample school year</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to insert sample school year: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ School years data exists</p>";
}

// Test querying data
echo "<h3>Testing data retrieval...</h3>";
$result = $conn->query("SELECT * FROM school_years");
if ($result && $result->num_rows > 0) {
    echo "<p style='color: green;'>✅ Successfully retrieved school years data</p>";
    echo "<table border='1'><tr><th>ID</th><th>Label</th><th>Start Date</th><th>End Date</th><th>Status</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['school_year_label'] . "</td>";
        echo "<td>" . $row['start_date'] . "</td>";
        echo "<td>" . $row['end_date'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ No school years data found</p>";
}

echo "<h3>Test completed!</h3>";
echo "<p><a href='super_admin-mis/content.php?page=school-calendar'>Go to School Calendar</a></p>";

$conn->close();
?> 