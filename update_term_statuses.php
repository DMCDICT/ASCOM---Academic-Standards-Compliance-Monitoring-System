<?php
// update_term_statuses.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Update Term Statuses Based on Current Date</h2>";

// Check if status column exists
$status_exists = $conn->query("SHOW COLUMNS FROM school_terms LIKE 'status'");
if (!$status_exists || $status_exists->num_rows === 0) {
    echo "❌ 'status' column does not exist in school_terms table<br>";
    echo "<p><a href='fix_school_terms_table.php' style='background-color: #FF9800; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔧 Fix Table Structure</a></p>";
    exit;
}

echo "✅ 'status' column exists<br>";

// Get current date
$current_date = date('Y-m-d');
echo "<p><strong>Current Date:</strong> " . $current_date . "</p>";

// Get all terms
$terms = $conn->query("SELECT id, title, start_date, end_date, status FROM school_terms ORDER BY start_date");
if (!$terms) {
    echo "❌ Could not fetch terms: " . $conn->error . "<br>";
    exit;
}

echo "<h3>Current Terms and Their Status:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background-color: #f0f0f0;'>";
echo "<th>ID</th><th>Title</th><th>Start Date</th><th>End Date</th><th>Current Status</th><th>Should Be</th><th>Action</th>";
echo "</tr>";

$updated_count = 0;
$no_change_count = 0;

while ($term = $terms->fetch_assoc()) {
    // Determine what the status should be
    $should_be_active = ($current_date >= $term['start_date'] && $current_date <= $term['end_date']);
    $should_be_status = $should_be_active ? 'Active' : 'Inactive';
    
    // Check if status needs to be updated
    $needs_update = ($term['status'] !== $should_be_status);
    
    echo "<tr>";
    echo "<td>" . $term['id'] . "</td>";
    echo "<td>" . $term['title'] . "</td>";
    echo "<td>" . $term['start_date'] . "</td>";
    echo "<td>" . $term['end_date'] . "</td>";
    echo "<td>" . $term['status'] . "</td>";
    echo "<td>" . $should_be_status . "</td>";
    
    if ($needs_update) {
        echo "<td style='color: orange;'>🔄 Update needed</td>";
    } else {
        echo "<td style='color: green;'>✅ Correct</td>";
    }
    
    echo "</tr>";
    
    // Update the status if needed
    if ($needs_update) {
        $update_sql = "UPDATE school_terms SET status = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param('si', $should_be_status, $term['id']);
        
        if ($update_stmt->execute()) {
            $updated_count++;
        } else {
            echo "<tr><td colspan='7' style='color: red;'>❌ Failed to update term ID " . $term['id'] . ": " . $update_stmt->error . "</td></tr>";
        }
        $update_stmt->close();
    } else {
        $no_change_count++;
    }
}

echo "</table>";

echo "<h3>Summary:</h3>";
echo "<p>✅ <strong>Terms with correct status:</strong> " . $no_change_count . "</p>";
echo "<p>🔄 <strong>Terms updated:</strong> " . $updated_count . "</p>";

if ($updated_count > 0) {
    echo "<p style='color: green;'>🎉 Successfully updated " . $updated_count . " term(s)!</p>";
} else {
    echo "<p style='color: blue;'>ℹ️ All terms already have the correct status.</p>";
}

echo "<h3>Status Logic:</h3>";
echo "<ul>";
echo "<li><strong>Active:</strong> Current date is between or equal to start_date and end_date</li>";
echo "<li><strong>Inactive:</strong> Current date is before start_date or after end_date</li>";
echo "</ul>";

echo "<h3>Next Steps:</h3>";
echo "<p><a href='test_api_fix.php' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Test Add Term API</a></p>";
echo "<p><a href='super_admin-mis/content.php?page=school-calendar&v=8' style='background-color: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Test Add Term Modal</a></p>";

$conn->close();
?>
