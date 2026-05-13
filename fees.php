<?php include 'config.php'; if(!isset($_SESSION['admin'])) header('Location: index.php'); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Fees Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>💰 Fee Payment</h2>
    <?php
    $id = $_GET['id'] ?? 0;
    $student = $conn->query("SELECT * FROM students WHERE id=$id")->fetch_assoc();
    if($student):
    ?>
    <h3><?= $student['full_name'] ?> (<?= $student['admission_no'] ?>)</h3>
    <p>Class: <?= $student['class'] ?></p>
    <p>Total Fees: ₹<?= $student['total_fees'] ?></p>
    <p>Already Paid: ₹<?= $student['fee_paid'] ?></p>
    <p>Pending: ₹<?= $student['total_fees'] - $student['fee_paid'] ?></p>

    <form method="POST">
        <input type="number" name="amount" placeholder="Enter Amount" required>
        <button type="submit" name="pay">Pay Now</button>
    </form>
    <?php
    if(isset($_POST['pay'])){
        $amount = $_POST['amount'];
        $new_paid = $student['fee_paid'] + $amount;
        $conn->query("UPDATE students SET fee_paid = $new_paid WHERE id=$id");
        echo "<p style='color:green'>✅ Payment Recorded</p>";
        echo "<script>window.location.href='fees.php?id=$id';</script>";
    }
    endif;
    ?>
    <a href="students_list.php">⬅ Back to Students</a>
</div>
</body>
</html>