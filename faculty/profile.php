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

/* ================= GET FACULTY INFO ================= */

$result = $conn->query("
SELECT users.name, users.email, faculty.*
FROM users
LEFT JOIN faculty ON users.id = faculty.user_id
WHERE users.id='$user_id'
");

$data = $result->fetch_assoc();

if(!$data){
    die("Faculty profile not found");
}

/* ================= UPDATE PROFILE ================= */

$success = "";

if(isset($_POST['update'])){

    $dept = trim($_POST['department']);
    $desig = trim($_POST['designation']);

    $conn->query("
    UPDATE faculty
    SET department='$dept',
        designation='$desig'
    WHERE user_id='$user_id'
    ");

    $success = "Profile updated successfully!";

    $result = $conn->query("
    SELECT users.name, users.email, faculty.*
    FROM users
    LEFT JOIN faculty ON users.id = faculty.user_id
    WHERE users.id='$user_id'
    ");

    $data = $result->fetch_assoc();
}
?>

<style>

.profile-card{
    width:100%;
    background:white;
    border-radius:24px;
    padding:35px;
    box-shadow:0 5px 20px rgba(0,0,0,0.05);
    display:flex;
    gap:35px;
    align-items:flex-start;
}

.profile-left{
    width:260px;
    text-align:center;
    border-right:1px solid #e2e8f0;
    padding-right:30px;
}

.avatar{
    width:110px;
    height:110px;
    margin:auto;
    border-radius:50%;
    background:#2563eb;
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:40px;
    font-weight:bold;
    margin-bottom:20px;
}

.profile-left h2{
    font-size:24px;
    margin-bottom:8px;
}

.profile-left p{
    color:#64748b;
}

.profile-right{
    flex:1;
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
    margin-bottom:20px;
}

.form-group label{
    display:block;
    margin-bottom:8px;
    font-weight:600;
    color:#334155;
}

.form-group input{
    width:100%;
    padding:14px;
    border:1px solid #cbd5e1;
    border-radius:12px;
    background:#f8fafc;
    outline:none;
    transition:0.3s;
}

.form-group input:focus{
    border-color:#2563eb;
    background:white;
    box-shadow:0 0 0 3px rgba(37,99,235,0.1);
}

.form-group input:disabled{
    background:#e2e8f0;
}

.save-btn{
    background:#2563eb;
    color:white;
    border:none;
    padding:15px 20px;
    border-radius:12px;
    cursor:pointer;
    font-weight:bold;
    font-size:15px;
    transition:0.3s;
    display:flex;
    align-items:center;
    gap:10px;
}

.save-btn:hover{
    background:#1d4ed8;
}

@media(max-width:768px){

    .profile-card{
        flex-direction:column;
    }

    .profile-left{
        width:100%;
        border-right:none;
        border-bottom:1px solid #e2e8f0;
        padding-right:0;
        padding-bottom:25px;
    }
}

</style>
<div class="topbar">

<div>

<h1>
Faculty Panel
</h1>

<p>
Faculty Research Management Dashboard
</p>

</div>

<div>

<a href="javascript:history.back()"
class="btn">

<i class="fa-solid fa-arrow-left"></i>
Back

</a>

</div>

</div>
<div class="card">


<div class="profile-card">

<!-- LEFT -->

<div class="profile-left">

<div class="avatar">
<?= strtoupper(substr($data['name'],0,1)); ?>
</div>

<h2>
<?= htmlspecialchars($data['name']); ?>
</h2>

<p>Faculty Member</p>

</div>

<!-- RIGHT -->

<div class="profile-right">

<h2 style="margin-bottom:25px; color:#2563eb;">
Profile Settings
</h2>

<?php if($success != ""){ ?>

<div class="success">

<i class="fa-solid fa-circle-check"></i>
<?= $success; ?>

</div>

<?php } ?>

<form method="POST">

<div class="form-group">

<label>Full Name</label>

<input type="text"
value="<?= htmlspecialchars($data['name']); ?>"
disabled>

</div>

<div class="form-group">

<label>Email Address</label>

<input type="email"
value="<?= htmlspecialchars($data['email']); ?>"
disabled>

</div>

<div class="form-group">

<label>Department</label>

<input
type="text"
name="department"
placeholder="Enter department"
value="<?= htmlspecialchars($data['department'] ?? ''); ?>"
required>

</div>

<div class="form-group">

<label>Designation</label>

<input
type="text"
name="designation"
placeholder="Enter designation"
value="<?= htmlspecialchars($data['designation'] ?? ''); ?>"
required>

</div>

<button class="save-btn" name="update">

<i class="fa-solid fa-floppy-disk"></i>

Save Changes

</button>

</form>

</div>

</div>

</div>