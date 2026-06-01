<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../db.php";

/* ================= LOGIN CHECK ================= */

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student'){
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

/* ================= CREATE UPLOAD FOLDER ================= */

$uploadDir = "../uploads/";

if(!is_dir($uploadDir)){
    mkdir($uploadDir, 0777, true);
}

/* ================= SUCCESS MESSAGE ================= */

$success = "";
$error = "";

/* ================= SUBMIT TASK ================= */

if(isset($_POST['upload'])){

    $task_id = intval($_POST['task_id']);

    if(isset($_FILES['file']) && $_FILES['file']['error'] == 0){

        $allowed = [
            'pdf',
            'doc',
            'docx',
            'zip',
            'rar',
            'ppt',
            'pptx'
        ];

        $originalName = $_FILES['file']['name'];
        $tmp = $_FILES['file']['tmp_name'];

        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if(in_array($ext, $allowed)){

            /* SAFE FILE NAME */

            $cleanName = preg_replace("/[^a-zA-Z0-9._-]/", "_", $originalName);

            $fileName = time() . "_" . $cleanName;

            $uploadPath = $uploadDir . $fileName;

            if(move_uploaded_file($tmp, $uploadPath)){

                /* UPDATE TASK */

                $stmt = $conn->prepare("
                UPDATE tasks
                SET
                    file=?,
                    status='submitted'
                WHERE task_id=?
                ");

                $stmt->bind_param("si", $fileName, $task_id);
                $stmt->execute();

                /* ================= NOTIFY FACULTY ================= */

                $fac = $conn->query("
                SELECT faculty.user_id
                FROM tasks
                JOIN faculty
                ON tasks.faculty_id = faculty.faculty_id
                WHERE tasks.task_id='$task_id'
                ")->fetch_assoc();

                if($fac){

                    $msg = "Student submitted a task";

                    $stmt2 = $conn->prepare("
                    INSERT INTO notifications
                    (user_id,message,type)
                    VALUES (?,?,?)
                    ");

                    $type = "task";

                    $stmt2->bind_param(
                        "iss",
                        $fac['user_id'],
                        $msg,
                        $type
                    );

                    $stmt2->execute();
                }

                $success = "Task submitted successfully!";

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
AND status IN ('assigned','rejected')
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

<style>

.task-card{
    background:white;
    padding:30px;
    border-radius:20px;
    box-shadow:0 5px 20px rgba(0,0,0,0.05);
    margin-bottom:30px;
}

.task-card h2{
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

.error{
    background:#fee2e2;
    color:#dc2626;
    padding:14px;
    border-radius:12px;
    margin-bottom:20px;
    font-weight:600;
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

.status{
    padding:6px 12px;
    border-radius:30px;
    font-size:13px;
    font-weight:bold;
    display:inline-block;
}

.assigned{
    background:#dbeafe;
    color:#1d4ed8;
}

.submitted{
    background:#fef3c7;
    color:#b45309;
}

.approved{
    background:#dcfce7;
    color:#166534;
}

.rejected{
    background:#fee2e2;
    color:#dc2626;
}

.upload-form{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.upload-form input[type="file"]{
    padding:10px;
    border:1px solid #cbd5e1;
    border-radius:10px;
    background:#f8fafc;
}

.upload-btn{
    background:#2563eb;
    color:white;
    border:none;
    padding:10px 16px;
    border-radius:10px;
    cursor:pointer;
    font-weight:bold;
    transition:0.3s;
}

.upload-btn:hover{
    background:#1d4ed8;
}

.view-btn{
    background:#0f766e;
    color:white;
    padding:8px 14px;
    border-radius:10px;
    text-decoration:none;
    font-size:14px;
    display:inline-block;
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

<?php if($error != ""){ ?>

<div class="error">

<i class="fa-solid fa-triangle-exclamation"></i>

<?= $error; ?>

</div>

<?php } ?>

<!-- ================= ASSIGNED TASKS ================= -->

<div class="task-card">

<h2>

<i class="fa-solid fa-list-check"></i>

Assigned Tasks

</h2>

<div class="table-container">

<?php if($assigned->num_rows > 0){ ?>

<table>

<tr>
<th>Title</th>
<th>Description</th>
<th>Status</th>
<th>Upload Work</th>
</tr>

<?php while($row = $assigned->fetch_assoc()){ ?>

<tr>

<td>

<strong>

<?= htmlspecialchars($row['title']); ?>

</strong>

</td>

<td>

<?= nl2br(htmlspecialchars($row['description'])); ?>

</td>

<td>

<span class="status <?= $row['status']; ?>">

<?= ucfirst($row['status']); ?>

</span>

</td>

<td>

<form
method="POST"
enctype="multipart/form-data"
class="upload-form">

<input
type="hidden"
name="task_id"
value="<?= $row['task_id']; ?>">

<input
type="file"
name="file"
required>

<button class="upload-btn" name="upload">

<i class="fa-solid fa-upload"></i>

Submit

</button>

</form>

</td>

</tr>

<?php } ?>

</table>

<?php } else { ?>

<div class="empty">

<h3>No Assigned Tasks</h3>

</div>

<?php } ?>

</div>

</div>

<!-- ================= SUBMITTED TASKS ================= -->

<div class="task-card">

<h2>

<i class="fa-solid fa-file-circle-check"></i>

Submitted Tasks

</h2>

<div class="table-container">

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

<strong>

<?= htmlspecialchars($row['title']); ?>

</strong>

</td>

<td>

<?php if(!empty($row['file']) && file_exists("../uploads/".$row['file'])){ ?>

<a
class="view-btn"
target="_blank"
href="../uploads/<?= urlencode($row['file']); ?>">

View File

</a>

<?php } else { ?>

<span style="color:#94a3b8;">
No File
</span>

<?php } ?>

</td>

<td>

<span class="status <?= $row['status']; ?>">

<?= ucfirst($row['status']); ?>

</span>

</td>

</tr>

<?php } ?>

</table>

<?php } else { ?>

<div class="empty">

<h3>No Submitted Tasks</h3>

</div>

<?php } ?>

</div>

</div>