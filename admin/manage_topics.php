<?php
session_start();
include "../db.php";

/* ================= APPROVE ================= */

if(isset($_GET['approve'])){

    $id = $_GET['approve'];

    $conn->query("
    UPDATE topics
    SET status='approved'
    WHERE topic_id='$id'
    ");

    /* Notify Faculty */

    $topic = $conn->query("
    SELECT
    users.id as user_id,
    topics.title

    FROM topics

    JOIN faculty
    ON topics.faculty_id = faculty.faculty_id

    JOIN users
    ON faculty.user_id = users.id

    WHERE topics.topic_id='$id'
    ")->fetch_assoc();

    if($topic){

        $conn->query("
        INSERT INTO notifications
        (
            user_id,
            message,
            type
        )
        VALUES
        (
            '".$topic['user_id']."',
            'Your topic \"".$topic['title']."\" has been approved.',
            'topic'
        )
        ");
    }
}

/* ================= REJECT ================= */

if(isset($_GET['reject'])){

    $id = $_GET['reject'];

    $conn->query("
    UPDATE topics
    SET status='rejected'
    WHERE topic_id='$id'
    ");

    /* Notify Faculty */

    $topic = $conn->query("
    SELECT
    users.id as user_id,
    topics.title

    FROM topics

    JOIN faculty
    ON topics.faculty_id = faculty.faculty_id

    JOIN users
    ON faculty.user_id = users.id

    WHERE topics.topic_id='$id'
    ")->fetch_assoc();

    if($topic){

        $conn->query("
        INSERT INTO notifications
        (
            user_id,
            message,
            type
        )
        VALUES
        (
            '".$topic['user_id']."',
            'Your topic \"".$topic['title']."\" has been rejected.',
            'topic'
        )
        ");
    }
}

/* ================= TOPICS ================= */

$sql = "
SELECT
topics.*,
users.name

FROM topics

JOIN faculty
ON topics.faculty_id = faculty.faculty_id

JOIN users
ON faculty.user_id = users.id

ORDER BY topics.topic_id DESC
";

$result = $conn->query($sql);
?>

<style>

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#2563eb;
    color:white;
    padding:12px;
}

td{
    padding:12px;
    border:1px solid #ddd;
}

.action-col{
    width:140px;
    text-align:center;
    white-space:nowrap;
}

.approve-btn{
    color:green;
    text-decoration:none;
    font-weight:bold;
}

.reject-btn{
    color:red;
    text-decoration:none;
    font-weight:bold;
}

</style>

<h2>Manage Topics</h2>

<table>

<tr>
<th>Title</th>
<th>Faculty</th>
<th>Created Date</th>
<th>Max Students</th>
<th>Status</th>
<th class="action-col">Action</th>
</tr>

<?php while($row = $result->fetch_assoc()){ ?>

<tr>

<td><?= htmlspecialchars($row['title']) ?></td>

<td><?= htmlspecialchars($row['name']) ?></td>

<td><?= $row['deadline'] ?></td>

<td><?= $row['max_students'] ?></td>

<td><?= ucfirst($row['status']) ?></td>

<td class="action-col">

<a
class="approve-btn"
href="?approve=<?= $row['topic_id'] ?>">
✔ Approve
</a>

|

<a
class="reject-btn"
href="?reject=<?= $row['topic_id'] ?>">
✖ Reject
</a>
</td>

</tr>

<?php } ?>

</table>