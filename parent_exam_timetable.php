<?php 
include 'config.php'; 
if(!isset($_SESSION['parent_logged_in'])) header('Location: parent_login.php');

$student_class = $_SESSION['student_class'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Exam Timetable - <?= $_SESSION['student_name'] ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .timetable-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .timetable-header {
            background: #667eea;
            color: white;
            padding: 15px 20px;
            font-size: 18px;
            font-weight: bold;
        }
        .upcoming-badge {
            background: #28a745;
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            margin-left: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📅 Exam Timetable</h2>
    <div style="background:#f0f4ff; padding:15px; border-radius:10px; margin-bottom:20px;">
        <p><strong>Student:</strong> <?= htmlspecialchars($_SESSION['student_name']) ?> | <strong>Class:</strong> <?= $student_class ?></p>
    </div>
    
    <?php
    // Upcoming exams
    $upcoming = $conn->query("
        SELECT * FROM exam_timetable 
        WHERE class = '$student_class' AND exam_date >= CURDATE() 
        ORDER BY exam_date, start_time
    ");
    
    if($upcoming->num_rows > 0):
    ?>
        <div class="timetable-card">
            <div class="timetable-header">📌 Upcoming Exams <span class="upcoming-badge">Upcoming</span></div>
            <table border="1" cellpadding="10" width="100%">
                <thead>
                    <tr style="background:#e9ecef;">
                        <th>Exam Name</th><th>Subject</th><th>Date</th><th>Time</th><th>Venue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($exam = $upcoming->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($exam['exam_name']) ?></td>
                            <td><?= htmlspecialchars($exam['subject']) ?></td>
                            <td><?= date('d M Y', strtotime($exam['exam_date'])) ?></td>
                            <td><?= date('h:i A', strtotime($exam['start_time'])) ?> - <?= date('h:i A', strtotime($exam['end_time'])) ?></td>
                            <td><?= htmlspecialchars($exam['venue']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="background:#fff3cd; padding:15px; border-radius:8px; margin-bottom:20px;">
            📌 No upcoming exams scheduled.
        </div>
    <?php endif; ?>
    
    <?php
    // Past exams
    $past = $conn->query("
        SELECT * FROM exam_timetable 
        WHERE class = '$student_class' AND exam_date < CURDATE() 
        ORDER BY exam_date DESC
    ");
    
    if($past->num_rows > 0):
    ?>
        <div class="timetable-card">
            <div class="timetable-header">📋 Past Exams</div>
            <table border="1" cellpadding="10" width="100%">
                <thead>
                    <tr style="background:#e9ecef;">
                        <th>Exam Name</th><th>Subject</th><th>Date</th><th>Time</th><th>Venue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($exam = $past->fetch_assoc()): ?>
                        <tr style="color:#6c757d;">
                            <td><?= htmlspecialchars($exam['exam_name']) ?></td>
                            <td><?= htmlspecialchars($exam['subject']) ?></td>
                            <td><?= date('d M Y', strtotime($exam['exam_date'])) ?></td>
                            <td><?= date('h:i A', strtotime($exam['start_time'])) ?> - <?= date('h:i A', strtotime($exam['end_time'])) ?></td>
                            <td><?= htmlspecialchars($exam['venue']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <a href="parent_dashboard.php">⬅ Back to Dashboard</a>
</div>
</body>
</html>