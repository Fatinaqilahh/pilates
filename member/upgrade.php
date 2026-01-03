<?php
include("../config/db.php");
include("../includes/header.php");

$plans = mysqli_query($conn,"
    SELECT * FROM membershipplan
    WHERE plan_Name != 'Free'
");
?>

<section class="upgrade-page">

<div class="upgrade-hero">
    <span class="upgrade-badge">Membership</span>
    <h1>Upgrade Your Membership</h1>
    <p>Choose the plan that fits your Pilates journey.</p>
</div>

<form method="POST" action="payment.php" id="upgradeForm">

<div class="plans-wrapper">

<?php while($p = mysqli_fetch_assoc($plans)): ?>

<label class="plan-card <?= $p['plan_Name']=='Premium' ? 'featured' : '' ?>">
    <input type="radio" name="plan_id"
           value="<?= $p['plan_ID'] ?>"
           onchange="enableButton()">

    <img src="/pilates/assets/<?= strtolower($p['plan_Name']) ?>.jpg"
         alt="<?= $p['plan_Name'] ?>">

    <h3><?= $p['plan_Name'] ?></h3>
    <p class="plan-desc"><?= $p['plan_Description'] ?></p>

    <div class="plan-price">
        RM <?= $p['plan_Price'] ?>
    </div>

    <?php if($p['plan_Name']=='Premium'): ?>
        <span class="featured-tag">Most Popular</span>
    <?php endif; ?>
</label>

<?php endwhile; ?>

</div>

<div class="upgrade-footer">
    <button type="submit" class="btn-dark" id="payBtn" disabled>
        Proceed to Payment
    </button>
</div>

</form>

</section>

<script>
function enableButton(){
    document.getElementById("payBtn").disabled = false;
}
</script>

<?php include("../includes/footer.php"); ?>
