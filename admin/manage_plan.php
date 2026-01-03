<?php
include("auth.php");
include("../config/db.php");

if (isset($_POST['add'])) {
    mysqli_query($conn,"
        INSERT INTO MembershipPlan
        (plan_Name,plan_Description,plan_Price,plan_Duration,class_ID)
        VALUES
        ('{$_POST['name']}','{$_POST['desc']}',
         {$_POST['price']},{$_POST['duration']},{$_POST['class']})
    ");
}

if (isset($_GET['delete'])) {
    mysqli_query($conn,"DELETE FROM MembershipPlan WHERE plan_ID={$_GET['delete']}");
}
?>

<h2>Membership Plans</h2>

<form method="POST">
<input name="name" placeholder="Plan Name">
<input name="price" placeholder="Price">
<input name="duration" placeholder="Duration (days)">
<textarea name="desc" placeholder="Description"></textarea>

<select name="class">
<?php
$c=mysqli_query($conn,"SELECT * FROM Class");
while($row=mysqli_fetch_assoc($c)){
    echo "<option value='{$row['class_ID']}'>{$row['class_Name']}</option>";
}
?>
</select>

<button name="add">Add Plan</button>
</form>
