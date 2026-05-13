<?php 
include 'config.php'; 

// Only teachers can access this page
if(!isset($_SESSION['teacher'])){
    header('Location: access_denied.php');
    exit();
}

$teacher = $_SESSION['teacher_data'];
// ... rest of the page
include 'config.php'; 
if(!isset($_SESSION['teacher'])) header('Location: teacher_login.php');
$teacher = $_SESSION['teacher_data'];
$class = $teacher['class_assigned'];
$subject = $teacher['subject'];

// Function to calculate grade
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
    <title>View Results - <?= $subject ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .grade-A { background-color: #d4edda; color: #155724; font-weight: bold; }
        .grade-B { background-color: #d1ecf1; color: #0c5460; font-weight: bold; }
        .grade-C { background-color: #fff3cd; color: #856404; font-weight: bold; }
        .grade-D { background-color: #ffeeba; color: #856404; }
        .grade-E { background-color: #f8d7da; color: #721c24; }
        .filter-bar { background: #f0f4ff; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
        .export-btn { background: #28a745; }
    </style>
</head>
<body>
<div class="container">
    <h2>📊 View Results - <?= $subject ?> (<?= $class ?>)</h2>
    
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
            <select name="student_id">
                <option value="">-- All Students --</option>
                <?php
                $students = $conn->query("SELECT id, admission_no, full_name FROM students WHERE class='$class'");
                while($student = $students->fetch_assoc()){
                    $selected = (isset($_GET['student_id']) && $_GET['student_id'] == $student['id']) ? 'selected' : '';
                    echo "<option value='{$student['id']}' $selected>{$student['admission_no']} - {$student['full_name']}</option>";
                }
                ?>
            </select>
            <button type="submit">🔍 Filter</button>
            <button type="button" class="export-btn" onclick="exportToCSV()">📎 Export to CSV</button>
        </form>
    </div>
    
    <?php
    // Build query
    $where = "r.subject = '$subject' AND s.class = '$class'";
    if(isset($_GET['exam_name']) && $_GET['exam_name'] != ''){
        $where .= " AND r.exam_name = '{$_GET['exam_name']}'";
    }
    if(isset($_GET['student_id']) && $_GET['student_id'] != ''){
        $where .= " AND r.student_id = {$_GET['student_id']}";
    }
    
    $results = $conn->query("
        SELECT r.*, s.full_name, s.admission_no 
        FROM results r 
        JOIN students s ON r.student_id = s.id 
        WHERE $where 
        ORDER BY s.full_name, r.exam_name DESC
    ");
    
    if($results->num_rows > 0):
    ?>
        <table border="1" cellpadding="10" cellspacing="0" width="100%" id="resultsTable">
            <thead>
                <tr>
                    <th>Admission No</th>
                    <th>Student Name</th>
                    <th>Exam</th>
                    <th>Subject</th>
                    <th>Marks</th>
                    <th>Total</th>
                    <th>Percentage</th>
                    <th>Grade</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $results->fetch_assoc()):
                    $percentage = ($row['marks'] / $row['total_marks']) * 100;
                    $grade = getGrade($row['marks'], $row['total_marks']);
                    $grade_class = "grade-" . $grade;
                ?>
                    <tr class="<?= $grade_class ?>">
                        <td><?= $row['admission_no'] ?></td>
                        <td><?= $row['full_name'] ?></td>
                        <td><?= $row['exam_name'] ?></td>
                        <td><?= $row['subject'] ?></td>
                        <td><?= $row['marks'] ?></td>
                        <td><?= $row['total_marks'] ?></td>
                        <td><?= round($percentage, 1) ?>%</td>
                        <td><?= $grade ?></td>
                        <td>
                            <a href="teacher_edit_result.php?id=<?= $row['id'] ?>" style="color:#ffc107;">✏️ Edit</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <!-- Summary Statistics -->
        <?php
        $stats = $conn->query("
            SELECT 
                AVG((r.marks / r.total_marks) * 100) as avg_percentage,
                COUNT(r.id) as total_results
            FROM results r
            JOIN students s ON r.student_id = s.id
            WHERE $where
        ")->fetch_assoc();
        ?>
        <div style="background: #e8f5e9; padding: 15px; border-radius: 10px; margin-top: 20px;">
            <h4>📈 Summary Statistics</h4>
            <p>Total Results: <?= $stats['total_results'] ?> | Average Score: <?= round($stats['avg_percentage'], 1) ?>%</p>
        </div>
        
    <?php else: ?>
        <div style="background: #f8d7da; padding: 15px; border-radius: 10px; color: #721c24;">
            ❌ No results found for the selected criteria.
        </div>
    <?php endif; ?>
    
    <div style="margin-top: 20px;">
        <a href="teacher_dashboard.php">⬅ Back to Dashboard</a>
    </div>
</div>

<script>
function exportToCSV() {
    let table = document.getElementById("resultsTable");
    let rows = table.querySelectorAll("tr");
    let csv = [];
    
    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll("th, td");
        for (let j = 0; j < cols.length - 1; j++) { // Exclude Action column
            row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
        }
        csv.push(row.join(","));
    }
    
    let blob = new Blob([csv.join("\n")], { type: "text/csv" });
    let a = document.createElement("a");
    a.href = URL.createObjectURL(blob);
    a.download = "results_<?= $subject ?>_<?= date('Y-m-d') ?>.csv";
    a.click();
}
</script>
</body>
</html>