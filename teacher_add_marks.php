<?php 
include 'config.php'; 

// Only teachers can access this page
if(!isset($_SESSION['teacher'])){
    header('Location: access_denied.php');
    exit();
}

$teacher = $_SESSION['teacher_data'];
$class = $teacher['class_assigned'];
$subject = $teacher['subject'];
// ... rest of the page

include 'config.php'; 
if(!isset($_SESSION['teacher'])) header('Location: teacher_login.php');
$teacher = $_SESSION['teacher_data'];
$class = $teacher['class_assigned'];
$subject = $teacher['subject'];

// Handle marks submission
if(isset($_POST['save_marks'])){
    $exam_name = $_POST['exam_name'];
    $total_marks = $_POST['total_marks'];
    
    foreach($_POST['marks'] as $student_id => $marks){
        if($marks !== ''){
            $check = $conn->query("SELECT id FROM results WHERE student_id=$student_id AND exam_name='$exam_name' AND subject='$subject'");
            if($check->num_rows > 0){
                $conn->query("UPDATE results SET marks='$marks', total_marks='$total_marks' WHERE student_id=$student_id AND exam_name='$exam_name' AND subject='$subject'");
            } else {
                $conn->query("INSERT INTO results (student_id, exam_name, subject, marks, total_marks) VALUES ('$student_id', '$exam_name', '$subject', '$marks', '$total_marks')");
            }
        }
    }
    echo "<script>alert('✅ Marks saved successfully!');</script>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Marks - <?= $subject ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .marks-table input { width: 80px; text-align: center; }
    </style>
</head>
<body>
<div class="container">
    <h2>📝 Enter Marks for <?= $subject ?></h2>
    <p>Class: <?= $class ?> | Teacher: <?= $teacher['full_name'] ?></p>
    
    <form method="POST">
        <div style="background:#f0f4ff; padding:15px; border-radius:10px; margin:15px 0;">
            <input type="text" name="exam_name" placeholder="Exam Name (e.g., Mid Term, Final)" required style="width:40%;">
            <input type="number" name="total_marks" placeholder="Total Marks" value="100" required style="width:20%;">
        </div>
        
        <table class="marks-table" border="1" cellpadding="8" width="100%">
            <thead>
                <tr><th>Admission No</th><th>Student Name</th><th>Marks Obtained</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php
                $students = $conn->query("SELECT * FROM students WHERE class='$class' ORDER BY admission_no");
                while($student = $students->fetch_assoc()):
                    // Get existing marks if any
                    $existing = $conn->query("SELECT marks FROM results WHERE student_id={$student['id']} AND subject='$subject' AND exam_name='".($exam_name ?? '')."' LIMIT 1")->fetch_assoc();
                ?>
                <tr>
                    <td><?= $student['admission_no'] ?></td>
                    <td><?= $student['full_name'] ?></td>
                    <td><input type="number" name="marks[<?= $student['id'] ?>]" value="<?= $existing['marks'] ?? '' ?>" min="0" max="<?= $_POST['total_marks'] ?? 100 ?>"></td>
                    <td id="status_<?= $student['id'] ?>"></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <button type="submit" name="save_marks" style="background:#28a745;">💾 Save All Marks</button>
    </form>
    
    <a href="teacher_dashboard.php">⬅ Back to Dashboard</a>
</div>

<script>
// Real-time status update (optional)
document.querySelectorAll('input[type="number"]').forEach(input => {
    input.addEventListener('change', function() {
        let row = this.closest('tr');
        let statusCell = row.cells[3];
        let marks = parseInt(this.value);
        let total = parseInt(document.querySelector('input[name="total_marks"]').value);
        if(!isNaN(marks) && !isNaN(total) && total > 0){
            let percentage = (marks / total) * 100;
            let grade = percentage >= 80 ? 'A' : (percentage >= 70 ? 'B' : (percentage >= 60 ? 'C' : (percentage >= 50 ? 'D' : 'E')));
            statusCell.innerHTML = `${percentage.toFixed(1)}% (${grade})`;
            statusCell.style.color = percentage >= 75 ? 'green' : (percentage >= 50 ? 'orange' : 'red');
        } else {
            statusCell.innerHTML = '-';
        }
    });
});
</script>
</body>
</html>