<?php
include("auth.php");
include("../config/db.php");

if (isset($_POST['add'])) {
    mysqli_query($conn,"
        INSERT INTO Class(class_Name,class_Schedule,instructor_ID)
        VALUES('{$_POST['name']}','{$_POST['schedule']}',{$_POST['instructor']})
    ");
}

if (isset($_GET['delete'])) {
    mysqli_query($conn,"DELETE FROM Class WHERE class_ID={$_GET['delete']}");
}
?>

<h2>Classes</h2>

<form method="POST">
<input name="name" placeholder="Class Name" required>
<input name="schedule" placeholder="Schedule" required>

<select name="instructor">
<?php
$i = mysqli_query($conn,"SELECT * FROM Instructor");
while($row=mysqli_fetch_assoc($i)){
    echo "<option value='{$row['instructor_ID']}'>{$row['instructor_Name']}</option>";
}
?>
</select>

<button name="add">Add Class</button>
</form>

<table>
<?php
$q = mysqli_query($conn,"
SELECT Class.*, Instructor.instructor_Name
FROM Class
JOIN Instructor ON Class.instructor_ID = Instructor.instructor_ID
");

while($c=mysqli_fetch_assoc($q)){
    echo "<tr>
        <td>{$c['class_Name']}</td>
        <td>{$c['instructor_Name']}</td>
        <td><a href='?delete={$c['class_ID']}'>Delete</a></td>
    </tr>";
}
?>
</table>
