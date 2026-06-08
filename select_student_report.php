v<?php 
include 'config.php'; 

if(!isset($_SESSION['teacher'])){ 
    header('Location: teacher_login.php');
    exit();
}

$teacher = $_SESSION['teacher_data'];
$class = $teacher['class_assigned'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Select Student for Report Card</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>📄 Generate Report Card</h2>
    <p>Select a student to generate their report card:</p>
    
    <table border="1" cellpadding="10" width="100%">
        <thead>
            <tr style="background:#667eea; color:white;">
                <th>Admission No</th>
                <th>Student Name</th>
                <th>Class</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $students = $conn->prepare("SELECT id, admission_no, full_name, class FROM students WHERE class = ? ORDER BY admission_no");
            $students->bind_param("s", $class);
            $students->execute();
            $result = $students->get_result();
            
            while($student = $result->fetch_assoc()):
            ?>
            <tr>
                <td><?= htmlspecialchars($student['admission_no']) ?></td>
                <td><?= htmlspecialchars($student['full_name']) ?></td>
                <td><?= htmlspecialchars($student['class']) ?></td>
                <td>
                    <a href="student_report_card.php?id=<?= $student['id'] ?>" style="background:#28a745; color:white; padding:5px 10px; text-decoration:none; border-radius:5px;">📄 Generate Report</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <a href="teacher_dashboard.php">⬅ Back to Dashboard</a>
</div>
</body>
</html>