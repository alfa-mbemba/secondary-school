<?php 
include 'config.php'; 
if(!isset($_SESSION['admin'])) header('Location: index.php');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fee Collection Report</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>💰 Fee Collection Report</h2>
    
    <?php
    // Summary statistics
    $total_fees = $conn->query("SELECT SUM(total_fees) as total FROM students")->fetch_assoc();
    $total_collected = $conn->query("SELECT SUM(fee_paid) as collected FROM students")->fetch_assoc();
    $total_pending = $total_fees['total'] - $total_collected['collected'];
    $total_students = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc();
    $defaulters = $conn->query("SELECT COUNT(*) as count FROM students WHERE total_fees - fee_paid > 500")->fetch_assoc();
    ?>
    
    <div class="summary-grid">
        <div class="summary-card">
            <h3>₹<?= number_format($total_fees['total'], 2) ?></h3>
            <p>Total Fees Expected</p>
        </div>
        <div class="summary-card">
            <h3 style="color:#d4edda;">₹<?= number_format($total_collected['collected'], 2) ?></h3>
            <p>Total Collected</p>
        </div>
        <div class="summary-card">
            <h3 style="color:#f8d7da;">₹<?= number_format($total_pending, 2) ?></h3>
            <p>Pending Amount</p>
        </div>
        <div class="summary-card">
            <h3><?= $defaulters['count'] ?></h3>
            <p>Defaulters (>₹500)</p>
        </div>
    </div>
    
    <h3>📋 Student Fee Details</h3>
    <table border="1" cellpadding="10" width="100%">
        <thead>
            <tr style="background:#667eea; color:white;">
                <th>Admission No</th>
                <th>Student Name</th>
                <th>Class</th>
                <th>Total Fees</th>
                <th>Paid</th>
                <th>Pending</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $students = $conn->query("SELECT * FROM students ORDER BY class, full_name");
            while($student = $students->fetch_assoc()):
                $pending = $student['total_fees'] - $student['fee_paid'];
                $status = $pending <= 0 ? 'Paid' : ($pending < 1000 ? 'Partial' : 'Pending');
                $status_color = $pending <= 0 ? 'green' : ($pending < 1000 ? 'orange' : 'red');
            ?>
                <tr>
                    <td><?= $student['admission_no'] ?></td>
                    <td><?= $student['full_name'] ?></td>
                    <td><?= $student['class'] ?></td>
                    <td>₹<?= number_format($student['total_fees'], 2) ?></td>
                    <td>₹<?= number_format($student['fee_paid'], 2) ?></td>
                    <td>₹<?= number_format($pending, 2) ?></td>
                    <td style="color: <?= $status_color ?>; font-weight: bold;"><?= $status ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <div style="margin-top: 20px;">
        <a href="index.php">⬅ Back to Dashboard</a>
        <button onclick="window.print()" style="float:right; background:#28a745;">🖨️ Print Report</button>
    </div>
</div>
</body>
</html>