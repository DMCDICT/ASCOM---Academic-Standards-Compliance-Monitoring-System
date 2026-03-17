<?php
// update_selected_term.php
// Updates the server-side session with the selected term ID

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Get the term ID from POST data
    $termId = $_POST['term_id'] ?? null;
    
    if ($termId === null) {
        throw new Exception('No term ID provided');
    }
    
    // Update the session with the selected term ID
    $_SESSION['selectedTermId'] = $termId;
    
    // Log the update for debugging
    error_log("Session updated - selectedTermId: " . $termId);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Session updated successfully',
        'selectedTermId' => $termId
    ]);
    
} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Error updating session: ' . $e->getMessage()
    ]);
}
?>
