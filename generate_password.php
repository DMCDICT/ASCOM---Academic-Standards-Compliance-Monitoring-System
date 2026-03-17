<?php
// Password Hash Generator for ASCOM Monitoring System
// Use this script to generate password hashes for your users

echo "<h2>Password Hash Generator</h2>";

if ($_POST) {
    $password = $_POST['password'] ?? '';
    if (!empty($password)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        echo "<p><strong>Password:</strong> $password</p>";
        echo "<p><strong>Hash:</strong> $hash</p>";
        echo "<p><strong>SQL Update Command:</strong></p>";
        echo "<code>UPDATE users SET password_hash = '$hash' WHERE email = 'user@example.com';</code>";
    }
}
?>

<form method="POST">
    <label for="password">Enter Password:</label><br>
    <input type="text" name="password" id="password" required><br><br>
    <button type="submit">Generate Hash</button>
</form>

<hr>

<h3>Quick Password Examples:</h3>
<ul>
    <li><strong>dean123</strong> → <?php echo password_hash('dean123', PASSWORD_DEFAULT); ?></li>
    <li><strong>teacher123</strong> → <?php echo password_hash('teacher123', PASSWORD_DEFAULT); ?></li>
    <li><strong>librarian123</strong> → <?php echo password_hash('librarian123', PASSWORD_DEFAULT); ?></li>
    <li><strong>qa123</strong> → <?php echo password_hash('qa123', PASSWORD_DEFAULT); ?></li>
</ul>

<h3>How to Update Passwords in Database:</h3>
<ol>
    <li>Run this script in your browser</li>
    <li>Enter the desired password</li>
    <li>Copy the generated hash</li>
    <li>Run the SQL command in your database</li>
</ol>

<h3>Example SQL Commands:</h3>
<pre>
-- Update Department Dean password
UPDATE users SET password_hash = '<?php echo password_hash('dean123', PASSWORD_DEFAULT); ?>' 
WHERE email = 'dean.cse@sccpag.edu.ph';

-- Update Teacher password  
UPDATE users SET password_hash = '<?php echo password_hash('teacher123', PASSWORD_DEFAULT); ?>' 
WHERE email = 'teacher1@sccpag.edu.ph';

-- Update Librarian password
UPDATE users SET password_hash = '<?php echo password_hash('librarian123', PASSWORD_DEFAULT); ?>' 
WHERE email = 'librarian@sccpag.edu.ph';

-- Update Admin QA password
UPDATE users SET password_hash = '<?php echo password_hash('qa123', PASSWORD_DEFAULT); ?>' 
WHERE email = 'qa.admin@sccpag.edu.ph';
</pre> 