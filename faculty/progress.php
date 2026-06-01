<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../db.php";

/* ================= LOGIN CHECK ================= */

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'faculty'){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ================= FACULTY ================= */

$fac = $conn->query("
SELECT faculty_id
FROM faculty
WHERE user_id='$user_id'
")->fetch_assoc();

$faculty_id = $fac['faculty_id'];

/* ================= STUDENTS ================= */

$students = $conn->query("
SELECT DISTINCT
users.id,
users.name,
students.student_id

FROM applications a

JOIN students
ON a.student_id = students.student_id

JOIN users
ON students.user_id = users.id

JOIN topics t
ON a.topic_id = t.topic_id

WHERE t.faculty_id='$faculty_id'
AND a.status='approved'

ORDER BY users.name ASC
");

/* ================= HELPERS ================= */

function countTasks($conn,$student_id,$status){

    $q = $conn->query("
    SELECT COUNT(*) as total
    FROM tasks
    WHERE student_id='$student_id'
    AND status='$status'
    ");

    return $q->fetch_assoc()['total'];
}

?>

<style>

.progress-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
    gap:24px;
}

.progress-card{
    background:white;
    border-radius:24px;
    padding:28px;
    box-shadow:0 5px 20px rgba(0,0,0,0.05);
    transition:0.3s;
    position:relative;
    overflow:hidden;
}

.progress-card:hover{
    transform:translateY(-5px);
}

.progress-card::before{
    content:'';
    position:absolute;
    width:120px;
    height:120px;
    border-radius:50%;
    background:#dbeafe;
    top:-50px;
    right:-50px;
}

.student-header{
    display:flex;
    align-items:center;
    gap:14px;
    margin-bottom:20px;
}

.avatar{
    width:55px;
    height:55px;
    border-radius:50%;
    background:#2563eb;
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
    font-weight:bold;
}

.student-header h3{
    color:#1e293b;
    margin-bottom:4px;
}

.student-header p{
    color:#64748b;
    font-size:14px;
}

.stats{
    margin-top:20px;
}

.stat{
    display:flex;
    justify-content:space-between;
    margin-bottom:14px;
    color:#334155;
    font-size:15px;
}

.empty{
    background:white;
    padding:40px;
    border-radius:20px;
    text-align:center;
    color:#94a3b8;
    box-shadow:0 5px 20px rgba(0,0,0,0.05);
}

.empty i{
    font-size:60px;
    margin-bottom:15px;
}

.rating-badge{
    margin-top:18px;
    display:inline-block;
    background:#2563eb;
    color:white;
    padding:8px 16px;
    border-radius:30px;
    font-size:13px;
    font-weight:bold;
}

</style>

<div class="card">

<h2 style="margin-bottom:25px; color:#2563eb;">

<i class="fa-solid fa-chart-line"></i>

Student Progress Analytics

</h2>

<?php if($students->num_rows > 0){ ?>

<div class="progress-grid">

<?php while($row = $students->fetch_assoc()){

$student_id = $row['student_id'];

/* ================= TASK COUNTS ================= */

/* ================= TASK ANALYTICS ================= */

/* Total tasks ever assigned */

$assigned = $conn->query("
SELECT COUNT(*) AS total
FROM tasks
WHERE student_id='$student_id'
")->fetch_assoc()['total'];

/* Tasks that reached submitted stage */

$submitted = $conn->query("
SELECT COUNT(*) AS total
FROM tasks
WHERE student_id='$student_id'
AND status IN ('submitted','approved')
")->fetch_assoc()['total'];

/* Tasks approved */

$approved = $conn->query("
SELECT COUNT(*) AS total
FROM tasks
WHERE student_id='$student_id'
AND status='approved'
")->fetch_assoc()['total'];
/* ================= AVG RATING ================= */

/* ================= AVG RATING ================= */

$q = $conn->query("
SELECT AVG(rating) as avg_rating
FROM reports
WHERE student_id='$student_id'
");

$rating = round($q->fetch_assoc()['avg_rating'],1);

if(!$rating){
    $rating = 0;
}

?>

<div class="progress-card">

<div class="student-header">

<div class="avatar">

<?= strtoupper(substr($row['name'],0,1)); ?>

</div>

<div>

<h3>

<?= htmlspecialchars($row['name']); ?>

</h3>

<p>
Research Student
</p>

</div>

</div>

<div class="stats">

<div class="stat">

<span>Assigned Tasks</span>

<strong><?= $assigned; ?></strong>

</div>

<div class="stat">

<span>Submitted Tasks</span>

<strong><?= $submitted; ?></strong>

</div>

<div class="stat">

<span>Approved Tasks</span>

<strong><?= $approved; ?></strong>

</div>

<div class="stat">

<span>Average Rating</span>

<strong><?= $rating; ?>/5</strong>

</div>

</div>

<span class="rating-badge">

Performance Rating: <?= $rating; ?>/5

</span>

</div>

<?php } ?>

</div>

<?php } else { ?>

<div class="empty">

<i class="fa-solid fa-chart-column"></i>

<h3>No Student Analytics Available</h3>

<p>
Approved students will appear here.
</p>

</div>

<?php } ?>

</div>