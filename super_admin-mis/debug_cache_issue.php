<?php
// debug_cache_issue.php
// Debug script to check cache and function loading issues

// Force no caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Debug Cache Issue</title>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .debug-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #d1ecf1; color: #0c5460; }
        .refresh-button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            margin-left: 10px;
            transition: background-color 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
            position: relative;
        }
        .refresh-status-icon {
            font-size: 14px;
            margin-left: 4px;
            opacity: 0;
            transition: opacity 0.3s ease;
            animation: spin 1s linear infinite;
        }
        .refresh-status-icon.show {
            opacity: 1;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <h1>Debug Cache and Function Loading Issues</h1>
    
    <div class="debug-section info">
        <h2>Current Timestamp</h2>
        <p>Page loaded at: <?php echo date('Y-m-d H:i:s'); ?></p>
        <p>CSS Version: v1.3</p>
        <p>JS Version: v1.3</p>
    </div>
    
    <div class="debug-section">
        <h2>Refresh Button Test</h2>
        <button class="refresh-button" onclick="testRefresh()">
            🔄 Refresh
            <span id="refreshStatusIcon" class="refresh-status-icon" style="display: none;">⏳</span>
        </button>
        <div id="refreshResults"></div>
    </div>
    
    <div class="debug-section">
        <h2>Function Availability Check</h2>
        <div id="functionCheck"></div>
    </div>
    
    <div class="debug-section">
        <h2>Console Log</h2>
        <div id="consoleLog"></div>
    </div>

    <script>
        // Override console.log to capture output
        const originalLog = console.log;
        const originalError = console.error;
        const logDiv = document.getElementById('consoleLog');
        
        console.log = function(...args) {
            originalLog.apply(console, args);
            logDiv.innerHTML += '<div class="info">LOG: ' + args.join(' ') + '</div>';
        };
        
        console.error = function(...args) {
            originalError.apply(console, args);
            logDiv.innerHTML += '<div class="error">ERROR: ' + args.join(' ') + '</div>';
        };
        
        function testRefresh() {
            const results = document.getElementById('refreshResults');
            const icon = document.getElementById('refreshStatusIcon');
            
            results.innerHTML = '<div class="info">Testing refresh functionality...</div>';
            
            // Test icon functionality
            if (icon) {
                icon.style.display = 'inline';
                icon.classList.add('show');
                results.innerHTML += '<div class="success">✅ Refresh icon appeared</div>';
                
                // Hide after 2 seconds
                setTimeout(() => {
                    icon.classList.remove('show');
                    setTimeout(() => {
                        icon.style.display = 'none';
                    }, 300);
                }, 2000);
            } else {
                results.innerHTML += '<div class="error">❌ Refresh icon not found</div>';
            }
            
            // Test function availability
            if (typeof window.manualRefreshUserList === 'function') {
                results.innerHTML += '<div class="success">✅ manualRefreshUserList function exists</div>';
                try {
                    window.manualRefreshUserList();
                    results.innerHTML += '<div class="success">✅ Function called successfully</div>';
                } catch (error) {
                    results.innerHTML += '<div class="error">❌ Error calling function: ' + error.message + '</div>';
                }
            } else {
                results.innerHTML += '<div class="error">❌ manualRefreshUserList function does not exist</div>';
            }
        }
        
        function checkFunctions() {
            const checkDiv = document.getElementById('functionCheck');
            const functions = [
                'manualRefreshUserList',
                'refreshUserList',
                'openUserDetailsModal',
                'showStatusInfo'
            ];
            
            let results = '';
            let available = 0;
            
            functions.forEach(func => {
                if (typeof window[func] === 'function') {
                    results += '<div class="success">✅ ' + func + ' is available</div>';
                    available++;
                } else {
                    results += '<div class="error">❌ ' + func + ' is NOT available</div>';
                }
            });
            
            results += '<div class="info">Available: ' + available + '/' + functions.length + ' functions</div>';
            checkDiv.innerHTML = results;
        }
        
        // Run checks when page loads
        window.addEventListener('DOMContentLoaded', function() {
            console.log('Page loaded, checking functions...');
            checkFunctions();
        });
        
        // Check again after a delay to see if functions load
        setTimeout(() => {
            console.log('Delayed function check...');
            checkFunctions();
        }, 1000);
    </script>
    
    <!-- Include the JavaScript files with cache busting -->
    <script src="./scripts/user-account-management.js?v=1.3&t=<?php echo time(); ?>"></script>
</body>
</html> 