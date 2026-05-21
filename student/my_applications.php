<?php
session_start();
include "../db.php";

/* LOGIN CHECK */
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student'){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* get student_id */
$stu = $conn->query("
SELECT student_id FROM students WHERE user_id='$user_id'
")->fetch_assoc();

$student_id = $stu['student_id'];

/* ================= FETCH APPLICATIONS ================= */
$sql = "
SELECT 
applications.*,
topics.title,
users.name AS faculty_name

FROM applications

JOIN topics 
ON applications.topic_id = topics.topic_id

JOIN faculty 
ON topics.faculty_id = faculty.faculty_id

JOIN users 
ON faculty.user_id = users.id

WHERE applications.student_id='$student_id'

ORDER BY applications.application_id DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>

<title>My Applications</title>

<style>

body{
    margin:0;
    font-family:'Segoe UI';
    background:#0f2027;
    color:white;
}

.container{
    width:90%;
    max-width:1100px;
    margin:50px auto;
}

.card{
    background:white;
    color:#081c15;
    padding:20px;
    border-radius:12px;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#2d6a4f;
    color:white;
    padding:12px;
}

td{
    padding:12px;
    border-bottom:1px solid #ddd;
}

/* STATUS */
.status{
    padding:6px 10px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
}

.pending{background:#fff3cd;color:#f39c12;}
.approved{background:#d4edda;color:#27ae60;}
.rejected{background:#f8d7da;color:#e74c3c;}

.score{
    font-weight:bold;
    color:#2d6a4f;
}

</style>

</head>

<body>

<div class="container">

<h2>📄 My Applications</h2>

<div class="card">

<?php if($result->num_rows > 0){ ?>

<table>

<tr>
<th>Topic</th>
<th>Faculty</th>
<th>Match %</th>
<th>Status</th>
<th>Date</th>
</tr>

<?php while($row = $result->fetch_assoc()){ ?>

<tr>

<td><?php echo $row['title']; ?></td>

<td><?php echo $row['faculty_name']; ?></td>

<td class="score">
<?php echo round($row['match_score']*100,2); ?>%
</td>

<td>
<span class="status <?php echo strtolower($row['status']); ?>">
<?php echo ucfirst($row['status']); ?>
</span>
</td>

<td>
<?php echo $row['created_at'] ?? 'N/A'; ?>
</td>

</tr>

<?php } ?>

</table>

<?php } else { ?>

<p>No applications found.</p>

<?php } ?>

</div>

</div>

</body>
</html>