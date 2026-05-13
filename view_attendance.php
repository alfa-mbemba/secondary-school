<?php 
include 'config.php'; 
if(!isset($_SESSION['teacher'])) header('Location: teacher_login.php');
$teacher = $_SESSION['teacher_data'];
$class = $teacher['class_assigned'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Attendance Report</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>📊 Attendance Report - <?= $class ?></h2>
    
    <form method="GET">
        <input type="date" name="from_date" required placeholder="From Date">
        <input type="date" name="to_date" required placeholder="To Date">
        <button type="submit">Filter</button>
    </form>
    
    <table border="1" cellpadding="8">
        <tr>
            <th>Student</th>
            <th>Date</th>
            <th>Status</th>
            <th>Remark</th>
            <th>Marked By</th>
        </tr>
        <?php
        if(isset($_GET['from_date']) && isset($_GET['to_date'])){
            $from = $_GET['from_date'];
            $to = $_GET['to_date'];
            $attendance = $conn->query("
                SELECT a.*, s.full_name, s.admission_no, t.full_name as teacher_name 
                FROM attendance a 
                JOIN students s ON a.student_id = s.id 
                JOIN teachers t ON a.teacher_id = t.id 
                WHERE s.class = '$class' AND a.date BETWEEN '$from' AND '$to'
                ORDER BY a.date DESC, s.full_name
            ");
            
            while($row = $attendance->fetch_assoc()):
            ?>
            <tr>
                <td><?= $row['admission_no'] . " - " . $row['full_name'] ?></td>
                <td><?= $row['date'] ?></td>
                <td>
                    <?php 
                    $badge = $row['status'] == 'Present' ? '🟢' : ($row['status'] == 'Absent' ? '🔴' : '🟡');
                    echo $badge . " " . $row['status'];
                    ?>
                </td>
                <td><?= $row['remark'] ?></td>
                <td><?= $row['teacher_name'] ?></td>
            </tr>
            <?php endwhile;
        }
        ?>
    </table>
    <a href="teacher_dashboard.php">⬅ Back</a>
</div>
</body>
</html>