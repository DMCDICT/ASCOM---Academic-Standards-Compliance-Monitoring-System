<?php
// Step by step test to identify the exact issue
?>
<!DOCTYPE html>
<html>
<head>
    <title>Step by Step Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .step { 
            background: #f9f9f9; 
            padding: 15px; 
            margin: 10px 0; 
            border-left: 4px solid #4CAF50; 
            border-radius: 4px;
        }
        .error { border-left-color: #f44336; background: #ffebee; }
        .success { border-left-color: #4CAF50; background: #e8f5e8; }
        button { 
            background: #4CAF50; 
            color: white; 
            padding: 10px 20px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            margin: 5px;
        }
        .debug { 
            background: #f0f0f0; 
            padding: 10px; 
            margin: 10px 0; 
            border-radius: 4px; 
            white-space: pre-wrap;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <h1>Step by Step Debug Test</h1>
    
    <div class="step">
        <h3>Step 1: Basic JavaScript Test</h3>
        <button onclick="testStep1()">Test JavaScript</button>
        <div id="step1" class="debug">Click button to test...</div>
    </div>
    
    <div class="step">
        <h3>Step 2: Fetch API Test</h3>
        <button onclick="testStep2()">Test Fetch</button>
        <div id="step2" class="debug">Click button to test...</div>
    </div>
    
    <div class="step">
        <h3>Step 3: School Years API Test</h3>
        <button onclick="testStep3()">Test School Years</button>
        <div id="step3" class="debug">Click button to test...</div>
    </div>
    
    <div class="step">
        <h3>Step 4: Programs API Test</h3>
        <button onclick="testStep4()">Test Programs</button>
        <div id="step4" class="debug">Click button to test...</div>
    </div>
    
    <div class="step">
        <h3>Step 5: Modal Test</h3>
        <button onclick="testStep5()">Test Modal</button>
        <div id="step5" class="debug">Click button to test...</div>
    </div>

    <!-- Simple Modal -->
    <div id="testModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="background: white; margin: 50px auto; padding: 20px; width: 400px; border-radius: 8px;">
            <h2>Test Modal</h2>
            <p>This is a test modal.</p>
            <button onclick="closeTestModal()">Close</button>
        </div>
    </div>

    <script>
        function log(stepId, msg, isError = false) {
            const element = document.getElementById(stepId);
            const time = new Date().toLocaleTimeString();
            const className = isError ? 'error' : 'success';
            element.innerHTML += `<div class="${className}">[${time}] ${msg}</div>`;
        }
        
        function testStep1() {
            const step1 = document.getElementById('step1');
            step1.innerHTML = '';
            
            try {
                log('step1', '✅ JavaScript is working');
                log('step1', '✅ Console.log is working');
                log('step1', '✅ DOM manipulation is working');
                log('step1', '✅ Event handlers are working');
            } catch (error) {
                log('step1', `❌ JavaScript error: ${error.message}`, true);
            }
        }
        
        function testStep2() {
            const step2 = document.getElementById('step2');
            step2.innerHTML = '';
            
            try {
                log('step2', 'Testing fetch API...');
                
                if (typeof fetch === 'undefined') {
                    log('step2', '❌ Fetch API not available', true);
                    return;
                }
                
                log('step2', '✅ Fetch API is available');
                
                // Test a simple fetch
                fetch('api/get_school_years.php')
                    .then(response => {
                        log('step2', `✅ Fetch successful, status: ${response.status}`);
                        return response.text();
                    })
                    .then(text => {
                        log('step2', `✅ Response received: ${text.substring(0, 100)}...`);
                    })
                    .catch(error => {
                        log('step2', `❌ Fetch error: ${error.message}`, true);
                    });
                    
            } catch (error) {
                log('step2', `❌ Error: ${error.message}`, true);
            }
        }
        
        function testStep3() {
            const step3 = document.getElementById('step3');
            step3.innerHTML = '';
            
            log('step3', 'Testing School Years API...');
            
            fetch('api/get_school_years.php')
                .then(response => {
                    log('step3', `Response status: ${response.status}`);
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    log('step3', `Response data: ${JSON.stringify(data)}`);
                    if (data.success) {
                        log('step3', `✅ School Years API works: ${data.school_years.length} years found`);
                    } else {
                        log('step3', `❌ School Years API failed: ${data.message}`, true);
                    }
                })
                .catch(error => {
                    log('step3', `❌ School Years API error: ${error.message}`, true);
                });
        }
        
        function testStep4() {
            const step4 = document.getElementById('step4');
            step4.innerHTML = '';
            
            log('step4', 'Testing Programs API...');
            
            fetch('get_dean_programs.php')
                .then(response => {
                    log('step4', `Response status: ${response.status}`);
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    log('step4', `Response data: ${JSON.stringify(data)}`);
                    if (data.success) {
                        log('step4', `✅ Programs API works: ${data.programs.length} programs found`);
                    } else {
                        log('step4', `❌ Programs API failed: ${data.message}`, true);
                    }
                })
                .catch(error => {
                    log('step4', `❌ Programs API error: ${error.message}`, true);
                });
        }
        
        function testStep5() {
            const step5 = document.getElementById('step5');
            step5.innerHTML = '';
            
            try {
                log('step5', 'Testing modal functionality...');
                
                const modal = document.getElementById('testModal');
                modal.style.display = 'block';
                
                log('step5', '✅ Modal opened successfully');
                
                setTimeout(() => {
                    modal.style.display = 'none';
                    log('step5', '✅ Modal closed successfully');
                }, 2000);
                
            } catch (error) {
                log('step5', `❌ Modal error: ${error.message}`, true);
            }
        }
        
        function closeTestModal() {
            document.getElementById('testModal').style.display = 'none';
        }
        
        // Auto-run step 1
        setTimeout(() => {
            testStep1();
        }, 1000);
    </script>
</body>
</html>
