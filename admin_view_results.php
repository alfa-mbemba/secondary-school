<?php 
include 'config.php'; 
if(!isset($_SESSION['admin'])) header('Location: index.php');

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
    <title>Admin View Results</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .readonly-badge {
            background: #17a2b8;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            margin-left: 10px;
        }
        .warning-box {
            background: #e2e3e5;
            border-left: 4px solid #6c757d;
            padding: 12px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📊 View All Results <span class="readonly-badge">Read Only</span></h2>
    
    <div class="warning-box">
        🔒 <strong>Read-Only Mode:</strong> As an Admin, you can only view results. 
        To add or edit marks, please login as a <strong>Teacher</strong>.
    </div>
    
    <div class="filter-bar" style="background:#f0f4ff; padding:15px; border-radius:10px; margin-bottom:20px;">
        <form method="GET">
            <select name="class">
                <option value="">-- All Classes --</option>
                <option>Form 1</option><option>Form 2</option><option>Form 3</option><option>Form 4</option>
            </select>
            <select name="exam_name">
                <option value="">-- All Exams --</option>
                <?php
                $exams = $conn->query("SELECT DISTINCT exam_name FROM results");
                while($exam = $exams->fetch_assoc()){
                    $selected = (isset($_GET['exam_name']) && $_GET['exam_name'] == $exam['exam_name']) ? 'selected' : '';
                    echo "<option value='{$exam['exam_name']}' $selected>{$exam['exam_name']}</option>";
                }
                ?>
            </select>
            <button type="submit">🔍 Filter</button>
            <button type="button" onclick="window.print()" style="background:#28a745;">🖨️ Print</button>
        </form>
    </div>
    
    <?php
    $where = "1=1";
    if(isset($_GET['class']) && $_GET['class'] != ''){
        $where .= " AND s.class = '{$_GET['class']}'";
    }
    if(isset($_GET['exam_name']) && $_GET['exam_name'] != ''){
        $where .= " AND r.exam_name = '{$_GET['exam_name']}'";
    }
    
    $results = $conn->query("
        SELECT r.*, s.full_name, s.admission_no, s.class 
        FROM results r 
        JOIN students s ON r.student_id = s.id 
        WHERE $where 
        ORDER BY s.class, s.full_name, r.exam_name DESC
    ");
    
    if($results->num_rows > 0):
    ?>
        <table border="1" cellpadding="10" cellspacing="0" width="100%">
            <thead>
                <tr style="background:#667eea; color:white;">
                    <th>Admission No</th>
                    <th>Student Name</th>
                    <th>Class</th>
                    <th>Exam</th>
                    <th>Subject</th>
                    <th>Marks</th>
                    <th>Total</th>
                    <th>%</th>
                    <th>Grade</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $results->fetch_assoc()):
                    $percentage = ($row['marks'] / $row['total_marks']) * 100;
                    $grade = getGrade($row['marks'], $row['total_marks']);
                ?>
                    <tr>
                        <td><?= $row['admission_no'] ?></td>
                        <td><?= $row['full_name'] ?></td>
                        <td><?= $row['class'] ?></td>
                        <td><?= $row['exam_name'] ?></td>
                        <td><?= $row['subject'] ?></td>
                        <td><?= $row['marks'] ?></td>
                        <td><?= $row['total_marks'] ?></td>
                        <td><?= round($percentage, 1) ?>%</td>
                        <td><?= $grade ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="background:#f8d7da; padding:15px; border-radius:8px;">
            ❌ No results found.
        </div>
    <?php endif; ?>
    
    <div style="margin-top: 20px;">
        <a href="index.php">⬅ Back to Dashboard</a>
    </div>
</div>
</body>
</html>