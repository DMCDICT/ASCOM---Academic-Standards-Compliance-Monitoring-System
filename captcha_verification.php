<?php
// captcha_verification.php
// CAPTCHA verification for inactive users

session_start();

// Generate a simple math CAPTCHA
function generateCaptcha() {
    $num1 = rand(1, 10);
    $num2 = rand(1, 10);
    $operators = ['+', '-', '*'];
    $operator = $operators[array_rand($operators)];
    
    switch ($operator) {
        case '+':
            $answer = $num1 + $num2;
            break;
        case '-':
            $answer = $num1 - $num2;
            break;
        case '*':
            $answer = $num1 * $num2;
            break;
    }
    
    return [
        'question' => "$num1 $operator $num2 = ?",
        'answer' => $answer
    ];
}

// Handle CAPTCHA verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userAnswer = $_POST['captcha_answer'] ?? '';
    $correctAnswer = $_SESSION['captcha_answer'] ?? '';
    $username = $_POST['username'] ?? '';
    
    if ($userAnswer == $correctAnswer) {
        // CAPTCHA passed, allow login to proceed
        $_SESSION['captcha_verified'] = true;
        $_SESSION['captcha_username'] = $username;
        $_SESSION['captcha_verification_time'] = time();
        
        echo json_encode([
            'success' => true,
            'message' => 'CAPTCHA verification successful'
        ]);
    } else {
        // CAPTCHA failed
        echo json_encode([
            'success' => false,
            'message' => 'Incorrect answer. Please try again.'
        ]);
    }
    exit;
}

// Generate new CAPTCHA
$captcha = generateCaptcha();
$_SESSION['captcha_answer'] = $captcha['answer'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAPTCHA Verification - ASCOM Monitoring System</title>
    <style>
        @font-face {
            font-family: 'TT Interphases';
            src: url('./src/assets/fonts/tt-interphases/TT Interphases Pro Trial Regular.ttf') format('truetype');
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'TT Interphases', sans-serif;
        }
        
        body {
            background: #0C4B34;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .captcha-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }
        
        .captcha-header {
            margin-bottom: 30px;
        }
        
        .captcha-header h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .captcha-header p {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .captcha-question {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 2px solid #e9ecef;
        }
        
        .captcha-question h2 {
            color: #333;
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .captcha-question .math-problem {
            font-size: 24px;
            font-weight: bold;
            color: #495057;
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            display: inline-block;
            min-width: 120px;
        }
        
        .captcha-input {
            margin-bottom: 20px;
        }
        
        .captcha-input input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            text-align: center;
            transition: border-color 0.3s;
        }
        
        .captcha-input input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .captcha-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .error-message {
            color: #dc3545;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            display: none;
        }
        
        .success-message {
            color: #155724;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            display: none;
        }
        
        .robot-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="captcha-container">
        <div class="captcha-header">
            <div class="robot-icon">🤖</div>
            <h1>Security Verification</h1>
            <p>Your account has been inactive for over 30 days. Please complete this verification to prove you're not a robot.</p>
        </div>
        
        <div class="error-message" id="errorMessage"></div>
        <div class="success-message" id="successMessage"></div>
        
        <form id="captchaForm">
            <div class="captcha-question">
                <h2>Solve this math problem:</h2>
                <div class="math-problem"><?php echo $captcha['question']; ?></div>
            </div>
            
            <div class="captcha-input">
                <input type="number" id="captchaAnswer" name="captcha_answer" placeholder="Enter your answer" required>
            </div>
            
            <div class="captcha-buttons">
                <button type="submit" class="btn btn-primary">Verify</button>
                <button type="button" class="btn btn-secondary" onclick="generateNewCaptcha()">New Question</button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('captchaForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const answer = document.getElementById('captchaAnswer').value;
            const username = new URLSearchParams(window.location.search).get('username') || '';
            
            fetch('captcha_verification.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `captcha_answer=${answer}&username=${encodeURIComponent(username)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('successMessage').textContent = data.message;
                    document.getElementById('successMessage').style.display = 'block';
                    document.getElementById('errorMessage').style.display = 'none';
                    
                    // Redirect to role selection page directly after captcha verification
                    setTimeout(() => {
                        window.location.href = `role_selection.php?captcha_verified=true&username=${encodeURIComponent(username)}`;
                    }, 1500);
                } else {
                    document.getElementById('errorMessage').textContent = data.message;
                    document.getElementById('errorMessage').style.display = 'block';
                    document.getElementById('successMessage').style.display = 'none';
                    document.getElementById('captchaAnswer').value = '';
                    document.getElementById('captchaAnswer').focus();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('errorMessage').textContent = 'An error occurred. Please try again.';
                document.getElementById('errorMessage').style.display = 'block';
            });
        });
        
        function generateNewCaptcha() {
            window.location.reload();
        }
        
        // Auto-focus on input
        document.getElementById('captchaAnswer').focus();
    </script>
</body>
</html> 