<?php
include("../config/db.php");
include("../includes/header.php");

$id = $_SESSION['customer_id'];

$invoice = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT p.*, mp.plan_Name
    FROM payment p
    JOIN customermembership cm ON p.membership_ID = cm.membership_ID
    JOIN membershipplan mp ON cm.plan_ID = mp.plan_ID
    WHERE cm.customer_ID = $id
    ORDER BY p.payment_Date DESC
    LIMIT 1
"));
?>

<section class="invoice-page">

<div class="invoice-box">

<h2>MyPilates Invoice</h2>
<hr>

<p><strong>Membership:</strong> <?= $invoice['plan_Name'] ?></p>
<p><strong>Amount Paid:</strong> RM <?= $invoice['amount'] ?></p>
<p><strong>Payment Date:</strong> <?= $invoice['payment_Date'] ?></p>
<p><strong>Status:</strong> <?= $invoice['payment_status'] ?></p>

<button onclick="window.print()" class="btn-dark">
    Download / Print Invoice
</button>

</div>

</section>

<?php include("../includes/footer.php"); ?>
