<?php
include("auth.php");
include("../config/db.php");

$q = mysqli_query($conn,"
SELECT Payment.*, Customer.customer_Name
FROM Payment
JOIN CustomerMembership cm ON Payment.membership_ID = cm.membership_ID
JOIN Customer ON cm.customer_ID = Customer.customer_ID
");

while($p=mysqli_fetch_assoc($q)){
    echo "<p>{$p['customer_Name']} - RM {$p['amount']} ({$p['payment_status']})</p>";
}
?>
