<?php 
include 'config.php'; 
if(!isset($_SESSION['parent'])) header('Location: parent_login.php');
$student_id = $_GET['id'] ?? 0;
$student = $conn->query("SELECT * FROM students WHERE id=$student_id")->fetch_assoc();

if(!$student) die("Student not found");

$pending = $student['total_fees'] - $student['fee_paid'];

if(isset($_POST['pay_now'])){
    $amount = $_POST['amount'];
    $method = $_POST['payment_method'];
    $transaction_id = 'TXN' . time() . rand(1000,9999);
    $receipt_no = 'RCP' . date('Ymd') . rand(100,999);
    
    // Update student fee_paid
    $new_paid = $student['fee_paid'] + $amount;
    $conn->query("UPDATE students SET fee_paid = $new_paid WHERE id=$student_id");
    
    // Record transaction
    $conn->query("INSERT INTO fee_payments (student_id, amount, payment_date, payment_method, transaction_id, receipt_no, status) 
                   VALUES ('$student_id', '$amount', CURDATE(), '$method', '$transaction_id', '$receipt_no', 'Completed')");
    
    echo "<script>alert('✅ Payment Successful!\\nTransaction ID: $transaction_id\\nReceipt No: $receipt_no'); window.location.href='parent_fees.php?id=$student_id';</script>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Online Fee Payment</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .payment-box {
            max-width: 500px;
            margin: auto;
            background: #f8f9fa;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
        }
        .payment-details {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="payment-box">
        <h2>💰 Online Fee Payment</h2>
        <div class="payment-details">
            <p><strong>Student:</strong> <?= $student['full_name'] ?></p>
            <p><strong>Admission No:</strong> <?= $student['admission_no'] ?></p>
            <p><strong>Total Fees:</strong> ₹<?= number_format($student['total_fees'], 2) ?></p>
            <p><strong>Paid:</strong> ₹<?= number_format($student['fee_paid'], 2) ?></p>
            <p><strong>Pending Amount:</strong> <span style="color:red; font-weight:bold;">₹<?= number_format($pending, 2) ?></span></p>
        </div>
        
        <?php if($pending > 0): ?>
        <form method="POST">
            <label>Enter Amount (Max: ₹<?= $pending ?>):</label>
            <input type="number" name="amount" min="1" max="<?= $pending ?>" required style="text-align:center;">
            
            <label>Payment Method:</label>
            <select name="payment_method" required>
                <option>Credit Card</option>
                <option>Debit Card</option>
                <option>Net Banking</option>
                <option>UPI</option>
                <option>Wallet</option>
            </select>
            
            <div style="background:#fff3cd; padding:15px; border-radius:8px; margin:15px 0;">
                <p>🔒 Secure Payment Gateway (Demo Mode)</p>
                <p>Card: 4111 1111 1111 1111 | CVV: 123 | Expiry: 12/25</p>
            </div>
            
            <button type="submit" name="pay_now" style="background:#28a745;">💳 Pay ₹<span id="amount_display">0</span></button>
        </form>
        <?php else: ?>
            <div style="background:#d4edda; padding:15px; border-radius:8px; color:green;">
                ✅ All fees paid! No pending amount.
            </div>
        <?php endif; ?>
        
        <a href="parent_dashboard.php">⬅ Back to Dashboard</a>
    </div>
</div>

<script>
    document.querySelector('input[name="amount"]')?.addEventListener('input', function(){
        document.getElementById('amount_display').innerText = this.value || 0;
    });
</script>
</body>
</html>