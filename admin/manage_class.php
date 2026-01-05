<?php
include("auth.php");
include("../config/db.php");

// Check if editing
$edit_mode = false;
$edit_data = [];
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_result = mysqli_query($conn, "SELECT Class.*, Instructor.instructor_Name 
                                        FROM Class 
                                        JOIN Instructor ON Class.instructor_ID = Instructor.instructor_ID 
                                        WHERE class_ID = $edit_id");
    if (mysqli_num_rows($edit_result) > 0) {
        $edit_mode = true;
        $edit_data = mysqli_fetch_assoc($edit_result);
    }
}

if (isset($_POST['add'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $schedule = mysqli_real_escape_string($conn, $_POST['schedule']);
    $instructor = intval($_POST['instructor']);
    
    mysqli_query($conn,"
        INSERT INTO Class(class_Name, class_Schedule, instructor_ID)
        VALUES('$name', '$schedule', $instructor)
    ");
    $success = "Class added successfully!";
}

if (isset($_POST['update'])) {
    $class_id = intval($_POST['class_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $schedule = mysqli_real_escape_string($conn, $_POST['schedule']);
    $instructor = intval($_POST['instructor']);
    
    mysqli_query($conn,"
        UPDATE Class 
        SET class_Name = '$name',
            class_Schedule = '$schedule',
            instructor_ID = $instructor
        WHERE class_ID = $class_id
    ");
    $success = "Class updated successfully!";
}

if (isset($_GET['delete'])) {
    mysqli_query($conn,"DELETE FROM Class WHERE class_ID={$_GET['delete']}");
    $success = "Class deleted successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Classes - MyPilates Admin</title>
<link rel="stylesheet" href="../assets/style.css">
<!-- Keep your original Font Awesome link -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Add our custom stylesheet -->
<link rel="stylesheet" href="manage-class-styles.css">
</head>

<body>
    <!-- Keep your original site header -->
    <div class="site-header">
        <div class="logo">
            <a href="../public/index.php">MyPilates</a>
        </div>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php" class="btn">Logout</a>
        </div>
    </div>

    <!-- Main content with new classes -->
    <div class="dashboard-container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h1>Manage Classes</h1>
            <p class="dashboard-subtitle">
                <i class="fas fa-plus-circle"></i> Add, edit, or remove classes
            </p>
        </div>

        <?php if(isset($success)): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <!-- Add/Edit Class Form -->
        <div class="form-card">
            <h2>
                <i class="fas <?php echo $edit_mode ? 'fa-edit' : 'fa-plus-circle'; ?>"></i> 
                <?php echo $edit_mode ? 'Edit Class' : 'Add New Class'; ?>
            </h2>
            
            <?php if($edit_mode): ?>
                <div class="edit-notice">
                    <p>
                        <i class="fas fa-info-circle"></i> 
                        Editing: <strong><?php echo htmlspecialchars($edit_data['class_Name']); ?></strong>
                    </p>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <?php if($edit_mode): ?>
                    <input type="hidden" name="class_id" value="<?php echo $edit_data['class_ID']; ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="required">
                            <i class="fas fa-chalkboard-teacher"></i> Class Name
                        </label>
                        <input type="text" name="name" class="input-field"
                               value="<?php echo htmlspecialchars($edit_mode ? $edit_data['class_Name'] : ''); ?>" 
                               placeholder="e.g., Morning Pilates" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="required">
                            <i class="fas fa-calendar-alt"></i> Schedule
                        </label>
                        <input type="text" name="schedule" class="input-field"
                               value="<?php echo htmlspecialchars($edit_mode ? $edit_data['class_Schedule'] : ''); ?>" 
                               placeholder="e.g., Mon/Wed/Fri 9:00 AM" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="required">
                        <i class="fas fa-user-tie"></i> Instructor
                    </label>
                    <select name="instructor" class="input-field" required>
                        <option value="">Select Instructor</option>
                        <?php
                        $i = mysqli_query($conn,"SELECT * FROM Instructor");
                        while($row=mysqli_fetch_assoc($i)){
                            $selected = ($edit_mode && $row['instructor_ID'] == $edit_data['instructor_ID']) ? 'selected' : '';
                            echo "<option value='{$row['instructor_ID']}' $selected>{$row['instructor_Name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-actions">
                    <?php if($edit_mode): ?>
                        <button type="submit" name="update" class="btn-primary">
                            <i class="fas fa-save"></i> Update Class
                        </button>
                        <a href="manage_class.php" class="btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    <?php else: ?>
                        <button type="submit" name="add" class="btn-primary">
                            <i class="fas fa-plus-circle"></i> Add Class
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Classes List -->
        <div class="classes-card">
            <h2>
                <i class="fas fa-list"></i> All Classes
            </h2>
            
            <div class="table-container">
                <table class="classes-table">
                    <thead>
                        <tr>
                            <th>
                                <i class="fas fa-chalkboard-teacher"></i> Class Name
                            </th>
                            <th>
                                <i class="fas fa-user-tie"></i> Instructor
                            </th>
                            <th>
                                <i class="fas fa-calendar-alt"></i> Schedule
                            </th>
                            <th>
                                <i class="fas fa-cogs"></i> Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $q = mysqli_query($conn,"
                        SELECT Class.*, Instructor.instructor_Name
                        FROM Class
                        JOIN Instructor ON Class.instructor_ID = Instructor.instructor_ID
                        ORDER BY class_Name
                        ");
                        
                        if(mysqli_num_rows($q) == 0): ?>
                            <tr>
                                <td colspan="4" class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    No classes found. Add your first class!
                                </td>
                            </tr>
                        <?php else:
                        while($c=mysqli_fetch_assoc($q)){
                            echo "<tr>
                                <td class='class-name'>{$c['class_Name']}</td>
                                <td class='instructor-name'>{$c['instructor_Name']}</td>
                                <td class='schedule'>{$c['class_Schedule']}</td>
                                <td>
                                    <div class='action-buttons'>
                                        <a href='?edit={$c['class_ID']}' class='edit-btn'>
                                            <i class='fas fa-edit'></i> Edit
                                        </a>
                                        <a href='?delete={$c['class_ID']}' 
                                           class='delete-btn'
                                           onclick='return confirm(\"Are you sure you want to delete the \\\"{$c['class_Name']}\\\" class?\\n\\nThis action cannot be undone.\")'>
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
        
        <!-- Back Link - Same as View Payments -->
        <div class="back-to-dashboard">
            <a href="dashboard.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>