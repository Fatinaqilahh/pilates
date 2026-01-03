<?php
include("../config/db.php");
include("../includes/header.php");
?>

<section class="success-page">

    <div class="success-card">
        <h1>Payment Successful 🎉</h1>
        <p>Your membership has been updated successfully.</p>

        <div class="success-actions">
            <a href="dashboard.php" class="btn">Go to Dashboard</a>
            <a href="history.php" class="btn-light">View Payment History</a>
        </div>
    </div>

</section>

<?php include("../includes/footer.php"); ?>
