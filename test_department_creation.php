<?php
// test_department_creation.php
// Simple test file to test department creation functionality

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Department Creation</title>
    <style>
        body {
            font-family: 'TT Interphases', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .test-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        input[type="text"], input[type="color"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
        }
        button {
            background-color: #0f7a53;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin-right: 10px;
        }
        button:hover {
            background-color: #0a5a3f;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            font-weight: bold;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .test-data {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .test-data h3 {
            margin-top: 0;
            color: #495057;
        }
        .test-data button {
            background-color: #6c757d;
            margin: 5px;
            padding: 8px 16px;
            font-size: 14px;
        }
        .test-data button:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>Test Department Creation</h1>
        
        <div class="test-data">
            <h3>Quick Test Data</h3>
            <button onclick="fillTestData('CS', 'Computer Science', '#FF6B6B')">Computer Science</button>
            <button onclick="fillTestData('IT', 'Information Technology', '#4ECDC4')">Information Technology</button>
            <button onclick="fillTestData('ENG', 'Engineering', '#45B7D1')">Engineering</button>
            <button onclick="fillTestData('BUS', 'Business Administration', '#96CEB4')">Business Admin</button>
            <button onclick="fillTestData('EDU', 'Education', '#FFEAA7')">Education</button>
        </div>

        <form id="testForm">
            <div class="form-group">
                <label for="department_code">Department Code:</label>
                <input type="text" id="department_code" name="department_code" required>
            </div>
            
            <div class="form-group">
                <label for="department_name">Department Name:</label>
                <input type="text" id="department_name" name="department_name" required>
            </div>
            
            <div class="form-group">
                <label for="color_code">Color Code:</label>
                <input type="color" id="colorPicker" value="#4A7DFF">
                <input type="text" id="color_code" name="color_code" value="#4A7DFF" required>
            </div>
            
            <button type="submit">Create Department</button>
            <button type="button" onclick="clearForm()">Clear Form</button>
        </form>
        
        <div id="result"></div>
    </div>

    <script>
        // Color picker functionality
        const colorPicker = document.getElementById('colorPicker');
        const colorCode = document.getElementById('color_code');
        
        colorPicker.addEventListener('input', function() {
            colorCode.value = this.value;
        });
        
        colorCode.addEventListener('input', function() {
            if (/^#[0-9A-F]{6}$/i.test(this.value)) {
                colorPicker.value = this.value;
            }
        });

        // Test data functions
        function fillTestData(code, name, color) {
            document.getElementById('department_code').value = code;
            document.getElementById('department_name').value = name;
            document.getElementById('color_code').value = color;
            document.getElementById('colorPicker').value = color;
        }

        function clearForm() {
            document.getElementById('testForm').reset();
            document.getElementById('color_code').value = '#4A7DFF';
            document.getElementById('colorPicker').value = '#4A7DFF';
            document.getElementById('result').innerHTML = '';
        }

        // Form submission
        document.getElementById('testForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Show loading
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<div class="result">Creating department...</div>';
            
            fetch('./super_admin-mis/process_add_department.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="result success">
                            ✅ Success! ${data.message}<br>
                            Department ID: ${data.department.id}<br>
                            Code: ${data.department.code}<br>
                            Name: ${data.department.name}<br>
                            Color: ${data.department.color}
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="result error">
                            ❌ Error: ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                resultDiv.innerHTML = `
                    <div class="result error">
                        ❌ Network Error: ${error.message}
                    </div>
                `;
            });
        });
    </script>
</body>
</html>
