<?php
// fix_school_calendar_database.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>School Calendar Database Fixer</h2>";

// Check database connection
if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
} else {
    echo "<p style='color: green;'>✅ Database connection successful</p>";
}

// Fix school_years table
echo "<h3>Fixing school_years table...</h3>";

// Check if table exists
$check_table = $conn->query("SHOW TABLES LIKE 'school_years'");
if ($check_table->num_rows === 0) {
    // Create table if it doesn't exist
    $create_table = "CREATE TABLE school_years (
        id INT PRIMARY KEY AUTO_INCREMENT,
        school_year_label VARCHAR(50) UNIQUE NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        status ENUM('Active', 'Inactive') DEFAULT 'Inactive',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($create_table)) {
        echo "<p style='color: green;'>✅ Created school_years table</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to create school_years table: " . $conn->error . "</p>";
        exit;
    }
} else {
    echo "<p style='color: green;'>✅ school_years table exists</p>";
    
    // Check current structure
    $columns = $conn->query("DESCRIBE school_years");
    $existing_columns = [];
    while ($row = $columns->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }
    
    echo "<p>Current columns: " . implode(', ', $existing_columns) . "</p>";
    
    // Add missing columns
    if (!in_array('school_year_label', $existing_columns)) {
        $alter_sql = "ALTER TABLE school_years ADD COLUMN school_year_label VARCHAR(50) UNIQUE NOT NULL AFTER id";
        if ($conn->query($alter_sql)) {
            echo "<p style='color: green;'>✅ Added school_year_label column</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to add school_year_label column: " . $conn->error . "</p>";
        }
    }
    
    if (!in_array('start_date', $existing_columns)) {
        $alter_sql = "ALTER TABLE school_years ADD COLUMN start_date DATE NOT NULL DEFAULT '2023-08-01' AFTER school_year_label";
        if ($conn->query($alter_sql)) {
            echo "<p style='color: green;'>✅ Added start_date column</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to add start_date column: " . $conn->error . "</p>";
        }
    }
    
    if (!in_array('end_date', $existing_columns)) {
        $alter_sql = "ALTER TABLE school_years ADD COLUMN end_date DATE NOT NULL DEFAULT '2024-05-31' AFTER start_date";
        if ($conn->query($alter_sql)) {
            echo "<p style='color: green;'>✅ Added end_date column</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to add end_date column: " . $conn->error . "</p>";
        }
    }
    
    if (!in_array('status', $existing_columns)) {
        $alter_sql = "ALTER TABLE school_years ADD COLUMN status ENUM('Active', 'Inactive') DEFAULT 'Inactive' AFTER end_date";
        if ($conn->query($alter_sql)) {
            echo "<p style='color: green;'>✅ Added status column</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to add status column: " . $conn->error . "</p>";
        }
    }
    
    if (!in_array('created_at', $existing_columns)) {
        $alter_sql = "ALTER TABLE school_years ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER status";
        if ($conn->query($alter_sql)) {
            echo "<p style='color: green;'>✅ Added created_at column</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to add created_at column: " . $conn->error . "</p>";
        }
    }
    
    // Update existing data if old structure exists
    $check_old_columns = $conn->query("DESCRIBE school_years");
    $old_columns = [];
    while ($row = $check_old_columns->fetch_assoc()) {
        $old_columns[] = $row['Field'];
    }
    
    if (in_array('year_start', $old_columns) && in_array('year_end', $old_columns)) {
        echo "<p>Updating existing data from old structure...</p>";
        
        // Update school_year_label
        $update_label = "UPDATE school_years SET school_year_label = CONCAT(year_start, '-', year_end) WHERE school_year_label IS NULL OR school_year_label = ''";
        if ($conn->query($update_label)) {
            echo "<p style='color: green;'>✅ Updated school_year_label from old data</p>";
        }
        
        // Update start_date and end_date
        $update_dates = "UPDATE school_years SET 
            start_date = CONCAT(year_start, '-08-01'),
            end_date = CONCAT(year_end, '-05-31')
            WHERE start_date = '2023-08-01' OR end_date = '2024-05-31'";
        if ($conn->query($update_dates)) {
            echo "<p style='color: green;'>✅ Updated dates from old data</p>";
        }
        
        // Update status
        $update_status = "UPDATE school_years SET status = CASE WHEN is_active = 1 THEN 'Active' ELSE 'Inactive' END WHERE status = 'Inactive'";
        if ($conn->query($update_status)) {
            echo "<p style='color: green;'>✅ Updated status from old data</p>";
        }
    }
}

// Fix school_terms table
echo "<h3>Fixing school_terms table...</h3>";

$check_terms_table = $conn->query("SHOW TABLES LIKE 'school_terms'");
if ($check_terms_table->num_rows === 0) {
    $create_terms_table = "CREATE TABLE school_terms (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(100) NOT NULL,
        school_year_id INT NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (school_year_id) REFERENCES school_years(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($create_terms_table)) {
        echo "<p style='color: green;'>✅ Created school_terms table</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to create school_terms table: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ school_terms table exists</p>";
}

// Insert sample data if no data exists
echo "<h3>Checking for sample data...</h3>";
$check_data = $conn->query("SELECT COUNT(*) as count FROM school_years");
$row = $check_data->fetch_assoc();
if ($row['count'] == 0) {
    $insert_sample = "INSERT INTO school_years (school_year_label, start_date, end_date, status) VALUES 
        ('2023-2024', '2023-08-01', '2024-05-31', 'Active'),
        ('2024-2025', '2024-08-01', '2025-05-31', 'Inactive')";
    
    if ($conn->query($insert_sample)) {
        echo "<p style='color: green;'>✅ Inserted sample school years</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to insert sample data: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ School years data exists</p>";
}

// Verify the fix
echo "<h3>Verifying the fix...</h3>";
$verify_query = "SELECT id, school_year_label, start_date, end_date, status FROM school_years LIMIT 5";
$verify_result = $conn->query($verify_query);

if ($verify_result && $verify_result->num_rows > 0) {
    echo "<p style='color: green;'>✅ Database structure is correct!</p>";
    echo "<table border='1' style='margin-top: 10px;'>";
    echo "<tr><th>ID</th><th>School Year Label</th><th>Start Date</th><th>End Date</th><th>Status</th></tr>";
    while ($row = $verify_result->fetch_assoc()) {
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
    echo "<p style='color: red;'>❌ Verification failed</p>";
}

echo "<h3>Database fix completed!</h3>";
echo "<p><a href='super_admin-mis/content.php?page=school-calendar'>Go to School Calendar</a></p>";

$conn->close();
?> 