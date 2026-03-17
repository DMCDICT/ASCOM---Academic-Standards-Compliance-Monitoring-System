<!DOCTYPE html>
<html>
<head>
    <title>Modal Include Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-box { background: white; margin: 50px auto; padding: 20px; width: 500px; border-radius: 8px; }
        .test-btn { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .test-btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>Modal Include Test</h1>
    
    <p>This page tests if the edit course modal can be included properly.</p>
    
    <button class="test-btn" onclick="testModal()">Test Modal</button>
    
    <div id="test-results" style="margin-top: 20px; padding: 10px; background: #f8f9fa; border-radius: 4px;">
        <h3>Test Results:</h3>
        <div id="results-content">Click "Test Modal" to check...</div>
    </div>

    <!-- Include the modal -->
    <?php 
    echo "<!-- DEBUG: About to include modal -->";
    include 'modals/edit_course_modal.php'; 
    echo "<!-- DEBUG: Modal include completed -->";
    ?>

    <script>
        function testModal() {
            const resultsDiv = document.getElementById('results-content');
            let results = [];
            
            // Test 1: Check if modal element exists
            const modal = document.getElementById('editCourseModal');
            if (modal) {
                results.push('✅ Modal element found in DOM');
                results.push('Modal classes: ' + modal.classList.toString());
                results.push('Modal style display: ' + modal.style.display);
            } else {
                results.push('❌ Modal element NOT found in DOM');
            }
            
            // Test 2: Try to open modal
            if (modal) {
                modal.style.display = 'flex';
                results.push('✅ Modal display set to flex');
                
                // Test 3: Check if modal is visible
                setTimeout(() => {
                    const computedStyle = window.getComputedStyle(modal);
                    results.push('Computed display: ' + computedStyle.display);
                    results.push('Computed visibility: ' + computedStyle.visibility);
                    
                    // Close modal
                    modal.style.display = 'none';
                    results.push('✅ Modal closed');
                    
                    resultsDiv.innerHTML = results.join('<br>');
                }, 100);
            } else {
                resultsDiv.innerHTML = results.join('<br>');
            }
        }
        
        // Auto-test on page load
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('editCourseModal');
            console.log('Modal on load:', modal);
        });
    </script>
</body>
</html>
