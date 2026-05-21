<?php
session_start();
include "../db.php";

if($_SESSION['role'] != 'student'){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* FIX: get student_id */
$stu = $conn->query("
SELECT student_id FROM students WHERE user_id='$user_id'
")->fetch_assoc();

$student_id = $stu['student_id'];

/* fetch reports */
$res = $conn->query("
SELECT reports.*, users.name AS faculty_name
FROM reports
JOIN faculty ON faculty.faculty_id = reports.faculty_id
JOIN users ON faculty.user_id = users.id
WHERE reports.student_id='$student_id'
ORDER BY reports.report_date DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>My Reports</title>

<style>
body{
    background:#0f1f1c;
    color:white;
    font-family:'Segoe UI';
}

.card{
    width:80%;
    margin:20px auto;
    background:white;
    color:black;
    padding:20px;
    border-radius:10px;
}
</style>
</head>

<body>

<h2 style="text-align:center;">My Reports</h2>

<?php if($res->num_rows == 0){ ?>
<p style="text-align:center;">No reports yet</p>
<?php } ?>

<?php while($row=$res->fetch_assoc()){ ?>

<div class="card">

<h3><?php echo $row['faculty_name']; ?></h3>

<p><?php echo $row['feedback']; ?></p>

<p><b>Rating:</b> <?php echo $row['rating']; ?></p>

<p><small><?php echo $row['report_date']; ?></small></p>

</div>

<?php } ?>

</body>
</html>