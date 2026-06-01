<?php
session_start();
include "../db.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student'){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$message = "";
$message_type = "";

/* ================= STUDENT INFO ================= */

$stu = $conn->query("
SELECT student_id, skills
FROM students
WHERE user_id='$user_id'
")->fetch_assoc();

$student_id = $stu['student_id'];
$student_skills = $stu['skills'];

/* ================= COSINE SIMILARITY ================= */

function cosineSimilarity($text1, $text2){

    $words1 = array_count_values(
        array_map('trim',
        array_map('strtolower',
        explode(',', $text1)))
    );

    $words2 = array_count_values(
        array_map('trim',
        array_map('strtolower',
        explode(',', $text2)))
    );

    $allWords = array_unique(
        array_merge(array_keys($words1), array_keys($words2))
    );

    $dot = 0;
    $mag1 = 0;
    $mag2 = 0;

    foreach($allWords as $word){

        $v1 = $words1[$word] ?? 0;
        $v2 = $words2[$word] ?? 0;

        $dot += $v1 * $v2;
        $mag1 += $v1 * $v1;
        $mag2 += $v2 * $v2;
    }

    if($mag1 == 0 || $mag2 == 0){
        return 0;
    }

    return $dot / (sqrt($mag1) * sqrt($mag2));
}

/* ================= APPLY ================= */

if(isset($_POST['apply'])){

    $topic_id = intval($_POST['topic_id']);

    /* CHECK IF STUDENT ALREADY HAS APPROVED TOPIC */

    $approvedCheck = $conn->query("
    SELECT *
    FROM applications
    WHERE student_id='$student_id'
    AND status='approved'
    ");

    if($approvedCheck->num_rows > 0){

        $message = "You already have an approved topic.";
        $message_type = "warning";

    }else{

        /* CHECK IF STUDENT ALREADY HAS APPROVED TOPIC */

$approvedCheck = $conn->query("
SELECT *
FROM applications
WHERE student_id='$student_id'
AND status='approved'
");

if($approvedCheck->num_rows > 0){

    $message = "You already have an approved topic.";
    $message_type = "warning";

}else{

    /* CHECK DUPLICATE APPLICATION */

    $check = $conn->query("
    SELECT *
    FROM applications
    WHERE student_id='$student_id'
    AND topic_id='$topic_id'
    ");

    if($check->num_rows > 0){

        $message = "You already applied for this topic.";
        $message_type = "warning";

    }else{

        /* GET TOPIC */

        $topic = $conn->query("
        SELECT *
        FROM topics
        WHERE topic_id='$topic_id'
        ")->fetch_assoc();

        $score = cosineSimilarity(
            $student_skills,
            $topic['skills_required']
        );

        /* COUNT APPROVED */

        $countRes = $conn->query("
        SELECT COUNT(*) as total
        FROM applications
        WHERE topic_id='$topic_id'
        AND status='approved'
        ")->fetch_assoc();

        $current_students = $countRes['total'];
        $max_students = $topic['max_students'];

        /* ================= DECISION ================= */

        if($score >= 0.3){

            if($current_students < $max_students){

                $status = 'approved';

                $conn->query("
                INSERT INTO applications
                (student_id, topic_id, match_score, status)
                VALUES
                ('$student_id','$topic_id','$score','$status')
                ");

                /* STUDENT NOTIFICATION */

                $conn->query("
                INSERT INTO notifications
                (user_id,message,type)
                VALUES
                (
                    '$user_id',
                    'You have been matched successfully!',
                    'match'
                )
                ");

                /* FACULTY NOTIFICATION */

                $conn->query("
                INSERT INTO notifications
                (user_id,message,type)

                SELECT user_id,
                CONCAT(
                    'A student matched your topic: ',
                    '{$topic['title']}'
                ),
                'match'

                FROM faculty
                WHERE faculty_id='{$topic['faculty_id']}'
                ");

                $message = "Matched Successfully!";
                $message_type = "success";

            }else{

                /* FULL CASE */

                $status = 'rejected';

                $conn->query("
                INSERT INTO applications
                (student_id, topic_id, match_score, status)
                VALUES
                ('$student_id','$topic_id','$score','$status')
                ");

                $conn->query("
                INSERT INTO notifications
                (user_id,message,type)
                VALUES
                (
                    '$user_id',
                    'Faculty seats are full.',
                    'match'
                )
                ");

                $message = "Faculty seats are full.";
                $message_type = "danger";
            }

        }else{

            /* LOW MATCH */

            $status = 'rejected';

            $conn->query("
            INSERT INTO applications
            (student_id, topic_id, match_score, status)
            VALUES
            ('$student_id','$topic_id','$score','$status')
            ");

            $conn->query("
            INSERT INTO notifications
            (user_id,message,type)
            VALUES
            (
                '$user_id',
                'No suitable faculty match found.',
                'match'
            )
            ");

            $message = "No suitable faculty found.";
            $message_type = "danger";
        }
    }
}       
}
}
/* ================= TOPICS ================= */

/* ================= GET TOPIC ID ================= */

if(!isset($_GET['topic_id'])){
    header("Location: browse_topics.php");
    exit();
}

$topic_id = intval($_GET['topic_id']);

/* ================= SINGLE TOPIC ================= */

$topics = $conn->query("
SELECT *
FROM topics
WHERE topic_id='$topic_id'
AND status='approved'
");
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Smart Research Fields Matching</title>

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
    background:#f4f7fb;
    color:#1e293b;
}

/* ================= CONTAINER ================= */

.container{
    width:90%;
    max-width:1200px;
    margin:40px auto;
}

/* ================= TOPBAR ================= */

.topbar{
    background:white;
    padding:22px 30px;
    border-radius:20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:30px;
    box-shadow:0 4px 20px rgba(0,0,0,0.05);
}

.topbar h1{
    font-size:28px;
    color:#1e3a8a;
}

.topbar p{
    color:#64748b;
    margin-top:5px;
}

/* ================= BUTTON ================= */

.btn{
    background:#2563eb;
    color:white;
    padding:12px 18px;
    border:none;
    border-radius:12px;
    cursor:pointer;
    text-decoration:none;
    transition:0.3s;
    font-size:14px;
    font-weight:600;
    display:inline-flex;
    align-items:center;
    gap:8px;
}

.btn:hover{
    background:#1d4ed8;
    transform:translateY(-2px);
}

/* ================= GRID ================= */

.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
    gap:25px;
}

/* ================= CARD ================= */

.card{
    background:white;
    border-radius:20px;
    padding:25px;
    box-shadow:0 5px 20px rgba(0,0,0,0.05);
    transition:0.3s;
    border-top:5px solid #2563eb;
}

.card:hover{
    transform:translateY(-5px);
}

.card h3{
    margin-bottom:15px;
    color:#1e3a8a;
}

.card p{
    margin-bottom:12px;
    color:#475569;
    line-height:1.6;
}

/* ================= BADGE ================= */

.badge{
    display:inline-block;
    background:#dbeafe;
    color:#1d4ed8;
    padding:7px 12px;
    border-radius:20px;
    font-size:13px;
    font-weight:600;
    margin-bottom:15px;
}

/* ================= ALERT ================= */

.alert{
    padding:16px 20px;
    border-radius:14px;
    margin-bottom:25px;
    font-weight:600;
}

.success{
    background:#dcfce7;
    color:#166534;
}

.warning{
    background:#fef3c7;
    color:#92400e;
}

.danger{
    background:#fee2e2;
    color:#991b1b;
}

/* ================= APPLY BUTTON ================= */

.apply-btn{
    width:100%;
    margin-top:15px;
}

/* ================= EMPTY ================= */

.empty{
    background:white;
    padding:40px;
    border-radius:20px;
    text-align:center;
    box-shadow:0 5px 20px rgba(0,0,0,0.05);
}

.empty i{
    font-size:60px;
    color:#cbd5e1;
    margin-bottom:15px;
}

/* ================= MOBILE ================= */

@media(max-width:768px){

    .topbar{
        flex-direction:column;
        align-items:flex-start;
        gap:15px;
    }

    .container{
        width:95%;
    }
}

</style>
</head>

<body>

<div class="container">

<!-- ================= TOPBAR ================= -->

<div class="topbar">

<div>
<h1>
<i class="fa-solid fa-brain"></i>
Smart Research Fields Matching
</h1>

<p>
Apply to research topics using intelligent skill matching.
</p>
</div>

<div>
<a href="javascript:history.back()" class="btn">
<i class="fa-solid fa-arrow-left"></i>
Back
</a>
</div>

</div>

<!-- ================= ALERT ================= -->

<?php if($message != ""){ ?>

<div class="alert <?= $message_type ?>">
<?= $message ?>
</div>

<?php } ?>

<!-- ================= TOPICS ================= -->

<?php if($topics->num_rows > 0){ ?>

<div class="grid">

<?php while($t = $topics->fetch_assoc()){ ?>

<div class="card">

<div class="badge">
<i class="fa-solid fa-code"></i>
<?= htmlspecialchars($t['skills_required']) ?>
</div>

<h3>
<?= htmlspecialchars($t['title']) ?>
</h3>

<p>
<b>Required Skills:</b><br>
<?= htmlspecialchars($t['skills_required']) ?>
</p>


<!-- ================= COUNT SEAT ================= -->

<?php

    $seatInfo = $conn->query("
    SELECT COUNT(*) AS total
    FROM applications
    WHERE topic_id='{$t['topic_id']}'
    AND status='approved'
    ")->fetch_assoc();

    $remaining = $t['max_students'] - $seatInfo['total'];

    if($remaining < 0){
        $remaining = 0;
    }

    ?>

    <p>
    <b>Available Seats:</b>

    <?php
    if($remaining > 0){
        echo $remaining;
    }else{
        echo "Full";
    }
    ?>
    </p>

    <form method="POST">

    <input
    type="hidden"
    name="topic_id"
    value="<?= $t['topic_id'] ?>"
    >

    <?php if($remaining > 0){ ?>

    <button class="btn apply-btn" name="apply">

    <i class="fa-solid fa-paper-plane"></i>
    Apply Now

    </button>

    <?php } else { ?>

    <button
    class="btn apply-btn"
    disabled
    style="
    background:#94a3b8;
    cursor:not-allowed;
    ">

    <i class="fa-solid fa-ban"></i>
    Full

    </button>

    <?php } ?>

    </form>

</div>

<?php } ?>

</div>

<?php } else { ?>

<div class="empty">

<i class="fa-regular fa-folder-open"></i>

<h2>No Topics Available</h2>

<p>
No approved topics are available right now.
</p>

</div>

<?php } ?>

</div>

</body>
</html>