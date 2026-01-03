<?php
include("../config/db.php");

if(!isset($_SESSION['customer_id'])){
    header("Location: ../auth/login.php");
    exit;
}

if(!isset($_POST['plan_id'])){
    die("Invalid access.");
}

$customer_id = $_SESSION['customer_id'];
$plan_id = (int) $_POST['plan_id'];

$plan = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT * FROM membershipplan WHERE plan_ID=$plan_id")
);

$customer = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT * FROM customer WHERE customer_ID=$customer_id")
);
?>

<?php include("../includes/header.php"); ?>

<section class="checkout-page">

<h1>Checkout</h1>
<p class="subtitle">Review your details and complete payment</p>

<form method="POST" action="payment_process.php">

<div class="checkout-grid">

    <!-- LEFT -->
    <div class="checkout-left">

        <div class="checkout-box">
            <h3>Customer Information</h3>
            <p><strong>Name:</strong> <?= $customer['customer_Name'] ?></p>
            <p><strong>Email:</strong> <?= $customer['customer_Email'] ?></p>
            <p><strong>Phone:</strong> <?= $customer['customer_Phone'] ?></p>
        </div>

        <div class="checkout-box">
    <h3>Payment Method</h3>

    <label class="payment-option">
        <input type="radio" name="payment_method" value="CARD" checked>
        <img src="/pilates/assets/visa.jpg" alt="Visa">
        <img src="/pilates/assets/mastercard.jpg" alt="Mastercard">
        Credit / Debit Card
    </label>

    <label class="payment-option">
        <input type="radio" name="payment_method" value="FPX">
        <img src="/pilates/assets/fpx.jpg" alt="FPX">
        Online Banking (FPX)
    </label>

    <label class="payment-option">
        <input type="radio" name="payment_method" value="EWALLET">
        <img src="/pilates/assets/ewallet.jpg" alt="E-Wallet">
        E-Wallet
    </label>
</div>


    </div>

    <!-- RIGHT -->
    <div class="checkout-right">

        <div class="checkout-summary">
            <h3>Order Summary</h3>

            <div class="summary-row">
                <span><?= $plan['plan_Name'] ?> Membership</span>
                <span>RM <?= $plan['plan_Price'] ?></span>
            </div>

            <div class="summary-row total">
                <span>Total</span>
                <span>RM <?= $plan['plan_Price'] ?></span>
            </div>

            <input type="hidden" name="plan_id" value="<?= $plan_id ?>">

            <button class="btn-dark full">Confirm & Pay</button>
        </div>

    </div>

</div>

</form>

</section>

<?php include("../includes/footer.php"); ?>
