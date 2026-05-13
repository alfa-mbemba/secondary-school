<?php 
include 'config.php'; 

// Check if parent is logged in
if(!isset($_SESSION['parent_logged_in']) || $_SESSION['parent_logged_in'] !== true){
    header('Location: parent_login.php');
    exit();
}

// Get student information from session
$student_id = $_SESSION['student_id'] ?? 0;
$student_name = $_SESSION['student_name'] ?? 'Unknown';
$student_class = $_SESSION['student_class'] ?? 'Unknown';
$admission_no = $_SESSION['student_admission_no'] ?? 'Unknown';
$parent_name = $_SESSION['parent_name'] ?? 'Parent';

// Get student details from database
$student = $conn->query("SELECT * FROM students WHERE id = $student_id");
if($student && $student->num_rows > 0){
    $student = $student->fetch_assoc();
} else {
    // If student not found, logout
    session_destroy();
    header('Location: parent_login.php');
    exit();
}

// Get statistics
$total_exams = $conn->query("SELECT COUNT(DISTINCT exam_name) as total FROM results WHERE student_id = $student_id");
$total_exams = $total_exams ? $total_exams->fetch_assoc()['total'] : 0;

$avg_percentage = $conn->query("SELECT AVG((marks/total_marks)*100) as avg FROM results WHERE student_id = $student_id");
$avg_percentage = $avg_percentage ? round(($avg_percentage->fetch_assoc()['avg'] ?? 0), 1) : 0;

$attendance_rate = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) as present
    FROM attendance WHERE student_id = $student_id
");
if($attendance_rate && $attendance_rate->num_rows > 0){
    $att_data = $attendance_rate->fetch_assoc();
    $att_percentage = $att_data['total'] > 0 ? round(($att_data['present'] / $att_data['total']) * 100, 1) : 0;
} else {
    $att_percentage = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Dashboard - <?= htmlspecialchars($student_name) ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .welcome-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 25px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin: 25px 0;
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
        .dashboard-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px;
            margin-top: 25px;
        }
        .action-btn {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 12px;
            text-decoration: none;
            color: #333;
            transition: 0.3s;
            border: 1px solid #ddd;
        }
        .action-btn:hover {
            background: #667eea;
            color: white;
            transform: translateY(-3px);
        }
        .action-btn span {
            font-size: 40px;
            display: block;
            margin-bottom: 10px;
        }
        .logout-btn {
            background: #dc3545;
            color: white;
        }
        .logout-btn:hover {
            background: #c82333;
            color: white;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="welcome-card">
        <h2>👪 Welcome, <?= htmlspecialchars($parent_name) ?></h2>
        <h3>📚 Student: <?= htmlspecialchars($student_name) ?> (<?= htmlspecialchars($admission_no) ?>)</h3>
        <p>🏫 Class: <?= htmlspecialchars($student_class) ?></p>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3><?= $total_exams ?></h3>
            <p>📝 Exams Taken</p>
        </div>
        <div class="stat-card">
            <h3><?= $avg_percentage ?>%</h3>
            <p>📊 Average Score</p>
        </div>
        <div class="stat-card">
            <h3><?= $att_percentage ?>%</h3>
            <p>📋 Attendance Rate</p>
        </div>
        <div class="stat-card">
            <h3>₹<?= number_format($student['fee_paid'], 0) ?></h3>
            <p>💰 Fees Paid</p>
        </div>
    </div>
    
    <div class="dashboard-actions">
        <a href="parent_view_results.php" class="action-btn">
            <span>📊</span>
            View Results
        </a>
        <a href="parent_view_attendance.php" class="action-btn">
            <span>📋</span>
            View Attendance
        </a>
        <a href="parent_generate_report.php" class="action-btn">
            <span>📄</span>
            Generate Report Card
        </a>
        <a href="parent_fees.php" class="action-btn">
            <span>💰</span>
            Fee Details
        </a>
        <a href="parent_exam_timetable.php" class="action-btn">
            <span>📅</span>
            Exam Timetable
        </a>
        <a href="logout.php" class="action-btn logout-btn">
            <span>🚪</span>
            Logout
        </a>
    </div>
</div>
</body>
</html>