<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "../db.php";

/* LOGIN CHECK */
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'faculty'){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* get faculty_id */
$fac = $conn->query("
SELECT faculty_id FROM faculty WHERE user_id='$user_id'
")->fetch_assoc();

$faculty_id = $fac['faculty_id'];

/* ================= FETCH MATCHED APPLICATIONS ================= */
$sql = "
SELECT 
applications.*,
users.name AS student_name,
users.email,
students.skills,
students.cgpa,
topics.title

FROM applications

JOIN students 
ON applications.student_id = students.student_id

JOIN users 
ON students.user_id = users.id

JOIN topics 
ON applications.topic_id = topics.topic_id

WHERE topics.faculty_id='$faculty_id'
AND applications.status='approved'

ORDER BY applications.match_score DESC
";

$res = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Matched Applications</title>

<style>
body{
    background:#091413;
    color:white;
    font-family:Segoe UI;
}

.container{
    width:90%;
    margin:auto;
    margin-top:30px;
}

.card{
    background:white;
    color:black;
    padding:20px;
    margin-bottom:15px;
    border-radius:10px;
}

.score{
    color:#2d6a4f;
    font-weight:bold;
}

</style>
</head>

<body>

<div class="container">

<h2>🎯 Auto Matched Students</h2>

<?php if($res->num_rows > 0){ ?>

<?php while($row = $res->fetch_assoc()){ ?>

<div class="card">

<h3><?php echo $row['student_name']; ?></h3>

<p><b>Email:</b> <?php echo $row['email']; ?></p>

<p><b>CGPA:</b> <?php echo $row['cgpa']; ?></p>

<p><b>Skills:</b> <?php echo $row['skills']; ?></p>

<p><b>Topic:</b> <?php echo $row['title']; ?></p>

<p class="score">
Match Score: <?php echo round($row['match_score']*100,2); ?>%
</p>

<p><b>Status:</b> <?php echo ucfirst($row['status']); ?></p>

</div>

<?php } ?>

<?php } else { ?>

<p>No matched students yet.</p>

<?php } ?>

</div>

</body>
</html>