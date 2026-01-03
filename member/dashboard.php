<?php
include("../config/db.php");

/* Protect page */
if (!isset($_SESSION['customer_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id = (int) $_SESSION['customer_id'];

/* ======================
   FETCH CUSTOMER PROFILE
   ====================== */
$profileResult = mysqli_query(
    $conn,
    "SELECT * FROM Customer WHERE customer_ID = $id"
);

$profile = mysqli_fetch_assoc($profileResult);

if (!$profile) {
    die("Profile not found. Please contact administrator.");
}

/* ======================
   FETCH MEMBERSHIP
   ====================== */
$membershipResult = mysqli_query(
    $conn,
    "SELECT MembershipPlan.plan_ID, MembershipPlan.plan_Name
     FROM CustomerMembership
     JOIN MembershipPlan 
       ON CustomerMembership.plan_ID = MembershipPlan.plan_ID
     WHERE CustomerMembership.customer_ID = $id"
);

$membership = mysqli_fetch_assoc($membershipResult);

/* ======================
   AUTO ASSIGN FREE PLAN IF MISSING
   ====================== */
if (!$membership) {

    $freePlan = mysqli_fetch_assoc(
        mysqli_query(
            $conn,
            "SELECT plan_ID FROM MembershipPlan WHERE plan_Name = 'Free'"
        )
    );

    if ($freePlan) {
        mysqli_query(
            $conn,
            "INSERT INTO CustomerMembership
             (customer_ID, plan_ID, membership_Status, start_Date)
             VALUES ($id, {$freePlan['plan_ID']}, 'FREE', CURDATE())"
        );

        $membership = [
            'plan_Name' => 'Free'
        ];
    } else {
        $membership = [
            'plan_Name' => 'Free'
        ];
    }
}
?>

<?php include("../includes/header.php"); ?>
<?php if(isset($_GET['payment'])): ?>
    <div class="success-box">
        Payment successful! Your membership has been updated.
    </div>
<?php endif; ?>


<section class="dashboard">

    <!-- HEADER -->
    <div class="dashboard-header">
        <h1>Welcome back, <?= htmlspecialchars($profile['customer_Name']) ?></h1>
        <p class="dashboard-subtitle">
            Manage your membership and book your next class
        </p>
    </div>

    <!-- PROFILE & MEMBERSHIP -->
    <div class="dashboard-grid">

        <div class="dashboard-card">
            <h3>Your Profile</h3>
            <p><strong>Email:</strong> <?= htmlspecialchars($profile['customer_Email']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($profile['customer_Phone']) ?></p>
        </div>

        <div class="dashboard-card highlight">
            <h3>Membership Status</h3>
            <p class="membership-name"><?= htmlspecialchars($membership['plan_Name']) ?></p>

            <?php if ($membership['plan_Name'] === 'Free'): ?>
                <p class="note">You are currently on a Free membership.</p>
                <a href="upgrade.php" class="btn">Upgrade Membership</a>
            <?php else: ?>
                <p class="note">Your membership is active.</p>
            <?php endif; ?>
        </div>

    </div>

    <!-- ACTION BUTTONS -->
    <div class="dashboard-actions">
        <a href="book.php" class="btn-dark">Book a Class</a>
        <a href="upgrade.php" class="btn-light">View Membership Plans</a>
    </div>

    <!-- AVAILABLE CLASSES -->
    <div class="dashboard-section">
        <h2>Available Classes</h2>

        <div class="dashboard-cards">

            <div class="class-preview">
                <img src="/pilates/assets/reformer.jpg" alt="Reformer Pilates">
                <h4>Reformer Pilates</h4>
                <p>Strengthen your core and improve posture.</p>
                <span>RM 75 / class</span>
            </div>

            <div class="class-preview">
                <img src="/pilates/assets/mat.jpg" alt="Mat Pilates">
                <h4>Mat Pilates</h4>
                <p>Controlled movements for full-body stability.</p>
                <span>RM 55 / class</span>
            </div>

            <div class="class-preview">
                <img src="/pilates/assets/private.jpg" alt="Private Pilates">
                <h4>Private Session Pilates</h4>
                <p>1-to-1 personalised Pilates training.</p>
                <span>RM 200 / class</span>
            </div>

        </div>
    </div>

    <!-- MEMBERSHIP PLANS -->
    <div class="dashboard-section">
        <h2>Membership Plans</h2>

        <div class="dashboard-cards">

            <div class="plan-preview">
                <h4>Basic</h4>
                <p>4 classes per month</p>
                <span>RM 230</span>
            </div>

            <div class="plan-preview popular">
                <h4>Premium</h4>
                <p>8 classes per month</p>
                <span>RM 500</span>
            </div>

            <div class="plan-preview">
                <h4>VIP</h4>
                <p>Private 1-to-1 sessions</p>
                <span>RM 1400</span>
            </div>

        </div>
    </div>

</section>
