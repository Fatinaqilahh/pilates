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

        <?php 
        if(mysqli_num_rows($q) > 0):
            while($row = mysqli_fetch_assoc($q)): 
        ?>
        <tr>
            <td><?= date('Y-m-d H:i', strtotime($row['payment_Date'])) ?></td>
            <td><?= htmlspecialchars($row['plan_Name']) ?></td>
            <td>RM <?= number_format($row['amount'], 2) ?></td>
            <td><?= htmlspecialchars($row['payment_status']) ?></td>
        </tr>
        <?php 
            endwhile;
        else: 
        ?>
        <tr>
            <td colspan="4">No payment history found.</td>
        </tr>
        <?php endif; ?>

    </table>
</section>

<?php include("../includes/footer.php"); ?>