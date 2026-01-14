<?php
session_start(); // Added session_start()
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

// Check if data was fetched successfully
if(!$plan) {
    die("Plan not found.");
}
if(!$customer) {
    die("Customer not found.");
}
?>

<?php include("../includes/header.php"); ?>

<style>
.checkout-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

.checkout-page h1 {
    font-size: 2.5rem;
    color: #333;
    margin-bottom: 10px;
    text-align: center;
}

.subtitle {
    color: #666;
    text-align: center;
    margin-bottom: 40px;
    font-size: 1.1rem;
}

.checkout-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 30px;
}

.checkout-box {
    background: white;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 25px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #eee;
    transition: transform 0.3s ease;
}

.checkout-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.checkout-box h3 {
    color: #333;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
    font-size: 1.4rem;
}

.checkout-box p {
    margin: 12px 0;
    color: #555;
    font-size: 1.05rem;
}

.checkout-box p strong {
    color: #333;
    display: inline-block;
    width: 80px;
}

.payment-option {
    display: flex;
    align-items: center;
    padding: 18px;
    margin: 12px 0;
    border: 2px solid #eee;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #fafafa;
}

.payment-option:hover {
    background: #f5f5f5;
    border-color: #ddd;
}

.payment-option input[type="radio"]:checked + img + img + span,
.payment-option input[type="radio"]:checked + img + span {
    color: #4CAF50;
    font-weight: bold;
}

.payment-option input[type="radio"]:checked ~ * {
    opacity: 1;
}

.payment-option input[type="radio"]:checked {
    accent-color: #4CAF50;
}

.payment-option input[type="radio"] {
    margin-right: 15px;
    transform: scale(1.2);
}

.payment-option img {
    height: 24px;
    margin-right: 10px;
    border-radius: 4px;
}

.checkout-summary {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #eee;
    position: sticky;
    top: 30px;
}

.checkout-summary h3 {
    color: #333;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
    font-size: 1.4rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 0;
    border-bottom: 1px solid #f0f0f0;
    color: #555;
    font-size: 1.05rem;
}

.summary-row.total {
    border-top: 2px solid #4CAF50;
    border-bottom: none;
    margin-top: 20px;
    padding-top: 25px;
    font-size: 1.3rem;
    font-weight: bold;
    color: #333;
}

.summary-row.total span:last-child {
    color: #4CAF50;
}

.btn-dark.full {
    width: 100%;
    padding: 18px;
    background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 1.1rem;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 25px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.btn-dark.full:hover {
    background: linear-gradient(135deg, #45a049 0%, #3d8b40 100%);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
}

.btn-dark.full:active {
    transform: translateY(0);
}

/* Responsive Design */
@media (max-width: 992px) {
    .checkout-grid {
        grid-template-columns: 1fr;
    }
    
    .checkout-summary {
        position: static;
    }
    
    .checkout-page {
        padding: 20px 15px;
    }
    
    .checkout-page h1 {
        font-size: 2rem;
    }
}

@media (max-width: 576px) {
    .checkout-box, .checkout-summary {
        padding: 20px;
    }
    
    .payment-option {
        padding: 15px;
    }
    
    .btn-dark.full {
        padding: 15px;
    }
}
</style>

<section class="checkout-page">

<h1>Checkout</h1>
<p class="subtitle">Review your details and complete payment</p>

<form method="POST" action="payment_process.php">

<div class="checkout-grid">

    <!-- LEFT -->
    <div class="checkout-left">

        <div class="checkout-box">
            <h3>Customer Information</h3>
            <p><strong>Name:</strong> <?= htmlspecialchars($customer['customer_Name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($customer['customer_Email']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($customer['customer_Phone']) ?></p>
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
                <input type="radio" name="payment_method" value="ONLINE">
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
                <span><?= htmlspecialchars($plan['plan_Name']) ?> Membership</span>
                <span>RM <?= number_format($plan['plan_Price'], 2) ?></span>
            </div>

            <div class="summary-row total">
                <span>Total</span>
                <span>RM <?= number_format($plan['plan_Price'], 2) ?></span>
            </div>

            <input type="hidden" name="plan_id" value="<?= $plan_id ?>">

            <button type="submit" class="btn-dark full">Confirm & Pay</button>
        </div>

    </div>

</div>

</form>

</section>

<?php include("../includes/footer.php"); ?>