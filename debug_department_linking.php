<?php
// Debug script for department linking in program creation
session_start();
require_once 'department-dean/includes/db_connection.php';

header('Content-Type: text/plain');

echo "=== Department Linking Debug ===\n\n";

// Check session data
echo "Session Data:\n";
echo "=============\n";
echo "selected_role: " . json_encode($_SESSION['selected_role'] ?? 'NOT_SET') . "\n";
echo "dean_department_id: " . ($_SESSION['dean_department_id'] ?? 'NOT_SET') . "\n";
echo "user_id: " . ($_SESSION['user_id'] ?? 'NOT_SET') . "\n\n";

// Check departments table
echo "Departments in Database:\n";
echo "========================\n";
try {
    $query = "SELECT * FROM departments";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($departments as $dept) {
        echo "- ID: {$dept['id']}, Code: {$dept['department_code']}, Name: {$dept['department_name']}, Dean User ID: " . ($dept['dean_user_id'] ?? 'NULL') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test the department ID retrieval logic
echo "Testing Department ID Retrieval:\n";
echo "================================\n";

$deanDepartmentId = null;

// Method 1: Check dean_department_id in session
if (isset($_SESSION['dean_department_id'])) {
    $deanDepartmentId = $_SESSION['dean_department_id'];
    echo "Method 1 - dean_department_id from session: $deanDepartmentId\n";
}

// Method 2: Check selected_role department_id
if (!$deanDepartmentId && isset($_SESSION['selected_role']['department_id'])) {
    $deanDepartmentId = $_SESSION['selected_role']['department_id'];
    echo "Method 2 - selected_role department_id: $deanDepartmentId\n";
}

// Method 3: Get from departments table using department_code
if (!$deanDepartmentId && isset($_SESSION['selected_role']['department_code'])) {
    $deptCode = $_SESSION['selected_role']['department_code'];
    echo "Method 3 - Looking up department by code: $deptCode\n";
    
    try {
        $deptQuery = "SELECT id FROM departments WHERE department_code = ?";
        $deptStmt = $pdo->prepare($deptQuery);
        $deptStmt->execute([$deptCode]);
        $deptResult = $deptStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($deptResult) {
            $deanDepartmentId = $deptResult['id'];
            echo "Method 3 - Found department ID: $deanDepartmentId\n";
        } else {
            echo "Method 3 - No department found with code: $deptCode\n";
        }
    } catch (Exception $e) {
        echo "Method 3 - Error: " . $e->getMessage() . "\n";
    }
}

// Method 4: Get from departments table using dean_user_id
if (!$deanDepartmentId && isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    echo "Method 4 - Looking up department by dean_user_id: $userId\n";
    
    try {
        $deptQuery = "SELECT id FROM departments WHERE dean_user_id = ?";
        $deptStmt = $pdo->prepare($deptQuery);
        $deptStmt->execute([$userId]);
        $deptResult = $deptStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($deptResult) {
            $deanDepartmentId = $deptResult['id'];
            echo "Method 4 - Found department ID: $deanDepartmentId\n";
        } else {
            echo "Method 4 - No department found with dean_user_id: $userId\n";
        }
    } catch (Exception $e) {
        echo "Method 4 - Error: " . $e->getMessage() . "\n";
    }
}

echo "\nFinal Department ID: " . ($deanDepartmentId ?? 'NOT_FOUND') . "\n";

// Check existing programs
echo "\nExisting Programs:\n";
echo "==================\n";
try {
    $query = "SELECT p.*, d.department_code, d.department_name FROM programs p LEFT JOIN departments d ON p.department_id = d.id";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($programs as $program) {
        echo "- {$program['program_code']}: {$program['program_name']} (Dept: {$program['department_code']} - {$program['department_name']}, Dept ID: {$program['department_id']})\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Debug Complete ===\n";
?>
