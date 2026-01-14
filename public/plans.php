<?php
include("../config/db.php");
include("../includes/header.php");

// Get all plans including Free
$plans = mysqli_query($conn, "SELECT * FROM membershipplan ORDER BY plan_Price ASC");
?>

<section class="plans-page">
    <div class="plans-hero">
        <h1 class="plans-title">Membership Plans</h1>
        <p class="plans-subtitle">
            Flexible options designed to suit every body and every lifestyle
        </p>
    </div>

    <div class="plans-grid" style="display: flex; justify-content: center; gap: 30px; max-width: 1350px; margin: 0 auto;">

        <?php while($p = mysqli_fetch_assoc($plans)): 
            // Map plan names to descriptions
            $descriptions = [
                'Free' => 'Get started with 1 free trial class.<br>Experience our studio and meet our instructors.',
                'Basic' => '4 classes per month. Includes Reformer & Mat Pilates. Suitable for beginners.',
                'Premium' => '8 classes per month. Includes Reformer & Mat Pilates. Suitable for intermediate.',
                'VIP' => '8 private 1-to-1 sessions per month with preferred instructor.'
            ];
            
            $plan_desc = isset($descriptions[$p['plan_Name']]) ? $descriptions[$p['plan_Name']] : $p['plan_Description'];
            
            // Check if it's Free plan to hide button
            $is_free_plan = ($p['plan_Name'] == 'Free');
        ?>

        <div class="plan-card <?= $p['plan_Name']=='Premium' ? 'featured' : '' ?>" 
             style="width: 300px; border: 1px solid #ddd; border-radius: 10px; padding: 25px; text-align: center; display: flex; flex-direction: column; height: auto;">

            <?php if(file_exists("../assets/" . strtolower($p['plan_Name']) . ".jpg")): ?>
                <img src="/pilates/assets/<?= strtolower($p['plan_Name']) ?>.jpg"
                     alt="<?= $p['plan_Name'] ?>"
                     style="width: 100%; height: 180px; object-fit: cover; border-radius: 8px; margin-bottom: 20px;">
            <?php else: ?>
                <div class="plan-image-placeholder" style="width: 100%; height: 180px; background: #f0f0f0; border-radius: 8px; margin-bottom: 20px;"></div>
            <?php endif; ?>

            <h3 style="margin: 10px 0; font-size: 1.5em; color: #333;"><?= $p['plan_Name'] ?></h3>
            <p class="plan-description" style="color: #666; line-height: 1.5; flex-grow: 1; margin-bottom: 20px; min-height: 80px;">
                <?= $plan_desc ?>
            </p>

            <div class="plan-price" style="font-size: 1.8em; font-weight: bold; color: #e74c3c; margin: 20px 0;">
                RM <?= $p['plan_Price'] ?>
            </div>

            <?php if($p['plan_Name']=='Premium'): ?>
                <span class="featured-tag" style="background: #e74c3c; color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.9em; margin-bottom: 15px; display: inline-block;">Most Popular</span>
            <?php endif; ?>

            <?php if(!$is_free_plan): ?>
            <div class="plan-btn-container" style="margin-top: auto;">
                <a href="/pilates/auth/login.php?plan_id=<?= $p['plan_ID'] ?>" 
                   class="plan-btn-join" 
                   style="display: block; background: #333; color: white; border: none; padding: 12px 30px; border-radius: 5px; cursor: pointer; width: 100%; font-size: 1em; transition: background 0.3s; text-decoration: none; text-align: center;">
                    Join Now
                </a>
            </div>
            <?php endif; ?>

        </div>

        <?php endwhile; ?>

    </div>
</section>

<?php include("../includes/footer.php"); ?>