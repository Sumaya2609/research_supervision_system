<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "../db.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'faculty'){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* faculty id */
$fac = $conn->query("
SELECT faculty_id FROM faculty WHERE user_id='$user_id'
")->fetch_assoc();

$faculty_id = $fac['faculty_id'];

/* students under this faculty */
$sql = "
SELECT DISTINCT 
users.id,
users.name,
users.email,
students.student_id,
students.cgpa

FROM applications a
JOIN students ON a.student_id = students.student_id
JOIN users ON students.user_id = users.id
JOIN topics t ON a.topic_id = t.topic_id

WHERE t.faculty_id='$faculty_id'
AND a.status='approved'
";

$result = $conn->query($sql);

/* count applications per student */
function getAppCount($conn,$student_id){
    $q = $conn->query("
    SELECT COUNT(*) as total 
    FROM applications 
    WHERE student_id='$student_id'
    ");
    return $q->fetch_assoc()['total'];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>My Students</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

body{
    margin:0;
    font-family:'Segoe UI';
    background:#0f1f1c;
    color:white;
}

.container{
    padding:20px;
}

.card{
    background:white;
    color:#081c15;
    padding:20px;
    border-radius:12px;
    margin-bottom:15px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 5px 15px rgba(0,0,0,0.3);
}

.left{
    display:flex;
    gap:15px;
    align-items:center;
}

.avatar{
    width:50px;
    height:50px;
    border-radius:50%;
    background:#2d6a4f;
    color:white;
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:18px;
    font-weight:bold;
}

.info h3{
    margin:0;
}

.info p{
    margin:2px 0;
    font-size:13px;
    color:#555;
}

.badge{
    background:#2d6a4f;
    color:white;
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
}

.stats{
    text-align:right;
}

.btn{
    background:#2d6a4f;
    color:white;
    padding:6px 10px;
    border-radius:6px;
    text-decoration:none;
    font-size:12px;
}

.btn:hover{
    background:#40916c;
}

</style>
</head>

<body>

<div class="container">

<h2>My Students</h2>

<?php while($row=$result->fetch_assoc()){ ?>

<div class="card">

<div class="left">

<div class="avatar">
<?php echo strtoupper(substr($row['name'],0,1)); ?>
</div>

<div class="info">
<h3><?php echo $row['name']; ?></h3>
<p><?php echo $row['email']; ?></p>
<p>CGPA: <?php echo $row['cgpa'] ?? '0'; ?></p>
</div>

</div>

<div class="stats">

<div class="badge">
Applications: <?php echo getAppCount($conn,$row['student_id']); ?>
</div>

<br><br>

<a class="btn" href="reports.php?student=<?php echo $row['id']; ?>">
View Report
</a>

</div>

</div>

<?php } ?>

</div>

</body>
</html>