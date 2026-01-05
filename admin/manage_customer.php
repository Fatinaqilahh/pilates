<?php
include("auth.php");
include("../config/db.php");

// Delete customer if requested
if (isset($_GET['delete'])) {
    $customer_id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM customermembership WHERE customer_ID = $customer_id");
    mysqli_query($conn, "DELETE FROM booking WHERE customer_ID = $customer_id");
    mysqli_query($conn, "DELETE FROM Customer WHERE customer_ID = $customer_id");
    $success = "Customer deleted successfully!";
}

// Get filter parameters
$search_query = $_GET['search'] ?? '';
$plan_filter = $_GET['plan'] ?? '';
$sort_by = $_GET['sort'] ?? 'customer_Name';

// Build query with filters
$query = "SELECT c.customer_ID, c.customer_Name, c.customer_Email, c.customer_Phone, mp.plan_Name FROM Customer c LEFT JOIN customermembership cm ON c.customer_ID = cm.customer_ID LEFT JOIN membershipplan mp ON cm.plan_ID = mp.plan_ID WHERE 1=1";

$params = [];
$param_types = '';

if (!empty($search_query)) {
    $query .= " AND (c.customer_Name LIKE ? OR c.customer_Email LIKE ? OR c.customer_Phone LIKE ?)";
    $params[] = "%{$search_query}%";
    $params[] = "%{$search_query}%";
    $params[] = "%{$search_query}%";
    $param_types .= 'sss';
}

if (!empty($plan_filter)) {
    $query .= " AND mp.plan_Name = ?";
    $params[] = $plan_filter;
    $param_types .= 's';
}

// Add sorting
$allowed_sorts = ['customer_Name', 'plan_Name'];
$sort_by = in_array($sort_by, $allowed_sorts) ? $sort_by : 'customer_Name';
$sort_order = $_GET['order'] ?? 'ASC';
$sort_order = strtoupper($sort_order) === 'DESC' ? 'DESC' : 'ASC';
$query .= " ORDER BY $sort_by $sort_order";

// Execute query
if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $query);
    if ($param_types) {
        mysqli_stmt_bind_param($stmt, $param_types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $q = mysqli_stmt_get_result($stmt);
} else {
    $q = mysqli_query($conn, $query);
}

$total_customers = mysqli_num_rows($q);

// Get unique plan names from membershipplan table
$plans_q = mysqli_query($conn, "SELECT DISTINCT plan_Name FROM membershipplan WHERE plan_Name IS NOT NULL AND plan_Name != '' ORDER BY plan_Name");
$plans = [];
while($plan_row = mysqli_fetch_assoc($plans_q)) {
    $plans[] = $plan_row['plan_Name'];
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
<title>Manage Customers - MyPilates Admin</title>
<link rel="stylesheet" href="../assets/style.css">
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

    <div class="customers-dashboard">
        <div class="dashboard-header">
            <h1>Manage Customers</h1>
            <p class="dashboard-subtitle">View and manage all customer accounts</p>
        </div>

        <?php if(isset($success)): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <!-- Filter Section -->
        <div class="filter-container">
            <h3>Filter Customers</h3>
            <div class="single-line-filters">
                <form method="GET" class="compact-filter-form">
                    <div class="filter-row">
                        <!-- Search Box -->
                        <div class="filter-group compact">
                            <input type="text" name="search" placeholder="Search" 
                                   value="<?php echo htmlspecialchars($search_query); ?>">
                        </div>
                        
                        <!-- Plan Filter -->
                        <div class="filter-group compact">
                            <select name="plan" class="filter-control">
                                <option value="">All Plans</option>
                                <?php foreach($plans as $plan): ?>
                                    <option value="<?php echo htmlspecialchars($plan); ?>" 
                                            <?php echo $plan_filter == $plan ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($plan); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Sort Options -->
                        <div class="filter-group compact">
                            <select name="sort" class="filter-control" onchange="this.form.submit()">
                                <option value="customer_Name" <?php echo $sort_by == 'customer_Name' ? 'selected' : ''; ?>>Sort by Name</option>
                                <option value="plan_Name" <?php echo $sort_by == 'plan_Name' ? 'selected' : ''; ?>>Sort by Plan</option>
                            </select>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="filter-buttons compact">
                            <button type="submit" class="btn-filter">
                                <i class="fas fa-filter"></i> Apply
                            </button>
                            <a href="manage_customer.php" class="btn-reset">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            
            <?php if(!empty($search_query) || !empty($plan_filter)): ?>
                <div class="active-filters">
                    <strong>Active Filters:</strong>
                    <?php if(!empty($search_query)): ?>
                        <span class="filter-tag">
                            Search: "<?php echo htmlspecialchars($search_query); ?>"
                            <a href="<?php echo removeParamFromUrl('search'); ?>" title="Remove">×</a>
                        </span>
                    <?php endif; ?>
                    <?php if(!empty($plan_filter)): ?>
                        <span class="filter-tag">
                            Plan: <?php echo htmlspecialchars($plan_filter); ?>
                            <a href="<?php echo removeParamFromUrl('plan'); ?>" title="Remove">×</a>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Customer Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <h4>Total Customers</h4>
                <div class="number"><?php echo $total_customers; ?></div>
            </div>
            <div class="stat-card">
                <h4>Free Plan</h4>
                <div class="number">
                    <?php 
                    $free_q = mysqli_query($conn, "SELECT COUNT(DISTINCT c.customer_ID) as count 
                                                     FROM Customer c 
                                                     JOIN customermembership cm ON c.customer_ID = cm.customer_ID 
                                                     JOIN membershipplan mp ON cm.plan_ID = mp.plan_ID
                                                     WHERE mp.plan_Name = 'Free'");
                    $free_row = mysqli_fetch_assoc($free_q);
                    echo $free_row['count'] ?? 0;
                    ?>
                </div>
            </div>
            <div class="stat-card">
                <h4>Basic Plan</h4>
                <div class="number">
                    <?php 
                    $basic_q = mysqli_query($conn, "SELECT COUNT(DISTINCT c.customer_ID) as count 
                                                     FROM Customer c 
                                                     JOIN customermembership cm ON c.customer_ID = cm.customer_ID 
                                                     JOIN membershipplan mp ON cm.plan_ID = mp.plan_ID
                                                     WHERE mp.plan_Name = 'Basic'");
                    $basic_row = mysqli_fetch_assoc($basic_q);
                    echo $basic_row['count'] ?? 0;
                    ?>
                </div>
            </div>
            <div class="stat-card">
                <h4>Premium Plan</h4>
                <div class="number">
                    <?php 
                    $premium_q = mysqli_query($conn, "SELECT COUNT(DISTINCT c.customer_ID) as count 
                                                     FROM Customer c 
                                                     JOIN customermembership cm ON c.customer_ID = cm.customer_ID 
                                                     JOIN membershipplan mp ON cm.plan_ID = mp.plan_ID
                                                     WHERE mp.plan_Name = 'Premium'");
                    $premium_row = mysqli_fetch_assoc($premium_q);
                    echo $premium_row['count'] ?? 0;
                    ?>
                </div>
            </div>
            <div class="stat-card">
                <h4>VIP Plan</h4>
                <div class="number">
                    <?php 
                    $vip_q = mysqli_query($conn, "SELECT COUNT(DISTINCT c.customer_ID) as count 
                                                     FROM Customer c 
                                                     JOIN customermembership cm ON c.customer_ID = cm.customer_ID 
                                                     JOIN membershipplan mp ON cm.plan_ID = mp.plan_ID
                                                     WHERE mp.plan_Name = 'VIP'");
                    $vip_row = mysqli_fetch_assoc($vip_q);
                    echo $vip_row['count'] ?? 0;
                    ?>
                </div>
            </div>
        </div>

        <!-- Customer List -->
        <div class="customers-card">
            <div class="customers-header">
                <h2>All Customers</h2>
                <div class="results-count">
                    Showing <strong><?php echo $total_customers; ?></strong> customer<?php echo $total_customers != 1 ? 's' : ''; ?>
                </div>
            </div>
            
            <?php if($total_customers > 0): ?>
                <div class="table-container">
                    <table class="customers-table">
                        <thead>
                            <tr>
                                <th>Customer ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Plan</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($customer = mysqli_fetch_assoc($q)): 
                                $customer_id = $customer['customer_ID'];
                                $customer_name = $customer['customer_Name'];
                                $customer_email = $customer['customer_Email'];
                                $customer_phone = $customer['customer_Phone'];
                                $plan_name = $customer['plan_Name'] ?? 'Free'; // Default to Free since all customers have a plan
                            ?>
                                <tr>
                                    <td><strong><?php echo $customer_id; ?></strong></td>
                                    <td><strong class="customer-name"><?php echo htmlspecialchars($customer_name); ?></strong></td>
                                    <td><?php echo htmlspecialchars($customer_email); ?></td>
                                    <td><?php echo htmlspecialchars($customer_phone); ?></td>
                                    <td>
                                        <span class="plan-badge <?php 
                                            $plan_class = strtolower($plan_name);
                                            if (strpos($plan_class, 'free') !== false) echo 'free';
                                            elseif (strpos($plan_class, 'basic') !== false) echo 'basic';
                                            elseif (strpos($plan_class, 'premium') !== false) echo 'premium';
                                            elseif (strpos($plan_class, 'vip') !== false) echo 'vip';
                                            else echo 'free'; // Default to free
                                        ?>">
                                            <?php echo htmlspecialchars($plan_name); ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <a href="?delete=<?php echo $customer_id; ?>" 
                                           class="btn-delete" 
                                           title="Delete Customer"
                                           onclick="return confirm('Are you sure you want to delete <?php echo addslashes($customer_name); ?>? This action cannot be undone.');">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3>No customers found</h3>
                    <p><?php echo (empty($search_query) && empty($plan_filter)) ? 
                        'There are no customers registered yet.' : 
                        'No customers match your filter criteria. Try adjusting your filters.'; ?></p>
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