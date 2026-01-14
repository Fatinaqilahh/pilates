<?php
include("auth.php");
include("../config/db.php");

// Check if editing
$edit_mode = false;
$edit_data = [];
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_result = mysqli_query($conn, "SELECT * FROM MembershipPlan WHERE plan_ID = $edit_id");
    if (mysqli_num_rows($edit_result) > 0) {
        $edit_mode = true;
        $edit_data = mysqli_fetch_assoc($edit_result);
    }
}

if (isset($_POST['add'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $desc = mysqli_real_escape_string($conn, $_POST['desc']);
    $price = floatval($_POST['price']);
    $duration = intval($_POST['duration']);
    
    mysqli_query($conn,"
        INSERT INTO MembershipPlan
        (plan_Name, plan_Description, plan_Price, plan_Duration)
        VALUES
        ('$name', '$desc', $price, $duration)
    ");
    $success = "Membership plan added successfully!";
}

if (isset($_POST['update'])) {
    $plan_id = intval($_POST['plan_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $desc = mysqli_real_escape_string($conn, $_POST['desc']);
    $price = floatval($_POST['price']);
    $duration = intval($_POST['duration']);
    
    mysqli_query($conn,"
        UPDATE MembershipPlan 
        SET plan_Name = '$name',
            plan_Description = '$desc',
            plan_Price = $price,
            plan_Duration = $duration
        WHERE plan_ID = $plan_id
    ");
    $success = "Membership plan updated successfully!";
}

if (isset($_GET['delete'])) {
    mysqli_query($conn,"DELETE FROM MembershipPlan WHERE plan_ID={$_GET['delete']}");
    $success = "Membership plan deleted successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Membership Plans - MyPilates Admin</title>
<link rel="stylesheet" href="../assets/style.css">
<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="manage-plan-styles.css">
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



    <div class="dashboard-container">
        <div class="back-to-dashboard">
           <div class="back-to-dashboard" style="text-align: left;">
    <a href="dashboard.php" class="back-link">
        ← Back to Dashboard
    </a>
</div>
        </div>


        <div class="dashboard-header">
            <h1><i class="fas fa-id-card"></i> Membership Plans</h1>
            <p class="dashboard-subtitle">Create and manage membership plans</p>
        </div>

        <?php if(isset($success)): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <!-- Add/Edit Plan Form -->
        <div class="form-card">
            <h2>
                <i class="fas <?php echo $edit_mode ? 'fa-edit' : 'fa-plus-circle'; ?>"></i> 
                <?php echo $edit_mode ? 'Edit Plan' : 'Add New Plan'; ?>
            </h2>
            
            <?php if($edit_mode): ?>
                <div class="edit-notice">
                    <p>
                        <i class="fas fa-info-circle"></i> Editing: <strong><?php echo htmlspecialchars($edit_data['plan_Name']); ?></strong>
                    </p>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <?php if($edit_mode): ?>
                    <input type="hidden" name="plan_id" value="<?php echo $edit_data['plan_ID']; ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="required">
                            <i class="fas fa-tag"></i> Plan Name
                        </label>
                        <input type="text" name="name" class="input-field"
                               value="<?php echo htmlspecialchars($edit_mode ? $edit_data['plan_Name'] : ''); ?>" 
                               placeholder="e.g., Monthly Unlimited" required>
                    </div>
                    <div class="form-group">
                        <label class="required">
                            <i class="fas fa-money-bill-wave"></i> Price (RM)
                        </label>
                        <input type="number" name="price" step="0.01" min="0" class="input-field"
                               value="<?php echo $edit_mode ? $edit_data['plan_Price'] : ''; ?>" 
                               placeholder="99.00" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="required">
                        <i class="fas fa-calendar-alt"></i> Duration (days)
                    </label>
                    <input type="number" name="duration" min="1" class="input-field"
                           value="<?php echo $edit_mode ? $edit_data['plan_Duration'] : ''; ?>" 
                           placeholder="30" required>
                </div>
                
                <div class="form-group">
                    <label class="required">
                        <i class="fas fa-align-left"></i> Description
                    </label>
                    <textarea name="desc" class="textarea-field" placeholder="Describe the plan benefits..." required><?php echo htmlspecialchars($edit_mode ? $edit_data['plan_Description'] : ''); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <?php if($edit_mode): ?>
                        <button type="submit" name="update" class="btn-primary">
                            <i class="fas fa-save"></i> Update Plan
                        </button>
                        <a href="manage_plan.php" class="btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    <?php else: ?>
                        <button type="submit" name="add" class="btn-primary">
                            <i class="fas fa-plus-circle"></i> Add Plan
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Plans List -->
        <div class="plans-card">
            <h2>
                <i class="fas fa-list"></i> All Membership Plans
            </h2>
            
            <div class="plans-grid">
                <?php
                $q = mysqli_query($conn, "SELECT * FROM MembershipPlan ORDER BY plan_Price ASC");
                
                if(mysqli_num_rows($q) == 0): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        No membership plans found. Add your first plan!
                    </div>
                <?php else:
                while($p = mysqli_fetch_assoc($q)){
                    // Escape quotes for JavaScript confirm
                    $escaped_name = addslashes($p['plan_Name']);
                    echo "
                    <div class='plan-card'>
                        <h3><i class='fas fa-id-card'></i> {$p['plan_Name']}</h3>
                        <p class='plan-desc'><i class='fas fa-align-left'></i> {$p['plan_Description']}</p>
                        <div class='plan-price'>RM " . number_format($p['plan_Price'], 2) . "</div>
                        <p><i class='fas fa-calendar-alt'></i> <strong>Duration:</strong> {$p['plan_Duration']} days</p>
                        
                        <div class='plan-actions'>
                            <a href='?edit={$p['plan_ID']}' class='btn-edit'>
                                <i class='fas fa-edit'></i> Edit
                            </a>
                            <a href='?delete={$p['plan_ID']}' 
                               class='btn-delete'
                               onclick='return confirm(\"Are you sure you want to delete the \\\"{$escaped_name}\\\" plan?\\n\\nThis action cannot be undone.\")'>
                                <i class='fas fa-trash'></i> Delete
                            </a>
                        </div>
                    </div>";
                }
                endif;
                ?>
            </div>
        </div>
        
        <div class="back-to-dashboard">
            <a href="dashboard.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>