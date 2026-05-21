<?php
session_start();
include "../db.php";

if($_SESSION['role'] != 'student'){
    header("Location: ../login.php");
    exit();
}

$sql = "SELECT topics.*, users.name AS faculty_name
        FROM topics
        LEFT JOIN faculty ON topics.faculty_id = faculty.faculty_id
        LEFT JOIN users ON faculty.user_id = users.id
        WHERE topics.status='approved'
        ORDER BY topics.topic_id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Browse Topics</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

/* GLOBAL */
body{
    margin:0;
    font-family:'Segoe UI', sans-serif;
    background:linear-gradient(135deg,#0f2027,#203a43,#2c5364);
    color:white;
}

/* SIDEBAR */
.sidebar{
    width:240px;
    height:100vh;
    background:rgba(40,90,72,0.95);
    position:fixed;
    padding-top:20px;
    backdrop-filter:blur(10px);
}

.sidebar h2{
    text-align:center;
    color:#B0E4CC;
    margin-bottom:25px;
}

.sidebar a{
    display:block;
    color:#B0E4CC;
    padding:14px 20px;
    text-decoration:none;
    transition:0.3s;
}

.sidebar a:hover{
    background:#408A71;
    padding-left:25px;
}

/* MAIN */
.main{
    margin-left:240px;
    padding:30px;
}

/* HEADER */
.header{
    margin-bottom:25px;
}

.header h2{
    font-size:30px;
    margin:0;
}

.header p{
    color:#ddd;
    margin-top:5px;
}

/* GRID */
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
    gap:25px;
}

/* CARD */
.card{
    background:white;
    color:#091413;
    border-radius:15px;
    padding:20px;
    box-shadow:0 10px 25px rgba(0,0,0,0.3);
    transition:0.3s;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
}

.card:hover{
    transform:translateY(-6px);
    box-shadow:0 20px 40px rgba(0,0,0,0.4);
}

/* CARD HEADER */
.card-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.card-header h3{
    margin:0;
    color:#285A48;
}

/* BADGE */
.badge{
    background:#27ae60;
    color:white;
    padding:5px 10px;
    border-radius:20px;
    font-size:11px;
}

/* FACULTY */
.faculty{
    display:flex;
    align-items:center;
    margin:10px 0;
}

.avatar{
    width:35px;
    height:35px;
    border-radius:50%;
    background:#285A48;
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    margin-right:10px;
}

/* DESCRIPTION */
.desc{
    font-size:14px;
    color:#333;
    margin:10px 0;
    line-height:1.5;
}

/* SKILLS */
.skills{
    margin-top:10px;
}

.skill-tag{
    display:inline-block;
    background:#285A48;
    color:white;
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
    margin:3px;
}

/* FOOTER */
.card-footer{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-top:15px;
}

/* CTA TEXT */
.apply-text{
    font-size:13px;
    color:#555;
}

/* BUTTON */
.btn{
    background:linear-gradient(135deg,#285A48,#40916c);
    color:white;
    padding:10px 16px;
    border-radius:8px;
    text-decoration:none;
    font-weight:bold;
    transition:0.3s;
    box-shadow:0 5px 15px rgba(0,0,0,0.3);
}

.btn:hover{
    transform:translateY(-2px) scale(1.05);
    box-shadow:0 10px 25px rgba(0,0,0,0.4);
}

</style>

</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">

<h2><i class="fa fa-user-graduate"></i> Student</h2>

<a href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a>
<a href="profile.php"><i class="fa fa-user"></i> Profile</a>
<a href="browse_topics.php"><i class="fa fa-search"></i> Browse Topics</a>
<a href="my_applications.php"><i class="fa fa-file"></i> My Applications</a>

</div>

<!-- MAIN -->
<div class="main">

<div class="header">
<h2>Explore Research Topics</h2>
<p>Find topics that match your passion and skills</p>
</div>

<div class="grid">

<?php
if($result->num_rows > 0){
while($row=$result->fetch_assoc()){

$skills = explode(",", $row['skills_required']);
?>

<div class="card">

<!-- HEADER -->
<div class="card-header">
    <h3><?php echo $row['title']; ?></h3>
    <span class="badge">Available</span>
</div>

<!-- BODY -->
<div>

<div class="faculty">
<div class="avatar">
<?php echo strtoupper(substr($row['faculty_name'],0,1)); ?>
</div>
<div><?php echo $row['faculty_name']; ?></div>
</div>

<div class="desc">
<?php echo $row['description']; ?>
</div>

<div class="skills">
<?php
foreach($skills as $skill){
echo "<span class='skill-tag'>".trim($skill)."</span>";
}
?>
</div>

</div>

<!-- FOOTER -->
<div class="card-footer">

<div class="apply-text">
Want to apply? Click here →
</div>

<a class="btn"
href="apply.php?topic_id=<?php echo $row['topic_id']; ?>">
<i class="fa fa-paper-plane"></i> Apply
</a>

</div>

</div>

<?php }} else { ?>

<p>No topics available.</p>

<?php } ?>

</div>

</div>

</body>
</html>