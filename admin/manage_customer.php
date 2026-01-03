<?php
include("auth.php");
include("../config/db.php");

if (isset($_GET['delete'])) {
    mysqli_query($conn, "DELETE FROM Customer WHERE customer_ID={$_GET['delete']}");
}
?>

<h2>Customers</h2>

<table>
<tr><th>Name</th><th>Email</th><th>Action</th></tr>

<?php
$q = mysqli_query($conn,"SELECT * FROM Customer");
while($c=mysqli_fetch_assoc($q)){
    echo "
    <tr>
        <td>{$c['customer_Name']}</td>
        <td>{$c['customer_Email']}</td>
        <td>
            <a href='?delete={$c['customer_ID']}'>Delete</a>
        </td>
    </tr>";
}
?>
</table>
