<?php
include("../config/db.php");

if (isset($_POST['subscribe'])) {
    $customer = 1; // demo
    $plan = $_POST['plan'];

    mysqli_query($conn, "
        INSERT INTO CustomerMembership
        (start_Date, end_Date, membership_Status, customer_ID, plan_ID)
        VALUES (CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'ACTIVE', $customer, $plan)
    ");
}
?>

<form method="POST">
    <select name="plan">
        <?php
        $plans = mysqli_query($conn,"SELECT * FROM MembershipPlan");
        while($p = mysqli_fetch_assoc($plans)){
            echo "<option value='{$p['plan_ID']}'>{$p['plan_Name']}</option>";
        }
        ?>
    </select>
    <button name="subscribe">Subscribe</button>
</form>
