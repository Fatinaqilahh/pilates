<?php
include("../config/db.php");
include("../includes/header.php");

$id = $_SESSION['customer_id'];

$q = mysqli_query($conn,"
    SELECT p.payment_Date, p.amount, p.payment_status, mp.plan_Name
    FROM payment p
    JOIN customermembership cm ON p.membership_ID = cm.membership_ID
    JOIN membershipplan mp ON cm.plan_ID = mp.plan_ID
    WHERE cm.customer_ID = $id
    ORDER BY p.payment_Date DESC
");
?>

<section class="page">
<h2>Payment History</h2>

<table class="schedule-table">
<tr>
    <th>Date</th>
    <th>Membership</th>
    <th>Amount</th>
    <th>Status</th>
</tr>

<?php while($row = mysqli_fetch_assoc($q)): ?>
<tr>
    <td><?= $row['payment_Date'] ?></td>
    <td><?= $row['plan_Name'] ?></td>
    <td>RM <?= $row['amount'] ?></td>
    <td><?= $row['payment_status'] ?></td>
</tr>
<?php endwhile; ?>

</table>
</section>

<?php include("../includes/footer.php"); ?>
