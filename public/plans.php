<?php
include("../config/db.php");
include("../includes/header.php");
?>

<section class="plans-page">
    <h1 class="plans-title">Membership Plans</h1>
    <p class="plans-subtitle">
        Flexible options designed to suit every body and every lifestyle
    </p>

    <div class="plans-grid">
        <?php
        $plans = mysqli_query($conn,"SELECT * FROM MembershipPlan");

        while($p = mysqli_fetch_assoc($plans)){

            // highlight Premium as popular
            $popular = ($p['plan_Name'] == 'Premium') ? 'popular' : '';

            echo "<div class='plan-card $popular'>";
            echo "<h3>{$p['plan_Name']}</h3>";
            echo "<p class='plan-desc'>{$p['plan_Description']}</p>";
            echo "<div class='plan-price'>RM {$p['plan_Price']}</div>";

            // Included classes
            $classes = mysqli_query($conn,"
                SELECT Class.class_Name
                FROM PlanClass
                JOIN Class ON PlanClass.class_ID = Class.class_ID
                WHERE PlanClass.plan_ID = {$p['plan_ID']}
            ");

            if(mysqli_num_rows($classes) > 0){
                echo "<ul class='plan-features'>";
                while($c = mysqli_fetch_assoc($classes)){
                    echo "<li>{$c['class_Name']}</li>";
                }
                echo "</ul>";
            } else {
                echo "<p class='plan-note'>Pay per class</p>";
            }

            echo "<a href='register.php' class='plan-btn'>Join</a>";
            echo "</div>";
        }
        ?>
    </div>
</section>


<?php include("../includes/footer.php"); ?>
