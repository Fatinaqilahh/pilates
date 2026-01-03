<?php
include("../config/db.php");
include("../includes/header.php");
?>

<section class="page">
<h1>Our Classes</h1>
<p class="subtitle">Choose the class that fits your body and lifestyle</p>

<div class="grid">
<?php
$q = mysqli_query($conn,"SELECT * FROM Class");
while($c = mysqli_fetch_assoc($q)){

    $price = "";
    if($c['class_Name'] == "Reformer Pilates") $price = "RM 75 / class";
    if($c['class_Name'] == "Mat Pilates") $price = "RM 55 / class";
    if($c['class_Name'] == "Private Session Pilates") $price = "RM 200 / class";

    echo "
    <div class='card class-card'>
        <h3>{$c['class_Name']}</h3>
        <p>{$c['class_Schedule']}</p>
        <strong>$price</strong>
    </div>
    ";
}
?>
</div>
</section>

<?php include("../includes/footer.php"); ?>
