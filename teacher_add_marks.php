<?php 
include 'config.php'; 

if(!isset($_SESSION['teacher'])){ 
    header('Location: teacher_login.php');
    exit();
}

$teacher = $_SESSION['teacher_data'];
$class = $teacher['class_assigned'];
$subject = $teacher['subject'];
$message = '';
$error = '';

// Handle marks submission
if(isset($_POST['save_marks'])){
    $exam_name = trim($_POST['exam_name']);
    $total_marks = intval($_POST['total_marks']);
    
    foreach($_POST['marks'] as $student_id => $marks){
        if($marks !== ''){
            $marks = intval($marks);
            // Check if record exists
            $check = $conn->prepare("SELECT id FROM results WHERE student_id = ? AND exam_name = ? AND subject = ?");
            $check->bind_param("iss", $student_id, $exam_name, $subject);
            $check->execute();
            $result = $check->get_result();
            
            if($result->num_rows > 0){
                // Update existing
                $update = $conn->prepare("UPDATE results SET marks = ?, total_marks = ? WHERE student_id = ? AND exam_name = ? AND subject = ?");
                $update->bind_param("iiiss", $marks, $total_marks, $student_id, $exam_name, $subject);
                $update->execute();
                $update->close();
            } else {
                // Insert new
                $insert = $conn->prepare("INSERT INTO results (student_id, exam_name, subject, marks, total_marks) VALUES (?, ?, ?, ?, ?)");
                $insert->bind_param("issii", $student_id, $exam_name, $subject, $marks, $total_marks);
                $insert->execute();
                $insert->close();
            }
            $check->close();
        }
    }
    $message = "✅ Marks saved successfully for $exam_name!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Enter Marks - <?= htmlspecialchars($subject) ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .marks-form {
            background: white;
            padding: 20px;
            border-radius: 15px;
        }
        .exam-info {
            background: #f0f4ff;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .marks-table {
            width: 100%;
            border-collapse: collapse;
        }
        .marks-table th, .marks-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .marks-table th {
            background: #667eea;
            color: white;
        }
        .marks-table input {
            width: 80px;
            padding: 8px;
            text-align: center;
        }
        .btn-save {
            background: #28a745;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 20px;
        }
        .message-success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📝 Enter Marks</h2>
    <p><strong>Subject:</strong> <?= htmlspecialchars($subject) ?> | <strong>Class:</strong> <?= htmlspecialchars($class) ?></p>
    
    <?php if($message): ?>
        <div class="message-success"><?= $message ?></div>
    <?php endif; ?>
    
    <div class="marks-form">
        <form method="POST">
            <div class="exam-info">
                <label>Exam Name: </label>
                <input type="text" name="exam_name" placeholder="e.g., Mid Term, Final Exam" required style="width:200px;">
                <label style="margin-left:20px;">Total Marks: </label>
                <input type="number" name="total_marks" value="100" required style="width:80px;">
            </div>
            
            <?php
            $students = $conn->prepare("SELECT id, admission_no, full_name FROM students WHERE class = ? ORDER BY admission_no");
            $students->bind_param("s", $class);
            $students->execute();
            $students_result = $students->get_result();
            
            if($students_result->num_rows > 0):
            ?>
                <table class="marks-table">
                    <thead>
                        <tr>
                            <th>Admission No</th>
                            <th>Student Name</th>
                            <th>Marks Obtained</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($student = $students_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['admission_no']) ?></td>
                            <td><?= htmlspecialchars($student['full_name']) ?></td>
                            <td><input type="number" name="marks[<?= $student['id'] ?>]" min="0" max="100" value=""></td>
                            <td id="status_<?= $student['id'] ?>">-</td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <button type="submit" name="save_marks" class="btn-save">💾 Save All Marks</button>
            <?php else: ?>
                <p>No students found in this class.</p>
            <?php endif; ?>
        </form>
    </div>
    
    <div style="margin-top: 20px;">
        <a href="teacher_dashboard.php">⬅ Back to Dashboard</a>
    </div>
</div>

<script>
// Auto-calculate status as teacher types
document.querySelectorAll('input[type="number"]').forEach(input => {
    input.addEventListener('input', function() {
        let row = this.closest('tr');
        let statusCell = row.cells[3];
        let marks = parseInt(this.value);
        let total = parseInt(document.querySelector('input[name="total_marks"]').value);
        
        if(!isNaN(marks) && total > 0){
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