<?php
session_start();
include "../db.php";

if($_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// counts
$students = $conn->query("SELECT * FROM students")->num_rows;
$faculty = $conn->query("SELECT * FROM faculty")->num_rows;
$topics = $conn->query("SELECT * FROM topics")->num_rows;
$applications = $conn->query("SELECT * FROM applications")->num_rows;
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Segoe UI',sans-serif;
    background:#0f172a;
    color:white;
}

/* SIDEBAR */

.sidebar{
    position:fixed;
    left:0;
    top:0;
    width:260px;
    height:100vh;
    background:#111827;
    border-right:1px solid rgba(255,255,255,0.08);
    padding-top:20px;
}

.logo{
    text-align:center;
    padding:20px;
    border-bottom:1px solid rgba(255,255,255,0.08);
}

.logo h2{
    color:white;
    font-size:24px;
}

.logo p{
    color:#9ca3af;
    margin-top:5px;
    font-size:13px;
}

.sidebar a{
    display:flex;
    align-items:center;
    gap:12px;
    padding:16px 22px;
    color:#d1d5db;
    text-decoration:none;
    transition:0.3s;
    font-size:15px;
}

.sidebar a:hover{
    background:#1f2937;
    color:white;
    padding-left:28px;
}

.active{
    background:#2563eb;
    color:white !important;
}

/* TOPBAR */

.topbar{
    margin-left:260px;
    height:75px;
    background:#111827;
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:0 30px;
    border-bottom:1px solid rgba(255,255,255,0.08);
}

.topbar h1{
    font-size:22px;
}

/* MAIN */

.main{
    margin-left:260px;
    padding:30px;
}

/* CARDS */

.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
    margin-bottom:30px;
}

.stat-card{
    background:#1e293b;
    border-radius:18px;
    padding:25px;
    transition:0.3s;
    border:1px solid rgba(255,255,255,0.05);
}

.stat-card:hover{
    transform:translateY(-5px);
    box-shadow:0 10px 25px rgba(0,0,0,0.3);
}

.stat-card i{
    font-size:30px;
    margin-bottom:15px;
    color:#60a5fa;
}

.stat-card h2{
    font-size:32px;
    margin-bottom:8px;
}

.stat-card p{
    color:#cbd5e1;
}

/* CONTENT CARD */

.card{
    background:#1e293b;
    border-radius:18px;
    padding:25px;
    margin-bottom:25px;
    border:1px solid rgba(255,255,255,0.05);
}

.card h3{
    margin-bottom:20px;
    font-size:22px;
}

/* TABLE */

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
    padding:15px;
    text-align:left;
    font-size:14px;
}

td{
    padding:15px;
    border-bottom:1px solid rgba(255,255,255,0.06);
    color:#e5e7eb;
}

tr:hover{
    background:rgba(255,255,255,0.03);
}

/* BUTTONS */

.btn{
    border:none;
    padding:9px 12px;
    border-radius:10px;
    cursor:pointer;
    color:white;
    font-size:14px;
    transition:0.2s;
}

.btn:hover{
    transform:scale(1.05);
}

.btn-view{
    background:#3b82f6;
}

.btn-delete{
    background:#ef4444;
}

.btn-approve{
    background:#10b981;
}

.btn-reject{
    background:#f59e0b;
}

/* DETAILS */

.details p{
    margin-bottom:15px;
    font-size:16px;
    color:#e5e7eb;
}

.details b{
    color:white;
}

/* MOBILE */

@media(max-width:768px){

    .sidebar{
        width:80px;
    }

    .sidebar a span,
    .logo p,
    .logo h2{
        display:none;
    }

    .sidebar a{
        justify-content:center;
    }

    .topbar{
        margin-left:80px;
    }

    .main{
        margin-left:80px;
    }
}

</style>

</head>

<body>

<!-- SIDEBAR -->

<div class="sidebar">

<div class="logo">
<h2>ADMIN</h2>
<p>Dashboard Panel</p>
</div>

<a class="<?php if($page=='dashboard') echo 'active'; ?>"
href="?page=dashboard">
<i class="fa fa-home"></i>
<span>Dashboard</span>
</a>

<a class="<?php if($page=='students') echo 'active'; ?>"
href="?page=students">
<i class="fa fa-user-graduate"></i>
<span>Students</span>
</a>

<a class="<?php if($page=='faculty') echo 'active'; ?>"
href="?page=faculty">
<i class="fa fa-chalkboard-teacher"></i>
<span>Faculty</span>
</a>

<a class="<?php if($page=='topics') echo 'active'; ?>"
href="?page=topics">
<i class="fa fa-book"></i>
<span>Topics</span>
</a>

<a href="../logout.php">
<i class="fa fa-sign-out-alt"></i>
<span>Logout</span>
</a>

</div>

<!-- TOPBAR -->

<div class="topbar">
<h1>Research Supervision System</h1>

<div>
<i class="fa fa-user-shield"></i> Admin Panel
</div>
</div>

<!-- MAIN -->

<div class="main">

<?php if($page == 'dashboard'){ ?>

<div class="card">
<h3>Welcome Admin 👋</h3>

<p style="color:#cbd5e1; line-height:1.8;">
Manage students, faculty, research topics and applications
through the admin dashboard.
</p>

</div>

<div class="cards">

<div class="stat-card">
<i class="fa fa-user-graduate"></i>
<h2><?php echo $students; ?></h2>
<p>Total Students</p>
</div>

<div class="stat-card">
<i class="fa fa-chalkboard-teacher"></i>
<h2><?php echo $faculty; ?></h2>
<p>Total Faculty</p>
</div>

<div class="stat-card">
<i class="fa fa-book"></i>
<h2><?php echo $topics; ?></h2>
<p>Total Topics</p>
</div>

<div class="stat-card">
<i class="fa fa-file"></i>
<h2><?php echo $applications; ?></h2>
<p>Applications</p>
</div>

</div>



<?php } ?>

<!-- STUDENTS -->

<?php if($page == 'students'){ 

if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $conn->query("DELETE FROM users WHERE id='$id'");
}

$sql = "SELECT users.id, users.name, users.email, students.cgpa
        FROM users
        JOIN students ON users.id = students.user_id";

$res = $conn->query($sql);
?>

<div class="card">

<h3><i class="fa fa-user-graduate"></i> Students List</h3>

<div class="table-container">

<table>

<tr>
<th>Name</th>
<th>Email</th>
<th>CGPA</th>
<th>Action</th>
</tr>

<?php while($row = $res->fetch_assoc()){ ?>

<tr>

<td><?php echo $row['name']; ?></td>

<td><?php echo $row['email']; ?></td>

<td><?php echo $row['cgpa']; ?></td>

<td>

<a href="?page=view_student&id=<?php echo $row['id']; ?>">
<button class="btn btn-view">
<i class="fa fa-eye"></i>
</button>
</a>

<a href="?page=students&delete=<?php echo $row['id']; ?>">
<button class="btn btn-delete">
<i class="fa fa-trash"></i>
</button>
</a>

</td>

</tr>

<?php } ?>

</table>

</div>

</div>

<?php } ?>

<!-- VIEW STUDENT -->

<?php if($page == 'view_student'){ 

$id = $_GET['id'];

$data = $conn->query("
SELECT users.*, students.*
FROM users
JOIN students ON users.id = students.user_id
WHERE users.id='$id'
")->fetch_assoc();

?>

<div class="card">

<h3><i class="fa fa-user"></i> Student Details</h3>

<div class="details">

<p><b>Name:</b> <?php echo $data['name']; ?></p>

<p><b>Email:</b> <?php echo $data['email']; ?></p>

<p><b>CGPA:</b> <?php echo $data['cgpa']; ?></p>

<p><b>Skills:</b> <?php echo $data['skills']; ?></p>

<p><b>Interests:</b> <?php echo $data['interests']; ?></p>

</div>

</div>

<?php } ?>

<!-- FACULTY -->

<?php if($page == 'faculty'){ 

$sql = "SELECT users.name, users.email, faculty.department
        FROM users
        JOIN faculty ON users.id = faculty.user_id";

$res = $conn->query($sql);

?>

<div class="card">

<h3><i class="fa fa-chalkboard-teacher"></i> Faculty List</h3>

<div class="table-container">

<table>

<tr>
<th>Name</th>
<th>Email</th>
<th>Department</th>
</tr>

<?php while($row = $res->fetch_assoc()){ ?>

<tr>

<td><?php echo $row['name']; ?></td>

<td><?php echo $row['email']; ?></td>

<td><?php echo $row['department']; ?></td>

</tr>

<?php } ?>

</table>

</div>

</div>

<?php } ?>

<!-- TOPICS -->

<?php if($page == 'topics'){ 

if(isset($_GET['approve'])){
    $id = $_GET['approve'];
    $conn->query("UPDATE topics SET status='approved' WHERE topic_id='$id'");
}

if(isset($_GET['reject'])){
    $id = $_GET['reject'];
    $conn->query("UPDATE topics SET status='rejected' WHERE topic_id='$id'");
}

$sql = "SELECT topics.*, users.name
        FROM topics
        JOIN faculty ON topics.faculty_id = faculty.faculty_id
        JOIN users ON faculty.user_id = users.id";

$res = $conn->query($sql);

?>

<div class="card">

<h3><i class="fa fa-book"></i> Topics List</h3>

<div class="table-container">

<table>

<tr>
<th>Title</th>
<th>Faculty</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php while($row = $res->fetch_assoc()){ ?>

<tr>

<td><?php echo $row['title']; ?></td>

<td><?php echo $row['name']; ?></td>

<td><?php echo ucfirst($row['status']); ?></td>

<td>

<a href="?page=topics&approve=<?php echo $row['topic_id']; ?>">
<button class="btn btn-approve">
<i class="fa fa-check"></i>
</button>
</a>

<a href="?page=topics&reject=<?php echo $row['topic_id']; ?>">
<button class="btn btn-reject">
<i class="fa fa-times"></i>
</button>
</a>

</td>

</tr>

<?php } ?>

</table>

</div>

</div>

<?php } ?>

</div>

</body>
</html>