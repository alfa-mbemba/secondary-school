<?php include 'config.php'; if(!isset($_SESSION['admin'])) header('Location: index.php'); ?>
<!DOCTYPE html>
<html>
<head>
    <title>All Attendance Report</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>📊 School-Wide Attendance Report</h2>
    
    <form method="GET">
        <select name="class">
            <option value="">All Classes</option>
            <option>Form 1</option><option>Form 2</option><option>Form 3</option><option>Form 4</option>
        </select>
        <input type="date" name="from_date">
        <input type="date" name="to_date">
        <button type="submit">Filter</button>
    </form>
    
    <table border="1" cellpadding="8">
        <tr>
            <th>Student</th><th>Class</th><th>Total Days</th><th>Present</th><th>Absent</th><th>Late</th><th>% Present</th>
        </tr>
        <?php
        $class_filter = isset($_GET['class']) && $_GET['class'] != '' ? "AND class = '{$_GET['class']}'" : "";
        $students = $conn->query("SELECT * FROM students WHERE 1=1 $class_filter");
        
        while($student = $students->fetch_assoc()):
            $attendance = $conn->query("SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status='Absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status='Late' THEN 1 ELSE 0 END) as late
                FROM attendance WHERE student_id = {$student['id']}");
            $stats = $attendance->fetch_assoc();
            $total = $stats['total'] ?? 0;
            $present = $stats['present'] ?? 0;
            $percentage = $total > 0 ? round(($present / $total) * 100, 1) : 0;
        ?>
        <tr>
            <td><?= $student['full_name'] ?></td>
            <td><?= $student['class'] ?></td>
            <td><?= $total ?></td>
            <td style="color:green"><?= $present ?></td>
            <td style="color:red"><?= $stats['absent'] ?? 0 ?></td>
            <td style="color:orange"><?= $stats['late'] ?? 0 ?></td>
            <td><?= $percentage ?>%</td>
        </tr>
        <?php endwhile; ?>
    </table>
    <a href="index.php">⬅ Back</a>
</div>
</body>
</html>