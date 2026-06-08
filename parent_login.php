<?php 
include 'config.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Login - Student Portal</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .login-container {
            max-width: 500px;
            margin: 50px auto;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            color: #667eea;
        }
        .info-box {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            font-size: 14px;
            text-align: center;
        }
        .demo-box {
            background: #fff3cd;
            padding: 12px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 13px;
        }
        .error-box {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-top: 15px;
        }
        input {
            text-align: center;
            font-size: 18px;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
<div class="container login-container">
    <div class="login-header">
        <h1>👪 Parent / Student Portal</h1>
        <p>Enter your child's <strong>Admission Number</strong> to access their records</p>
    </div>
    
    <div class="login-box">
        <form method="POST" action="">
            <input type="text" name="admission_no" placeholder="Enter Student Admission Number (e.g., STU001)" 
                   required autofocus style="text-align:center; font-size:20px; padding:15px;">
            <button type="submit" name="login" style="background:#28a745; font-size:18px; padding:12px;">🔐 Access Portal</button>
        </form>
        
        <p style="margin-top: 15px; text-align: center;">
            <a href="index.php">← Admin Login</a> | <br><br>
            <a href="teacher_login.php">👨‍🏫 Teacher Login</a>
        </p>
        
        <?php
        if(isset($_POST['login'])){
            // Get and sanitize admission number
            $admission_no = isset($_POST['admission_no']) ? strtoupper(trim($_POST['admission_no'])) : '';
            
            if(empty($admission_no)){
                echo "<div class='error-box'>❌ Please enter an admission number.</div>";
            } else {
                // Check if connection exists
                if(!isset($conn) || $conn->connect_error){
                    echo "<div class='error-box'>❌ Database connection error. Please check config.php</div>";
                } else {
                    // Check if student exists with this admission number
                    $stmt = $conn->prepare("SELECT * FROM students WHERE admission_no = ?");
                    $stmt->bind_param("s", $admission_no);
                    $stmt->execute();
                    $student = $stmt->get_result();
                    
                    if($student && $student->num_rows == 1){
                        $student_data = $student->fetch_assoc();
                        
                        // Store student info in session
                        $_SESSION['parent_logged_in'] = true;
                        $_SESSION['student_id'] = $student_data['id'];
                        $_SESSION['student_name'] = $student_data['full_name'];
                        $_SESSION['student_admission_no'] = $admission_no;
                        $_SESSION['student_class'] = $student_data['class'];
                        $_SESSION['parent_name'] = "Parent of " . $student_data['full_name'];
                        
                        // Redirect to dashboard
                        echo "<script>window.location.href='parent_dashboard.php';</script>";
                        exit();
                    } else {
                        echo "<div class='error-box'>❌ Invalid Admission Number. Student not found. Please check and try again.</div>";
                    }
                    $stmt->close();
                }
            }
        }
        ?>
        
          
        </div>
        
        
    </div>
</div>
</body>
</html>