<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['payment_id']) || !is_numeric($_GET['payment_id'])) {
    die("Invalid invoice request.");
}

$customer_id = (int) $_SESSION['customer_id'];
$payment_id  = (int) $_GET['payment_id'];

$q = mysqli_query($conn,"
    SELECT 
        p.payment_ID,
        p.amount,
        p.payment_method,
        p.payment_status,
        p.payment_Date,
        mp.plan_Name,
        mp.plan_Price,
        c.customer_Name,
        c.customer_Email,
        c.customer_Phone
    FROM payment p
    JOIN membershipplan mp ON p.plan_ID = mp.plan_ID
    JOIN customer c ON p.customer_ID = c.customer_ID
    WHERE p.payment_ID = $payment_id
      AND p.customer_ID = $customer_id
    LIMIT 1
");

$invoice = mysqli_fetch_assoc($q);

if (!$invoice) {
    die("Invoice not found.");
}

$invoiceNo = "INV" . str_pad($invoice['payment_ID'], 6, "0", STR_PAD_LEFT);
?>

<?php include("../includes/header.php"); ?>

<style>
.invoice-page {
    padding: 80px 20px;
    background: #f6f4ef;
}
.invoice-wrapper {
    max-width: 820px;
    margin: auto;
    background: white;
    border-radius: 20px;
    box-shadow: 0 15px 40px rgba(0,0,0,.1);
    padding: 50px;
}
.invoice-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
}
.invoice-header h1 {
    margin: 0;
    color: #0b4d2b;
}
.invoice-meta {
    text-align: right;
    font-size: 14px;
}
.section {
    margin-bottom: 35px;
}
.section h3 {
    margin-bottom: 12px;
    color: #333;
    border-bottom: 2px solid #eee;
    padding-bottom: 8px;
}
.invoice-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}
.invoice-table th {
    background: #0b4d2b;
    color: white;
    padding: 14px;
    text-align: left;
}
.invoice-table td {
    padding: 14px;
    border-bottom: 1px solid #eee;
}
.invoice-total {
    text-align: right;
    margin-top: 20px;
    font-size: 20px;
    font-weight: bold;
    color: #0b4d2b;
}
.status {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: bold;
}
.status.paid {
    background: #d4edda;
    color: #155724;
}
.invoice-actions {
    margin-top: 40px;
    display: flex;
    gap: 15px;
    justify-content: center;
}
@media print {
    header, nav, .invoice-actions {
        display: none !important;
    }
}
</style>

<section class="invoice-page">

<div class="invoice-wrapper">

    <!-- HEADER -->
    <div class="invoice-header">
        <div>
            <h1>MyPilates</h1>
            <p>Premium Pilates Studio</p>
        </div>
        <div class="invoice-meta">
            <p><strong>Invoice:</strong> <?= $invoiceNo ?></p>
            <p><strong>Date:</strong> <?= date("d M Y", strtotime($invoice['payment_Date'])) ?></p>
            <span class="status paid"><?= htmlspecialchars($invoice['payment_status']) ?></span>
        </div>
    </div>

    <!-- CUSTOMER -->
    <div class="section">
        <h3>Billed To</h3>
        <p><strong><?= htmlspecialchars($invoice['customer_Name']) ?></strong></p>
        <p><?= htmlspecialchars($invoice['customer_Email']) ?></p>
        <p><?= htmlspecialchars($invoice['customer_Phone']) ?></p>
    </div>

    <!-- MEMBERSHIP -->
    <div class="section">
        <h3>Membership Details</h3>
        <table class="invoice-table">
            <tr>
                <th>Plan</th>
                <th>Price</th>
            </tr>
            <tr>
                <td><?= htmlspecialchars($invoice['plan_Name']) ?></td>
                <td>RM <?= number_format($invoice['plan_Price'], 2) ?></td>
            </tr>
        </table>

        <div class="invoice-total">
            Total Paid: RM <?= number_format($invoice['amount'], 2) ?>
        </div>
    </div>

    <!-- PAYMENT -->
    <div class="section">
        <h3>Payment Information</h3>
        <p><strong>Method:</strong> <?= htmlspecialchars($invoice['payment_method']) ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($invoice['payment_status']) ?></p>
    </div>

    <!-- ACTIONS -->
    <div class="invoice-actions">
        <button onclick="window.print()" class="btn-dark">Print / Download</button>
        <a href="history.php" class="btn-light">Back to History</a>
    </div>

</div>
</section>

<?php include("../includes/footer.php"); ?>
