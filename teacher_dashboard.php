<?php 
include 'config.php'; 
if(!isset($_SESSION['teacher'])){ 
    header('Location: teacher_login.php');
    exit();
}

$teacher = $_SESSION['teacher_data'];
$class = $teacher['class_assigned'];
$subject = $teacher['subject'];

// Get statistics
$student_count = $conn->query("SELECT COUNT(*) as total FROM students WHERE class='$class'")->fetch_assoc();
$exam_count = $conn->query("SELECT COUNT(DISTINCT exam_name) as total FROM results WHERE subject='$subject'")->fetch_assoc();
$today_attendance = $conn->query("SELECT COUNT(*) as total FROM attendance WHERE date=CURDATE()")->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .welcome-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 25px;
        }
        .teacher-badge {
            background: #28a745;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
            margin-top: 10px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
        }
        .stat-card h3 {
            font-size: 32px;
            margin: 0;
            color: #667eea;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .dashboard-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: 0.3s;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: block;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .dashboard-card span {
            font-size: 48px;
            display: block;
            margin-bottom: 10px;
        }
        .dashboard-card h3 {
            margin: 10px 0;
        }
        .dashboard-card p {
            color: #666;
            font-size: 14px;
        }
        .card-attendance { border-top: 4px solid #17a2b8; }
        .card-marks { border-top: 4px solid #28a745; }
        .card-results { border-top: 4px solid #ffc107; }
        .card-students { border-top: 4px solid #fd7e14; }
        .card-password { border-top: 4px solid #6c757d; }
        .card-timetable { border-top: 4px solid #20c997; }
        .logout-card { border-top: 4px solid #dc3545; }
    </style>
</head>
<body>
<div class="container dashboard-container">
    <div class="welcome-card">
        <h2>👨‍🏫 Welcome, <?= htmlspecialchars($teacher['full_name']) ?></h2>
        <div class="teacher-badge">📚 Teaching: <?= htmlspecialchars($subject) ?> | 🏫 Class: <?= htmlspecialchars($class) ?></div>
        <p style="margin-top: 15px;">📧 <?= htmlspecialchars($teacher['email']) ?> | 📞 <?= htmlspecialchars($teacher['phone']) ?></p>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3><?= $student_count['total'] ?></h3>
            <p>Total Students</p>
        </div>
        <div class="stat-card">
            <h3><?= $exam_count['total'] ?></h3>
            <p>Exams Conducted</p>
        </div>
        <div class="stat-card">
            <h3><?= $today_attendance['total'] ?? 0 ?></h3>
            <p>Today's Attendance</p>
        </div>
    </div>
    
    <div class="dashboard-grid">
        <a href="mark_attendance.php" class="dashboard-card card-attendance">
            <span>📋</span>
            <h3>Mark Attendance</h3>
            <p>Record daily student attendance</p>
        </a>
        
        <a href="teacher_view_attendance.php" class="dashboard-card card-attendance">
            <span>👀</span>
            <h3>View Attendance</h3>
            <p>View attendance reports</p>
        </a>
        
        <a href="teacher_add_marks.php" class="dashboard-card card-marks">
            <span>📝</span>
            <h3>Enter / Edit Marks</h3>
            <p>Add or update student marks</p>
        </a>
        
        <a href="teacher_view_results.php" class="dashboard-card card-results">
            <span>📊</span>
            <h3>View Results</h3>
            <p>View all student results</p>
        </a>
        
        <a href="my_students.php" class="dashboard-card card-students">
            <span>👨‍🎓</span>
            <h3>My Students</h3>
            <p>View student list and details</p>
        </a>
        
        <a href="select_student_report.php" class="dashboard-card card-results">
            <span>📄</span>
            <h3>Generate Report Card</h3>
            <p>Print student report cards</p>
        </a>
        
        <a href="exam_timetable_view.php" class="dashboard-card card-timetable">
            <span>📅</span>
            <h3>Exam Timetable</h3>
            <p>View exam schedule</p>
        </a>
        
        <a href="teacher_manage_password.php" class="dashboard-card card-password">
            <span>🔐</span>
            <h3>Change Password</h3>
            <p>Update your password</p>
        </a>
        
        <a href="logout.php" class="dashboard-card logout-card">
            <span>🚪</span>
            <h3>Logout</h3>
            <p>Exit dashboard</p>
        </a>
    </div>
</div>
</body>
</html>