<?php
// test_school_years.php - Debug what's in the school_years table

try {
    $host = 'localhost';
    $dbname = 'ascom_db';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>School Years Table Debug</h2>";
    
    // Get all columns first
    $query = "DESCRIBE school_years";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Table Structure:</h3>";
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
    
    // Get all data
    $query = "SELECT * FROM school_years ORDER BY id DESC LIMIT 10";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $schoolYears = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Table Data:</h3>";
    echo "<pre>";
    print_r($schoolYears);
    echo "</pre>";
    
    echo "<h3>HTML Options:</h3>";
    foreach ($schoolYears as $year) {
        $id = $year['id'];
        $label = $year['school_year_label'] ?? $year['school_year'] ?? 'Unknown';
        echo "<option value='$id'>$label</option><br>";
    }
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
