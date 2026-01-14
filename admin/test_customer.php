<?php
include("../config/db.php");

$query = "SELECT customer_Name, customer_Email FROM Customer";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

echo "<h2>Customer List</h2>";

while ($row = mysqli_fetch_assoc($result)) {
    echo $row['customer_Name'] . " - " . $row['customer_Email'] . "<br>";
}
?>
