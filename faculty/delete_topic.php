<?php
session_start();
include "../db.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'faculty'){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$id = intval($_GET['id']);

$conn->query("
DELETE topics
FROM topics
JOIN faculty
ON topics.faculty_id = faculty.faculty_id
WHERE topics.topic_id='$id'
AND faculty.user_id='$user_id'
");

header("Location: dashboard.php?page=create_topic");
exit();
?>