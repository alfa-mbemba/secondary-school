<?php include 'config.php'; if(!isset($_SESSION['admin'])) header('Location: index.php'); ?>
<!DOCTYPE html>
<html>
<head>
    <title>All Results</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>📊 Student Results</h2>
    <form method="GET">
        <select name="student_id">
            <option value="">-- All Students --</option>
            <?php
            $stu = $conn->query("SELECT id, full_name, admission_no FROM students");
            while($s = $stu->fetch_assoc()){
                $selected = ($_GET['student_id'] ?? '') == $s['id'] ? 'selected' : '';
                echo "<option value='{$s['id']}' $selected>{$s['admission_no']} - {$s['full_name']}</option>";
            }
            ?>
        </select>
        <button type="submit">Filter</button>
    </form>
    <table border="1" cellpadding="8">
        <tr><th>Student</th><th>Exam</th><th>Subject</th><th>Marks</th><th>Total</th></tr>
        <?php
        $filter = isset($_GET['student_id']) && $_GET['student_id'] != '' ? "WHERE student_id = ".$_GET['student_id'] : "";
        $res = $conn->query("SELECT r.*, s.full_name, s.admission_no FROM results r JOIN students s ON r.student_id = s.id $filter ORDER BY r.id DESC");
        while($row = $res->fetch_assoc()):
        ?>
        <tr>
            <td><?= $row['admission_no']." - ".$row['full_name'] ?></td>
            <td><?= $row['exam_name'] ?></td>
            <td><?= $row['subject'] ?></td>
            <td><?= $row['marks'] ?></td>
            <td><?= $row['total_marks'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <a href="index.php">⬅ Back</a>
</div>
</body>
</html>