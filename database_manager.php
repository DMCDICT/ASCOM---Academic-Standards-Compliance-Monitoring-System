<?php
// Comprehensive Database Manager for ASCOM Monitoring System
// This replaces all SQL files and manages everything through PHP

class DatabaseManager {
    private $conn;
    private $database = "ascom_db";
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        $servername = "localhost";
        $username = "root";
        $password = "";
        
        // Connect without specifying database first
        $this->conn = new mysqli($servername, $username, $password);
        
        if ($this->conn->connect_error) {
            throw new Exception("Connection failed: " . $this->conn->connect_error);
        }
        
        // Create database if it doesn't exist
        $this->createDatabase();
        
        // Select the database
        $this->conn->select_db($this->database);
    }
    
    private function createDatabase() {
        $sql = "CREATE DATABASE IF NOT EXISTS {$this->database}";
        if (!$this->conn->query($sql)) {
            throw new Exception("Error creating database: " . $this->conn->error);
        }
    }
    
    public function setupAllTables() {
        $this->createUsersTable();
        $this->createSuperAdminTable();
        $this->createDepartmentsTable();
        $this->createSchoolYearsTable();
        $this->createTermsTable();
        $this->createSchoolCalendarTable();
        // Add more tables as needed
    }
    
    private function createUsersTable() {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('department_dean', 'teacher', 'librarian', 'admin_qa') NOT NULL,
            department_id INT NULL,
            first_name VARCHAR(100) NULL,
            last_name VARCHAR(100) NULL,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_role (role),
            INDEX idx_active (is_active)
        )";
        
        if (!$this->conn->query($sql)) {
            throw new Exception("Error creating users table: " . $this->conn->error);
        }
    }
    
    private function createSuperAdminTable() {
        $sql = "CREATE TABLE IF NOT EXISTS super_admin (
            id INT PRIMARY KEY AUTO_INCREMENT,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if (!$this->conn->query($sql)) {
            throw new Exception("Error creating super_admin table: " . $this->conn->error);
        }
    }
    
    private function createDepartmentsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS departments (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            code VARCHAR(50) UNIQUE NOT NULL,
            description TEXT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if (!$this->conn->query($sql)) {
            throw new Exception("Error creating departments table: " . $this->conn->error);
        }
    }
    
    private function createSchoolYearsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS school_years (
            id INT PRIMARY KEY AUTO_INCREMENT,
            year_start INT NOT NULL,
            year_end INT NOT NULL,
            is_active BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_year (year_start, year_end)
        )";
        
        if (!$this->conn->query($sql)) {
            throw new Exception("Error creating school_years table: " . $this->conn->error);
        }
    }
    
    private function createTermsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS terms (
            id INT PRIMARY KEY AUTO_INCREMENT,
            school_year_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            is_active BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (school_year_id) REFERENCES school_years(id) ON DELETE CASCADE
        )";
        
        if (!$this->conn->query($sql)) {
            throw new Exception("Error creating terms table: " . $this->conn->error);
        }
    }
    
    private function createSchoolCalendarTable() {
        $sql = "CREATE TABLE IF NOT EXISTS school_calendar (
            id INT PRIMARY KEY AUTO_INCREMENT,
            term_id INT NOT NULL,
            event_type ENUM('class', 'holiday', 'exam', 'event') NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT NULL,
            start_date DATE NOT NULL,
            end_date DATE NULL,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (term_id) REFERENCES terms(id) ON DELETE CASCADE
        )";
        
        if (!$this->conn->query($sql)) {
            throw new Exception("Error creating school_calendar table: " . $this->conn->error);
        }
    }
    
    public function insertSampleData() {
        $this->insertSampleUsers();
        $this->insertSampleSuperAdmin();
        $this->insertSampleDepartments();
        $this->insertSampleSchoolYears();
    }
    
    private function insertSampleUsers() {
        // Check if users already exist
        $result = $this->conn->query("SELECT COUNT(*) as count FROM users");
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            return; // Users already exist
        }
        
        $password = 'password123';
        
        $users = [
            ['dean.cse@sccpag.edu.ph', $password, 'department_dean', 'John', 'Smith'],
            ['teacher1@sccpag.edu.ph', $password, 'teacher', 'Jane', 'Doe'],
            ['teacher2@sccpag.edu.ph', $password, 'teacher', 'Mike', 'Johnson'],
            ['librarian@sccpag.edu.ph', $password, 'librarian', 'Sarah', 'Wilson'],
            ['qa.admin@sccpag.edu.ph', $password, 'admin_qa', 'Robert', 'Brown']
        ];
        
        $stmt = $this->conn->prepare("INSERT INTO users (email, password, role, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($users as $user) {
            $stmt->bind_param("sssss", $user[0], $user[1], $user[2], $user[3], $user[4]);
            $stmt->execute();
        }
        
        $stmt->close();
    }
    
    private function insertSampleSuperAdmin() {
        $result = $this->conn->query("SELECT COUNT(*) as count FROM super_admin");
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            return; // Super admin already exists
        }
        
        $password = 'password123';
        
        $stmt = $this->conn->prepare("INSERT INTO super_admin (email, password) VALUES (?, ?)");
        $email = "superadmin.mis@sccpag.edu.ph";
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $stmt->close();
    }
    
    private function insertSampleDepartments() {
        $result = $this->conn->query("SELECT COUNT(*) as count FROM departments");
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            return; // Departments already exist
        }
        
        $departments = [
            ['College of Computer Studies', 'CCS', 'Computer Science and Information Technology programs'],
            ['College of Engineering', 'COE', 'Engineering programs'],
            ['College of Business Administration', 'CBA', 'Business and Management programs'],
            ['College of Arts and Sciences', 'CAS', 'Liberal Arts and Sciences programs']
        ];
        
        $stmt = $this->conn->prepare("INSERT INTO departments (name, code, description) VALUES (?, ?, ?)");
        
        foreach ($departments as $dept) {
            $stmt->bind_param("sss", $dept[0], $dept[1], $dept[2]);
            $stmt->execute();
        }
        
        $stmt->close();
    }
    
    private function insertSampleSchoolYears() {
        $result = $this->conn->query("SELECT COUNT(*) as count FROM school_years");
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            return; // School years already exist
        }
        
        $school_years = [
            [2023, 2024, true],  // Current active year
            [2024, 2025, false], // Next year
            [2022, 2023, false]  // Previous year
        ];
        
        $stmt = $this->conn->prepare("INSERT INTO school_years (year_start, year_end, is_active) VALUES (?, ?, ?)");
        
        foreach ($school_years as $year) {
            $stmt->bind_param("iii", $year[0], $year[1], $year[2]);
            $stmt->execute();
        }
        
        $stmt->close();
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function closeConnection() {
        $this->conn->close();
    }
    
    public function getTableInfo() {
        $tables = ['users', 'super_admin', 'departments', 'school_years', 'terms', 'school_calendar'];
        $info = [];
        
        foreach ($tables as $table) {
            $result = $this->conn->query("SELECT COUNT(*) as count FROM $table");
            if ($result) {
                $row = $result->fetch_assoc();
                $info[$table] = $row['count'];
            } else {
                $info[$table] = 'Table not found';
            }
        }
        
        return $info;
    }
}

// Usage example and setup interface
if (isset($_GET['action'])) {
    try {
        $dbManager = new DatabaseManager();
        
        switch ($_GET['action']) {
            case 'setup':
                echo "<h2>Setting up database...</h2>";
                $dbManager->setupAllTables();
                echo "✅ All tables created successfully!<br>";
                
                echo "<h3>Inserting sample data...</h3>";
                $dbManager->insertSampleData();
                echo "✅ Sample data inserted successfully!<br>";
                break;
                
            case 'info':
                echo "<h2>Database Information</h2>";
                $info = $dbManager->getTableInfo();
                foreach ($info as $table => $count) {
                    echo "<strong>$table:</strong> $count records<br>";
                }
                break;
                
            case 'reset':
                echo "<h2>Resetting database...</h2>";
                $conn = $dbManager->getConnection();
                $tables = ['school_calendar', 'terms', 'school_years', 'departments', 'users', 'super_admin'];
                
                foreach ($tables as $table) {
                    $conn->query("DROP TABLE IF EXISTS $table");
                    echo "✅ Dropped table: $table<br>";
                }
                
                echo "<h3>Recreating tables...</h3>";
                $dbManager->setupAllTables();
                echo "✅ Tables recreated!<br>";
                
                echo "<h3>Inserting fresh sample data...</h3>";
                $dbManager->insertSampleData();
                echo "✅ Fresh sample data inserted!<br>";
                break;
        }
        
        $dbManager->closeConnection();
        
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Manager - ASCOM Monitoring System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .button { 
            display: inline-block; 
            padding: 10px 20px; 
            margin: 5px; 
            background: #007bff; 
            color: white; 
            text-decoration: none; 
            border-radius: 5px; 
        }
        .button:hover { background: #0056b3; }
        .danger { background: #dc3545; }
        .danger:hover { background: #c82333; }
    </style>
</head>
<body>
    <h1>Database Manager</h1>
    <p>Manage your ASCOM Monitoring System database through PHP</p>
    
    <a href="?action=setup" class="button">Setup Database</a>
    <a href="?action=info" class="button">View Database Info</a>
    <a href="?action=reset" class="button danger">Reset Database</a>
    
    <hr>
    
    <h3>Available Actions:</h3>
    <ul>
        <li><strong>Setup Database:</strong> Creates all tables and inserts sample data</li>
        <li><strong>View Database Info:</strong> Shows record counts for all tables</li>
        <li><strong>Reset Database:</strong> Drops all tables and recreates them (WARNING: This will delete all data)</li>
    </ul>
    
    <h3>Test Credentials (after setup):</h3>
    <ul>
        <li><strong>Super Admin:</strong> superadmin.mis@sccpag.edu.ph / password123</li>
        <li><strong>Department Dean:</strong> dean.cse@sccpag.edu.ph / password123</li>
        <li><strong>Teacher:</strong> teacher1@sccpag.edu.ph / password123</li>
        <li><strong>Librarian:</strong> librarian@sccpag.edu.ph / password123</li>
        <li><strong>Admin QA:</strong> qa.admin@sccpag.edu.ph / password123</li>
    </ul>
</body>
</html> 