<?php
include("../config/db.php");

if(!isset($_SESSION['customer_id'])){
    header("Location: ../auth/login.php");
    exit;
}

if(!isset($_POST['plan_id'])){
    die("Invalid request.");
}

$customer_id = $_SESSION['customer_id'];
$plan_id = (int) $_POST['plan_id'];

/* Fetch plan */
$plan = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT plan_Price FROM membershipplan WHERE plan_ID=$plan_id")
);

if(!$plan){
    die("Plan not found.");
}

$amount = $plan['plan_Price'];

/* Get or create membership */
$membership = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT membership_ID FROM customermembership WHERE customer_ID=$customer_id")
);

if($membership){
    mysqli_query($conn,"
        UPDATE customermembership
        SET plan_ID=$plan_id,
            membership_Status='ACTIVE',
            start_Date=CURDATE(),
            end_Date=DATE_ADD(CURDATE(), INTERVAL 1 MONTH)
        WHERE membership_ID={$membership['membership_ID']}
    ");
    $membership_id = $membership['membership_ID'];
} else {
    mysqli_query($conn,"
        INSERT INTO customermembership
        (customer_ID, plan_ID, membership_Status, start_Date, end_Date)
        VALUES
        ($customer_id,$plan_id,'ACTIVE',CURDATE(),DATE_ADD(CURDATE(), INTERVAL 1 MONTH))
    ");
    $membership_id = mysqli_insert_id($conn);
}

/* Save payment */
mysqli_query($conn,"
    INSERT INTO payment
    (membership_ID, amount, payment_method, payment_status, payment_Date)
    VALUES
    ($membership_id,$amount,'ONLINE','PAID',NOW())
");

header("Location: payment_success.php");
exit;

