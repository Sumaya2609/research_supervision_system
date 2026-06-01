<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../db.php";

/* ================= LOGIN CHECK ================= */

if(
    !isset($_SESSION['user_id']) ||
    $_SESSION['role'] != 'faculty'
){
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
users.name,
students.student_id

FROM applications

JOIN students
ON applications.student_id = students.student_id

JOIN users
ON students.user_id = users.id

JOIN topics
ON applications.topic_id = topics.topic_id

WHERE topics.faculty_id='$faculty_id'
AND applications.status='approved'

ORDER BY users.name ASC
");

/* ================= SUCCESS ================= */

$success = "";

/* ================= SUBMIT REPORT ================= */

if(isset($_POST['submit'])){

    $student_id = intval($_POST['student_id']);

    $feedback = trim($_POST['feedback']);

    $rating = intval($_POST['rating']);

    if(
        empty($student_id) ||
        empty($feedback) ||
        empty($rating)
    ){

        $success = "All fields are required!";

    }else{

        $insert = $conn->query("
        INSERT INTO reports
        (
            student_id,
            faculty_id,
            feedback,
            rating,
            report_date
        )
        VALUES
        (
            '$student_id',
            '$faculty_id',
            '$feedback',
            '$rating',
            NOW()
        )
        ");

        if($insert){

            /* get student user id */

            $stuUser = $conn->query("
            SELECT user_id
            FROM students
            WHERE student_id='$student_id'
            ")->fetch_assoc();

            if($stuUser){

                $student_user_id = $stuUser['user_id'];

                /* notify student */

                $conn->query("
                INSERT INTO notifications
                (
                    user_id,
                    message,
                    type
                )
                VALUES
                (
                    '$student_user_id',
                    'New performance report received',
                    'report'
                )
                ");
            }

            $success = "Report submitted successfully!";

        }else{

            $success = "Database Error: " . $conn->error;
        }
    }
}

/* ================= REPORT HISTORY ================= */

$reports = $conn->query("
SELECT
reports.*,
users.name

FROM reports

JOIN students
ON reports.student_id = students.student_id

JOIN users
ON students.user_id = users.id

WHERE reports.faculty_id='$faculty_id'

ORDER BY reports.report_date DESC
");

?>

<style>

.report-card{
    background:white;
    border-radius:24px;
    padding:30px;
    box-shadow:0 5px 20px rgba(0,0,0,0.05);
    margin-bottom:30px;
}

.report-card h2{
    color:#2563eb;
    margin-bottom:25px;
}

.success{
    background:#dcfce7;
    color:#166534;
    padding:14px;
    border-radius:12px;
    margin-bottom:20px;
    font-weight:600;
}

.form-group{
    margin-bottom:22px;
}

.form-group label{
    display:block;
    margin-bottom:8px;
    font-weight:600;
    color:#334155;
}

.form-group input,
.form-group select,
.form-group textarea{
    width:100%;
    padding:14px;
    border:1px solid #cbd5e1;
    border-radius:14px;
    background:#f8fafc;
    outline:none;
    transition:0.3s;
}

.form-group textarea{
    min-height:140px;
    resize:vertical;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus{
    border-color:#2563eb;
    background:white;
    box-shadow:0 0 0 3px rgba(37,99,235,0.1);
}

.submit-btn{
    background:#2563eb;
    color:white;
    border:none;
    padding:15px 22px;
    border-radius:14px;
    cursor:pointer;
    font-weight:bold;
    transition:0.3s;
}

.submit-btn:hover{
    background:#1d4ed8;
    transform:translateY(-2px);
}

.table-container{
    overflow-x:auto;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#2563eb;
    color:white;
    padding:14px;
    text-align:left;
}

td{
    padding:14px;
    border-bottom:1px solid #e2e8f0;
    vertical-align:top;
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
    text-align:center;
    color:#94a3b8;
    padding:30px;
}

small{
    color:#64748b;
}

</style>

<?php if($success != ""){ ?>

<div class="success">

<i class="fa-solid fa-circle-check"></i>

<?= $success; ?>

</div>

<?php } ?>

<div class="report-card">

<h2>

<i class="fa-solid fa-clipboard"></i>

Generate Student Report

</h2>

<form method="POST">

<div class="form-group">

<label>Select Student</label>

<select name="student_id" required>

<option value="">
Choose Student
</option>

<?php while($s = $students->fetch_assoc()){ ?>

<option value="<?= $s['student_id']; ?>">

<?= htmlspecialchars($s['name']); ?>

</option>

<?php } ?>

</select>

</div>

<div class="form-group">

<label>Feedback</label>

<textarea
name="feedback"
placeholder="Write performance feedback..."
required></textarea>

</div>

<div class="form-group">

<label>Performance Rating</label>

<select name="rating" required>

<option value="5">
Excellent (5)
</option>

<option value="4">
Good (4)
</option>

<option value="3">
Average (3)
</option>

<option value="2">
Poor (2)
</option>

</select>

</div>

<button class="submit-btn" name="submit">

<i class="fa-solid fa-paper-plane"></i>

Submit Report

</button>

</form>

</div>

<div class="report-card">

<h2>

<i class="fa-solid fa-clock-rotate-left"></i>

Report History

</h2>

<div class="table-container">

<?php if($reports->num_rows > 0){ ?>

<table>

<tr>
<th>Student</th>
<th>Feedback</th>
<th>Rating</th>
<th>Date</th>
</tr>

<?php while($row = $reports->fetch_assoc()){

$rating = intval($row['rating']);

if($rating >= 5){
    $class = "excellent";
}
elseif($rating >= 4){
    $class = "good";
}
elseif($rating >= 3){
    $class = "average";
}
else{
    $class = "poor";
}

?>

<tr>

<td>

<strong>

<?= htmlspecialchars($row['name']); ?>

</strong>

</td>

<td>

<?= nl2br(htmlspecialchars($row['feedback'])); ?>

</td>

<td>

<span class="rating <?= $class; ?>">

<?= $row['rating']; ?>/5

</span>

</td>

<td>

<?= $row['report_date']; ?>

</td>

</tr>

<?php } ?>

</table>

<?php } else { ?>

<div class="empty">

<h3>No Reports Generated Yet</h3>

<p>
Generated reports will appear here.
</p>

</div>

<?php } ?>

</div>

</div>