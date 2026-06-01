<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$allowed_pages = ['home', 'profile', 'browse', 'applications', 'tasks', 'reports'];
$page = $_GET['page'] ?? 'home';

if (!in_array($page, $allowed_pages)) {
    $page = 'home';
}

/* ================= STUDENT INFO ================= */

$data = $conn->query("
SELECT users.name, users.email, students.*
FROM users
LEFT JOIN students ON users.id = students.user_id
WHERE users.id='$user_id'
")->fetch_assoc();

/* ================= APPLICATION COUNT ================= */

$appCount = $conn->query("
SELECT COUNT(*) as total
FROM applications a
JOIN students s ON a.student_id = s.student_id
WHERE s.user_id='$user_id'
")->fetch_assoc();

/* ================= NOTIFICATION COUNT ================= */

$notifCount = $conn->query("
SELECT COUNT(*) as total
FROM notifications
WHERE user_id='$user_id' AND is_read=0
")->fetch_assoc();

/* ================= STUDENT ID ================= */

$studentRow = $conn->query("
SELECT student_id FROM students WHERE user_id='$user_id'
")->fetch_assoc();

$student_id = $studentRow['student_id'] ?? 0;

/* ================= RATING ================= */

$ratingRow = $conn->query("
SELECT AVG(rating) as avg_rating
FROM reports
WHERE student_id='$student_id'
")->fetch_assoc();

$rating = $ratingRow['avg_rating'] ? round($ratingRow['avg_rating'], 1) : 0;

$success = "";

/* ================= TASK UPLOAD ================= */
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Student Dashboard</title>

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
    background:#f1f5f9;
    color:#1e293b;
}

/* ================= SIDEBAR ================= */

.sidebar{
    width:260px;
    height:100vh;
    background:linear-gradient(180deg,#0f172a,#1e293b);
    position:fixed;
    left:0;
    top:0;
    padding:20px;
    overflow-y:auto;
    box-shadow:4px 0 20px rgba(0,0,0,0.08);
}

.logo{
    text-align:center;
    color:white;
    font-size:24px;
    font-weight:bold;
    margin-bottom:35px;
}

.sidebar a{
    display:flex;
    align-items:center;
    gap:12px;
    padding:14px 16px;
    margin-bottom:10px;
    border-radius:12px;
    color:#cbd5e1;
    text-decoration:none;
    transition:0.3s;
    font-size:15px;
}

.sidebar a:hover,
.sidebar a.active{
    background:#2563eb;
    color:white;
    transform:translateX(4px);
}

/* ================= MAIN ================= */

.main{
    margin-left:260px;
    padding:30px;
}

/* ================= TOPBAR ================= */

.topbar{
    background:white;
    padding:20px 25px;
    border-radius:18px;
    margin-bottom:25px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 4px 20px rgba(0,0,0,0.05);
}

.topbar h2{
    font-size:24px;
    margin-bottom:5px;
}

.topbar p{
    color:#64748b;
}

/* ================= BUTTON ================= */

.btn{
    background:#2563eb;
    color:white;
    padding:11px 18px;
    border:none;
    border-radius:10px;
    text-decoration:none;
    cursor:pointer;
    transition:0.3s;
    display:inline-flex;
    align-items:center;
    gap:8px;
    font-size:14px;
}

.btn:hover{
    background:#1d4ed8;
}

/* ================= CARDS ================= */

.card{
    background:white;
    padding:25px;
    border-radius:20px;
    margin-bottom:25px;
    box-shadow:0 4px 20px rgba(0,0,0,0.05);
}

.card h2,
.card h3{
    margin-bottom:12px;
}

/* ================= GRID ================= */

.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
    gap:20px;
}

.box{
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
    color:white;
    padding:25px;
    border-radius:20px;
    text-align:center;
    transition:0.3s;
    box-shadow:0 8px 25px rgba(37,99,235,0.2);
}

.box:hover{
    transform:translateY(-5px);
}

.box h3{
    font-size:32px;
    margin-bottom:10px;
}

/* ================= TABLE ================= */

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
}

tr:hover{
    background:#f8fafc;
}

/* ================= FORMS ================= */

input[type="file"]{
    width:100%;
    padding:14px;
    border:1px solid #cbd5e1;
    border-radius:10px;
    margin-bottom:15px;
    background:white;
}

/* ================= STATUS ================= */

.pending{
    color:#f59e0b;
    font-weight:bold;
}

.approved{
    color:#16a34a;
    font-weight:bold;
}

.rejected{
    color:#dc2626;
    font-weight:bold;
}

/* ================= ALERT ================= */

.alert{
    background:#dcfce7;
    color:#166534;
    padding:15px;
    border-radius:12px;
    margin-bottom:20px;
}

/* ================= TOPIC CARD ================= */

.topic-card{
    border-left:5px solid #2563eb;
}

.topic-card p{
    margin-top:10px;
    color:#475569;
}

/* ================= MOBILE ================= */

@media(max-width:768px){

    .sidebar{
        width:100%;
        height:auto;
        position:relative;
    }

    .main{
        margin-left:0;
        padding:15px;
    }

    .topbar{
        flex-direction:column;
        align-items:flex-start;
        gap:15px;
    }
}

</style>
</head>

<body>

<!-- ================= SIDEBAR ================= -->

<div class="sidebar">

<div class="logo">
<i class="fa-solid fa-graduation-cap"></i>
Student Panel
</div>

<a class="<?=($page=='home')?'active':''?>" href="?page=home">
<i class="fa-solid fa-house"></i>
Dashboard
</a>

<a class="<?=($page=='profile')?'active':''?>" href="?page=profile">
<i class="fa-solid fa-user"></i>
Profile
</a>

<a class="<?=($page=='browse')?'active':''?>" href="?page=browse">
<i class="fa-solid fa-book-open"></i>
Browse Fields
</a>

<a class="<?=($page=='applications')?'active':''?>" href="?page=applications">
<i class="fa-solid fa-file-circle-check"></i>
Applications
</a>

<a class="<?=($page=='tasks')?'active':''?>" href="?page=tasks">
<i class="fa-solid fa-list-check"></i>
My Tasks
</a>

<a class="<?=($page=='reports')?'active':''?>" href="?page=reports">
<i class="fa-solid fa-chart-line"></i>
Reports
</a>

<a href="chat.php">
<i class="fa-solid fa-comments"></i>
Chat
</a>

<a href="notifications.php">
<i class="fa-solid fa-bell"></i>
Notifications (<?= $notifCount['total']; ?>)
</a>

<a href="../logout.php">
    <i class="fa-solid fa-right-from-bracket"></i>
    Logout
</a>

</div>

<!-- ================= MAIN ================= -->

<div class="main">

<!-- ================= TOPBAR ================= -->

<div class="topbar">

<div>
<h2><?= ucfirst($page); ?></h2>
<p>Welcome back, <?= htmlspecialchars($data['name']); ?></p>
</div>

<div>
<a href="javascript:history.back()" class="btn">
<i class="fa-solid fa-arrow-left"></i>
Back
</a>
</div>

</div>

<?php if($success != ""){ ?>
<div class="alert">
<?= $success ?>
</div>
<?php } ?>

<?php

/* ================= HOME ================= */

if($page == 'home'){
?>
<div class="grid">

<div class="box">
<h3><?= $data['cgpa'] ?? '0.00'; ?></h3>
<p>Current CGPA</p>
</div>

<div class="box">
<h3><?= $appCount['total']; ?></h3>
<p>Total Applications</p>
</div>

<div class="box">
<h3><?= $rating; ?> ⭐</h3>
<p>Average Rating</p>
</div>

</div>

<?php
}
/* ================= PROFILE ================= */
elseif($page == 'profile'){

    include "profile.php";

}


/* ================= BROWSE ================= */

elseif($page == 'browse'){

$search_topic = trim($_GET['topic'] ?? '');
$search_faculty = trim($_GET['faculty'] ?? '');

$res = $conn->query("
SELECT topics.*, users.name AS faculty_name
FROM topics
JOIN faculty ON topics.faculty_id = faculty.faculty_id
JOIN users ON faculty.user_id = users.id
WHERE topics.status='approved'
");

// echo "
// <div class='card'>
// <h2>Browse Research Topics</h2>
// <p>Select approved topics and apply easily.</p>
// </div>
// ";

while($row = $res->fetch_assoc()){

echo "
<div class='card topic-card'>

<h3>".htmlspecialchars($row['title'])."</h3>

<p>".htmlspecialchars($row['description'])."</p>

<p>
<b>Faculty:</b>
".htmlspecialchars($row['faculty_name'])."
</p>

<br>

<a class='btn'
href='apply.php?topic_id={$row['topic_id']}'>
<i class='fa-solid fa-paper-plane'></i>
Apply Now
</a>

</div>
";
}

}

/* ================= APPLICATIONS ================= */

elseif($page == 'applications'){

$res = $conn->query("
SELECT applications.*, topics.title
FROM applications
JOIN students ON applications.student_id = students.student_id
JOIN topics ON applications.topic_id = topics.topic_id
WHERE students.user_id='$user_id'
");

echo "
<div class='card'>
<h2>My Applications</h2>
<p>Track all your submitted applications.</p>
</div>
";

echo "<div class='card table-container'>";

echo "<table>";

echo "
<tr>
<th>Topic</th>
<th>Status</th>
</tr>
";

while($row = $res->fetch_assoc()){

$status = strtolower($row['status']);

echo "
<tr>
<td>".htmlspecialchars($row['title'])."</td>
<td class='{$status}'>".ucfirst($status)."</td>
</tr>
";
}

echo "</table>";

echo "</div>";
}

    /* ================= TASKS ================= */

    elseif($page == 'tasks'){

        $success = "";
        $error = "";

        /* ================= UPLOAD TASK ================= */

        if(isset($_POST['upload'])){

            $task_id = intval($_POST['task_id']);

            if(isset($_FILES['file']) && $_FILES['file']['error'] == 0){

                $allowed = ['pdf','doc','docx','ppt','pptx','zip','rar'];

                $file = $_FILES['file']['name'];
                $tmp  = $_FILES['file']['tmp_name'];

                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                if(in_array($ext, $allowed)){

                    /* SAFE FILE NAME */

                    $safeName = preg_replace("/[^a-zA-Z0-9._-]/", "_", $file);

                    $fileName = time() . "_" . $safeName;

                    /* UPLOAD DIRECTORY */

                    $uploadDir = "../uploads/";

                    if(!is_dir($uploadDir)){
                        mkdir($uploadDir, 0777, true);
                    }

                    $target = $uploadDir . $fileName;

                    /* MOVE FILE */

                    if(move_uploaded_file($tmp, $target)){

                        $conn->query("
                        UPDATE tasks
                        SET
                            file='$fileName',
                            status='submitted'
                        WHERE task_id='$task_id'
                        ");

                        /* ================= NOTIFICATION ================= */

                        $fac = $conn->query("
                        SELECT faculty.user_id
                        FROM tasks
                        JOIN faculty
                        ON tasks.faculty_id = faculty.faculty_id
                        WHERE tasks.task_id='$task_id'
                        ")->fetch_assoc();

                        if($fac){

                            $conn->query("
                            INSERT INTO notifications
                            (user_id,message,type)
                            VALUES
                            (
                                '{$fac['user_id']}',
                                'Student submitted a task',
                                'task'
                            )
                            ");
                        }

                        $success = "Task uploaded successfully!";

                    } else {

                        $error = "File upload failed!";
                    }

                } else {

                    $error = "Invalid file type!";
                }

            } else {

                $error = "Please select a file.";
            }
        }

        /* ================= ASSIGNED TASKS ================= */

        $assigned = $conn->query("
        SELECT *
        FROM tasks
        WHERE student_id='$student_id'
        AND (
            status='assigned'
            OR status='rejected'
            OR status=''
            OR status IS NULL
        )
        ORDER BY task_id DESC
        ");

        /* ================= SUBMITTED TASKS ================= */

        $submitted = $conn->query("
        SELECT *
        FROM tasks
        WHERE student_id='$student_id'
        AND status IN ('submitted','approved')
        ORDER BY task_id DESC
        ");
    ?>

    <?php if($success != ""){ ?>

    <div class="alert">
    <?= $success ?>
    </div>

    <?php } ?>

    <?php if($error != ""){ ?>

    <div class="alert" style="background:#fee2e2;color:#dc2626;">
    <?= $error ?>
    </div>

    <?php } ?>

    <!-- ================= ASSIGNED TASKS ================= -->

    <div class="card">

    <h2>Assigned Tasks</h2>

    <p>Upload your assigned work.</p>

    </div>

    <div class="card table-container">

    <?php if($assigned->num_rows > 0){ ?>

    <table>

    <tr>
    <th>Title</th>
    <th>Description</th>
    <th>Status</th>
    <th>Upload</th>
    </tr>

    <?php while($row = $assigned->fetch_assoc()){ ?>

    <tr>

    <td>
    <?= htmlspecialchars($row['title']); ?>
    </td>

    <td>
    <?= htmlspecialchars($row['description']); ?>
    </td>

    <td class="<?= $row['status']; ?>">
    <?= ucfirst($row['status']); ?>
    </td>

    <td>

    <form method="POST" enctype="multipart/form-data">

    <input
    type="hidden"
    name="task_id"
    value="<?= $row['task_id']; ?>">

    <input
    type="file"
    name="file"
    required>

    <button class="btn" name="upload">

    <i class="fa-solid fa-upload"></i>

    Submit

    </button>

    </form>

    </td>

    </tr>

    <?php } ?>

    </table>

    <?php } else { ?>

    <p>No assigned tasks available.</p>

    <?php } ?>

    </div>

    <!-- ================= SUBMITTED TASKS ================= -->

    <div class="card">

    <h2>Submitted Tasks</h2>

    <p>View uploaded task files and status.</p>

    </div>

    <div class="card table-container">

    <?php if($submitted->num_rows > 0){ ?>

    <table>

    <tr>
    <th>Title</th>
    <th>File</th>
    <th>Status</th>
    </tr>

    <?php while($row = $submitted->fetch_assoc()){ ?>

    <tr>

    <td>
    <?= htmlspecialchars($row['title']); ?>
    </td>

    <td>

    <?php if(!empty($row['file'])){ ?>

    <a
    class="btn"
    target="_blank"
    href="../uploads/<?= urlencode($row['file']); ?>">

    <i class="fa-solid fa-eye"></i>

    View File

    </a>

    <?php } else { ?>

    No File

    <?php } ?>

    </td>

    <td class="<?= $row['status']; ?>">

    <?= ucfirst($row['status']); ?>

    </td>

    </tr>

    <?php } ?>

    </table>

    <?php } else { ?>

    <p>No submitted tasks available.</p>

    <?php } ?>

    </div>

    <?php
    }


/* ================= REPORTS ================= */

elseif($page == 'reports'){

    include "reports.php";

}

?>
