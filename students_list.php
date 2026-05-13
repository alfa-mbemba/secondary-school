<?php include 'config.php'; if(!isset($_SESSION['admin'])) header('Location: index.php'); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Student List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>📋 All Students</h2>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr><th>Adm No</th><th>Name</th><th>Class</th><th>Parent Phone</th><th>Fee Paid</th><th>Total Fees</th><th>Action</th></tr>
        <?php
        $result = $conn->query("SELECT * FROM students");
        while($row = $result->fetch_assoc()):
        ?>
        <tr>
            <td><?= $row['admission_no'] ?></td>
            <td><?= $row['full_name'] ?></td>
            <td><?= $row['class'] ?></td>
            <td><?= $row['parent_phone'] ?></td>
            <td>₹<?= $row['fee_paid'] ?></td>
            <td>₹<?= $row['total_fees'] ?></td>
            <td><a href="fees.php?id=<?= $row['id'] ?>">Pay Fee</a></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <a href="index.php">⬅ Back</a>
</div>
</body>
</html>