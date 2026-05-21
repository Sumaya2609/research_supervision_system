<?php
session_start();
include "../db.php";

if(!isset($_SESSION['user_id'])){
    exit();
}

$user_id = $_SESSION['user_id'];

$result = $conn->query("
SELECT COUNT(*) as total
FROM notifications
WHERE user_id='$user_id'
AND is_read=0
");

$row = $result->fetch_assoc();

echo $row['total'];
?>