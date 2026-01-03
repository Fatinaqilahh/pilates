<?php
include("auth.php");
include("../config/db.php");

if (isset($_POST['add'])) {
    mysqli_query($conn,"
        INSERT INTO Instructor(instructor_Name,instructor_Email,instructor_Phone)
        VALUES('{$_POST['name']}','{$_POST['email']}','{$_POST['phone']}')
    ");
}

if (isset($_GET['delete'])) {
    mysqli_query($conn,"DELETE FROM Instructor WHERE instructor_ID={$_GET['delete']}");
}
?>

<h2>Instructors</h2>

<form method="POST">
<input name="name" placeholder="Name" required>
<input name="email" placeholder="Email" required>
<input name="phone" placeholder="Phone" required>
<button name="add">Add Instructor</button>
</form>

<table>
<?php
$q = mysqli_query($conn,"SELECT * FROM Instructor");
while($i=mysqli_fetch_assoc($q)){
    echo "<tr>
        <td>{$i['instructor_Name']}</td>
        <td><a href='?delete={$i['instructor_ID']}'>Delete</a></td>
    </tr>";
}
?>
</table>
