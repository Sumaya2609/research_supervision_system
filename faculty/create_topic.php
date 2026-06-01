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

/* ================= GET FACULTY ================= */

$f = $conn->query("
SELECT faculty_id
FROM faculty
WHERE user_id='$user_id'
");

$faculty = $f->fetch_assoc();

if(!$faculty){
    die("Faculty profile not found. Please contact admin.");
}

$faculty_id = $faculty['faculty_id'];

$success = "";

/* ================= ADMIN FIXED LIMIT ================= */

$limitData = $conn->query("
SELECT setting_value
FROM settings
WHERE setting_name='max_student_limit'
")->fetch_assoc();

$fixed_limit = $limitData
? intval($limitData['setting_value'])
: 5;

/* ================= CREATE TOPIC ================= */

if(isset($_POST['create'])){

    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $skills = trim($_POST['skills']);
    $max = trim($_POST['max']);
    $deadline = trim($_POST['deadline']);

    /* ================= VALIDATION ================= */

    if(
        empty($title) ||
        empty($desc) ||
        empty($skills) ||
        empty($max) ||
        empty($deadline)
    ){

        $success = "All fields are required!";

    }
    elseif(
        !is_numeric($max) ||
        $max < 1 ||
        $max > $fixed_limit
    ){

        $success =
        "Maximum students must be between 1 and "
        .$fixed_limit.
        " only!";

    }
    else{

        $conn->query("
        INSERT INTO topics
        (
            faculty_id,
            title,
            description,
            skills_required,
            max_students,
            deadline,
            status
        )
        VALUES
        (
            '$faculty_id',
            '$title',
            '$desc',
            '$skills',
            '$max',
            '$deadline',
            'pending'
        )
        ");


            $admins = $conn->query("
    SELECT id
    FROM users
    WHERE role='admin'
    ");

    while($admin = $admins->fetch_assoc()){

        $conn->query("
        INSERT INTO notifications
        (
            user_id,
            message,
            type
        )
        VALUES
        (
            '".$admin['id']."',
            'A new topic \"$title\" has been submitted and is waiting for approval.',
            'topic'
        )
        ");
    }

        $success = "Topic submitted for admin approval!";
    }
}

$myTopics = $conn->query("
SELECT *
FROM topics
WHERE faculty_id='$faculty_id'
ORDER BY topic_id DESC
");
?>


<style>

.topic-card{
    background:white;
    border-radius:24px;
    padding:35px;
    box-shadow:0 5px 20px rgba(0,0,0,0.05);
    max-width:850px;
    margin:auto;
}

.topic-card h2{
    color:#2563eb;
    margin-bottom:25px;
    font-size:28px;
}

.success{
    background:#dcfce7;
    color:#166534;
    padding:14px;
    border-radius:12px;
    margin-bottom:25px;
    font-weight:600;
}

.form-group{
    margin-bottom:22px;
}

.form-group label{
    display:block;
    margin-bottom:8px;
    font-weight:600;
    color:#334155;
}

.form-group input,
.form-group textarea{
    width:100%;
    padding:15px;
    border:1px solid #cbd5e1;
    border-radius:14px;
    background:#f8fafc;
    font-size:15px;
    outline:none;
    transition:0.3s;
    box-sizing:border-box;
}

.form-group textarea{
    min-height:140px;
    resize:vertical;
}

.form-group input:focus,
.form-group textarea:focus{
    border-color:#2563eb;
    background:white;
    box-shadow:0 0 0 3px rgba(37,99,235,0.1);
}

.submit-btn{
    background:#2563eb;
    color:white;
    border:none;
    padding:14px 24px;
    border-radius:12px;
    cursor:pointer;
    font-size:15px;
    font-weight:600;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    transition:0.3s;
}

.submit-btn:hover{
    background:#1d4ed8;
    transform:translateY(-2px);
}

@media(max-width:768px){

    .topic-card{
        padding:25px;
    }
}

</style>

<div class="card">

<div class="topic-card">

<h2>
<i class="fa-solid fa-lightbulb"></i>
Create Research Field
</h2>

<?php if($success != ""){ ?>

<div class="success">

<i class="fa-solid fa-circle-check"></i>

<?= $success; ?>

</div>

<?php } ?>

<form method="POST">

<div class="form-group">

<label>Topic Field</label>

<input
type="text"
name="title"
placeholder="Enter research Field"
required>

</div>

<div class="form-group">

<label>Description</label>

<textarea
name="description"
placeholder="Enter Field description"
required></textarea>

</div>

<div class="form-group">

<label>Required Skills</label>

<input
type="text"
name="skills"
placeholder="Example: Python, AI, Laravel"
required>

</div>

<div class="form-group">

<label>Maximum Students</label>

<input
type="number"
name="max"
id="max_students"
placeholder="Enter maximum students"
min="1"
max="<?= $fixed_limit ?>"
required>

<small
id="limitMessage"
style="
color:#dc2626;
font-weight:600;
display:none;
margin-top:8px;
display:block;
">
</small>

</div>



<div class="form-group">

<label>Created Date</label>

<input
type="date"
name="deadline"
required>

</div>

<button class="submit-btn" name="create">

<i class="fa-solid fa-paper-plane"></i>

<span>Submit Topic</span>

</button>

</form>

</div>

<hr style="margin:40px 0;">

<h2>
<i class="fa-solid fa-list"></i>
My Research Fields
</h2>

<br>

<?php if($myTopics->num_rows > 0){ ?>

<<<<<<< HEAD
=======
<?php if($myTopics->num_rows > 0){ ?>

>>>>>>> ff8f1c8e2111a47f077e73c0467b5e09ce4609ff
<table style="
width:100%;
border-collapse:collapse;
background:white;
">

<tr style="background:#2563eb;color:white;">
    <th style="padding:12px;">Title</th>
    <th>Status</th>
    <th>Max Students</th>
    <th>Actions</th>
</tr>

<?php while($topic = $myTopics->fetch_assoc()){ ?>

<tr>
<<<<<<< HEAD
    <td style="padding:12px;">
        <?= htmlspecialchars($topic['title']) ?>
    </td>

    <td>
        <?= ucfirst($topic['status']) ?>
    </td>

    <td>
        <?= $topic['max_students'] ?>
    </td>

    <td>

        <a
        href="edit_topic.php?id=<?= $topic['topic_id'] ?>"
        class="btn"
        style="padding:8px 12px;">
            <i class="fa-solid fa-pen"></i>
            Edit
        </a>

        <a
        href="delete_topic.php?id=<?= $topic['topic_id'] ?>"
        class="btn"
        style="background:#dc2626;padding:8px 12px;"
        onclick="return confirm('Delete this topic?')">
            <i class="fa-solid fa-trash"></i>
            Delete
        </a>

    </td>
=======

<td style="padding:12px;">
<?= htmlspecialchars($topic['title']) ?>
</td>

<td>
<?= ucfirst($topic['status']) ?>
</td>

<td>
<?= $topic['max_students'] ?>
</td>

<td>

<a
href="edit_topic.php?id=<?= $topic['topic_id'] ?>"
class="btn"
style="padding:8px 12px;"
>
<i class="fa-solid fa-pen"></i>
Edit
</a>

<a
href="delete_topic.php?id=<?= $topic['topic_id'] ?>"
class="btn"
style="background:#dc2626;padding:8px 12px;"
onclick="return confirm('Delete this topic?')"
>
<i class="fa-solid fa-trash"></i>
Delete
</a>

</td>

>>>>>>> ff8f1c8e2111a47f077e73c0467b5e09ce4609ff
</tr>

<?php } ?>

</table>

<?php } else { ?>

<p>No research fields created yet.</p>

<?php } ?>

<script>

const maxInput = document.getElementById("max_students");
const limitMessage = document.getElementById("limitMessage");
const fixedLimit = <?= $fixed_limit ?>;

maxInput.addEventListener("input", function(){

    let value = parseInt(this.value);

    if(value > fixedLimit){

        limitMessage.style.display = "block";
        limitMessage.innerHTML =
        "Maximum allowed students is " + fixedLimit;

        this.value = fixedLimit;

    }else{

        limitMessage.style.display = "none";
    }
});

</script>

</div>
<<<<<<< HEAD
=======

</div>
>>>>>>> ff8f1c8e2111a47f077e73c0467b5e09ce4609ff
