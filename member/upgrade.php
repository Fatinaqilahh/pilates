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

<label class="plan-card <?= $p['plan_Name']=='Premium' ? 'featured' : '' ?>" id="plan-<?= $p['plan_ID'] ?>">
    <input type="radio" name="plan_id"
           value="<?= $p['plan_ID'] ?>"
           onchange="enableButton()"
           class="plan-radio">

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
    
    <div class="selection-indicator" style="display: none; margin-top: 10px; color: #4CAF50; font-weight: bold;">
        ✓ Selected
    </div>
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
    // Enable the payment button
    document.getElementById("payBtn").disabled = false;
    
    // Get the selected radio button
    const selectedRadio = document.querySelector('input[name="plan_id"]:checked');
    
    // Hide all selection indicators first
    document.querySelectorAll('.selection-indicator').forEach(indicator => {
        indicator.style.display = 'none';
    });
    
    // Remove selected class from all cards
    document.querySelectorAll('.plan-card').forEach(card => {
        card.classList.remove('selected');
        card.style.borderColor = '';
        card.style.boxShadow = '';
    });
    
    // Show selection indicator for selected card
    if (selectedRadio) {
        const selectedCard = selectedRadio.closest('.plan-card');
        const indicator = selectedCard.querySelector('.selection-indicator');
        
        // Add selected class
        selectedCard.classList.add('selected');
        
        // Highlight the selected card
        selectedCard.style.borderColor = '#4CAF50';
        selectedCard.style.boxShadow = '0 0 10px rgba(76, 175, 80, 0.3)';
        
        // Show selection indicator
        if (indicator) {
            indicator.style.display = 'block';
        }
    }
}

// Add click event to each plan card
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.plan-card').forEach(card => {
        card.addEventListener('click', function() {
            const radio = this.querySelector('.plan-radio');
            radio.checked = true;
            enableButton();
        });
    });
    
    // Check if any plan is already selected on page load
    const selectedRadio = document.querySelector('input[name="plan_id"]:checked');
    if (selectedRadio) {
        enableButton();
    }
});
</script>

<style>
.plan-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.plan-card.selected {
    border-color: #4CAF50 !important;
    box-shadow: 0 0 10px rgba(76, 175, 80, 0.3) !important;
}

.plan-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
</style>

<?php include("../includes/footer.php"); ?>