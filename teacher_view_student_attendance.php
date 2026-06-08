<?php 
include 'config.php'; 
if(!isset($_SESSION['teacher'])) header('Location: teacher_login.php');
$student_id = $_GET['id'] ?? 0;
$student = $conn->query("SELECT * FROM students WHERE id=$student_id")->fetch_assoc();
if(!$student) die("Student not found");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Attendance - <?= $student['full_name'] ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .present { color: green; font-weight: bold; }
        .absent { color: red; font-weight: bold; }
        .late { color: orange; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h2>📋 Attendance Record</h2>
    <div style="background:#f0f4ff; padding:15px; border-radius:10px; margin-bottom:20px;">
        <p><strong>Student:</strong> <?= $student['full_name'] ?> (<?= $student['admission_no'] ?>)</p>
        <p><strong>Class:</strong> <?= $student['class'] ?></p>
    </div>
    
    <form method="GET" style="margin-bottom:20px;">
        <input type="hidden" name="id" value="<?= $student_id ?>">
        <input type="date" name="from_date" value="<?= $_GET['from_date'] ?? date('Y-m-01') ?>" required>
        <input type="date" name="to_date" value="<?= $_GET['to_date'] ?? date('Y-m-t') ?>" required>
        <button type="submit">Filter</button>
    </form>
    
    <?php
    $from = $_GET['from_date'] ?? date('Y-m-01');
    $to = $_GET['to_date'] ?? date('Y-m-t');
    
    // Statistics
    $stats = $conn->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) as present,
            SUM(CASE WHEN status='Absent' THEN 1 ELSE 0 END) as absent,
            SUM(CASE WHEN status='Late' THEN 1 ELSE 0 END) as late
        FROM attendance 
        WHERE student_id=$student_id AND date BETWEEN '$from' AND '$to'
    ")->fetch_assoc();
    
    $percentage = $stats['total'] > 0 ? round(($stats['present'] / $stats['total']) * 100, 1) : 0;
    ?>
    
    <div style="display: flex; gap: 15px; margin-bottom: 20px;">
        <div style="background:#d4edda; padding:15px; border-radius:10px; text-align:center; flex:1;">
            <h3 class="present">✅ <?= $stats['present'] ?></h3>
            <p>Present</p>
        </div>
        <div style="background:#f8d7da; padding:15px; border-radius:10px; text-align:center; flex:1;">
            <h3 class="absent">❌ <?= $stats['absent'] ?></h3>
            <p>Absent</p>
        </div>
        <div style="background:#fff3cd; padding:15px; border-radius:10px; text-align:center; flex:1;">
            <h3 class="late">⏰ <?= $stats['late'] ?></h3>
            <p>Late</p>
        </div>
        <div style="background:#e2e3e5; padding:15px; border-radius:10px; text-align:center; flex:1;">
            <h3><?= $percentage ?>%</h3>
            <p>Attendance</p>
        </div>
    </div>
    
    <table border="1" cellpadding="8" width="100%">
        <thead>
            <tr style="background:#667eea; color:white;">
                <th>Date</th>
                <th>Status</th>
                <th>Remark</th>
                <th>Marked By</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $attendance = $conn->query("
                SELECT a.*, t.full_name as teacher_name 
                FROM attendance a
                JOIN teachers t ON a.teacher_id = t.id
                WHERE a.student_id=$student_id AND a.date BETWEEN '$from' AND '$to'
                ORDER BY a.date DESC
            ");
            
            if($attendance->num_rows > 0):
                while($row = $attendance->fetch_assoc()):
                    $status_class = strtolower($row['status']);
            ?>
                <tr>
                    <td><?= date('d M Y', strtotime($row['date'])) ?></td>
                    <td class="<?= $status_class ?>"><?= $row['status'] ?></td>
                    <td><?= $row['remark'] ?: '-' ?></td>
                    <td><?= $row['teacher_name'] ?></td>
                </tr>
            <?php 
                endwhile;
            else:
            ?>
                <tr><td colspan="4">No attendance records found for this period.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <a href="my_students.php">⬅ Back to Students</a>
</div>
</body>
</html>