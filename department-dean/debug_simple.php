<?php
// Super simple debug test
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .debug-box { 
            background: #f0f0f0; 
            padding: 15px; 
            margin: 10px 0; 
            border-radius: 4px; 
            white-space: pre-wrap;
            font-family: monospace;
        }
        button { 
            background: #4CAF50; 
            color: white; 
            padding: 10px 20px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            margin: 5px;
        }
    </style>
</head>
<body>
    <h1>Super Simple Debug Test</h1>
    
    <button onclick="test1()">Test 1: Basic JavaScript</button>
    <button onclick="test2()">Test 2: Fetch API</button>
    <button onclick="test3()">Test 3: School Years API</button>
    <button onclick="test4()">Test 4: Programs API</button>
    <button onclick="clearDebug()">Clear</button>
    
    <div id="debug" class="debug-box">Ready to test...</div>

    <script>
        function log(msg) {
            const debug = document.getElementById('debug');
            const time = new Date().toLocaleTimeString();
            debug.textContent += `[${time}] ${msg}\n`;
        }
        
        function clearDebug() {
            document.getElementById('debug').textContent = 'Cleared...\n';
        }
        
        function test1() {
            log('✅ Test 1: Basic JavaScript is working');
        }
        
        function test2() {
            log('Testing fetch API...');
            fetch('api/get_school_years.php')
                .then(response => {
                    log(`Response status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    log(`Response data: ${JSON.stringify(data)}`);
                })
                .catch(error => {
                    log(`Error: ${error.message}`);
                });
        }
        
        function test3() {
            log('Testing School Years API...');
            fetch('api/get_school_years.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        log(`✅ School Years API works: ${data.school_years.length} years found`);
                    } else {
                        log(`❌ School Years API failed: ${data.message}`);
                    }
                })
                .catch(error => {
                    log(`❌ School Years API error: ${error.message}`);
                });
        }
        
        function test4() {
            log('Testing Programs API...');
            fetch('get_dean_programs.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        log(`✅ Programs API works: ${data.programs.length} programs found`);
                    } else {
                        log(`❌ Programs API failed: ${data.message}`);
                    }
                })
                .catch(error => {
                    log(`❌ Programs API error: ${error.message}`);
                });
        }
        
        // Auto-run basic test
        log('Debug test loaded successfully');
    </script>
</body>
</html>
