<?php
include("auth.php");
include("../config/db.php");

// Check if editing
$edit_mode = false;
$edit_data = [];
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_result = mysqli_query($conn, "SELECT * FROM Instructor WHERE instructor_ID = $edit_id");
    if (mysqli_num_rows($edit_result) > 0) {
        $edit_mode = true;
        $edit_data = mysqli_fetch_assoc($edit_result);
    }
}

if (isset($_POST['add'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    
    mysqli_query($conn,"
        INSERT INTO Instructor(instructor_Name, instructor_Email, instructor_Phone)
        VALUES('$name', '$email', '$phone')
    ");
    $success = "Instructor added successfully!";
}

if (isset($_POST['update'])) {
    $instructor_id = intval($_POST['instructor_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    
    mysqli_query($conn,"
        UPDATE Instructor 
        SET instructor_Name = '$name',
            instructor_Email = '$email',
            instructor_Phone = '$phone'
        WHERE instructor_ID = $instructor_id
    ");
    $success = "Instructor updated successfully!";
}

if (isset($_GET['delete'])) {
    mysqli_query($conn,"DELETE FROM Instructor WHERE instructor_ID={$_GET['delete']}");
    $success = "Instructor deleted successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Instructors - MyPilates Admin</title>
<link rel="stylesheet" href="../assets/style.css">
<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="manage-instructor-styles.css">
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
        <div class="dashboard-header">
            <h1>Manage Instructors</h1>
            <p class="dashboard-subtitle">
                <i class="fas fa-plus-circle"></i> Add, edit, or remove instructors
            </p>
        </div>

        <?php if(isset($success)): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <!-- Add/Edit Instructor Form -->
        <div class="form-card">
            <h2>
                <i class="fas <?php echo $edit_mode ? 'fa-edit' : 'fa-plus-circle'; ?>"></i>
                <?php echo $edit_mode ? 'Edit Instructor' : 'Add New Instructor'; ?>
            </h2>
            
            <?php if($edit_mode): ?>
                <div class="edit-notice">
                    <p>
                        <i class="fas fa-info-circle"></i>
                        Editing: <strong><?php echo htmlspecialchars($edit_data['instructor_Name']); ?></strong>
                    </p>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <?php if($edit_mode): ?>
                    <input type="hidden" name="instructor_id" value="<?php echo $edit_data['instructor_ID']; ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="required">
                            <i class="fas fa-user"></i> Full Name
                        </label>
                        <input type="text" name="name" class="input-field full-field"
                               value="<?php echo htmlspecialchars($edit_mode ? $edit_data['instructor_Name'] : ''); ?>" 
                               placeholder="e.g., Sarah Johnson" required>
                    </div>
                    <div class="form-group">
                        <label class="required">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <input type="email" name="email" class="input-field full-field"
                               value="<?php echo htmlspecialchars($edit_mode ? $edit_data['instructor_Email'] : ''); ?>" 
                               placeholder="sarah@example.com" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="required">
                        <i class="fas fa-phone"></i> Phone Number
                    </label>
                    <input type="tel" name="phone" class="input-field phone-field"
                           value="<?php echo htmlspecialchars($edit_mode ? $edit_data['instructor_Phone'] : ''); ?>" 
                           placeholder="e.g., 011-23456789" required>
                </div>
                
                <div class="form-actions">
                    <?php if($edit_mode): ?>
                        <button type="submit" name="update" class="btn-primary">
                            <i class="fas fa-save"></i> Update Instructor
                        </button>
                        <a href="manage_instructor.php" class="btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    <?php else: ?>
                        <button type="submit" name="add" class="btn-primary">
                            <i class="fas fa-plus-circle"></i> Add Instructor
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Instructors List -->
        <div class="instructors-card">
            <h2>
                <i class="fas fa-list"></i> All Instructors
            </h2>
            <div class="table-container">
                <table class="instructors-table">
                    <thead>
                        <tr>
                            <th>
                                <i class="fas fa-user"></i> Name
                            </th>
                            <th>
                                <i class="fas fa-envelope"></i> Email
                            </th>
                            <th>
                                <i class="fas fa-phone"></i> Phone
                            </th>
                            <th>
                                <i class="fas fa-cogs"></i> Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $q = mysqli_query($conn,"SELECT * FROM Instructor ORDER BY instructor_Name");
                        
                        if(mysqli_num_rows($q) == 0): ?>
                            <tr>
                                <td colspan="4" class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    No instructors found. Add your first instructor!
                                </td>
                            </tr>
                        <?php else:
                        while($i=mysqli_fetch_assoc($q)){
                            // FIXED: Escape quotes properly for JavaScript
                            $escaped_name = addslashes($i['instructor_Name']);
                            echo "<tr>
                                <td>{$i['instructor_Name']}</td>
                                <td>{$i['instructor_Email']}</td>
                                <td>{$i['instructor_Phone']}</td>
                                <td>
                                    <div class='action-buttons'>
                                        <a href='?edit={$i['instructor_ID']}' class='edit-btn'>
                                            <i class='fas fa-edit'></i> Edit
                                        </a>
                                        <a href='?delete={$i['instructor_ID']}' 
                                           class='delete-btn'
                                           onclick='return confirm(\"Are you sure you want to delete the instructor \\\"{$escaped_name}\\\"?\\n\\nThis action cannot be undone.\")'>
                                            <i class='fas fa-trash'></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>";
                        }
                        endif;
                        ?>
                    </tbody>
                </table>
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