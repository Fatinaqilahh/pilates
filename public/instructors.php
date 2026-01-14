<?php
include("../config/db.php");
include("../includes/header.php");

$extra = [
    1 => [
        "photo" => "/pilates/assets/instructors/alicia.jpg",
        "role"  => "Senior Pilates Instructor",
        "bio"   => "Alicia specialises in reformer Pilates and posture correction with over 8 years of professional experience.",
        "tags"  => ["Reformer", "Posture", "Core"]
    ],
    2 => [
        "photo" => "/pilates/assets/instructors/jamilla.jpg",
        "role"  => "Mat Pilates Specialist",
        "bio"   => "Jamilla focuses on flexibility, recovery, and beginner-friendly Pilates sessions.",
        "tags"  => ["Mat Pilates", "Stretch", "Beginner"]
    ],
    5 => [
        "photo" => "/pilates/assets/instructors/hazy.jpg",
        "role"  => "Strength & Conditioning Coach",
        "bio"   => "Hazy combines Pilates with strength training to build endurance and muscle balance.",
        "tags"  => ["Strength", "Fitness", "Conditioning"]
    ],
    6 => [
        "photo" => "/pilates/assets/instructors/amelia.jpg",
        "role"  => "Rehabilitation Instructor",
        "bio"   => "Amelia specialises in rehabilitation Pilates, mobility improvement, and injury recovery.",
        "tags"  => ["Rehab", "Mobility", "Therapy"]
    ],
    7 => [
        "photo" => "/pilates/assets/instructors/sarah.jpg",
        "role"  => "Mindful Pilates Coach",
        "bio"   => "Sarah blends mindful movement with Pilates to enhance flexibility, balance, and relaxation.",
        "tags"  => ["Mindfulness", "Flexibility", "Balance"]
    ],
    8 => [
        "photo" => "/pilates/assets/instructors/mary.jpg",
        "role"  => "Dance & Pilates Instructor",
        "bio"   => "Mary brings elegance and flow into Pilates with her dance-based training approach.",
        "tags"  => ["Dance", "Flow", "Flexibility"]
    ]
];

$instructors = mysqli_query($conn, "SELECT * FROM instructor ORDER BY instructor_ID ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Our Expert Team | MyPilates</title>
<link rel="stylesheet" href="/pilates/assets/style.css">

<style>
/* ===== INSTRUCTOR PAGE ===== */
.instructor-section {
    max-width: 1200px;
    margin: 100px auto;
    padding: 0 20px;
}

.instructor-header {
    text-align: center;
    margin-bottom: 60px;
}

.instructor-header h1 {
    font-size: 36px;
    color: #1f2937;
}

.instructor-header p {
    color: #6b7280;
    font-size: 16px;
}

/* GRID */
.instructor-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
}

/* CARD */
.instructor-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 15px 40px rgba(0,0,0,.08);
    transition: all .3s ease;
}

.instructor-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 25px 55px rgba(0,0,0,.12);
}

/* IMAGE */
.instructor-photo {
    width: 100%;
    height: 220px;
    object-fit: cover;
}

/* CONTENT */
.instructor-content {
    padding: 25px;
}

.instructor-name {
    font-size: 18px;
    font-weight: 700;
}

.instructor-role {
    font-size: 14px;
    font-weight: 600;
    color: #0b4d2b;
    margin: 6px 0 12px;
}

.instructor-bio {
    font-size: 14px;
    color: #555;
    line-height: 1.6;
    margin-bottom: 15px;
}

/* TAGS */
.instructor-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.instructor-tag {
    background: #e6f3ec;
    color: #0b4d2b;
    padding: 5px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 500;
}
</style>
</head>

<body>


<section class="instructor-section">

    <div class="instructor-header">
        <h1>Our Expert Team</h1>
        <p>Meet the certified professionals guiding your Pilates journey</p>
    </div>

    <div class="instructor-grid">

        <?php while ($i = mysqli_fetch_assoc($instructors)):
            $id = $i['instructor_ID'];
            $info = $extra[$id] ?? null;
        ?>
        <div class="instructor-card">

            <img src="<?= $info['photo'] ?? '/pilates/assets/instructors/default.jpg' ?>"
                 class="instructor-photo"
                 onerror="this.src='/pilates/assets/instructors/default.jpg'">

            <div class="instructor-content">
                <div class="instructor-name"><?= htmlspecialchars($i['instructor_Name']) ?></div>
                <div class="instructor-role"><?= $info['role'] ?? 'Pilates Instructor' ?></div>

                <div class="instructor-bio">
                    <?= $info['bio'] ?? 'Professional Pilates instructor.' ?>
                </div>

                <div class="instructor-tags">
                    <?php if (!empty($info['tags'])):
                        foreach ($info['tags'] as $tag): ?>
                            <span class="instructor-tag"><?= $tag ?></span>
                    <?php endforeach; endif; ?>
                </div>
            </div>

        </div>
        <?php endwhile; ?>

    </div>
</section>

</body>
</html>
<?php include("../includes/footer.php"); ?>
