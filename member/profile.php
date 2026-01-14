<?php
session_start();
include("../config/db.php");

/* ================= AUTH GUARD ================= */
if (!isset($_SESSION['customer_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id = (int) $_SESSION['customer_id'];

/* ================= FETCH PROFILE ================= */
$profileQ = mysqli_query($conn, "SELECT * FROM customer WHERE customer_ID = $id LIMIT 1");
$profile = mysqli_fetch_assoc($profileQ);
if (!$profile) {
    die("Profile not found.");
}

/* ================= HANDLE UPDATE ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name  = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    $profile_image = $profile['profile_image']; // keep old image

    /* ===== IMAGE UPLOAD ===== */
    if (!empty($_FILES['profile_image']['name'])) {

        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $_SESSION['error_message'] = "Invalid image format.";
            header("Location: profile.php");
            exit;
        }

        if ($_FILES['profile_image']['size'] > 5 * 1024 * 1024) {
            $_SESSION['error_message'] = "Image must be under 5MB.";
            header("Location: profile.php");
            exit;
        }

        $upload_dir = "../assets/uploads/profile_images/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $new_name = uniqid() . "_" . $id . "." . $ext;
        $target = $upload_dir . $new_name;

        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target)) {

            // delete old image
            if (!empty($profile['profile_image']) && file_exists($upload_dir . $profile['profile_image'])) {
                unlink($upload_dir . $profile['profile_image']);
            }

            $profile_image = $new_name;
        }
    }

    /* ===== UPDATE DB ===== */
    mysqli_query($conn, "
        UPDATE customer SET
            customer_Name='$name',
            customer_Email='$email',
            customer_Phone='$phone',
            customer_Address='$address',
            profile_image='$profile_image'
        WHERE customer_ID=$id
    ");

    $_SESSION['success_message'] = "Profile updated successfully!";
    header("Location: profile.php?t=" . time());
    exit;
}

/* ================= IMAGE URL ================= */
$image_path = "../assets/uploads/profile_images/default.png";
if (!empty($profile['profile_image']) && file_exists("../assets/uploads/profile_images/".$profile['profile_image'])) {
    $image_path = "../assets/uploads/profile_images/".$profile['profile_image'];
}
?>

<?php include("../includes/header.php"); ?>

<style>
.profile-container{max-width:800px;margin:40px auto}
.profile-header{background:#30693b;color:#fff;padding:30px;border-radius:12px;text-align:center}
.profile-card{background:#fff;padding:30px;border-radius:12px;margin-top:30px}
.profile-img{width:140px;height:140px;border-radius:50%;object-fit:cover;border:4px solid #30693b}
.img-upload{position:relative;display:inline-block}
.img-upload label{position:absolute;bottom:0;right:0;background:#30693b;color:#fff;width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer}
input[type=file]{display:none}
.form-group{margin-bottom:20px}
.form-group label{display:block;margin-bottom:6px}
.form-control{width:100%;padding:12px;border:1px solid #ccc;border-radius:6px}
.btn{padding:12px 20px;border-radius:6px;border:none;cursor:pointer}
.btn-primary{background:#30693b;color:#fff}
.btn-back{background:#777;color:#fff;text-decoration:none}
.alert{margin:20px auto;padding:12px;border-radius:6px;max-width:800px}
.alert.success{background:#d4edda}
.alert.error{background:#f8d7da}

/* ===== NEW STYLES FOR IMAGE UPLOAD ===== */
.image-upload-section {
    text-align: center;
    margin-bottom: 30px;
    padding: 25px;
    background: #f9f9f9;
    border-radius: 12px;
    border: 2px dashed #30693b;
}

.upload-instructions {
    margin-top: 15px;
    color: #666;
    font-size: 14px;
}

.upload-instructions ul {
    list-style: none;
    padding: 0;
    margin: 10px 0;
}

.upload-instructions li {
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.upload-instructions i {
    color: #30693b;
}

.upload-button {
    display: inline-block;
    margin-top: 15px;
    padding: 12px 24px;
    background: #30693b;
    color: white;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    font-size: 16px;
}

.upload-button:hover {
    background: #285a32;
    transform: translateY(-2px);
}

.upload-button i {
    margin-right: 8px;
}

.current-image-info {
    margin-top: 10px;
    font-size: 13px;
    color: #777;
}
</style>

<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert success"><?= $_SESSION['success_message'] ?></div>
<?php unset($_SESSION['success_message']); endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert error"><?= $_SESSION['error_message'] ?></div>
<?php unset($_SESSION['error_message']); endif; ?>

<div class="profile-container">

    <div class="profile-header">
        <!-- CHANGED: Added user name to profile title -->
        <h2><?= htmlspecialchars($profile['customer_Name']) ?>'s Profile</h2>
        <p>Manage your account</p>
    </div>

    <div class="profile-card">
        <form method="POST" enctype="multipart/form-data">

            <!-- UPDATED: Better organized profile image upload section -->
            <div class="image-upload-section">
                <!-- Profile image preview -->
                <div class="img-upload">
                    <img src="<?= $image_path ?>?t=<?= time() ?>" class="profile-img" id="preview">
                </div>
                
                <!-- Upload button and instructions -->
                <div class="upload-instructions">
                    <button type="button" class="upload-button" onclick="document.getElementById('profileImageInput').click()">
                        <i class="fas fa-camera"></i> Change Profile Picture
                    </button>
                    
                    <input type="file" id="profileImageInput" name="profile_image" 
                           accept="image/*" onchange="previewImage(this)" style="display: none;">
                    
                    <ul>
                        <li><i class="fas fa-info-circle"></i> Click the button above to select a new photo</li>
                        <li><i class="fas fa-file-image"></i> Supports: JPG, PNG, GIF (Max 5MB)</li>
                        <li><i class="fas fa-eye"></i> Preview will appear automatically</li>
                    </ul>
                    
                    <div class="current-image-info" id="fileNameDisplay">
                        No file selected
                    </div>
                </div>
            </div>

            <!-- FORM -->
            <div class="form-group">
                <label>Full Name</label>
                <input class="form-control" name="name" value="<?= htmlspecialchars($profile['customer_Name']) ?>" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input class="form-control" type="email" name="email" value="<?= htmlspecialchars($profile['customer_Email']) ?>" required>
            </div>

            <div class="form-group">
                <label>Phone</label>
                <input class="form-control" name="phone" value="<?= htmlspecialchars($profile['customer_Phone']) ?>">
            </div>

            <div class="form-group">
                <label>Address</label>
                <input class="form-control" name="address" value="<?= htmlspecialchars($profile['customer_address'] ?? '') ?>">
            </div>

            <div style="display:flex;gap:10px">
                <a href="dashboard.php" class="btn btn-back">← Back</a>
                <button class="btn btn-primary">Save Changes</button>
            </div>

        </form>
    </div>

</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('preview');
    const fileNameDisplay = document.getElementById('fileNameDisplay');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Show file info
        const fileSize = (file.size / 1024).toFixed(1);
        fileNameDisplay.innerHTML = `
            <strong>Selected:</strong> ${file.name}<br>
            <strong>Size:</strong> ${fileSize} KB<br>
            <strong>Type:</strong> ${file.type}
        `;
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        }
        reader.readAsDataURL(file);
        
        // Validate file size
        if (file.size > 5 * 1024 * 1024) {
            alert('Image must be under 5MB. Please select a smaller file.');
            input.value = '';
            fileNameDisplay.innerHTML = 'File too large. Please select a smaller image.';
            return;
        }
    }
}

// Make the whole image clickable for upload
document.addEventListener('DOMContentLoaded', function() {
    const previewImg = document.getElementById('preview');
    if (previewImg) {
        previewImg.style.cursor = 'pointer';
        previewImg.addEventListener('click', function() {
            document.getElementById('profileImageInput').click();
        });
    }
});
</script>

<?php include("../includes/footer.php"); ?>