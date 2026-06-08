<?php include 'config.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password - Teacher</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .reset-container {
            max-width: 500px;
            margin: 50px auto;
        }
        .steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .step {
            text-align: center;
            flex: 1;
            position: relative;
        }
        .step-number {
            width: 35px;
            height: 35px;
            background: #ddd;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .step.active .step-number {
            background: #667eea;
            color: white;
        }
        .step.completed .step-number {
            background: #28a745;
            color: white;
        }
        .step-label {
            font-size: 12px;
        }
    </style>
</head>
<body>
<div class="container reset-container">
    <h2>🔐 Forgot Password?</h2>
    
    <div class="steps">
        <div class="step <?= !isset($_GET['step']) || $_GET['step'] == 1 ? 'active' : '' ?>">
            <div class="step-number">1</div>
            <div class="step-label">Enter Email</div>
        </div>
        <div class="step <?= isset($_GET['step']) && $_GET['step'] == 2 ? 'active' : '' ?>">
            <div class="step-number">2</div>
            <div class="step-label">Verify OTP</div>
        </div>
        <div class="step <?= isset($_GET['step']) && $_GET['step'] == 3 ? 'active' : '' ?>">
            <div class="step-number">3</div>
            <div class="step-label">New Password</div>
        </div>
    </div>
    
    <?php
    $step = $_GET['step'] ?? 1;
    $message = '';
    $error = '';
    
    // Step 1: Request reset
    if($step == 1 && isset($_POST['send_otp'])){
        $email = $_POST['email'];
        $teacher = $conn->query("SELECT * FROM teachers WHERE email = '$email'");
        
        if($teacher->num_rows == 1){
            $teacher_data = $teacher->fetch_assoc();
            $otp = rand(100000, 999999);
            $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            
            // Store OTP in session
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_otp'] = $otp;
            $_SESSION['reset_expires'] = $expires;
            
            // In production, send email here
            // mail($email, "Password Reset OTP", "Your OTP is: $otp", "From: school@system.com");
            
            $message = "✅ OTP sent to $email. (Demo OTP: $otp)";
            echo "<script>setTimeout(function(){ window.location.href='?step=2'; }, 2000);</script>";
        } else {
            $error = "❌ No teacher found with this email address!";
        }
    }
    
    // Step 2: Verify OTP
    if($step == 2 && isset($_POST['verify_otp'])){
        $entered_otp = $_POST['otp'];
        
        if($entered_otp == $_SESSION['reset_otp'] && strtotime('now') < strtotime($_SESSION['reset_expires'])){
            $message = "✅ OTP verified! Set your new password.";
            echo "<script>setTimeout(function(){ window.location.href='?step=3'; }, 1500);</script>";
        } else {
            $error = "❌ Invalid or expired OTP!";
        }
    }
    
    // Step 3: Set new password
    if($step == 3 && isset($_POST['reset_password'])){
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if(strlen($new_password) < 6){
            $error = "❌ Password must be at least 6 characters!";
        } elseif($new_password != $confirm_password){
            $error = "❌ Passwords do not match!";
        } else {
            $hashed_password = md5($new_password);
            $email = $_SESSION['reset_email'];
            $conn->query("UPDATE teachers SET password = '$hashed_password', last_password_change = NOW() WHERE email = '$email'");
            
            // Clear reset session
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_otp']);
            unset($_SESSION['reset_expires']);
            
            $message = "✅ Password reset successful! Please login.";
            echo "<script>setTimeout(function(){ window.location.href='teacher_login.php'; }, 2000);</script>";
        }
    }
    ?>
    
    <div class="login-box">
        <?php if($message): ?>
            <div style="background:#d4edda; padding:12px; border-radius:8px; margin-bottom:15px; color:#155724;">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div style="background:#f8d7da; padding:12px; border-radius:8px; margin-bottom:15px; color:#721c24;">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <?php if($step == 1): ?>
            <form method="POST">
                <label>Registered Email Address:</label>
                <input type="email" name="email" placeholder="teacher@school.com" required>
                <button type="submit" name="send_otp">📧 Send Reset OTP</button>
            </form>
            
        <?php elseif($step == 2): ?>
            <form method="POST">
                <label>Enter OTP (One Time Password):</label>
                <input type="text" name="otp" placeholder="6-digit OTP" maxlength="6" required>
                <button type="submit" name="verify_otp">✓ Verify OTP</button>
            </form>
            <p style="margin-top:15px; text-align:center;">
                <a href="?step=1">← Resend OTP</a>
            </p>
            
        <?php elseif($step == 3): ?>
            <form method="POST">
                <label>New Password:</label>
                <input type="password" name="new_password" minlength="6" required>
                <label>Confirm New Password:</label>
                <input type="password" name="confirm_password" required>
                <button type="submit" name="reset_password">🔄 Reset Password</button>
            </form>
        <?php endif; ?>
        
        <p style="margin-top: 20px; text-align: center;">
            <a href="teacher_login.php">← Back to Login</a>
        </p>
    </div>
</div>
</body>
</html>