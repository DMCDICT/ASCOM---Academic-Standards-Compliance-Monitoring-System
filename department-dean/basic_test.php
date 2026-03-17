<?php
// BASIC TEST - Absolute minimum to identify the issue
?>
<!DOCTYPE html>
<html>
<head>
    <title>Basic Test</title>
</head>
<body>
    <h1>Basic Test</h1>
    <p>If you can see this, PHP is working.</p>
    
    <button onclick="test1()">Test 1: JavaScript</button>
    <button onclick="test2()">Test 2: API</button>
    
    <div id="result" style="background: #f0f0f0; padding: 10px; margin: 10px 0; white-space: pre-wrap;"></div>

    <script>
        function test1() {
            document.getElementById('result').textContent = 'JavaScript is working!';
        }
        
        function test2() {
            document.getElementById('result').textContent = 'Testing API...';
            
            fetch('api/get_school_years.php')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('result').textContent = 'API Response: ' + data;
                })
                .catch(error => {
                    document.getElementById('result').textContent = 'API Error: ' + error.message;
                });
        }
    </script>
</body>
</html>
