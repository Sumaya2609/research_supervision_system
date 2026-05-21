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

function countApps($conn,$student_id){

    $q = $conn->query("
    SELECT COUNT(*) as total
    FROM applications
    WHERE student_id='$student_id'
    ");

    return $q->fetch_assoc()['total'];
}

function avgRating($conn,$student_id){

    $q = $conn->query("
    SELECT AVG(
        CASE
            WHEN rating='Excellent' THEN 5
            WHEN rating='Good' THEN 4
            WHEN rating='Average' THEN 3
            WHEN rating='Poor' THEN 2
            ELSE 1
        END
    ) as avg_rating
    FROM reports
    WHERE student_id='$student_id'
    ");

    return round($q->fetch_assoc()['avg_rating'],1) ?: 0;
}

/* ================= SCORE ================= */

function progressScore($apps,$submitted,$approved,$rating){

    return round(
        ($apps * 10) +
        ($submitted * 15) +
        ($approved * 20) +
        ($rating * 15)
    );
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

.progress-bar{
    width:100%;
    height:10px;
    background:#e2e8f0;
    border-radius:20px;
    overflow:hidden;
    margin-top:18px;
}

.progress-fill{
    height:100%;
    border-radius:20px;
}

.strong{
    background:#16a34a;
}

.medium{
    background:#f59e0b;
}

.weak{
    background:#dc2626;
}

.badge{
    display:inline-block;
    margin-top:18px;
    padding:8px 16px;
    border-radius:30px;
    font-size:13px;
    font-weight:bold;
    color:white;
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

$apps = countApps($conn,$student_id);

$submitted = countTasks(
    $conn,
    $student_id,
    'submitted'
);

$approved = countTasks(
    $conn,
    $student_id,
    'approved'
);

$rating = avgRating(
    $conn,
    $student_id
);

$score = progressScore(
    $apps,
    $submitted,
    $approved,
    $rating
);

/* ================= STATUS ================= */

if($score >= 120){

    $status = "Strong";
    $class = "strong";
    $width = "100%";

}
elseif($score >= 70){

    $status = "Average";
    $class = "medium";
    $width = "70%";

}
else{

    $status = "Weak";
    $class = "weak";
    $width = "40%";
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

<span>Applications</span>

<strong><?= $apps; ?></strong>

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

<div class="stat">

<span>Performance Score</span>

<strong><?= $score; ?></strong>

</div>

</div>

<div class="progress-bar">

<div
class="progress-fill <?= $class; ?>"
style="width:<?= $width; ?>;">
</div>

</div>

<span class="badge <?= $class; ?>">

<?= $status; ?> Performance

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