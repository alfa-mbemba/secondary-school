<?php 
include 'config.php'; 
if(!isset($_SESSION['parent_logged_in'])) header('Location: parent_login.php');

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];
$admission_no = $_SESSION['student_admission_no'];

// Rest of the file remains the same as before...
function getGrade($marks, $total = 100) {
    $percentage = ($marks / $total) * 100;
    if($percentage >= 80) return ['grade' => 'A', 'color' => '#28a745', 'remark' => 'Excellent'];
    if($percentage >= 70) return ['grade' => 'B', 'color' => '#17a2b8', 'remark' => 'Very Good'];
    if($percentage >= 60) return ['grade' => 'C', 'color' => '#ffc107', 'remark' => 'Good'];
    if($percentage >= 50) return ['grade' => 'D', 'color' => '#fd7e14', 'remark' => 'Satisfactory'];
    return ['grade' => 'E', 'color' => '#dc3545', 'remark' => 'Needs Improvement'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Results - <?= $student_name ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .exam-card {
            background: white;
            border-radius: 15px;
            margin-bottom: 25px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .exam-header {
            background: #667eea;
            color: white;
            padding: 15px 20px;
            font-size: 18px;
            font-weight: bold;
        }
        .exam-body {
            padding: 20px;
        }
        .result-table {
            width: 100%;
            border-collapse: collapse;
        }
        .result-table th, .result-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .result-table th {
            background: #f0f4ff;
        }
        .grade-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: bold;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>📊 Academic Results</h2>
        <button onclick="window.print()" class="no-print" style="background:#28a745; padding:10px 20px;">🖨️ Print Results</button>
    </div>
    
    <div style="background:#f0f4ff; padding:15px; border-radius:10px; margin-bottom:20px;">
        <p><strong>Student:</strong> <?= htmlspecialchars($student_name) ?> (<?= $admission_no ?>)</p>
        <p><strong>Class:</strong> <?= $_SESSION['student_class'] ?></p>
    </div>
    
    <?php
    $exams = $conn->query("SELECT DISTINCT exam_name FROM results WHERE student_id = $student_id ORDER BY exam_name DESC");
    
    if($exams->num_rows > 0):
        while($exam = $exams->fetch_assoc()):
            $exam_name = $exam['exam_name'];
            $results = $conn->query("SELECT * FROM results WHERE student_id = $student_id AND exam_name = '$exam_name'");
    ?>
            <div class="exam-card">
                <div class="exam-header">📝 <?= htmlspecialchars($exam_name) ?> Examination</div>
                <div class="exam-body">
                    <table class="result-table">
                        <thead>
                            <tr><th>Subject</th><th>Marks Obtained</th><th>Total Marks</th><th>Percentage</th><th>Grade</th></tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_obtained = 0;
                            $total_max = 0;
                            while($result = $results->fetch_assoc()):
                                $percentage = ($result['marks'] / $result['total_marks']) * 100;
                                $grade_info = getGrade($result['marks'], $result['total_marks']);
                                $total_obtained += $result['marks'];
                                $total_max += $result['total_marks'];
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($result['subject']) ?></td>
                                    <td><?= $result['marks'] ?></td>
                                    <td><?= $result['total_marks'] ?></td>
                                    <td><?= round($percentage, 1) ?>%</td>
                                    <td><span class="grade-badge" style="background:<?= $grade_info['color'] ?>20; color:<?= $grade_info['color'] ?>;"><?= $grade_info['grade'] ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot>
                            <tr style="background:#f0f4ff; font-weight:bold;">
                                <td colspan="2">TOTAL</td>
                                <td><?= $total_obtained ?> / <?= $total_max ?></td>
                                <td><?= round(($total_obtained / $total_max) * 100, 1) ?>%</td>
                                <td><?php $overall_grade = getGrade($total_obtained, $total_max); ?>
                                    <span class="grade-badge" style="background:<?= $overall_grade['color'] ?>20; color:<?= $overall_grade['color'] ?>;"><?= $overall_grade['grade'] ?></span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
    <?php 
        endwhile;
    else:
    ?>
        <div style="background:#f8d7da; padding:20px; border-radius:10px; text-align:center;">
            ❌ No results available for this student yet.
        </div>
    <?php endif; ?>
    
    <div class="no-print" style="margin-top: 20px;">
        <a href="parent_dashboard.php">⬅ Back to Dashboard</a>
    </div>
</div>
</body>
</html>