<?php
session_start();
include "../db.php";

if(!isset($_SESSION['user_id'])){
    exit("not logged in");
}

$sender = $_SESSION['user_id'];
$receiver = $_POST['receiver_id'] ?? 0;
$message = trim($_POST['message'] ?? '');

if($receiver == 0 || $message == ''){
    exit("invalid");
}

/* get sender name */
$getUser = $conn->prepare("SELECT name FROM users WHERE id=?");
$getUser->bind_param("i", $sender);
$getUser->execute();
$userRes = $getUser->get_result()->fetch_assoc();

$senderName = $userRes['name'] ?? 'Someone';

/* INSERT MESSAGE */
$stmt = $conn->prepare("
INSERT INTO messages (sender_id, receiver_id, message, created_at)
VALUES (?, ?, ?, NOW())
");
$stmt->bind_param("iis", $sender, $receiver, $message);
$stmt->execute();

/* 🔔 INSERT NOTIFICATION (better message) */
$notifMsg = $senderName . " sent you a message";

$stmt2 = $conn->prepare("
INSERT INTO notifications (user_id, message, is_read, created_at)
VALUES (?, ?, 0, NOW())
");
$stmt2->bind_param("is", $receiver, $notifMsg);
$stmt2->execute();

echo "success";
?>