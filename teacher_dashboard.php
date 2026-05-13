<?php 
include 'config.php'; 
if(!isset($_SESSION['teacher'])) header('Location: teacher_login.php');
$teacher = $_SESSION['teacher_data'];
$class = $teacher['class_assigned'];
$subject = $teacher['subject'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .teacher-badge {
            background: #28a745;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
        }
        .action-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            text-decoration: none;
            display: block;
            transition: 0.3s;
        }
        .action-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
<div class="container">
    <h2>👨‍🏫 Teacher Dashboard</h2>
    <div class="teacher-badge">📚 Teaching: <?= $subject ?> | 🏫 Class: <?= $class ?></div>
    
    <div style="background:#d4edda; padding:15px; border-radius:10px; margin: 20px 0;">
        ✅ <strong>Your Permissions:</strong> You can add/edit marks, mark attendance, view results, and generate report cards.
    </div>
    
    <div class="dashboard">
        <a href="teacher_add_marks.php" class="action-card" style="background:#28a745;">
            📝 Add / Edit Marks
        </a>
        <a href="mark_attendance.php" class="action-card">
            📋 Mark Attendance
        </a>
        <a href="teacher_view_results.php" class="action-card">
            📊 View All Results
        </a>
        <a href="teacher_manage_password.php">🔐 Change Password</a>
        <a href="teacher_view_attendance.php" class="action-card">
            👀 View Attendance Report
        </a>
        <a href="my_students.php" class="action-card">
            👨‍🎓 My Students
        </a>
        <a href="student_report_card.php?id=" class="action-card">
            📄 Generate Report Card
        </a>
        <a href="exam_timetable_view.php" class="action-card">
            📅 Exam Timetable
        </a>
        <a href="logout.php" class="action-card" style="background:#dc3545;">
            🚪 Logout
        </a>
    </div>
</div>
</body>
</html>