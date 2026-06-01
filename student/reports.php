<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../db.php";

/* ================= LOGIN CHECK ================= */

if(
    !isset($_SESSION['user_id']) ||
    $_SESSION['role'] != 'student'
){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ================= GET STUDENT ================= */

$stu = $conn->query("
SELECT student_id
FROM students
WHERE user_id='$user_id'
")->fetch_assoc();

$student_id = $stu['student_id'] ?? 0;

/* ================= FETCH REPORTS ================= */

$reports = $conn->query("
SELECT
reports.*,
users.name AS faculty_name

FROM reports

JOIN faculty
ON reports.faculty_id = faculty.faculty_id

JOIN users
ON faculty.user_id = users.id

WHERE reports.student_id='$student_id'

ORDER BY reports.report_date DESC
");

?>

<style>

.table-container{
    overflow-x:auto;
}

table{
    width:100%;
    border-collapse:collapse;
    background:white;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 5px 20px rgba(0,0,0,0.05);
}

th{
    background:#2563eb;
    color:white;
    padding:16px;
    text-align:left;
    font-size:15px;
}

td{
    padding:16px;
    border-bottom:1px solid #e2e8f0;
    vertical-align:top;
    color:#334155;
}

tr:hover{
    background:#f8fafc;
}

.rating{
    padding:6px 12px;
    border-radius:30px;
    font-size:13px;
    font-weight:bold;
    color:white;
    display:inline-block;
}

.excellent{
    background:#16a34a;
}

.good{
    background:#2563eb;
}

.average{
    background:#f59e0b;
}

.poor{
    background:#dc2626;
}

.empty{
    background:white;
    border-radius:20px;
    padding:50px;
    text-align:center;
    color:#94a3b8;
    box-shadow:0 5px 20px rgba(0,0,0,0.05);
}

.empty i{
    font-size:60px;
    margin-bottom:20px;
    color:#cbd5e1;
}

</style>

<div class="card">

<h2 style="margin-bottom:25px;color:#2563eb;">

<i class="fa-solid fa-chart-line"></i>

My Reports

</h2>

<?php if($reports && $reports->num_rows > 0){ ?>

<div class="table-container">

<table>

<tr>
<th>Faculty</th>
<th>Feedback</th>
<th>Rating</th>
<th>Date</th>
</tr>

<?php while($row = $reports->fetch_assoc()){

if($row['rating'] >= 5){
    $class = "excellent";
}
elseif($row['rating'] >= 4){
    $class = "good";
}
elseif($row['rating'] >= 3){
    $class = "average";
}
else{
    $class = "poor";
}

?>

<tr>

<td>
<strong>
<?= htmlspecialchars($row['faculty_name']); ?>
</strong>
</td>

<td style="line-height:1.7;">
<?= nl2br(htmlspecialchars($row['feedback'])); ?>
</td>

<td>

<span class="rating <?= $class; ?>">

<?= htmlspecialchars($row['rating']); ?>/5

</span>

</td>

<td>

<?= $row['report_date']; ?>

</td>

</tr>

<?php } ?>

</table>

</div>

<?php } else { ?>

<div class="empty">

<i class="fa-solid fa-file-circle-xmark"></i>

<h3>No Reports Available</h3>

<p>
Your faculty feedback and ratings will appear here.
</p>

</div>

<?php } ?>

</div>
