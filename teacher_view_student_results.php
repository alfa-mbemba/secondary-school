<?php 
include 'config.php'; 
if(!isset($_SESSION['teacher'])) header('Location: teacher_login.php');
$student_id = $_GET['id'] ?? 0;
$student = $conn->query("SELECT * FROM students WHERE id=$student_id")->fetch_assoc();
if(!$student) die("Student not found");

function getGrade($marks, $total = 100) {
    $percentage = ($marks / $total) * 100;
    if($percentage >= 80) return 'A';
    if($percentage >= 70) return 'B';
    if($percentage >= 60) return 'C';
    if($percentage >= 50) return 'D';
    return 'E';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Results - <?= $student['full_name'] ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .grade-A { background: #d4edda; }
        .grade-B { background: #d1ecf1; }
        .grade-C { background: #fff3cd; }
        .grade-D { background: #ffeeba; }
        .grade-E { background: #f8d7da; }
    </style>
</head>
<body>
<div class="container">
    <h2>📊 Student Results</h2>
    <div style="background:#f0f4ff; padding:15px; border-radius:10px; margin-bottom:20px;">
        <p><strong>Name:</strong> <?= $student['full_name'] ?> (<?= $student['admission_no'] ?>)</p>
        <p><strong>Class:</strong> <?= $student['class'] ?> | <strong>Parent:</strong> <?= $student['parent_phone'] ?></p>
    </div>
    
    <?php
    $exams = $conn->query("SELECT DISTINCT exam_name FROM results WHERE student_id=$student_id");
    while($exam = $exams->fetch_assoc()):
        $exam_name = $exam['exam_name'];
        $results = $conn->query("SELECT * FROM results WHERE student_id=$student_id AND exam_name='$exam_name'");
    ?>
        <h3>📝 <?= $exam_name ?></h3>
        <table border="1" cellpadding="8" width="100%">
            <tr><th>Subject</th><th>Marks</th><th>Total</th><th>%</th><th>Grade</th></tr>
            <?php 
            $total_obtained = 0;
            $total_max = 0;
            while($result = $results->fetch_assoc()):
                $percentage = ($result['marks'] / $result['total_marks']) * 100;
                $grade = getGrade($result['marks'], $result['total_marks']);
                $total_obtained += $result['marks'];
                $total_max += $result['total_marks'];
            ?>
                <tr class="grade-<?= $grade ?>">
                    <td><?= $result['subject'] ?></td>
                    <td><?= $result['marks'] ?></td>
                    <td><?= $result['total_marks'] ?></td>
                    <td><?= round($percentage, 1) ?>%</td>
                    <td><?= $grade ?></td>
                </tr>
            <?php endwhile; ?>
            <tr style="background:#e9ecef; font-weight:bold;">
                <td colspan="2">TOTAL</td>
                <td><?= $total_obtained ?> / <?= $total_max ?></td>
                <td><?= round(($total_obtained / $total_max) * 100, 1) ?>%</td>
                <td><?= getGrade($total_obtained, $total_max) ?></td>
            </tr>
        </table>
        <br>
    <?php endwhile; ?>
    
    <a href="my_students.php">⬅ Back to Students</a>
</div>
</body>
</html>