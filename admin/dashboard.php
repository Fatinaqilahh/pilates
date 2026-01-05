<?php
include("auth.php");
include("../config/db.php");

// Get counts for dashboard
$class_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM Class"))['count'];
$instructor_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM Instructor"))['count'];
$customer_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM Customer"))['count'];
$plan_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM MembershipPlan"))['count'];
$payment_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM Payment"))['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - MyPilates</title>
<link rel="stylesheet" href="../assets/style.css">
<style>
/* Minimal Sidebar Styles */
.admin-wrapper {
    display: flex;
    min-height: 100vh;
}

.admin-sidebar {
    width: 220px;
    background: #0b4d2b;
    color: white;
    padding: 20px 0;
}

.sidebar-logo {
    padding: 0 20px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    margin-bottom: 20px;
}

.sidebar-logo h2 {
    color: white;
    margin: 0;
}

.sidebar-menu {
    padding: 0 20px;
}

.sidebar-menu a {
    display: block;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    padding: 12px 15px;
    margin: 5px 0;
    border-radius: 5px;
    transition: all 0.3s;
}

.sidebar-menu a:hover,
.sidebar-menu a.active {
    background: rgba(255,255,255,0.1);
    color: white;
}

.sidebar-menu a.active {
    background: rgba(255,255,255,0.15);
    font-weight: 500;
}

.admin-content {
    flex: 1;
    padding: 30px;
    background: #f8f9fa;
}

.admin-header {
    margin-bottom: 30px;
}

.admin-header h1 {
    color: #1a1a1a;
    margin-bottom: 10px;
}

.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.08);
    text-align: center;
    border: 1px solid #eee;
}

.card h3 {
    color: #666;
    margin-bottom: 15px;
    font-size: 16px;
    text-transform: uppercase;
}

.card div {
    font-size: 40px;
    font-weight: bold;
    color: #0b4d2b;
    margin: 20px 0;
}

/* Recent Activity Styles */
.recent-activity-container {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.08);
    max-width: 1200px;
    margin: 0 auto;
}

.recent-activity-container h2 {
    margin-bottom: 25px;
    color: #333;
    font-size: 24px;
    border-bottom: 2px solid #0b4d2b;
    padding-bottom: 10px;
}

.recent-activity-list {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    padding: 15px;
    border-bottom: 1px solid #eee;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: background 0.2s;
}

.activity-item:hover {
    background: #f9f9f9;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.activity-icon.paid {
    background: #e8f5e8;
    color: #388e3c;
}

.activity-icon.pending {
    background: #fff3e0;
    color: #f57c00;
}

.activity-details {
    flex: 1;
}

.activity-title {
    font-weight: 500;
    color: #333;
    margin-bottom: 5px;
    font-size: 16px;
}

.activity-meta {
    font-size: 14px;
    color: #666;
}

.activity-amount {
    font-weight: bold;
    color: #0b4d2b;
    font-size: 18px;
    min-width: 120px;
    text-align: right;
}

.no-activity {
    text-align: center;
    padding: 40px 20px;
    color: #999;
}

.no-activity p {
    margin-top: 10px;
    font-size: 16px;
}
</style>
</head>

<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-logo">
                <h2>MyPilates</h2>
                <p style="color: rgba(255,255,255,0.7); font-size: 14px; margin-top: 5px;">Admin Panel</p>
            </div>
            
            <nav class="sidebar-menu">
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="manage_class.php">Classes</a>
                <a href="manage_instructor.php">Instructors</a>
                <a href="manage_customer.php">Customers</a>
                <a href="manage_plan.php">Membership Plans</a>
                <a href="view_payment.php">Payments</a>
                <a href="../public/index.php" style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">Back to Home</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <div class="admin-header">
                <h1 style="font-size: 32px;">Admin Dashboard</h1>
                <p style="color: #666;">Manage your Pilates studio</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid">
                <div class="card">
                    <h3>Classes</h3>
                    <div><?php echo $class_count; ?></div>
                </div>
                <div class="card">
                    <h3>Instructors</h3>
                    <div><?php echo $instructor_count; ?></div>
                </div>
                <div class="card">
                    <h3>Customers</h3>
                    <div><?php echo $customer_count; ?></div>
                </div>
                <div class="card">
                    <h3>Membership Plans</h3>
                    <div><?php echo $plan_count; ?></div>
                </div>
                <div class="card">
                    <h3>Revenue</h3>
                    <div>RM <?php echo number_format($payment_total, 2); ?></div>
                </div>
            </div>

            <!-- Recent Activity Section -->
            <div class="recent-activity-container">
                <h2>Recent Payment Activity</h2>
                <div class="recent-activity-list">
                    <?php 
                    // CORRECTED QUERY with customermembership table name
                    $recent_query = "SELECT p.*, c.customer_Name as customer_name 
                                     FROM Payment p 
                                     LEFT JOIN customermembership cm ON p.membership_ID = cm.membership_ID 
                                     LEFT JOIN Customer c ON cm.customer_ID = c.customer_ID 
                                     ORDER BY p.payment_Date DESC LIMIT 8";
                    
                    $recent_result = mysqli_query($conn, $recent_query);
                    
                    if(mysqli_num_rows($recent_result) > 0): 
                        while($activity = mysqli_fetch_assoc($recent_result)): 
                            // Format the date nicely
                            $date = date('M d, h:i A', strtotime($activity['payment_Date']));
                            $status = strtolower($activity['payment_status']);
                            
                            // Determine icon based on status
                            if($status == 'paid') {
                                $icon = '✅';
                                $icon_class = 'paid';
                            } else {
                                $icon = '⏳';
                                $icon_class = 'pending';
                            }
                    ?>
                    <div class="activity-item">
                        <div class="activity-icon <?php echo $icon_class; ?>">
                            <?php echo $icon; ?>
                        </div>
                        <div class="activity-details">
                            <div class="activity-title">
                                <?php if(!empty($activity['customer_name'])): ?>
                                    <?php echo $activity['customer_name']; ?>
                                <?php else: ?>
                                    Customer #<?php echo $activity['membership_ID']; ?>
                                <?php endif; ?>
                            </div>
                            <div class="activity-meta">
                                Payment #<?php echo $activity['payment_ID']; ?> • 
                                <?php echo strtoupper($activity['payment_method']); ?> • 
                                <span style="color: <?php echo ($status == 'paid') ? '#388e3c' : '#f57c00'; ?>">
                                    <?php echo strtoupper($status); ?>
                                </span> • 
                                <?php echo $date; ?>
                            </div>
                        </div>
                        <div class="activity-amount">
                            RM <?php echo number_format($activity['amount'], 2); ?>
                        </div>
                    </div>
                    <?php 
                        endwhile;
                    else: ?>
                    <div class="no-activity">
                        <div style="font-size: 48px; color: #ddd;">📊</div>
                        <p>No payment records found</p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if(mysqli_num_rows($recent_result) > 0): ?>
                <div style="text-align: center; margin-top: 20px;">
                    <a href="view_payment.php" style="color: #0b4d2b; text-decoration: none; font-weight: 500; font-size: 16px;">
                        View All Payments →
                    </a>
                </div>
                <?php endif; ?>
            </div>

        </main>
    </div>
</body>
</html>