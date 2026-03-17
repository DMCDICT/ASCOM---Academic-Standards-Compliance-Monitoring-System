<?php
// create_test_programs.php
// Script to create test programs for the current dean's department

require_once dirname(__FILE__) . '/../session_config.php';
require_once 'includes/db_connection.php';

// Ensure session configuration is applied before starting session
if (session_status() == PHP_SESSION_NONE) {
    session_name('ASCOM_SESSION');
    session_set_cookie_params([
        'lifetime' => 30 * 24 * 60 * 60, // 30 days
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

echo "<h2>Create Test Programs</h2>";

try {
    // Get the current dean's department code from session
    $deanDepartmentCode = $_SESSION['selected_role']['department_code'] ?? null;
    
    if (!$deanDepartmentCode) {
        echo "Error: No department code found in session!<br>";
        exit;
    }
    
    // Get department ID
    $deptQuery = "SELECT id, name FROM departments WHERE department_code = ?";
    $deptStmt = $pdo->prepare($deptQuery);
    $deptStmt->execute([$deanDepartmentCode]);
    $deptResult = $deptStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$deptResult) {
        echo "Error: Department not found in database!<br>";
        exit;
    }
    
    $departmentId = $deptResult['id'];
    $departmentName = $deptResult['name'];
    
    echo "Creating test programs for department: " . $departmentName . " (" . $deanDepartmentCode . ")<br><br>";
    
    // Define test programs based on department
    $testPrograms = [];
    
    if (strpos($deanDepartmentCode, 'CCS') !== false || strpos($deanDepartmentCode, 'CS') !== false) {
        // Computer Science programs
        $testPrograms = [
            ['BSCS', 'Bachelor of Science in Computer Science', 'General Computer Science', '#1976d2'],
            ['BSIT', 'Bachelor of Science in Information Technology', 'Information Technology', '#4caf50'],
            ['BSIS', 'Bachelor of Science in Information Systems', 'Information Systems', '#ff9800']
        ];
    } elseif (strpos($deanDepartmentCode, 'EDU') !== false || strpos($deanDepartmentCode, 'ED') !== false) {
        // Education programs
        $testPrograms = [
            ['BSE', 'Bachelor of Science in Education', 'Elementary Education', '#9c27b0'],
            ['BSE', 'Bachelor of Science in Education', 'Secondary Education', '#e91e63'],
            ['BSE', 'Bachelor of Science in Education', 'Special Education', '#673ab7']
        ];
    } elseif (strpos($deanDepartmentCode, 'BUS') !== false || strpos($deanDepartmentCode, 'BA') !== false) {
        // Business programs
        $testPrograms = [
            ['BSBA', 'Bachelor of Science in Business Administration', 'Marketing Management', '#f44336'],
            ['BSBA', 'Bachelor of Science in Business Administration', 'Financial Management', '#4caf50'],
            ['BSBA', 'Bachelor of Science in Business Administration', 'Human Resource Management', '#ff9800']
        ];
    } else {
        // Generic programs for any department
        $testPrograms = [
            ['BS', 'Bachelor of Science', 'General Studies', '#1976d2'],
            ['BA', 'Bachelor of Arts', 'Liberal Arts', '#4caf50'],
            ['BSE', 'Bachelor of Science in Education', 'Education', '#ff9800']
        ];
    }
    
    $createdCount = 0;
    
    foreach ($testPrograms as $program) {
        try {
            // Check if program already exists
            $checkQuery = "SELECT id FROM programs WHERE program_code = ? AND department_id = ?";
            $checkStmt = $pdo->prepare($checkQuery);
            $checkStmt->execute([$program[0], $departmentId]);
            
            if ($checkStmt->rowCount() > 0) {
                echo "Program " . $program[0] . " already exists, skipping...<br>";
                continue;
            }
            
            // Insert new program
            $insertQuery = "
                INSERT INTO programs (program_code, program_name, major, color_code, department_id, description, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ";
            $insertStmt = $pdo->prepare($insertQuery);
            $insertStmt->execute([
                $program[0],
                $program[1],
                $program[2],
                $program[3],
                $departmentId,
                'Test program created for ' . $departmentName
            ]);
            
            echo "Created program: " . $program[0] . " - " . $program[1] . " (" . $program[2] . ")<br>";
            $createdCount++;
            
        } catch (Exception $e) {
            echo "Error creating program " . $program[0] . ": " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<br><strong>Created " . $createdCount . " test programs successfully!</strong><br>";
    echo "You can now try opening the course modal again.<br>";
    echo "<a href='content.php'>Go back to main page</a>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}
?>
