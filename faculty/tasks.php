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

/* ================= FACULTY ================= */

$fac = $conn->query("
SELECT faculty_id
FROM faculty
WHERE user_id='$user_id'
")->fetch_assoc();

$faculty_id = $fac['faculty_id'] ?? 0;

/* ================= CREATE UPLOAD FOLDER ================= */

$uploadDir = "../uploads/";

if(!is_dir($uploadDir)){
    mkdir($uploadDir, 0777, true);
}

/* ================= STUDENTS ================= */

/* ================= STUDENTS ================= */

    $students = $conn->query("
    SELECT DISTINCT
        students.student_id,
        users.name
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

/* ================= ASSIGN TASK ================= */

if(isset($_POST['assign_task'])){

    $student_id = intval($_POST['student_id']);

    $title = trim($_POST['title']);

    $description = trim($_POST['description']);

    $stmt = $conn->prepare("
    INSERT INTO tasks
    (student_id, faculty_id, title, description, status)
    VALUES (?,?,?,?,?)
    ");

    $status = "assigned";

    $stmt->bind_param(
        "iisss",
        $student_id,
        $faculty_id,
        $title,
        $description,
        $status
    );

    $stmt->execute();

    /* NOTIFICATION */

    $user = $conn->query("
    SELECT user_id
    FROM students
    WHERE student_id='$student_id'
    ")->fetch_assoc();

    if($user){

        $msg = "New task assigned";

        $type = "task";

        $stmt2 = $conn->prepare("
        INSERT INTO notifications
        (user_id,message,type)
        VALUES (?,?,?)
        ");

        $stmt2->bind_param(
            "iss",
            $user['user_id'],
            $msg,
            $type
        );

        $stmt2->execute();
    }

    $success = "Task assigned successfully!";
}

/* ================= APPROVE ================= */

if(isset($_GET['approve'])){

    $id = intval($_GET['approve']);

    $task = $conn->query("
    SELECT student_id
    FROM tasks
    WHERE task_id='$id'
    ")->fetch_assoc();

    $conn->query("
    UPDATE tasks
    SET status='approved'
    WHERE task_id='$id'
    ");

    $user = $conn->query("
    SELECT user_id
    FROM students
    WHERE student_id='{$task['student_id']}'
    ")->fetch_assoc();

    if($user){

        $conn->query("
        INSERT INTO notifications
        (user_id,message,type)
        VALUES
        (
            '{$user['user_id']}',
            'Your task has been approved',
            'task'
        )
        ");
    }

    $success = "Task approved successfully!";
}

/* ================= REJECT ================= */

if(isset($_GET['reject'])){

    $id = intval($_GET['reject']);

    $task = $conn->query("
    SELECT student_id
    FROM tasks
    WHERE task_id='$id'
    ")->fetch_assoc();

    $conn->query("
    UPDATE tasks
    SET status='rejected'
    WHERE task_id='$id'
    ");

    $user = $conn->query("
    SELECT user_id
    FROM students
    WHERE student_id='{$task['student_id']}'
    ")->fetch_assoc();

    if($user){

        $conn->query("
        INSERT INTO notifications
        (user_id,message,type)
        VALUES
        (
            '{$user['user_id']}',
            'Task rejected. Please resubmit.',
            'task'
        )
        ");
    }

    $success = "Task rejected!";
}

/* ================= TASKS ================= */

$res = $conn->query("
SELECT tasks.*, users.name
FROM tasks
JOIN students
ON tasks.student_id = students.student_id
JOIN users
ON students.user_id = users.id
WHERE tasks.faculty_id='$faculty_id'
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

.form-group{
    margin-bottom:20px;
}

.form-group label{
    display:block;
    margin-bottom:8px;
    font-weight:600;
}

.form-group input,
.form-group textarea,
.form-group select{
    width:100%;
    padding:14px;
    border:1px solid #cbd5e1;
    border-radius:12px;
    background:#f8fafc;
    outline:none;
}

.form-group textarea{
    min-height:120px;
    resize:vertical;
}

.submit-btn{
    background:#2563eb;
    color:white;
    border:none;
    padding:14px 20px;
    border-radius:12px;
    cursor:pointer;
    font-weight:bold;
}

.submit-btn:hover{
    background:#1d4ed8;
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

.action-btn{
    padding:8px 12px;
    border-radius:10px;
    text-decoration:none;
    color:white;
    font-size:13px;
    margin-right:6px;
    display:inline-block;
}

.view-btn{
    background:#0f766e;
}

.approve-btn{
    background:#16a34a;
}

.reject-btn{
    background:#dc2626;
}

.empty{
    text-align:center;
    color:#94a3b8;
    padding:30px;
}

</style>

<?php if($success != ""){ ?>

<div class="success">
<?= $success; ?>
</div>

<?php } ?>

<!-- ASSIGN TASK -->

<div class="task-card">

<h2>
<i class="fa-solid fa-list-check"></i>
Assign New Task
</h2>

<form method="POST">

<div class="form-group">

<label>Select Student</label>

<select name="student_id" required>

<option value="">Choose Student</option>

<?php while($s = $students->fetch_assoc()){ ?>

<option value="<?= $s['student_id']; ?>">

<?= htmlspecialchars($s['name']); ?>

</option>

<?php } ?>

</select>

</div>

<div class="form-group">

<label>Task Title</label>

<input type="text"
name="title"
placeholder="Enter task title"
required>

</div>

<div class="form-group">

<label>Description</label>

<textarea
name="description"
placeholder="Enter task details"></textarea>

</div>

<button class="submit-btn" name="assign_task">

<i class="fa-solid fa-paper-plane"></i>
Assign Task

</button>

</form>

</div>

<!-- TASK LIST -->

<div class="task-card">

<h2>
<i class="fa-solid fa-table"></i>
All Tasks
</h2>

<div class="table-container">

<?php if($res->num_rows > 0){ ?>

<table>

<tr>
<th>Student</th>
<th>Task</th>
<th>Status</th>
<th>File</th>
<th>Action</th>
</tr>

<?php while($row = $res->fetch_assoc()){ ?>

<tr>

<td>
<?= htmlspecialchars($row['name']); ?>
</td>

<td>

<strong>
<?= htmlspecialchars($row['title']); ?>
</strong>

<br><br>

<small style="color:#64748b;">
<?= htmlspecialchars($row['description']); ?>
</small>

</td>

<td>

<span class="status <?= $row['status']; ?>">

<?= ucfirst($row['status']); ?>

</span>

</td>

<td>

<?php if(!empty($row['file']) && file_exists("../uploads/".$row['file'])){ ?>

<a
class="action-btn view-btn"
download
href="../uploads/<?= urlencode($row['file']); ?>">

Download File

</a>

<?php } else { ?>

<span style="color:#94a3b8;">No File</span>

<?php } ?>

</td>

<td>

<?php if(trim(strtolower($row['status'])) == 'submitted'){ ?>

<a
class="action-btn approve-btn"
href="?page=tasks&approve=<?= $row['task_id']; ?>">

<i class="fa-solid fa-check"></i>
Approve

</a>

<a
class="action-btn reject-btn"
href="?page=tasks&reject=<?= $row['task_id']; ?>">

<i class="fa-solid fa-xmark"></i>
Reject

</a>

<?php } else { ?>

<span style="color:#94a3b8;">No Action</span>

<?php } ?>

</td>

</tr>

<?php } ?>

</table>

<?php } else { ?>

<div class="empty">

<h3>No Tasks Found</h3>

</div>

<?php } ?>

</div>

</div>