<?php 
include 'config.php'; 
if(!isset($_SESSION['teacher'])) header('Location: teacher_login.php');

$teacher = $_SESSION['teacher_data'];
$message = '';
$error = '';

// Handle password change
if(isset($_POST['change_password'])){
    $current_password = md5($_POST['current_password']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    $check = $conn->query("SELECT id FROM teachers WHERE id = '{$teacher['id']}' AND password = '$current_password'");
    
    if($check->num_rows == 0){
        $error = "❌ Current password is incorrect!";
    } elseif(strlen($new_password) < 6){
        $error = "❌ New password must be at least 6 characters!";
    } elseif($new_password != $confirm_password){
        $error = "❌ New passwords do not match!";
    } else {
        $new_password_hashed = md5($new_password);
        $conn->query("UPDATE teachers SET password = '$new_password_hashed', last_password_change = NOW() WHERE id = '{$teacher['id']}'");
        $message = "✅ Password changed successfully! Please login again.";
        
        // Logout after password change
        session_destroy();
        echo "<script>alert('Password changed! Please login again.'); window.location.href='teacher_login.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Password - <?= $teacher['full_name'] ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .password-container {
            max-width: 500px;
            margin: 0 auto;
        }
        .password-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .password-strength {
            height: 5px;
            background: #ddd;
            border-radius: 5px;
            margin-top: 5px;
            overflow: hidden;
        }
        .strength-bar {
            height: 100%;
            width: 0%;
            transition: width 0.3s;
        }
        .strength-weak { background: #dc3545; width: 33%; }
        .strength-medium { background: #ffc107; width: 66%; }
        .strength-strong { background: #28a745; width: 100%; }
        .requirement {
            font-size: 12px;
            margin: 5px 0;
            color: #666;
        }
        .requirement.valid { color: #28a745; }
        .info-box {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>🔐 Password Management</h2>
    
    <div class="password-container">
        <div class="password-card">
            <div class="info-box">
                <strong>👨‍🏫 Teacher:</strong> <?= $teacher['full_name'] ?> (<?= $teacher['teacher_id'] ?>)<br>
                <strong>📧 Email:</strong> <?= $teacher['email'] ?><br>
                <strong>🕒 Last Password Change:</strong> <?= $teacher['last_password_change'] ? date('d M Y H:i', strtotime($teacher['last_password_change'])) : 'Never' ?>
            </div>
            
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
            
            <form method="POST" onsubmit="return validatePassword()">
                <label>Current Password:</label>
                <input type="password" name="current_password" required>
                
                <label>New Password:</label>
                <input type="password" id="new_password" name="new_password" onkeyup="checkStrength()" required>
                <div class="password-strength">
                    <div class="strength-bar" id="strengthBar"></div>
                </div>
                <div id="strengthText" style="font-size:12px; margin-bottom:10px;"></div>
                
                <div class="requirement" id="lengthReq">✓ At least 6 characters</div>
                <div class="requirement" id="upperReq">✓ At least 1 uppercase letter</div>
                <div class="requirement" id="numberReq">✓ At least 1 number</div>
                
                <label>Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" onkeyup="checkMatch()" required>
                <div id="matchMsg" style="font-size:12px;"></div>
                
                <button type="submit" name="change_password" style="background:#28a745; margin-top:15px;">🔄 Change Password</button>
            </form>
            
            <div style="margin-top: 20px; text-align: center;">
                <a href="teacher_dashboard.php">⬅ Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>

<script>
function checkStrength() {
    let password = document.getElementById('new_password').value;
    let strength = 0;
    
    // Length check
    if(password.length >= 6) {
        document.getElementById('lengthReq').style.color = '#28a745';
        strength++;
    } else {
        document.getElementById('lengthReq').style.color = '#666';
    }
    
    // Uppercase check
    if(/[A-Z]/.test(password)) {
        document.getElementById('upperReq').style.color = '#28a745';
        strength++;
    } else {
        document.getElementById('upperReq').style.color = '#666';
    }
    
    // Number check
    if(/[0-9]/.test(password)) {
        document.getElementById('numberReq').style.color = '#28a745';
        strength++;
    } else {
        document.getElementById('numberReq').style.color = '#666';
    }
    
    // Update strength bar
    let bar = document.getElementById('strengthBar');
    let text = document.getElementById('strengthText');
    
    if(strength === 0) {
        bar.className = 'strength-bar';
        text.innerHTML = '';
    } else if(strength <= 1) {
        bar.className = 'strength-bar strength-weak';
        text.innerHTML = 'Weak Password';
        text.style.color = '#dc3545';
    } else if(strength <= 2) {
        bar.className = 'strength-bar strength-medium';
        text.innerHTML = 'Medium Password';
        text.style.color = '#ffc107';
    } else {
        bar.className = 'strength-bar strength-strong';
        text.innerHTML = 'Strong Password!';
        text.style.color = '#28a745';
    }
}

function checkMatch() {
    let password = document.getElementById('new_password').value;
    let confirm = document.getElementById('confirm_password').value;
    let msg = document.getElementById('matchMsg');
    
    if(password === confirm && password !== '') {
        msg.innerHTML = '✓ Passwords match!';
        msg.style.color = '#28a745';
    } else if(confirm !== '') {
        msg.innerHTML = '✗ Passwords do not match!';
        msg.style.color = '#dc3545';
    } else {
        msg.innerHTML = '';
    }
}

function validatePassword() {
    let password = document.getElementById('new_password').value;
    let confirm = document.getElementById('confirm_password').value;
    
    if(password.length < 6) {
        alert('Password must be at least 6 characters!');
        return false;
    }
    if(password !== confirm) {
        alert('Passwords do not match!');
        return false;
    }
    return true;
}
</script>
</body>
</html>