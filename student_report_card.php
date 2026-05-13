<?php 
include 'config.php'; 
$student_id = $_GET['id'] ?? 0;
$student = $conn->query("SELECT * FROM students WHERE id=$student_id")->fetch_assoc();
if(!$student) die("Student not found");

// Function to calculate grade based on marks percentage
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
    <title>Report Card - <?= $student['full_name'] ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .report-card {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #667eea;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .student-info {
            background: #f0f4ff;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .grade-a { color: #28a745; font-weight: bold; }
        .grade-b { color: #17a2b8; font-weight: bold; }
        .grade-c { color: #ffc107; font-weight: bold; }
        .grade-d { color: #fd7e14; font-weight: bold; }
        .grade-e { color: #dc3545; font-weight: bold; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        @media print {
            body { background: white; padding: 0; }
            .no-print { display: none; }
            .report-card { box-shadow: none; padding: 0; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="report-card">
        <div class="header">
            <h2>🏫 SECONDARY SCHOOL</h2>
            <h3>STUDENT REPORT CARD</h3>
        </div>
        
        <div class="student-info">
            <p><strong>Admission No:</strong> <?= $student['admission_no'] ?></p>
            <p><strong>Student Name:</strong> <?= $student['full_name'] ?></p>
            <p><strong>Class:</strong> <?= $student['class'] ?></p>
            <p><strong>Parent Contact:</strong> <?= $student['parent_phone'] ?></p>
        </div>
        
        <?php
        // Get all exams for this student
        $exams = $conn->query("SELECT DISTINCT exam_name FROM results WHERE student_id=$student_id");
        while($exam = $exams->fetch_assoc()):
            $exam_name = $exam['exam_name'];
            $results = $conn->query("SELECT * FROM results WHERE student_id=$student_id AND exam_name='$exam_name'");
        ?>
        <h3>📝 <?= $exam_name ?> Examination</h3>
        <table border="1" cellpadding="8" width="100%">
            <tr>
                <th>Subject</th>
                <th>Marks Obtained</th>
                <th>Total Marks</th>
                <th>Percentage</th>
                <th>Grade</th>
            </tr>
            <?php 
            $total_obtained = 0;
            $total_max = 0;
            while($result = $results->fetch_assoc()):
                $percentage = ($result['marks'] / $result['total_marks']) * 100;
                $grade = getGrade($result['marks'], $result['total_marks']);
                $total_obtained += $result['marks'];
                $total_max += $result['total_marks'];
                
                // Update grade in database for future reference
                $conn->query("UPDATE results SET grade='$grade' WHERE id={$result['id']}");
                
                $grade_class = "grade-" . strtolower($grade);
            ?>
            <tr>
                <td><?= $result['subject'] ?></td>
                <td><?= $result['marks'] ?></td>
                <td><?= $result['total_marks'] ?></td>
                <td><?= round($percentage, 1) ?>%</td>
                <td class="<?= $grade_class ?>"><?= $grade ?></td>
            </tr>
            <?php endwhile; ?>
            <tr style="background: #f0f4ff; font-weight: bold;">
                <td colspan="2">TOTAL / AVERAGE</td>
                <td><?= $total_obtained ?> / <?= $total_max ?></td>
                <td><?= round(($total_obtained / $total_max) * 100, 1) ?>%</td>
                <td class="<?= 'grade-' . strtolower(getGrade($total_obtained, $total_max)) ?>">
                    <?= getGrade($total_obtained, $total_max) ?>
                </td>
            </tr>
        </table>
        <br>
        <?php endwhile; ?>
        
        <div class="footer">
            <p>Generated on: <?= date('d-m-Y H:i:s') ?></p>
            <p>** This is a system-generated report card **</p>
        </div>
    </div>
    
    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()">🖨️ Print Report Card</button>
        <a href="my_students.php">⬅ Back</a>
    </div>
</div>
</body>
</html>