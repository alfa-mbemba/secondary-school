<?php 
include 'config.php'; 

if(!isset($_SESSION['teacher'])){ 
    header('Location: teacher_login.php');
    exit();
}

$teacher = $_SESSION['teacher_data'];
$class = $teacher['class_assigned'];
$subject = $teacher['subject'];

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
    <title>View Results - <?= htmlspecialchars($subject) ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .filter-bar {
            background: #f0f4ff;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .results-table {
            width: 100%;
            border-collapse: collapse;
        }
        .results-table th, .results-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .results-table th {
            background: #667eea;
            color: white;
        }
        .grade-A { background-color: #d4edda; }
        .grade-B { background-color: #d1ecf1; }
        .grade-C { background-color: #fff3cd; }
        .grade-D { background-color: #ffeeba; }
        .grade-E { background-color: #f8d7da; }
        .btn-print {
            background: #17a2b8;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📊 View Results</h2>
    <p><strong>Subject:</strong> <?= htmlspecialchars($subject) ?> | <strong>Class:</strong> <?= htmlspecialchars($class) ?></p>
    
    <div class="filter-bar">
        <form method="GET">
            <select name="exam_name">
                <option value="">-- All Exams --</option>
                <?php
                $exams = $conn->query("SELECT DISTINCT exam_name FROM results WHERE subject='$subject'");
                while($exam = $exams->fetch_assoc()){
                    $selected = (isset($_GET['exam_name']) && $_GET['exam_name'] == $exam['exam_name']) ? 'selected' : '';
                    echo "<option value='{$exam['exam_name']}' $selected>{$exam['exam_name']}</option>";
                }
                ?>
            </select>
            <button type="submit">🔍 Filter</button>
            <button type="button" class="btn-print" onclick="window.print()">🖨️ Print</button>
        </form>
    </div>
    
    <?php
    $where = "r.subject = '$subject' AND s.class = '$class'";
    if(isset($_GET['exam_name']) && $_GET['exam_name'] != ''){
        $where .= " AND r.exam_name = '{$_GET['exam_name']}'";
    }
    
    $results = $conn->query("
        SELECT r.*, s.full_name, s.admission_no 
        FROM results r 
        JOIN students s ON r.student_id = s.id 
        WHERE $where 
        ORDER BY s.full_name, r.exam_name DESC
    ");
    
    if($results && $results->num_rows > 0):
    ?>
        <table class="results-table">
            <thead>
                <tr>
                    <th>Admission No</th>
                    <th>Student Name</th>
                    <th>Exam</th>
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
                    <tr class="grade-<?= $grade ?>">
                        <td><?= htmlspecialchars($row['admission_no']) ?></td>
                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                        <td><?= htmlspecialchars($row['exam_name']) ?></td>
                        <td><?= $row['marks'] ?></td>
                        <td><?= $row['total_marks'] ?></td>
                        <td><?= round($percentage, 1) ?>%</td>
                        <td><?= $grade ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No results found.</p>
    <?php endif; ?>
    
    <div style="margin-top: 20px;">
        <a href="teacher_dashboard.php">⬅ Back to Dashboard</a>
    </div>
</div>
</body>
</html>