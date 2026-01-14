<?php
session_start();
include("../config/db.php");

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Display success/error messages
$success_message = "";
$error_message = "";

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

/* ======================
   AUTH GUARD
   ====================== */
if (!isset($_SESSION['customer_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id = (int) $_SESSION['customer_id'];

/* ======================
   FETCH PROFILE
   ====================== */
$profileQ = mysqli_query(
    $conn,
    "SELECT * FROM customer WHERE customer_ID = $id LIMIT 1"
);
$profile = mysqli_fetch_assoc($profileQ);
if (!$profile) {
    die("Profile not found.");
}

/* ======================
   FETCH MEMBERSHIP
   ====================== */
$membershipQ = mysqli_query(
    $conn,
    "SELECT 
        mp.plan_ID,
        mp.plan_Name,
        mp.plan_Price,
        mp.plan_Image,
        mp.plan_Duration,
        mp.plan_Description,
        cm.membership_Status,
        cm.start_Date,
        cm.end_Date
     FROM customermembership cm
     JOIN membershipplan mp ON cm.plan_ID = mp.plan_ID
     WHERE cm.customer_ID = $id
     ORDER BY cm.start_Date DESC
     LIMIT 1"
);

$membership = mysqli_fetch_assoc($membershipQ);

/* ======================
   AUTO FREE PLAN
   ====================== */
if (!$membership) {
    $freeQ = mysqli_query(
        $conn,
        "SELECT * FROM membershipplan WHERE plan_Name='Free' LIMIT 1"
    );
    $free = mysqli_fetch_assoc($freeQ);

    mysqli_query($conn,"
        INSERT INTO customermembership
        (customer_ID, plan_ID, membership_Status, start_Date)
        VALUES ($id, {$free['plan_ID']}, 'ACTIVE', CURDATE())
    ");

    $membership = [
        'plan_Name' => $free['plan_Name'],
        'plan_Price' => $free['plan_Price'],
        'plan_Image' => $free['plan_Image'],
        'plan_Duration' => $free['plan_Duration'],
        'plan_Description' => $free['plan_Description'] ?? 'Basic membership',
        'membership_Status' => 'ACTIVE',
        'start_Date' => date('Y-m-d'),
        'end_Date' => null
    ];
}

/* ======================
   CALCULATE DAYS REMAINING
   ====================== */
$days_remaining = 0;
$progress_percentage = 0;
if ($membership['end_Date']) {
    $end_date = new DateTime($membership['end_Date']);
    $today = new DateTime();
    if ($today < $end_date) {
        $interval = $today->diff($end_date);
        $days_remaining = $interval->days;
        
        // Calculate progress percentage
        $total_days = $membership['plan_Duration'];
        $days_used = $total_days - $days_remaining;
        $progress_percentage = min(100, ($days_used / $total_days) * 100);
    }
}

/* ======================
   FETCH RECENT PAYMENTS
   ====================== */
$recentPaymentsQ = mysqli_query(
    $conn,
    "SELECT 
        p.payment_ID,
        p.payment_Date,
        p.amount,
        p.payment_method,
        mp.plan_Name
     FROM payment p
     JOIN membershipplan mp ON p.plan_ID = mp.plan_ID
     WHERE p.customer_ID = $id
     ORDER BY p.payment_Date DESC
     LIMIT 3"
);
$recent_payments = [];
while ($row = mysqli_fetch_assoc($recentPaymentsQ)) {
    $recent_payments[] = $row;
}

/* ======================
   FETCH TOTAL PAYMENT COUNT
   ====================== */
$totalPaymentsQ = mysqli_query($conn, "SELECT COUNT(*) as total FROM payment WHERE customer_ID = $id");
$totalPayments = mysqli_fetch_assoc($totalPaymentsQ)['total'] ?? 0;

?>

<?php include("../includes/header.php"); ?>

<style>
/* ===== RESET & BASE STYLES ===== */
:root {
    --primary: #30693b; /* Green color */
    --primary-dark: #285a32;
    --primary-light: #4a8c5a;
    --secondary: #2d4e36;
    --accent: #FFD700;
    --light: #f8f9fa;
    --light-green: #f0f8f0;
    --dark: #212529;
    --gray: #6c757d;
    --gray-light: #e9ecef;
    --border-radius: 12px;
    --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    --box-shadow-hover: 0 8px 30px rgba(0, 0, 0, 0.12);
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    background-color: #f5f7fa;
    margin: 0;
    padding: 0;
    color: var(--dark);
}

.dashboard-wrapper {
    min-height: 100vh;
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}

/* ===== NOTIFICATION TOAST ===== */
.notification-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10000;
    max-width: 400px;
}

.toast {
    padding: 16px 20px;
    border-radius: var(--border-radius);
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    background: white;
    box-shadow: var(--box-shadow-hover);
    animation: slideInRight 0.3s ease-out;
    border-left: 4px solid;
    gap: 12px;
}

.toast-success {
    border-left-color: #28a745;
}

.toast-error {
    border-left-color: #dc3545;
}

.toast-content {
    flex: 1;
    font-size: 14px;
    color: var(--dark);
}

.toast-close {
    background: none;
    border: none;
    color: var(--gray);
    font-size: 20px;
    cursor: pointer;
    padding: 0;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: var(--transition);
}

.toast-close:hover {
    background: var(--gray-light);
    color: var(--dark);
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* ===== HERO BANNER ===== */
.hero-banner {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    padding: 40px;
    border-radius: var(--border-radius);
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}

.hero-banner::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 300px;
    height: 300px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    transform: translate(100px, -100px);
}

.hero-content {
    position: relative;
    z-index: 2;
}

.hero-content h1 {
    font-size: 2.5rem;
    margin: 0 0 10px 0;
    font-weight: 700;
}

.hero-content p {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 0;
}

/* ===== MAIN LAYOUT ===== */
.dashboard-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 30px;
}

@media (min-width: 992px) {
    .dashboard-layout {
        grid-template-columns: 1fr 350px;
    }
}

/* ===== LEFT COLUMN ===== */
.left-column {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

/* ===== MEMBERSHIP SECTION ===== */
.membership-section {
    background: white;
    border-radius: var(--border-radius);
    padding: 30px;
    box-shadow: var(--box-shadow);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.section-header h2 {
    font-size: 1.5rem;
    color: var(--dark);
    margin: 0;
}

.status-badge {
    padding: 6px 15px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.membership-details {
    margin-bottom: 25px;
}

.membership-details h3 {
    font-size: 1.8rem;
    color: var(--primary);
    margin: 0 0 10px 0;
}

.membership-details p {
    color: var(--gray);
    margin: 0 0 20px 0;
    line-height: 1.6;
}

.membership-price {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 25px;
}

.membership-dates {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.date-item {
    background: var(--light-green);
    padding: 15px;
    border-radius: 8px;
    text-align: center;
}

.date-item label {
    display: block;
    font-size: 0.85rem;
    color: var(--gray);
    margin-bottom: 5px;
}

.date-item .date-value {
    font-size: 1.1rem;
    color: var(--dark);
    font-weight: 500;
}

.membership-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: none;
    font-size: 0.95rem;
    text-decoration: none;
}

.btn-primary {
    background: var(--primary);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
}

.btn-outline {
    background: transparent;
    color: var(--primary);
    border: 2px solid var(--primary);
}

.btn-outline:hover {
    background: var(--primary);
    color: white;
}

/* ===== QUICK ACTIONS SECTION ===== */
.quick-actions-section {
    background: white;
    border-radius: var(--border-radius);
    padding: 30px;
    box-shadow: var(--box-shadow);
}

.quick-actions-section h2 {
    font-size: 1.5rem;
    color: var(--dark);
    margin: 0 0 25px 0;
}

.action-buttons {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

.action-button {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px 20px;
    background: var(--light-green);
    color: var(--dark);
    border: 1px solid rgba(48, 105, 59, 0.1);
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
    text-align: left;
    font-size: 0.95rem;
    width: 100%;
}

.action-button:hover {
    background: #e8f5e8;
    transform: translateX(5px);
    border-color: var(--primary);
}

.action-button i {
    color: var(--primary);
    font-size: 1.2rem;
    width: 24px;
}

/* Profile Image Styles */
.user-avatar img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid white;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.user-avatar {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.8rem;
    font-weight: 600;
    flex-shrink: 0;
    overflow: hidden;
    position: relative;
}

/* ===== RIGHT COLUMN ===== */
.right-column {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

/* ===== USER INFO SECTION ===== */
.user-info-section {
    background: white;
    border-radius: var(--border-radius);
    padding: 30px;
    box-shadow: var(--box-shadow);
}

.user-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
}

.user-info h2 {
    font-size: 1.5rem;
    margin: 0 0 5px 0;
    color: var(--dark);
}

.user-info .badge {
    display: inline-block;
    background: linear-gradient(135deg, var(--accent) 0%, #ffcc00 100%);
    color: var(--dark);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.view-profile-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 24px;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
    width: 100%;
    margin-top: 20px;
    text-decoration: none;
    font-size: 0.95rem;
}

.view-profile-btn:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
}

/* ===== RECENT ACTIVITY ===== */
.recent-activity-section {
    background: white;
    border-radius: var(--border-radius);
    padding: 30px;
    box-shadow: var(--box-shadow);
}

.recent-activity-section h2 {
    font-size: 1.5rem;
    color: var(--dark);
    margin: 0 0 25px 0;
}

.activity-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.activity-item {
    padding: 20px;
    background: var(--light-green);
    border-radius: 8px;
    border-left: 4px solid var(--primary);
}

.activity-title {
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 5px;
    font-size: 1rem;
}

.activity-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: var(--gray);
    font-size: 0.9rem;
}

.amount {
    color: var(--primary);
    font-weight: 600;
}

.date {
    color: var(--gray);
}

/* ===== PROMOTIONS SECTION - ARROW STYLE (ORIGINAL STYLE) ===== */
.promotions-section {
    max-width: 1200px;
    margin: 60px auto 40px;
    padding: 0 20px;
}

.promotions-header {
    text-align: center;
    margin-bottom: 30px;
}

.promotions-header h2 {
    font-size: 1.8rem;
    color: var(--primary);
    margin: 0;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.promotions-header h2 i {
    color: var(--accent);
}

.promo-container-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    gap: 15px;
}

.promo-arrow {
    width: 50px;
    height: 50px;
    background: white;
    border: 2px solid var(--primary);
    border-radius: 50%;
    color: var(--primary);
    font-size: 1.2rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
    flex-shrink: 0;
    z-index: 2;
}

.promo-arrow:hover {
    background: var(--primary);
    color: white;
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(48, 105, 59, 0.3);
}

.promo-arrow:disabled {
    background: var(--gray-light);
    border-color: var(--gray);
    color: var(--gray);
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.promo-container {
    position: relative;
    overflow: hidden;
    border-radius: var(--border-radius);
    background: white;
    box-shadow: var(--box-shadow);
    padding: 20px;
    flex: 1;
}

.promo-track {
    display: flex;
    gap: 25px;
    transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.promo-card {
    flex: 0 0 calc(33.333% - 17px);
    background: linear-gradient(135deg, #f8fff8 0%, #e8f5e8 100%);
    border-radius: var(--border-radius);
    overflow: hidden;
    transition: var(--transition);
    border: 1px solid rgba(48, 105, 59, 0.1);
    cursor: pointer;
}

.promo-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--box-shadow-hover);
    border-color: var(--primary);
}

.promo-image {
    height: 200px;
    width: 100%;
    overflow: hidden;
    position: relative;
}

.promo-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.promo-card:hover .promo-image img {
    transform: scale(1.05);
}

.promo-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: linear-gradient(135deg, #ff6b6b 0%, #ff4757 100%);
    color: white;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    z-index: 2;
}

.promo-content {
    padding: 25px;
}

.promo-content h4 {
    margin: 0 0 12px 0;
    color: var(--primary);
    font-size: 1.3rem;
    font-weight: 600;
}

.promo-content p {
    margin: 0 0 20px 0;
    color: var(--gray);
    font-size: 0.95rem;
    line-height: 1.5;
    min-height: 70px;
}

.promo-features {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 25px;
}

.promo-feature {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--dark);
    font-size: 0.9rem;
}

.promo-feature i {
    color: var(--primary);
    font-size: 0.9rem;
    width: 16px;
}

.promo-cta {
    display: block;
    text-align: center;
    background: var(--primary);
    color: white;
    padding: 12px 24px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    transition: var(--transition);
    border: none;
    width: 100%;
    cursor: pointer;
    font-size: 1rem;
}

.promo-cta:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(48, 105, 59, 0.3);
}

.promo-dots {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 25px;
}

.promo-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--gray-light);
    cursor: pointer;
    transition: var(--transition);
    border: none;
    padding: 0;
}

.promo-dot.active {
    background: var(--primary);
    transform: scale(1.2);
}

/* ===== RESPONSIVE DESIGN ===== */
@media (max-width: 992px) {
    .dashboard-layout {
        grid-template-columns: 1fr;
    }
    
    .promo-card {
        flex: 0 0 calc(50% - 13px);
    }
    
    .promo-container-wrapper {
        gap: 10px;
    }
    
    .promo-arrow {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
}

@media (max-width: 768px) {
    .dashboard-wrapper {
        padding: 15px;
    }
    
    .hero-banner {
        padding: 30px 20px;
    }
    
    .hero-content h1 {
        font-size: 2rem;
    }
    
    .user-header {
        flex-direction: column;
        text-align: center;
    }
    
    .membership-dates {
        grid-template-columns: 1fr;
    }
    
    .membership-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
    
    .promo-card {
        flex: 0 0 calc(100% - 0px);
    }
    
    .promo-container-wrapper {
        gap: 8px;
    }
    
    .promo-arrow {
        width: 35px;
        height: 35px;
        font-size: 0.9rem;
    }
    
    .promo-content p {
        min-height: auto;
    }
}

@media (max-width: 480px) {
    .hero-banner {
        padding: 25px 15px;
    }
    
    .hero-content h1 {
        font-size: 1.6rem;
    }
    
    .hero-content p {
        font-size: 1rem;
    }
    
    .promotions-header h2 {
        font-size: 1.5rem;
    }
    
    .promo-container {
        padding: 15px;
    }
    
    .promo-content {
        padding: 20px;
    }
}
</style>

<!-- Notification Toasts -->
<div class="notification-toast" id="notificationToast">
    <?php if ($success_message): ?>
        <div class="toast toast-success">
            <i class="fas fa-check-circle"></i>
            <div class="toast-content"><?= htmlspecialchars($success_message) ?></div>
            <button class="toast-close" onclick="closeToast(this)">×</button>
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="toast toast-error">
            <i class="fas fa-exclamation-circle"></i>
            <div class="toast-content"><?= htmlspecialchars($error_message) ?></div>
            <button class="toast-close" onclick="closeToast(this)">×</button>
        </div>
    <?php endif; ?>
</div>

<div class="dashboard-wrapper">
    <!-- Hero Banner -->
    <div class="hero-banner">
        <div class="hero-content">
            <h1>Achieve Your Goals</h1>
            <p>Track your fitness journey with our progress tools</p>
        </div>
    </div>

    <div class="dashboard-layout">
        <!-- Left Column -->
        <div class="left-column">
            <!-- Current code remains the same until the Membership Section -->

<!-- Membership Section -->
<section class="membership-section">
    <div class="section-header">
        <h2>Current Membership</h2>
        <span class="status-badge"><?= htmlspecialchars($membership['membership_Status']) ?></span>
    </div>
    
    <div class="membership-details" style="position: relative;">
        <!-- Large Membership Image on the Right -->
        <?php
        // Determine which image to show based on plan name
        $plan_image = '';
        $plan_name = strtolower($membership['plan_Name']);
        
        if (strpos($plan_name, 'vip') !== false) {
            $plan_image = '../assets/vip.jpg';
        } elseif (strpos($plan_name, 'premium') !== false) {
            $plan_image = '../assets/premium.jpg';
        } elseif (strpos($plan_name, 'free') !== false) {
            $plan_image = '../assets/free.jpg';
        } elseif (strpos($plan_name, 'basic') !== false || strpos($plan_name, 'free') !== false) {
            $plan_image = '../assets/basic.jpg';
        } else {
            $plan_image = '../assets/basic.jpg';
        }
        
        // Check if file exists
        if (!file_exists($plan_image)) {
            $plan_image = 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=600&auto=format&fit=crop&q=80';
        }
        ?>
        
        <!-- Large Image on the Right Side -->
        <div style="position: absolute; top: 0; right: 0; width: 200px; height: 200px; border-radius: 15px; overflow: hidden; border: 4px solid var(--primary); box-shadow: 0 8px 25px rgba(48, 105, 59, 0.2);">
            <img src="<?= htmlspecialchars($plan_image) ?>" 
                 alt="<?= htmlspecialchars($membership['plan_Name']) ?>" 
                 style="width: 100%; height: 100%; object-fit: cover;"
                 onerror="this.src='https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=600&auto=format&fit=crop&q=80'">
        </div>
        
        <!-- Plan Name and Details on the Left -->
        <div style="padding-right: 220px;">
            <h3 style="font-size: 2.5rem; color: var(--primary); margin: 0 0 15px 0; font-weight: 700; line-height: 1.2;">
                <?= htmlspecialchars($membership['plan_Name']) ?>
            </h3>
            
            <p style="color: var(--gray); margin: 0 0 25px 0; line-height: 1.6; font-size: 1.1rem; max-width: 500px;">
                <?= htmlspecialchars($membership['plan_Description'] ?? 'Premium Pilates Membership') ?>
            </p>
            
            <div class="membership-price" style="font-size: 2.8rem; margin-bottom: 30px;">
                RM <?= number_format($membership['plan_Price'], 2) ?>
            </div>
        </div>
    </div>
    
    <div class="membership-dates" style="margin-top: 40px;">
        <div class="date-item">
            <label style="font-size: 1rem; margin-bottom: 8px;">Start Date</label>
            <div class="date-value" style="font-size: 1.3rem;">
                <?= date('d M, Y', strtotime($membership['start_Date'])) ?>
            </div>
        </div>
        <div class="date-item">
            <label style="font-size: 1rem; margin-bottom: 8px;"><?= $membership['end_Date'] ? 'End Date' : 'Plan Type' ?></label>
            <div class="date-value" style="font-size: 1.3rem;">
                <?= $membership['end_Date'] ? date('d M, Y', strtotime($membership['end_Date'])) : 'Ongoing Membership' ?>
            </div>
        </div>
        <div class="date-item">
            <label style="font-size: 1rem; margin-bottom: 8px;">Membership Progress</label>
            <div class="date-value" style="font-size: 1.3rem; color: var(--primary);">
                <?= $membership['plan_Duration'] - $days_remaining ?> days used
            </div>
        </div>
    </div>
    
    <!-- Progress Bar -->
    <?php if ($membership['end_Date'] && $days_remaining > 0): ?>
    <div style="margin: 30px 0; background: white; padding: 20px; border-radius: var(--border-radius); box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
            <span style="font-size: 1rem; color: var(--dark); font-weight: 500;">Membership Timeline</span>
            <span style="font-size: 1rem; color: var(--primary); font-weight: 600;">
                <?= round($progress_percentage) ?>% Complete
            </span>
        </div>
        <div style="height: 14px; background: var(--light-green); border-radius: 7px; overflow: hidden; position: relative;">
            <div style="height: 100%; width: <?= $progress_percentage ?>%; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); border-radius: 7px; transition: width 0.5s ease;"></div>
        </div>
        <div style="display: flex; justify-content: space-between; margin-top: 12px;">
            <span style="font-size: 0.9rem; color: var(--gray);">
                <?= date('M d, Y', strtotime($membership['start_Date'])) ?>
            </span>
            <span style="font-size: 0.9rem; color: var(--gray);">
                <?= date('M d, Y', strtotime($membership['end_Date'])) ?>
            </span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-top: 8px;">
            <span style="font-size: 0.9rem; color: var(--primary); font-weight: 500;">
                <?= $membership['plan_Duration'] - $days_remaining ?> days used
            </span>
            <span style="font-size: 0.9rem; color: var(--primary); font-weight: 500;">
                <?= $days_remaining ?> days remaining
            </span>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="membership-actions">
        <button class="btn btn-primary" onclick="window.location.href='history.php'" style="padding: 15px 30px; font-size: 1.1rem;">
            <i class="fas fa-receipt"></i> Payment History
        </button>
    </div>
</section>

            <!-- Quick Actions Section -->
            <section class="quick-actions-section">
                <h2>Quick Actions</h2>
                <div class="action-buttons">
                    <button class="action-button" onclick="window.location.href='upgrade.php'">
                        <i class="fas fa-arrow-up"></i>
                        Upgrade Membership
                    </button>
                    <button class="action-button" onclick="window.location.href='history.php'">
                        <i class="fas fa-history"></i>
                        Payment History
                    </button>
                </div>
            </section>
        </div>

        <!-- Right Column -->
        <div class="right-column">
            <!-- User Info Section -->
            <!-- In the User Info Section (Right Column) -->
<section class="user-info-section">
    <div class="user-header">
        <div class="user-avatar">
            <?php
            // Check if profile image exists in database
            $has_profile_image = false;
            $image_path = '';
            $timestamp = '';
            
            if (!empty($profile['profile_image'])) {
                $image_path = '../assets/uploads/profile_images/' . $profile['profile_image'];
                
                // Check if file actually exists on server
                if (file_exists($image_path)) {
                    $has_profile_image = true;
                    // Get file modification time for cache busting
                    $timestamp = '?t=' . filemtime($image_path);
                }
            }
            
            if ($has_profile_image) {
                // Display the uploaded profile image with cache busting
                echo '<img src="' . htmlspecialchars($image_path . $timestamp) . '" 
                      alt="Profile" 
                      style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;"
                      onerror="this.onerror=null; this.src=\'data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'100\' height=\'100\'><rect width=\'100%\' height=\'100%\' fill=\'%2330693b\'/><text x=\'50%\' y=\'55%\' font-size=\'40\' text-anchor=\'middle\' fill=\'white\' font-family=\'Arial, sans-serif\'>' . strtoupper(substr($profile['customer_Name'], 0, 1)) . '</text></svg>\';" 
                      id="profileImage">';
            } else {
                // Display fallback with user's initial
                echo '<div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: 600; color: white; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); border-radius: 50%;">';
                echo htmlspecialchars(strtoupper(substr($profile['customer_Name'], 0, 1)));
                echo '</div>';
            }
            ?>
        </div>
        <div class="user-info">
            <h2><?= htmlspecialchars($profile['customer_Name']) ?></h2>
            <!-- FIXED: Dynamic Membership Badge -->
            <span class="badge" style="
                background: linear-gradient(135deg, 
                    <?php 
                    // Different colors based on membership type
                    $plan_name = strtolower($membership['plan_Name']);
                    if (strpos($plan_name, 'vip') !== false) {
                        echo '#9b59b6, #8e44ad'; // Purple gradient for VIP
                    } elseif (strpos($plan_name, 'premium') !== false) {
                        echo '#FFD700, #ffcc00'; // Gold gradient for Premium
                    } elseif (strpos($plan_name, 'basic') !== false || strpos($plan_name, 'free') !== false) {
                        echo '#3498db, #2980b9'; // Blue gradient for Basic/Free
                    } else {
                        echo 'var(--accent), #ffcc00'; // Default gold
                    }
                    ?>
                );
                color: <?= strpos($plan_name, 'premium') !== false ? 'var(--dark)' : 'white' ?>;
            ">
                <?= htmlspecialchars($membership['plan_Name']) ?> Member
            </span>
        </div>
    </div>
    
    <button class="view-profile-btn" onclick="window.location.href='profile.php'">
        <i class="fas fa-user-circle"></i> View Full Profile
    </button>
    <button class="view-profile-btn" onclick="window.location.href='../auth/logout.php'">
        <i class="fas fa-sign-out-alt"></i> Logout
    </button>
</section>

            <!-- Recent Activity -->
            <section class="recent-activity-section">
                <h2>Recent Activity</h2>
                
                <div class="activity-list">
                    <?php if (!empty($recent_payments)): ?>
                        <?php foreach ($recent_payments as $payment): ?>
                        <div class="activity-item">
                            <div class="activity-title">Payment for <?= htmlspecialchars($payment['plan_Name']) ?></div>
                            <div class="activity-details">
                                <span class="amount">RM <?= number_format($payment['amount'], 2) ?></span>
                                <span class="date"><?= date('M d, Y', strtotime($payment['payment_Date'])) ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="activity-item">
                            <div class="activity-title">No Recent Activity</div>
                            <div class="activity-details">
                                <span class="date">Start by booking your first class!</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</div>

<!-- Latest Promotions Section (At the bottom following original code) -->
<section class="promotions-section">
    <div class="promotions-header">
        <h2><i class="fas fa-gift"></i> Latest Promotions & Offers</h2>
    </div>
    
    <div class="promo-container-wrapper">
        <!-- Left Arrow -->
        <button class="promo-arrow" id="prevPromo">
            <i class="fas fa-chevron-left"></i>
        </button>
        
        <div class="promo-container">
            <div class="promo-track" id="promoTrack">
                <?php
                // Pilates studio promotions
                $promotions = [
                    [
                        "title" => "New Year Special",
                        "description" => "Start your fitness journey with 30% off on all annual memberships",
                        "image" => "https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&auto=format&fit=crop&q=80",
                        "badge" => "30% OFF",
                        "features" => ["Valid until Jan 31", "All membership plans", "Free trial class included"],
                        "cta" => "Grab Offer"
                    ],
                    [
                        "title" => "Student Discount",
                        "description" => "Exclusive 25% discount for students with valid ID",
                        "image" => "https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?w=400&auto=format&fit=crop&q=80",
                        "badge" => "25% OFF",
                        "features" => ["Student ID required", "All class types", "Flexible schedules"],
                        "cta" => "Apply Now"
                    ],
                    [
                        "title" => "Refer & Earn",
                        "description" => "Refer a friend and get 1 month free on both memberships",
                        "image" => "https://images.unsplash.com/photo-1540497077202-7c8a3999166f?w=400&auto=format&fit=crop&q=80",
                        "badge" => "FREE MONTH",
                        "features" => ["Both get benefits", "No limit on referrals", "Instant activation"],
                        "cta" => "Refer Now"
                    ]
                ];
                
                foreach ($promotions as $promo):
                ?>
                <div class="promo-card" onclick="window.location.href='upgrade.php'">
                    <div class="promo-image">
                        <img src="<?= htmlspecialchars($promo['image']) ?>" alt="<?= htmlspecialchars($promo['title']) ?>">
                        <span class="promo-badge"><?= htmlspecialchars($promo['badge']) ?></span>
                    </div>
                    <div class="promo-content">
                        <h4><?= htmlspecialchars($promo['title']) ?></h4>
                        <p><?= htmlspecialchars($promo['description']) ?></p>
                        
                        <div class="promo-features">
                            <?php foreach ($promo['features'] as $feature): ?>
                            <div class="promo-feature">
                                <i class="fas fa-check"></i>
                                <span><?= htmlspecialchars($feature) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <button class="promo-cta">
                            <?= htmlspecialchars($promo['cta']) ?>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Right Arrow -->
        <button class="promo-arrow" id="nextPromo">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
    
    <div class="promo-dots" id="promoDots">
        <!-- Dots will be generated by JavaScript -->
    </div>
</section>

<script>
// Toast Notification Functions
function closeToast(button) {
    const toast = button.closest('.toast');
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(100%)';
    setTimeout(() => toast.remove(), 300);
}

// Auto-close toasts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const toasts = document.querySelectorAll('.toast');
    toasts.forEach(toast => {
        setTimeout(() => {
            const closeBtn = toast.querySelector('.toast-close');
            if (closeBtn) closeBtn.click();
        }, 5000);
    });
});

// Force refresh profile image when page loads
document.addEventListener('DOMContentLoaded', function() {
    const profileImage = document.getElementById('profileImage');
    if (profileImage) {
        // Add cache busting parameter
        const src = profileImage.src;
        if (src.indexOf('?t=') === -1) {
            profileImage.src = src + '?t=' + new Date().getTime();
        }
    }
});

// Promotions Carousel with Arrow Navigation
document.addEventListener('DOMContentLoaded', function() {
    const promoTrack = document.getElementById('promoTrack');
    const promoCards = document.querySelectorAll('.promo-card');
    const promoDotsContainer = document.getElementById('promoDots');
    const prevBtn = document.getElementById('prevPromo');
    const nextBtn = document.getElementById('nextPromo');
    
    if (!promoCards.length) return;
    
    let currentIndex = 0;
    const cardsPerView = window.innerWidth < 768 ? 1 : window.innerWidth < 992 ? 2 : 3;
    const totalCards = promoCards.length;
    const totalSlides = Math.ceil(totalCards / cardsPerView);
    
    // Create dots
    promoDotsContainer.innerHTML = '';
    for (let i = 0; i < totalSlides; i++) {
        const dot = document.createElement('button');
        dot.className = 'promo-dot';
        if (i === 0) dot.classList.add('active');
        dot.addEventListener('click', () => goToPromoSlide(i));
        promoDotsContainer.appendChild(dot);
    }
    
    const dots = document.querySelectorAll('.promo-dot');
    
    // Calculate card width including gap
    const cardStyle = window.getComputedStyle(promoCards[0]);
    const cardWidth = promoCards[0].offsetWidth + parseInt(cardStyle.marginRight || 25);
    
    function updatePromoCarousel() {
        const translateX = -currentIndex * (cardWidth * cardsPerView);
        promoTrack.style.transform = `translateX(${translateX}px)`;
        
        // Update dots
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === currentIndex);
        });
        
        // Update arrow button states
        if (prevBtn) prevBtn.disabled = currentIndex === 0;
        if (nextBtn) nextBtn.disabled = currentIndex === totalSlides - 1;
    }
    
    function goToPromoSlide(index) {
        currentIndex = index;
        updatePromoCarousel();
    }
    
    // Add event listeners to arrow buttons
    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            const newIndex = currentIndex - 1;
            if (newIndex >= 0) {
                goToPromoSlide(newIndex);
            }
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            const newIndex = currentIndex + 1;
            if (newIndex < totalSlides) {
                goToPromoSlide(newIndex);
            }
        });
    }
    
    // Handle window resize
    window.addEventListener('resize', function() {
        const newCardsPerView = window.innerWidth < 768 ? 1 : window.innerWidth < 992 ? 2 : 3;
        if (newCardsPerView !== cardsPerView) {
            // Reset to first slide on resize
            currentIndex = 0;
            updatePromoCarousel();
        }
    });
    
    // Initialize
    updatePromoCarousel();
});
</script>

<?php include("../includes/footer.php"); ?>