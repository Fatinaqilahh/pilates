<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_POST['plan_id']) || !isset($_POST['payment_method'])) {
    die("Invalid request. Missing required fields.");
}

$customer_id = (int) $_SESSION['customer_id'];
$plan_id = (int) $_POST['plan_id'];
$payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);

$allowed_methods = ['CARD', 'ONLINE', 'EWALLET'];
if (!in_array($payment_method, $allowed_methods)) {
    $payment_method = 'CARD';
}

/* Fetch plan */
$planQ = mysqli_query($conn, "
    SELECT plan_Price, plan_Duration 
    FROM membershipplan 
    WHERE plan_ID = $plan_id
");
$plan = mysqli_fetch_assoc($planQ);
if (!$plan) die("Plan not found.");

$amount = $plan['plan_Price'];
$duration = (int) $plan['plan_Duration'];
$start_date = date('Y-m-d');
$end_date = date('Y-m-d', strtotime("+$duration days"));

/* Get or create membership */
$membershipQ = mysqli_query($conn, "
    SELECT membership_ID 
    FROM customermembership 
    WHERE customer_ID = $customer_id
    LIMIT 1
");

$membership_id = null;

if (mysqli_num_rows($membershipQ) > 0) {
    $membership = mysqli_fetch_assoc($membershipQ);
    $membership_id = $membership['membership_ID'];
    
    mysqli_query($conn, "
        UPDATE customermembership
        SET plan_ID = $plan_id,
            membership_Status = 'ACTIVE',
            start_Date = '$start_date',
            end_Date = '$end_date'
        WHERE membership_ID = $membership_id
    ");
} else {
    mysqli_query($conn, "
        INSERT INTO customermembership
        (customer_ID, plan_ID, membership_Status, start_Date, end_Date)
        VALUES
        ($customer_id, $plan_id, 'ACTIVE', '$start_date', '$end_date')
    ");
    $membership_id = mysqli_insert_id($conn);
}

/* Fallback if membership_id still null */
if (!$membership_id) {
    $fallbackQuery = mysqli_query($conn, "
        SELECT membership_ID 
        FROM customermembership 
        WHERE customer_ID = $customer_id 
        ORDER BY membership_ID DESC 
        LIMIT 1
    ");
    if ($fallbackQuery && $row = mysqli_fetch_assoc($fallbackQuery)) {
        $membership_id = $row['membership_ID'];
    }
}

/* Save payment WITH membership_ID */
mysqli_query($conn, "
    INSERT INTO payment
    (customer_ID, plan_ID, membership_ID, amount, payment_method, payment_status, payment_Date)
    VALUES
    ($customer_id, $plan_id, $membership_id, $amount, '$payment_method', 'PAID', NOW())
");

$payment_id = mysqli_insert_id($conn);

/* Redirect to invoice */
header("Location: invoice.php?payment_id=$payment_id");
exit;
?>