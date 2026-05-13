<?php 
include 'config.php'; 
if(!isset($_SESSION['parent_logged_in'])) header('Location: parent_login.php');

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];
$admission_no = $_SESSION['student_admission_no'];
$student_class = $_SESSION['student_class'];

function getGrade($marks, $total = 100) {
    $percentage = ($marks / $total) * 100;
    if($percentage >= 80) return ['grade' => 'A', 'color' => '#28a745', 'remark' => 'Excellent'];
    if($percentage >= 70) return ['grade' => 'B', 'color' => '#17a2b8', 'remark' => 'Very Good'];
    if($percentage >= 60) return ['grade' => 'C', 'color' => '#ffc107', 'remark' => 'Good'];
    if($percentage >= 50) return ['grade' => 'D', 'color' => '#fd7e14', 'remark' => 'Satisfactory'];
    return ['grade' => 'E', 'color' => '#dc3545', 'remark' => 'Needs Improvement'];
}

// Get all results
$all_results = $conn->query("SELECT * FROM results WHERE student_id = $student_id");

// Get attendance summary for the year
$attendance_summary = $conn->query("
    SELECT 
        COUNT(*) as total_days,
        SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) as present_days,
        SUM(CASE WHEN status='Absent' THEN 1 ELSE 0 END) as absent_days,
        SUM(CASE WHEN status='Late' THEN 1 ELSE 0 END) as late_days
    FROM attendance WHERE student_id = $student_id
")->fetch_assoc();

$att_percentage = $attendance_summary['total_days'] > 0 ? round(($attendance_summary['present_days'] / $attendance_summary['total_days']) * 100, 1) : 0;

// Get fee status
$student = $conn->query("SELECT * FROM students WHERE id = $student_id")->fetch_assoc();
$pending_fee = $student['total_fees'] - $student['fee_paid'];
$fee_status = $pending_fee <= 0 ? 'Paid' : 'Pending: ₹' . number_format($pending_fee, 2);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Report Card - <?= $student_name ?></title>
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
        }
        .grade-A { background: #d4edda; }
        .grade-B { background: #d1ecf1; }
        .grade-C { background: #fff3cd; }
        .grade-D { background: #ffeeba; }
        .grade-E { background: #f8d7da; }
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
    </style>
</head>
<body>
<div class="report-card">
    <div class="header">
        <h1>🏫 SECONDARY SCHOOL</h1>
        <h3>Student Progress Report Card</h3>
        <p>Academic Year 2024-2025</p>
    </div>
    
    <div class="student-info">
        <div class="info-item"><span class="info-label">Student Name:</span><span class="info-value"><?= htmlspecialchars($student_name) ?></span></div>
        <div class="info-item"><span class="info-label">Admission No:</span><span class="info-value"><?= $admission_no ?></span></div>
        <div class="info-item"><span class="info-label">Class:</span><span class="info-value"><?= $student_class ?></span></div>
        <div class="info-item"><span class="info-label">Report Date:</span><span class="info-value"><?= date('d M Y') ?></span></div>
    </div>
    
    <div class="section">
        <div class="section-title">📊 Academic Performance</div>
        <?php
        $exams = $conn->query("SELECT DISTINCT exam_name FROM results WHERE student_id = $student_id ORDER BY exam_name DESC");
        if($exams->num_rows > 0):
            while($exam = $exams->fetch_assoc()):
                $exam_name = $exam['exam_name'];
                $results = $conn->query("SELECT * FROM results WHERE student_id = $student_id AND exam_name = '$exam_name'");
        ?>
                <h4 style="margin: 20px 0 10px 0;"><?= htmlspecialchars($exam_name) ?> Examination</h4>
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
                            <tr class="grade-<?= $grade_info['grade'] ?>">
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
            endwhile;
        else:
        ?>
            <p>No results available yet.</p>
        <?php endif; ?>
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
            <div class="summary-card"><h4>⚠️ Balance</h4><h2 style="color:#<?= $pending_fee <= 0 ? '28a745' : 'dc3545' ?>;"><?= $fee_status ?></h2></div>
        </div>
    </div>
    
    <div class="footer">
        <p>This is a system-generated report card. For any discrepancies, please contact the school office.</p>
        <p>Generated on: <?= date('d-m-Y h:i A') ?></p>
    </div>
</div>

<div class="no-print">
    <button onclick="window.print()">🖨️ Print Report Card</button>
    <button onclick="window.location.href='parent_dashboard.php'">⬅ Back to Dashboard</button>
</div>
</body>
</html>