<?php
include("../config/db.php");

if(!isset($_SESSION['customer_id'])){
    header("Location: ../auth/login.php");
    exit;
}

/* Fetch schedules with remaining slots */
$schedules = [];

$q = mysqli_query($conn,"
    SELECT cs.*, 
           c.class_Name, 
           i.instructor_Name,
           (cs.slots - COUNT(b.booking_ID)) AS remaining
    FROM classschedule cs
    JOIN class c ON cs.class_ID = c.class_ID
    JOIN instructor i ON cs.instructor_ID = i.instructor_ID
    LEFT JOIN booking b ON cs.schedule_ID = b.schedule_ID
    WHERE cs.schedule_Date >= CURDATE()
    GROUP BY cs.schedule_ID
");

while($row = mysqli_fetch_assoc($q)){
    if($row['remaining'] > 0){
        $schedules[$row['schedule_Date']][] = $row;
    }
}
?>

<?php include("../includes/header.php"); ?>

<section class="booking-6month">

<h1>Book a Class</h1>
<p class="subtitle">Select a date and time that works for you</p>

<div class="calendar-nav">
    <button onclick="prevMonth()">◀</button>
    <span id="monthLabel"></span>
    <button onclick="nextMonth()">▶</button>
</div>

<div id="calendar"></div>

<form method="POST" action="confirm_booking.php">
    <div id="slots" class="slots-box">
        <h3>Select a class</h3>
        <p class="subtitle">Choose a highlighted date</p>
    </div>

    <div class="booking-action">
        <button class="btn-dark">Proceed to Payment</button>
    </div>
</form>

</section>

<script>
const schedules = <?= json_encode($schedules) ?>;
let current = new Date();

function renderCalendar(){
    const year = current.getFullYear();
    const month = current.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDate = new Date(year, month + 1, 0).getDate();
    const startDay = firstDay.getDay() || 7;

    document.getElementById("monthLabel").innerText =
        firstDay.toLocaleString('default', { month: 'long', year: 'numeric' });

    let html = `
        <div class="calendar-grid">
            <div>Mon</div><div>Tue</div><div>Wed</div>
            <div>Thu</div><div>Fri</div><div>Sat</div><div>Sun</div>
    `;

    for(let i=1;i<startDay;i++) html += "<div></div>";

    for(let d=1; d<=lastDate; d++){
        const date = `${year}-${String(month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
        const hasClass = schedules[date];

        if(hasClass){
            html += `
            <div class="calendar-day active" onclick="showSlots('${date}')">
                ${d}
                <span>${hasClass.reduce((a,b)=>a+b.remaining,0)} slots</span>
            </div>`;
        } else {
            html += `<div class="calendar-day disabled">${d}</div>`;
        }
    }

    html += "</div>";
    document.getElementById("calendar").innerHTML = html;
}

function showSlots(date){
    const box = document.getElementById("slots");
    box.innerHTML = `<h3>${date}</h3>`;

    schedules[date].forEach(s=>{
        box.innerHTML += `
        <label class="slot">
            <input type="radio" name="schedule_id" value="${s.schedule_ID}" required>
            <div class="slot-info">
                <strong>${s.schedule_Time}</strong>
                <p>${s.class_Name}</p>
                <small>${s.instructor_Name} • ${s.remaining} slots left</small>
            </div>
        </label>`;
    });
}

function prevMonth(){ current.setMonth(current.getMonth()-1); renderCalendar(); }
function nextMonth(){ current.setMonth(current.getMonth()+1); renderCalendar(); }

renderCalendar();
</script>
