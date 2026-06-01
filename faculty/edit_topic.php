<?php
session_start();
include "../db.php";

$limitData = $conn->query("
SELECT setting_value
FROM settings
WHERE setting_name='max_student_limit'
")->fetch_assoc();

$fixed_limit = $limitData
? intval($limitData['setting_value'])
: 5;

if(!isset($_SESSION['user_id']) || $_SESSION['role']!='faculty'){
    header("Location: ../login.php");
    exit();
}

$id = intval($_GET['id']);

$topic = $conn->query("
SELECT *
FROM topics
WHERE topic_id='$id'
")->fetch_assoc();

if(!$topic){
    die("Topic not found");
}

$success = "";

if(isset($_POST['update'])){

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $skills = trim($_POST['skills']);
    $max = trim($_POST['max']);

    if(
        empty($title) ||
        empty($description) ||
        empty($skills) ||
        empty($max)
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
        .$fixed_limit;

    }
    else{

        $conn->query("
        UPDATE topics
        SET
        title='$title',
        description='$description',
        skills_required='$skills',
        max_students='$max',
        status='pending'
        WHERE topic_id='$id'
        ");

        header("Location: dashboard.php?page=create_topic");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Topic</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

body{
    font-family:'Segoe UI',sans-serif;
    background:#f4f7fb;
    padding:40px;
}

.card{
    max-width:800px;
    margin:auto;
    background:white;
    padding:30px;
    border-radius:20px;
    box-shadow:0 5px 20px rgba(0,0,0,0.05);
}

input,textarea{
    width:100%;
    padding:12px;
    margin-top:8px;
    margin-bottom:20px;
    border:1px solid #cbd5e1;
    border-radius:10px;
}

button{
    background:#2563eb;
    color:white;
    border:none;
    padding:12px 18px;
    border-radius:10px;
    cursor:pointer;
}

</style>
</head>

<body>

<div class="card">

<h2>
<i class="fa-solid fa-pen"></i>
Edit Research Field
</h2>

<form method="POST">

<label>Title</label>

<input
type="text"
name="title"
value="<?= htmlspecialchars($topic['title']) ?>"
required>

<label>Description</label>

<textarea
name="description"
required><?= htmlspecialchars($topic['description']) ?></textarea>

<label>Skills</label>

<input
type="text"
name="skills"
value="<?= htmlspecialchars($topic['skills_required']) ?>"
required>

<label>Maximum Students</label>

<input
type="number"
name="max"
id="max_students"
value="<?= $topic['max_students'] ?>"
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

<button name="update">
Update Topic
</button>

</form>

</div>

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

</body>
</html>