<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .security-note {
            background: #e8f5e9;
            padding: 12px;
            border-radius: 8px;
            font-size: 12px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>👨‍🏫 Teacher Login</h1>
    <div class="login-box">
        <form method="POST">
            <input type="text" name="teacher_id" placeholder="Teacher ID (e.g., TCH001)" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>
        
        <p style="margin-top: 15px; text-align: center;">
            <a href="teacher_forgot_password.php">🔐 Forgot Password?</a> |<br><br>
            <a href="index.php">← Admin Login</a> |
            <a href="parent_login.php">👪 Parent Login</a>
        </p>
        
        <div class="security-note">
            🔒 <strong>Security Tip:</strong> Never share your password. 
            Contact admin if you suspect unauthorized access.
        </div>
        
        <?php
        if(isset($_POST['login'])){
            $tid = $_POST['teacher_id'];
            $pass = md5($_POST['password']);
            
            // Check if account is locked
            $teacher_check = $conn->query("SELECT * FROM teachers WHERE teacher_id='$tid'");
            if($teacher_check->num_rows == 1){
                $teacher = $teacher_check->fetch_assoc();
                
                if($teacher['account_locked_until'] && strtotime($teacher['account_locked_until']) > time()){
                    echo "<p style='color:red'>⛔ Account locked until " . date('H:i:s', strtotime($teacher['account_locked_until'])) . ". Try again later.</p>";
                } else {
                    $result = $conn->query("SELECT * FROM teachers WHERE teacher_id='$tid' AND password='$pass'");
                    
                    if($result->num_rows == 1){
                        // Reset failed attempts on successful login
                        $conn->query("UPDATE teachers SET failed_login_attempts = 0, account_locked_until = NULL WHERE teacher_id='$tid'");
                        
                        $_SESSION['teacher'] = $tid;
                        $_SESSION['teacher_data'] = $result->fetch_assoc();
                        echo "<script>window.location.href='teacher_dashboard.php';</script>";
                    } else {
                        // Increment failed attempts
                        $attempts = $teacher['failed_login_attempts'] + 1;
                        if($attempts >= 5){
                            $lock_until = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                            $conn->query("UPDATE teachers SET failed_login_attempts = $attempts, account_locked_until = '$lock_until' WHERE teacher_id='$tid'");
                            echo "<p style='color:red'>⛔ Too many failed attempts. Account locked for 15 minutes.</p>";
                        } else {
                            $conn->query("UPDATE teachers SET failed_login_attempts = $attempts WHERE teacher_id='$tid'");
                            echo "<p style='color:red'>❌ Invalid Teacher ID or Password. Attempts: $attempts/5</p>";
                        }
                    }
                }
            } else {
                echo "<p style='color:red'>❌ Teacher ID not found</p>";
            }
        }
        ?>
    </div>
</div>
</body>
</html>