<?php 
include 'config.php'; 

if(!isset($_SESSION['teacher'])){ 
    header('Location: teacher_login.php');
    exit();
}

$teacher = $_SESSION['teacher_data'];
$message = '';
$error = '';

if(isset($_POST['change_password'])){
    $current = md5($_POST['current_password']);
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    
    // Verify current password
    $check = $conn->prepare("SELECT id FROM teachers WHERE id = ? AND password = ?");
    $check->bind_param("is", $teacher['id'], $current);
    $check->execute();
    
    if($check->get_result()->num_rows == 0){
        $error = "❌ Current password is incorrect!";
    } elseif(strlen($new) < 6){
        $error = "❌ New password must be at least 6 characters!";
    } elseif($new != $confirm){
        $error = "❌ Passwords do not match!";
    } else {
        $new_hash = md5($new);
        $update = $conn->prepare("UPDATE teachers SET password = ?, last_password_change = NOW() WHERE id = ?");
        $update->bind_param("si", $new_hash, $teacher['id']);
        $update->execute();
        $message = "✅ Password changed successfully! Please login again.";
        session_destroy();
        echo "<script>alert('Password changed! Please login again.'); window.location.href='teacher_login.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>🔐 Change Password</h2>
    
    <?php if($message): ?>
        <div style="background:#d4edda; padding:12px; border-radius:8px; margin-bottom:15px;"><?= $message ?></div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div style="background:#f8d7da; padding:12px; border-radius:8px; margin-bottom:15px;"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="POST" style="max-width: 400px; margin:0 auto;">
        <label>Current Password:</label>
        <input type="password" name="current_password" required>
        
        <label>New Password (min 6 characters):</label>
        <input type="password" name="new_password" minlength="6" required>
        
        <label>Confirm New Password:</label>
        <input type="password" name="confirm_password" required>
        
        <button type="submit" name="change_password" style="background:#28a745;">🔄 Change Password</button>
    </form>
    
    <div style="margin-top: 20px; text-align:center;">
        <a href="teacher_dashboard.php">⬅ Back to Dashboard</a>
    </div>
</div>
</body>
</html>