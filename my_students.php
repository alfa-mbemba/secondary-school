<?php 
include 'config.php'; 
if(!isset($_SESSION['teacher'])) header('Location: teacher_login.php');
$teacher = $_SESSION['teacher_data'];
$class = $teacher['class_assigned'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Students</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>👨‍🎓 My Students - <?= $class ?></h2>
    
    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <thead>
            <tr style="background:#667eea; color:white;">
                <th>Admission No</th>
                <th>Student Name</th>
                <th>Parent Phone</th>
                <th>Address</th>
                <th>Attendance %</th>
                <th>Fee Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $students = $conn->query("SELECT * FROM students WHERE class='$class' ORDER BY admission_no");
            while($student = $students->fetch_assoc()):
                // Calculate attendance percentage
                $total_days = $conn->query("SELECT COUNT(DISTINCT date) as total FROM attendance WHERE student_id = {$student['id']}")->fetch_assoc();
                $present_days = $conn->query("SELECT COUNT(*) as present FROM attendance WHERE student_id = {$student['id']} AND status = 'Present'")->fetch_assoc();
                
                $percentage = $total_days['total'] > 0 ? round(($present_days['present'] / $total_days['total']) * 100, 1) : 0;
                $percentage_class = $percentage >= 75 ? 'present' : ($percentage >= 50 ? 'late' : 'absent');
                
                // Fee status
                $pending = $student['total_fees'] - $student['fee_paid'];
                $fee_status = $pending <= 0 ? 'Paid' : ($pending < 1000 ? 'Partial' : 'Pending');
                $fee_color = $pending <= 0 ? 'green' : ($pending < 1000 ? 'orange' : 'red');
            ?>
                <tr>
                    <td><?= $student['admission_no'] ?></td>
                    <td><strong><?= $student['full_name'] ?></strong></td>
                    <td><?= $student['parent_phone'] ?></td>
                    <td><?= substr($student['address'], 0, 30) ?>...</td>
                    <td class="<?= $percentage_class ?>"><?= $percentage ?>%</td>
                    <td style="color: <?= $fee_color ?>; font-weight: bold;"><?= $fee_status ?></td>
                    <td>
                        <a href="teacher_view_student_results.php?id=<?= $student['id'] ?>">📊 Results</a> |
                        <a href="teacher_view_student_attendance.php?id=<?= $student['id'] ?>">📋 Attendance</a> |
                        <a href="student_report_card.php?id=<?= $student['id'] ?>">📄 Report</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <div style="margin-top: 20px;">
        <a href="teacher_dashboard.php">⬅ Back to Dashboard</a>
    </div>
</div>
</body>
</html>