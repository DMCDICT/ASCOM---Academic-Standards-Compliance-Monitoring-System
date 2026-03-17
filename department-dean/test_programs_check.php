<?php
// test_programs_check.php
// Simple test to check what's happening with the program check

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

echo "<h2>Program Check Test</h2>";

echo "<h3>Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>Database Connection Test:</h3>";
try {
    $testQuery = "SELECT 1 as test";
    $testStmt = $pdo->prepare($testQuery);
    $testStmt->execute();
    $result = $testStmt->fetch(PDO::FETCH_ASSOC);
    echo "Database connection: " . ($result ? "SUCCESS" : "FAILED") . "<br>";
} catch (Exception $e) {
    echo "Database connection: FAILED - " . $e->getMessage() . "<br>";
}

echo "<h3>Department Check:</h3>";
$deanDepartmentCode = $_SESSION['selected_role']['department_code'] ?? null;
echo "Department Code: " . ($deanDepartmentCode ?? 'NOT_SET') . "<br>";

if ($deanDepartmentCode) {
    try {
        // Check if department exists
        $deptQuery = "SELECT id, name FROM departments WHERE department_code = ?";
        $deptStmt = $pdo->prepare($deptQuery);
        $deptStmt->execute([$deanDepartmentCode]);
        $deptResult = $deptStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($deptResult) {
            echo "Department found: " . $deptResult['name'] . " (ID: " . $deptResult['id'] . ")<br>";
            
            // Check programs for this department
            $programsQuery = "
                SELECT COUNT(*) as program_count
                FROM programs p
                JOIN departments d ON p.department_id = d.id
                WHERE d.department_code = ?
            ";
            $programsStmt = $pdo->prepare($programsQuery);
            $programsStmt->execute([$deanDepartmentCode]);
            $programsResult = $programsStmt->fetch(PDO::FETCH_ASSOC);
            
            echo "Programs count: " . $programsResult['program_count'] . "<br>";
            
            if ($programsResult['program_count'] > 0) {
                // Show actual programs
                $showProgramsQuery = "
                    SELECT p.id, p.program_code, p.program_name, p.major
                    FROM programs p
                    JOIN departments d ON p.department_id = d.id
                    WHERE d.department_code = ?
                    ORDER BY p.program_code ASC
                ";
                $showProgramsStmt = $pdo->prepare($showProgramsQuery);
                $showProgramsStmt->execute([$deanDepartmentCode]);
                $programs = $showProgramsStmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<h4>Programs found:</h4>";
                echo "<ul>";
                foreach ($programs as $program) {
                    echo "<li>" . $program['program_code'] . " - " . $program['program_name'] . ($program['major'] ? " (" . $program['major'] . ")" : "") . "</li>";
                }
                echo "</ul>";
            } else {
                echo "<strong>No programs found for this department!</strong><br>";
                echo "This is why you're seeing the 'No Programs Available' modal.<br>";
            }
        } else {
            echo "Department not found in database!<br>";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "No department code in session!<br>";
}

echo "<h3>All Departments in Database:</h3>";
try {
    $allDeptsQuery = "SELECT id, name, code FROM departments ORDER BY name";
    $allDeptsStmt = $pdo->prepare($allDeptsQuery);
    $allDeptsStmt->execute();
    $allDepts = $allDeptsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($allDepts) > 0) {
        echo "<ul>";
        foreach ($allDepts as $dept) {
            echo "<li>" . $dept['name'] . " (" . $dept['code'] . ")</li>";
        }
        echo "</ul>";
    } else {
        echo "No departments found in database!<br>";
    }
} catch (Exception $e) {
    echo "Error fetching departments: " . $e->getMessage() . "<br>";
}

echo "<h3>All Programs in Database:</h3>";
try {
    $allProgramsQuery = "
        SELECT p.id, p.program_code, p.program_name, p.major, d.name as department_name, d.code as department_code
        FROM programs p
        JOIN departments d ON p.department_id = d.id
        ORDER BY d.name, p.program_code
    ";
    $allProgramsStmt = $pdo->prepare($allProgramsQuery);
    $allProgramsStmt->execute();
    $allPrograms = $allProgramsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($allPrograms) > 0) {
        echo "<ul>";
        foreach ($allPrograms as $program) {
            echo "<li>" . $program['program_code'] . " - " . $program['program_name'] . " (" . $program['department_name'] . " - " . $program['department_code'] . ")" . ($program['major'] ? " - " . $program['major'] : "") . "</li>";
        }
        echo "</ul>";
    } else {
        echo "No programs found in database!<br>";
        echo "<strong>This explains why you're seeing the 'No Programs Available' modal.</strong><br>";
    }
} catch (Exception $e) {
    echo "Error fetching programs: " . $e->getMessage() . "<br>";
}
?>
