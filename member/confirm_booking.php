<?php
include("../config/db.php");

$id = $_SESSION['customer_id'];
$schedule_id = $_POST['schedule_id'];

/* Get quota */
$plan = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT mp.monthly_quota
    FROM customermembership cm
    JOIN membershipplan mp ON cm.plan_ID = mp.plan_ID
    WHERE cm.customer_ID = $id
"));

/* Count bookings this month */
$count = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT COUNT(*) AS total
    FROM booking
    WHERE customer_ID = $id
    AND MONTH(booking_Date) = MONTH(CURDATE())
"));

if($plan['monthly_quota'] > 0 && $count['total'] >= $plan['monthly_quota']){
    die("Monthly booking limit reached.");
}

/* Save booking */
mysqli_query($conn,"
    INSERT INTO Booking (customer_ID, schedule_ID)
    VALUES ($id, $schedule_id)
");

/* Redirect to payment */
header("Location: payment.php?schedule=$schedule_id");
exit;
