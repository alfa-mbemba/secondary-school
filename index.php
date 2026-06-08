<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management System - Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .role-warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 14px;
        }
        .admin-badge {
            background: #dc3545;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏫 Secondary School Management System</h1>
        <?php if(!isset($_SESSION['admin'])): ?>
        <div class="login-box">
            <h2>Admin Login</h2>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Login</button>
            </form>
            <p style="margin-top: 15px; text-align: center;">
                <a href="teacher_login.php">👨‍🏫 Teacher Login</a> <br><br>| 
                <a href="parent_login.php">👪 Parent Login</a>
            </p>
            <?php
            if(isset($_POST['login'])){
                $user = $_POST['username'];
                $pass = md5($_POST['password']);
                $result = $conn->query("SELECT * FROM admin WHERE username='$user' AND password='$pass'");
                if($result->num_rows == 1){
                    $_SESSION['admin'] = $user;
                    echo "<script>window.location.href='index.php';</script>";
                } else {
                    echo "<p style='color:red'>Invalid Credentials</p>";
                }
            }
            ?>
        </div>
        <?php else: ?>
        <div class="role-warning">
            ⚠️ <strong>Note:</strong> As an Admin, you can manage students, teachers, parents, fees, and view reports. 
            You <strong>CANNOT add or edit marks/results</strong> - this is restricted to Teachers only.
        </div>
        <!-- In the dashboard div of index.php -->
<div class="dashboard">
    <!-- Student Management -->
    <div style="margin-bottom: 10px; width:100%;"><strong>📚 STUDENT MANAGEMENT</strong></div>
    <a href="add_student.php">➕ Add Student</a>
    <a href="students_list.php">📋 Students List</a>
    <a href="create_parent_account.php">👪 Create Parent Account</a>
    
    <!-- Teacher Management -->
    <div style="margin-bottom: 10px; margin-top: 15px; width:100%;"><strong>👨‍🏫 TEACHER MANAGEMENT</strong></div>
    <a href="manage_teachers.php">➕ Add / Manage Teachers</a>
    <a href="admin_manage_teachers.php">👨‍🏫 Manage Teachers & Passwords</a>
    
    <!-- Fee Management -->
    <div style="margin-bottom: 10px; margin-top: 15px; width:100%;"><strong>💰 FEE MANAGEMENT</strong></div>
    <a href="fees.php">💵 Manage Student Fees</a>
    
    <!-- Reports (View Only) -->
    <div style="margin-bottom: 10px; margin-top: 15px; width:100%;"><strong>📊 REPORTS (VIEW ONLY)</strong></div>
    <a href="admin_view_results.php">📊 View All Results</a>
    <a href="attendance_report_all.php">📈 View Attendance Report</a>
    <a href="admin_view_fees_report.php">💰 Fee Collection Report</a>
    
    <!-- Exam Management -->
    <div style="margin-bottom: 10px; margin-top: 15px; width:100%;"><strong>📅 EXAM MANAGEMENT</strong></div>
   <a href="exam_timetable.php">📅 Manage Exam Timetable</a>
    
    <!-- System -->
    <div style="margin-bottom: 10px; margin-top: 15px; width:100%;"><strong>⚙️ SYSTEM</strong></div>
    <a href="logout.php">🚪 Logout</a>
</div>
        <?php endif; ?>
    </div>
</body>
</html>