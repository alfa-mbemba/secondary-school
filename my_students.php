<?php 
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
    <title>My Students - <?= htmlspecialchars($class) ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .students-table {
            width: 100%;
            border-collapse: collapse;
        }
        .students-table th, .students-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .students-table th {
            background: #667eea;
            color: white;
        }
        .action-links a {
            margin: 0 5px;
            text-decoration: none;
        }
        .present { color: green; font-weight: bold; }
        .absent { color: red; font-weight: bold; }
        .late { color: orange; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h2>👨‍🎓 My Students</h2>
    <p><strong>Class:</strong> <?= htmlspecialchars($class) ?> | <strong>Teacher:</strong> <?= htmlspecialchars($teacher['full_name']) ?></p>
    
    <?php
    $students = $conn->prepare("SELECT * FROM students WHERE class = ? ORDER BY admission_no");
    $students->bind_param("s", $class);
    $students->execute();
    $students_result = $students->get_result();
    
    if($students_result->num_rows > 0):
    ?>
        <table class="students-table">
            <thead>
                <tr>
                    <th>Admission No</th>
                    <th>Student Name</th>
                    <th>Parent Phone</th>
                    <th>Attendance %</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($student = $students_result->fetch_assoc()):
                    // Calculate attendance percentage
                    $att_stats = $conn->prepare("
                        SELECT COUNT(*) as total, SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) as present 
                        FROM attendance WHERE student_id = ?
                    ");
                    $att_stats->bind_param("i", $student['id']);
                    $att_stats->execute();
                    $att_data = $att_stats->get_result()->fetch_assoc();
                    $percentage = ($att_data['total'] > 0) ? round(($att_data['present'] / $att_data['total']) * 100, 1) : 0;
                    $percent_class = $percentage >= 75 ? 'present' : ($percentage >= 50 ? 'late' : 'absent');
                ?>
                <tr>
                    <td><?= htmlspecialchars($student['admission_no']) ?></td>
                    <td><strong><?= htmlspecialchars($student['full_name']) ?></strong></td>
                    <td><?= htmlspecialchars($student['parent_phone']) ?></td>
                    <td class="<?= $percent_class ?>"><?= $percentage ?>%</td>
                    <td class="action-links">
                        <a href="teacher_view_student_results.php?id=<?= $student['id'] ?>">📊 Results</a>
                        <a href="teacher_view_student_attendance.php?id=<?= $student['id'] ?>">📋 Attendance</a>
                        <a href="student_report_card.php?id=<?= $student['id'] ?>">📄 Report</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No students found in your class.</p>
    <?php endif; ?>
    
    <div style="margin-top: 20px;">
        <a href="teacher_dashboard.php">⬅ Back to Dashboard</a>
    </div>
</div>
</body>
</html>