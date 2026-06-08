<?php 
include 'config.php'; 

// Only teachers can access this page
if(!isset($_SESSION['teacher'])){ 
    header('Location: access_denied.php');
    exit();
}

$teacher = $_SESSION['teacher_data'];
$class = $teacher['class_assigned'];

// Handle attendance submission
$message = '';
$error = '';

if(isset($_POST['save_attendance'])){
    $date = $_POST['attendance_date'];
    
    // Check if attendance already marked for this date
    $check = $conn->prepare("SELECT id FROM attendance WHERE date = ? AND teacher_id = ? LIMIT 1");
    $check->bind_param("si", $date, $teacher['id']);
    $check->execute();
    $check_result = $check->get_result();
    
    if($check_result->num_rows > 0){
        // Delete existing attendance for this date to allow update
        $delete = $conn->prepare("DELETE FROM attendance WHERE date = ? AND teacher_id = ?");
        $delete->bind_param("si", $date, $teacher['id']);
        $delete->execute();
        $delete->close();
    }
    $check->close();
    
    $success_count = 0;
    $error_count = 0;
    
    // Insert new attendance records
    if(isset($_POST['status']) && is_array($_POST['status'])){
        $stmt = $conn->prepare("INSERT INTO attendance (student_id, teacher_id, date, status, remark) VALUES (?, ?, ?, ?, ?)");
        
        foreach($_POST['status'] as $student_id => $status){
            $remark = isset($_POST['remark'][$student_id]) ? $_POST['remark'][$student_id] : '';
            $stmt->bind_param("iisss", $student_id, $teacher['id'], $date, $status, $remark);
            if($stmt->execute()){
                $success_count++;
            } else {
                $error_count++;
            }
        }
        $stmt->close();
        $message = "✅ Attendance saved! $success_count records updated. $error_count errors.";
    } else {
        $error = "❌ No attendance data received.";
    }
}

// Get students for this class
$students = $conn->prepare("SELECT id, admission_no, full_name FROM students WHERE class = ? ORDER BY admission_no");
$students->bind_param("s", $class);
$students->execute();
$students_result = $students->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mark Attendance - <?= htmlspecialchars($class) ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .attendance-form {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
        }
        .date-box {
            background: #f0f4ff;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        .date-box label {
            font-weight: bold;
        }
        .date-box input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
        }
        .attendance-table th, .attendance-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .attendance-table th {
            background: #667eea;
            color: white;
        }
        .attendance-table select {
            padding: 6px 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .status-present { background-color: #d4edda; color: #155724; }
        .status-absent { background-color: #f8d7da; color: #721c24; }
        .status-late { background-color: #fff3cd; color: #856404; }
        .btn-save {
            background: #28a745;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
        .btn-save:hover {
            background: #218838;
        }
        .message-success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .message-error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .legend {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .legend span {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 3px;
            margin-right: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📋 Mark Attendance</h2>
    <p><strong>Class:</strong> <?= htmlspecialchars($class) ?> | <strong>Teacher:</strong> <?= htmlspecialchars($teacher['full_name']) ?></p>
    
    <?php if($message): ?>
        <div class="message-success"><?= $message ?></div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div class="message-error"><?= $error ?></div>
    <?php endif; ?>
    
    <div class="legend">
        <div><span style="background:#d4edda;"></span> Present</div>
        <div><span style="background:#f8d7da;"></span> Absent</div>
        <div><span style="background:#fff3cd;"></span> Late</div>
    </div>
    
    <div class="attendance-form">
        <form method="POST">
            <div class="date-box">
                <label>📅 Date:</label>
                <input type="date" name="attendance_date" value="<?= date('Y-m-d') ?>" required>
                <button type="submit" name="save_attendance" class="btn-save" style="margin-top:0; padding:8px 20px;">💾 Save Attendance</button>
            </div>
            
            <?php if($students_result->num_rows > 0): ?>
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th>Admission No</th>
                            <th>Student Name</th>
                            <th>Status</th>
                            <th>Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($student = $students_result->fetch_assoc()): 
                            // Check if attendance already exists for today
                            $today = date('Y-m-d');
                            $check_attendance = $conn->prepare("SELECT status, remark FROM attendance WHERE student_id = ? AND date = ?");
                            $check_attendance->bind_param("is", $student['id'], $today);
                            $check_attendance->execute();
                            $existing = $check_attendance->get_result()->fetch_assoc();
                            $check_attendance->close();
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($student['admission_no']) ?></td>
                            <td><strong><?= htmlspecialchars($student['full_name']) ?></strong></td>
                            <td>
                                <select name="status[<?= $student['id'] ?>]" required>
                                    <option value="Present" <?= ($existing && $existing['status'] == 'Present') ? 'selected' : '' ?>>✅ Present</option>
                                    <option value="Absent" <?= ($existing && $existing['status'] == 'Absent') ? 'selected' : '' ?>>❌ Absent</option>
                                    <option value="Late" <?= ($existing && $existing['status'] == 'Late') ? 'selected' : '' ?>>⏰ Late</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="remark[<?= $student['id'] ?>]" placeholder="Optional remark..." value="<?= htmlspecialchars($existing['remark'] ?? '') ?>" style="width:100%;">
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <button type="submit" name="save_attendance" class="btn-save">💾 Save Attendance</button>
            <?php else: ?>
                <div style="background:#f8d7da; padding:20px; text-align:center; border-radius:8px;">
                    ❌ No students found in <?= htmlspecialchars($class) ?>. Please ask admin to add students.
                </div>
            <?php endif; ?>
        </form>
    </div>
    
    <div style="margin-top: 20px;">
        <a href="teacher_dashboard.php">⬅ Back to Dashboard</a>
        <a href="teacher_view_attendance.php" style="margin-left: 15px;">📊 View Attendance Report</a>
    </div>
</div>
</body>
</html>