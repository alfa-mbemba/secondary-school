<?php 
include 'config.php'; 
if(!isset($_SESSION['parent_logged_in'])) header('Location: parent_login.php');

$student_id = $_SESSION['student_id'];
$student = $conn->query("SELECT * FROM students WHERE id = $student_id")->fetch_assoc();
$pending = $student['total_fees'] - $student['fee_paid'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fee Details - <?= $student['full_name'] ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .fee-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .fee-amount {
            font-size: 36px;
            font-weight: bold;
        }
        .paid { color: #28a745; }
        .pending { color: #dc3545; }
        .progress-bar {
            background: #e9ecef;
            border-radius: 10px;
            height: 20px;
            overflow: hidden;
            margin: 15px 0;
        }
        .progress-fill {
            background: linear-gradient(90deg, #28a745, #20c997);
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s;
        }
        .payment-table {
            width: 100%;
            border-collapse: collapse;
        }
        .payment-table th, .payment-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .payment-table th {
            background: #f0f4ff;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>💰 Fee Statement</h2>
    
    <div style="background:#f0f4ff; padding:15px; border-radius:10px; margin-bottom:20px;">
        <p><strong>Student:</strong> <?= htmlspecialchars($student['full_name']) ?> (<?= $student['admission_no'] ?>)</p>
        <p><strong>Class:</strong> <?= $student['class'] ?></p>
    </div>
    
    <div class="fee-card">
        <h3>Fee Summary</h3>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); text-align: center;">
            <div>
                <small>Total Fees</small>
                <div class="fee-amount">₹<?= number_format($student['total_fees'], 2) ?></div>
            </div>
            <div>
                <small>Paid Amount</small>
                <div class="fee-amount paid">₹<?= number_format($student['fee_paid'], 2) ?></div>
            </div>
            <div>
                <small>Pending Amount</small>
                <div class="fee-amount pending">₹<?= number_format($pending, 2) ?></div>
            </div>
        </div>
        
        <?php $percentage = ($student['fee_paid'] / $student['total_fees']) * 100; ?>
        <div class="progress-bar">
            <div class="progress-fill" style="width: <?= $percentage ?>%;"></div>
        </div>
        
        <?php if($pending > 0): ?>
            <div style="background:#fff3cd; padding:15px; border-radius:8px; margin-top:15px;">
                ⚠️ Please clear the pending fee of <strong>₹<?= number_format($pending, 2) ?></strong> by <strong><?= date('d M Y', strtotime('+30 days')) ?></strong>
            </div>
        <?php else: ?>
            <div style="background:#d4edda; padding:15px; border-radius:8px; margin-top:15px;">
                ✅ All fees paid. Thank you!
            </div>
        <?php endif; ?>
    </div>
    
    <h3>Payment History</h3>
    <?php
    $payments = $conn->query("SELECT * FROM fee_payments WHERE student_id = $student_id ORDER BY payment_date DESC");
    if($payments->num_rows > 0):
    ?>
        <table class="payment-table">
            <thead>
                <tr><th>Date</th><th>Amount</th><th>Method</th><th>Transaction ID</th><th>Receipt No</th></tr>
            </thead>
            <tbody>
                <?php while($payment = $payments->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('d M Y', strtotime($payment['payment_date'])) ?></td>
                        <td>₹<?= number_format($payment['amount'], 2) ?></td>
                        <td><?= $payment['payment_method'] ?></td>
                        <td><?= $payment['transaction_id'] ?: '-' ?></td>
                        <td><?= $payment['receipt_no'] ?: '-' ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No payment records found.</p>
    <?php endif; ?>
    
    <div style="margin-top: 20px;">
        <a href="parent_dashboard.php">⬅ Back to Dashboard</a>
    </div>
</div>
</body>
</html>