<?php 
include 'config.php'; 

// Check if user is logged in (either teacher or parent)
$is_teacher = isset($_SESSION['teacher']);
$is_parent = isset($_SESSION['parent_logged_in']);

if(!$is_teacher && !$is_parent){
    header('Location: index.php');
    exit();
}

// Get student ID from URL
$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no ID provided, try to get from session (for parents)
if($student_id == 0 && $is_parent){
    $student_id = $_SESSION['student_id'] ?? 0;
}

// If still no ID, show error
if($student_id == 0){
    die("❌ No student selected. Please go back and select a student.");
}

// Get student details
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student_result = $stmt->get_result();

if($student_result->num_rows == 0){
    die("❌ Student not found. ID: $student_id");
}

$student = $student_result->fetch_assoc();
$stmt->close();

// Function to calculate grade
function getGrade($marks, $total = 100) {
    $percentage = ($marks / $total) * 100;
    if($percentage >= 80) return ['grade' => 'A', 'color' => '#28a745', 'remark' => 'Excellent'];
    if($percentage >= 70) return ['grade' => 'B', 'color' => '#17a2b8', 'remark' => 'Very Good'];
    if($percentage >= 60) return ['grade' => 'C', 'color' => '#ffc107', 'remark' => 'Good'];
    if($percentage >= 50) return ['grade' => 'D', 'color' => '#fd7e14', 'remark' => 'Satisfactory'];
    return ['grade' => 'E', 'color' => '#dc3545', 'remark' => 'Needs Improvement'];
}

// Get all exams for this student
$exams_stmt = $conn->prepare("SELECT DISTINCT exam_name FROM results WHERE student_id = ? ORDER BY exam_name DESC");
$exams_stmt->bind_param("i", $student_id);
$exams_stmt->execute();
$exams_result = $exams_stmt->get_result();

// Get attendance summary
$attendance_stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_days,
        SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) as present_days,
        SUM(CASE WHEN status='Absent' THEN 1 ELSE 0 END) as absent_days,
        SUM(CASE WHEN status='Late' THEN 1 ELSE 0 END) as late_days
    FROM attendance WHERE student_id = ?
");
$attendance_stmt->bind_param("i", $student_id);
$attendance_stmt->execute();
$attendance_summary = $attendance_stmt->get_result()->fetch_assoc();
$att_percentage = ($attendance_summary['total_days'] > 0) ? round(($attendance_summary['present_days'] / $attendance_summary['total_days']) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Report Card - <?= htmlspecialchars($student['full_name']) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background: #f0f2f5;
            padding: 40px 20px;
        }
        .report-card {
            max-width: 900px;
            margin: auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            padding: 30px;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        .header h3 {
            font-size: 16px;
            font-weight: normal;
        }
        .student-info {
            background: #f8f9fa;
            padding: 20px 30px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            border-bottom: 2px solid #667eea;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }
        .info-label {
            font-weight: bold;
            color: #555;
        }
        .section {
            padding: 20px 30px;
            border-bottom: 1px solid #eee;
        }
        .section-title {
            font-size: 18px;
            color: #667eea;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #667eea;
            display: inline-block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f0f4ff;
            color: #333;
        }
        .grade-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: bold;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 15px;
        }
        .summary-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            text-align: center;
        }
        .summary-card h4 {
            color: #667eea;
            margin-bottom: 8px;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        @media print {
            body { background: white; padding: 0; }
            .no-print { display: none; }
            .report-card { box-shadow: none; border-radius: 0; }
        }
        .no-print {
            text-align: center;
            margin-top: 20px;
        }
        .no-print button {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            cursor: pointer;
            margin: 0 10px;
            font-size: 16px;
        }
        .no-print button:hover {
            background: #764ba2;
        }
        .error-box {
            background: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin: 50px auto;
            max-width: 500px;
        }
    </style>
</head>
<body>

<?php if($exams_result->num_rows == 0): ?>
<div class="error-box">
    <h3>⚠️ No Results Available</h3>
    <p>No exam results have been recorded for <?= htmlspecialchars($student['full_name']) ?> yet.</p>
    <br>
    <a href="javascript:history.back()" class="no-print">← Go Back</a>
</div>
<?php else: ?>

<div class="report-card">
    <div class="header">
        <h1>🏫 SECONDARY SCHOOL</h1>
        <h3>Student Progress Report Card</h3>
        <p>Academic Year <?= date('Y') . '-' . (date('Y')+1) ?></p>
    </div>
    
    <div class="student-info">
        <div class="info-item"><span class="info-label">Student Name:</span><span class="info-value"><?= htmlspecialchars($student['full_name']) ?></span></div>
        <div class="info-item"><span class="info-label">Admission No:</span><span class="info-value"><?= htmlspecialchars($student['admission_no']) ?></span></div>
        <div class="info-item"><span class="info-label">Class:</span><span class="info-value"><?= htmlspecialchars($student['class']) ?></span></div>
        <div class="info-item"><span class="info-label">Report Date:</span><span class="info-value"><?= date('d M Y') ?></span></div>
    </div>
    
    <div class="section">
        <div class="section-title">📊 Academic Performance</div>
        
        <?php while($exam = $exams_result->fetch_assoc()): 
            $exam_name = $exam['exam_name'];
            $results_stmt = $conn->prepare("SELECT * FROM results WHERE student_id = ? AND exam_name = ?");
            $results_stmt->bind_param("is", $student_id, $exam_name);
            $results_stmt->execute();
            $results = $results_stmt->get_result();
        ?>
            <h4 style="margin: 20px 0 10px 0;">📝 <?= htmlspecialchars($exam_name) ?> Examination</h4>
            <table>
                <thead>
                    <tr><th>Subject</th><th>Marks</th><th>Total</th><th>%</th><th>Grade</th><th>Remark</th></tr>
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
                            <td><?= $grade_info['remark'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                    <tr style="background:#f0f4ff; font-weight:bold;">
                        <td colspan="2">TOTAL / AVERAGE</td>
                        <td><?= $total_obtained ?> / <?= $total_max ?></td>
                        <td><?= round(($total_obtained / $total_max) * 100, 1) ?>%</td>
                        <td colspan="2"><?php $overall = getGrade($total_obtained, $total_max); ?>
                            <span class="grade-badge" style="background:<?= $overall['color'] ?>20; color:<?= $overall['color'] ?>;"><?= $overall['grade'] ?> - <?= $overall['remark'] ?></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php 
            $results_stmt->close();
        endwhile; 
        ?>
    </div>
    
    <div class="section">
        <div class="section-title">📋 Attendance Summary</div>
        <div class="summary-grid">
            <div class="summary-card"><h4>Total Days</h4><h2><?= $attendance_summary['total_days'] ?? 0 ?></h2></div>
            <div class="summary-card"><h4>✅ Present</h4><h2 style="color:#28a745;"><?= $attendance_summary['present_days'] ?? 0 ?></h2></div>
            <div class="summary-card"><h4>📊 Attendance %</h4><h2 style="color:#667eea;"><?= $att_percentage ?>%</h2></div>
        </div>
    </div>
    
    <div class="section">
        <div class="section-title">💰 Fee Status</div>
        <div class="summary-grid">
            <div class="summary-card"><h4>Total Fees</h4><h2>₹<?= number_format($student['total_fees'], 2) ?></h2></div>
            <div class="summary-card"><h4>✅ Paid</h4><h2 style="color:#28a745;">₹<?= number_format($student['fee_paid'], 2) ?></h2></div>
            <div class="summary-card"><h4>⚠️ Balance</h4><h2 style="color:<?= ($student['total_fees'] - $student['fee_paid']) > 0 ? '#dc3545' : '#28a745' ?>;">
                ₹<?= number_format($student['total_fees'] - $student['fee_paid'], 2) ?>
            </h2></div>
        </div>
    </div>
    
    <div class="footer">
        <p>This is a system-generated report card. For any discrepancies, please contact the school office.</p>
        <p>Generated on: <?= date('d-m-Y h:i A') ?></p>
    </div>
</div>

<div class="no-print">
    <button onclick="window.print()">🖨️ Print Report Card</button>
    <button onclick="window.location.href='javascript:history.back()'">⬅ Back</button>
</div>

<?php endif; ?>

</body>
</html>