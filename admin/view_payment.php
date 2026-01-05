<?php
include("auth.php");
include("../config/db.php");

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$payment_method_filter = $_GET['payment_method'] ?? '';
$search_query = $_GET['search'] ?? '';

// Build query with filters
$query = "SELECT p.*, c.customer_Name 
          FROM payment p 
          LEFT JOIN customermembership m ON p.membership_ID = m.membership_ID 
          LEFT JOIN customer c ON m.customer_ID = c.customer_ID 
          WHERE 1=1";
$params = [];
$param_types = '';

if (!empty($status_filter)) {
    $query .= " AND p.payment_status = ?";
    $params[] = $status_filter;
    $param_types .= 's';
}

if (!empty($payment_method_filter)) {
    $query .= " AND p.payment_method = ?";
    $params[] = $payment_method_filter;
    $param_types .= 's';
}

if (!empty($start_date)) {
    $query .= " AND DATE(p.payment_Date) >= ?";
    $params[] = $start_date;
    $param_types .= 's';
}

if (!empty($end_date)) {
    $query .= " AND DATE(p.payment_Date) <= ?";
    $params[] = $end_date;
    $param_types .= 's';
}

if (!empty($search_query)) {
    $query .= " AND (c.customer_Name LIKE ? OR p.payment_ID LIKE ?)";
    $params[] = "%{$search_query}%";
    $params[] = "%{$search_query}%";
    $param_types .= 'ss';
}

$query .= " ORDER BY p.payment_Date DESC";

// Execute query
if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, $param_types, ...$params);
    mysqli_stmt_execute($stmt);
    $q = mysqli_stmt_get_result($stmt);
} else {
    $q = mysqli_query($conn, $query);
}

// Calculate totals
$total_revenue = 0;
$total_payments = 0;
$card_total = 0;
$online_banking_total = 0;
$ewallet_total = 0;
$today_revenue = 0;
$today_date = date('Y-m-d');

if($q && mysqli_num_rows($q) > 0) {
    mysqli_data_seek($q, 0);
    
    while($p = mysqli_fetch_assoc($q)) {
        $amount = floatval($p['amount'] ?? 0);
        $total_revenue += $amount;
        $total_payments++;
        
        // Check if payment is from today
        $payment_date = $p['payment_Date'] ?? '';
        if (!empty($payment_date) && date('Y-m-d', strtotime($payment_date)) == $today_date) {
            $today_revenue += $amount;
        }
        
        // Categorize by payment method - FIXED LOGIC
        $method = $p['payment_method'] ?? '';
        $method_lower = strtolower($method);
        
        // Normalize payment method names
        if (strpos($method_lower, 'card') !== false || 
            strpos($method_lower, 'credit') !== false || 
            strpos($method_lower, 'debit') !== false ||
            strpos($method_lower, 'visa') !== false ||
            strpos($method_lower, 'mastercard') !== false) {
            $card_total += $amount;
        } elseif (strpos($method_lower, 'banking') !== false || 
                  strpos($method_lower, 'fpx') !== false ||
                  strpos($method_lower, 'transfer') !== false) {
            $online_banking_total += $amount;
        } elseif (strpos($method_lower, 'wallet') !== false ||
                  strpos($method_lower, 'ewallet') !== false ||
                  strpos($method_lower, 'grabpay') !== false ||
                  strpos($method_lower, 'touch n go') !== false ||
                  strpos($method_lower, 'boost') !== false) {
            $ewallet_total += $amount;
        }
    }
    
    mysqli_data_seek($q, 0);
}

// Get statuses and methods for filters
$statuses_q = mysqli_query($conn, "SELECT DISTINCT payment_status FROM payment WHERE payment_status IS NOT NULL AND payment_status != '' ORDER BY payment_status");
$statuses = [];
while($status_row = mysqli_fetch_assoc($statuses_q)) {
    $statuses[] = $status_row['payment_status'];
}

// Function to remove parameter from URL
function removeParamFromUrl($param) {
    $url = $_SERVER['PHP_SELF'];
    $query = $_GET;
    unset($query[$param]);
    return $url . (!empty($query) ? '?' . http_build_query($query) : '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Payments - MyPilates Admin</title>
<link rel="stylesheet" href="../assets/style.css">
<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="site-header">
        <div class="logo">
            <a href="../public/index.php">MyPilates</a>
        </div>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php" class="btn">Logout</a>
        </div>
    </div>

    <div class="payments-dashboard">
        <div class="dashboard-header">
            <h1>Payment History</h1>
            <p class="dashboard-subtitle">View and filter all customer payments</p>
        </div>

        <!-- Filter Section -->
        <div class="filter-container">
            <h3>Filter Payments</h3>
            <div class="single-line-filters">
                <form method="GET" class="compact-filter-form">
                    <div class="filter-row">
                        <!-- Search Box -->
                        <div class="filter-group compact">
                            <input type="text" name="search" placeholder="Search " 
                                   value="<?php echo htmlspecialchars($search_query); ?>">
                        </div>
                        
                        <!-- Payment Status -->
                        <div class="filter-group compact">
                            <select name="status" class="filter-control">
                                <option value="">All Statuses</option>
                                <?php foreach($statuses as $status): ?>
                                    <option value="<?php echo htmlspecialchars($status); ?>" 
                                            <?php echo $status_filter == $status ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($status); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Payment Method -->
                        <div class="filter-group compact">
                            <select name="payment_method" class="filter-control">
                                <option value="">All Methods</option>
                                <option value="Credit/Debit Card" <?php echo $payment_method_filter == 'Credit/Debit Card' ? 'selected' : ''; ?>>Credit/Debit Card</option>
                                <option value="Online Banking" <?php echo $payment_method_filter == 'Online Banking' ? 'selected' : ''; ?>>Online Banking</option>
                                <option value="E-Wallet" <?php echo $payment_method_filter == 'E-Wallet' ? 'selected' : ''; ?>>E-Wallet</option>
                            </select>
                        </div>
                        
                       <!-- Date Range-->
<div class="date-range-group">
    <input type="date" name="start_date" 
           value="<?php echo htmlspecialchars($start_date); ?>" 
           title="Start Date">
    <span class="date-separator">to</span>
    <input type="date" name="end_date" 
           value="<?php echo htmlspecialchars($end_date); ?>" 
           title="End Date">
</div>
                        
                        <!-- Action Buttons -->
                        <div class="filter-buttons compact">
                            <button type="submit" class="btn-filter">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                            <a href="view_payment.php" class="btn-reset">
                                <i class="fas fa-redo"></i> Reset All
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            
            <?php if(!empty($status_filter) || !empty($payment_method_filter) || !empty($start_date) || !empty($end_date) || !empty($search_query)): ?>
                <div class="active-filters">
                    <strong>Active Filters:</strong>
                    <?php if(!empty($search_query)): ?>
                        <span class="filter-tag">
                            Search: "<?php echo htmlspecialchars($search_query); ?>"
                            <a href="<?php echo removeParamFromUrl('search'); ?>" title="Remove">×</a>
                        </span>
                    <?php endif; ?>
                    <?php if(!empty($status_filter)): ?>
                        <span class="filter-tag">
                            Status: <?php echo htmlspecialchars($status_filter); ?>
                            <a href="<?php echo removeParamFromUrl('status'); ?>" title="Remove">×</a>
                        </span>
                    <?php endif; ?>
                    <?php if(!empty($payment_method_filter)): ?>
                        <span class="filter-tag">
                            Method: <?php echo htmlspecialchars($payment_method_filter); ?>
                            <a href="<?php echo removeParamFromUrl('payment_method'); ?>" title="Remove">×</a>
                        </span>
                    <?php endif; ?>
                    <?php if(!empty($start_date)): ?>
                        <span class="filter-tag">
                            From: <?php echo htmlspecialchars($start_date); ?>
                            <a href="<?php echo removeParamFromUrl('start_date'); ?>" title="Remove">×</a>
                        </span>
                    <?php endif; ?>
                    <?php if(!empty($end_date)): ?>
                        <span class="filter-tag">
                            To: <?php echo htmlspecialchars($end_date); ?>
                            <a href="<?php echo removeParamFromUrl('end_date'); ?>" title="Remove">×</a>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Payment Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <h4>Total Revenue</h4>
                <div class="number">RM <?php echo number_format($total_revenue, 2); ?></div>
            </div>
            <div class="stat-card">
                <h4>Total Payments</h4>
                <div class="number"><?php echo $total_payments; ?></div>
            </div>
            <div class="stat-card">
                <h4>Today's Revenue</h4>
                <div class="number">RM <?php echo number_format($today_revenue, 2); ?></div>
            </div>
            <div class="stat-card">
                <h4>Credit/Debit Card</h4>
                <div class="number">RM <?php echo number_format($card_total, 2); ?></div>
            </div>
            <div class="stat-card">
                <h4>Online Banking</h4>
                <div class="number">RM <?php echo number_format($online_banking_total, 2); ?></div>
            </div>
        </div>

        <!-- Payment List -->
        <div class="payments-card">
            <div class="payments-header">
                <h2>All Payments</h2>
                <div class="results-count">
                    Showing <strong><?php echo $total_payments; ?></strong> payment<?php echo $total_payments != 1 ? 's' : ''; ?>
                </div>
            </div>
            
            <?php if($total_payments > 0): ?>
                <div class="table-container">
                    <table class="payments-table">
                        <thead>
                            <tr>
                                <th>Payment ID</th>
                                <th>Customer Name</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Payment Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($p = mysqli_fetch_assoc($q)): 
                                $payment_id = $p['payment_ID'] ?? 'N/A';
                                $customer_name = $p['customer_Name'] ?? 'Unknown Customer';
                                $amount = $p['amount'] ?? '0.00';
                                $payment_method = $p['payment_method'] ?? 'N/A';
                                $payment_status = $p['payment_status'] ?? 'pending';
                                $payment_date = $p['payment_Date'] ?? null;
                                
                                $formatted_date = $payment_date ? date('Y-m-d H:i', strtotime($payment_date)) : 'N/A';
                                
                                // Set badge class based on payment method
                                $method_class = 'method-card';
                                $method_lower = strtolower($payment_method);
                                
                                if (strpos($method_lower, 'banking') !== false || 
                                    strpos($method_lower, 'fpx') !== false ||
                                    strpos($method_lower, 'transfer') !== false) {
                                    $method_class = 'method-online-banking';
                                } elseif (strpos($method_lower, 'wallet') !== false ||
                                          strpos($method_lower, 'ewallet') !== false) {
                                    $method_class = 'method-ewallet';
                                }
                            ?>
                                <tr>
                                    <td><strong><?php echo $payment_id; ?></strong></td>
                                    <td class="customer-name"><?php echo htmlspecialchars($customer_name); ?></td>
                                    <td class="amount"><strong>RM <?php echo number_format($amount, 2); ?></strong></td>
                                    <td>
                                        <span class="method-badge <?php echo $method_class; ?>">
                                            <?php 
                                            // Display proper method name - fixed logic
                                            $method_lower = strtolower($payment_method);
                                            
                                            if (strpos($method_lower, 'card') !== false || 
                                                strpos($method_lower, 'credit') !== false || 
                                                strpos($method_lower, 'debit') !== false) {
                                                echo 'Credit/Debit Card';
                                            } elseif (strpos($method_lower, 'banking') !== false || 
                                                      strpos($method_lower, 'fpx') !== false) {
                                                echo 'Online Banking';
                                            } elseif (strpos($method_lower, 'wallet') !== false) {
                                                echo 'E-Wallet';
                                            } else {
                                                echo htmlspecialchars($payment_method);
                                            }
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($payment_status); ?>">
                                            <?php echo htmlspecialchars($payment_status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $formatted_date; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No payments found</h3>
                    <p><?php echo (empty($status_filter) && empty($payment_method_filter) && empty($start_date) && empty($end_date) && empty($search_query)) ? 
                        'There are no payments in the system yet.' : 
                        'No payments match your filter criteria. Try adjusting your filters.'; ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="back-to-dashboard">
            <a href="dashboard.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>