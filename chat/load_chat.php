<?php
session_start();

include "../db.php";

if(!isset($_SESSION['user_id'])){
    exit("not logged in");
}

$me = $_SESSION['user_id'];

$other = $_GET['user_id'] ?? 0;

if($other == 0){
    exit("invalid");
}

/* LOAD CHAT */

$stmt = $conn->prepare("
SELECT sender_id, message, created_at
FROM messages
WHERE
(sender_id = ? AND receiver_id = ?)
OR
(sender_id = ? AND receiver_id = ?)
ORDER BY created_at ASC
");

$stmt->bind_param("iiii", $me, $other, $other, $me);

$stmt->execute();

$result = $stmt->get_result();

/* DISPLAY CHAT */

while($row = $result->fetch_assoc()){

    $msg = htmlspecialchars($row['message']);

    if($row['sender_id'] == $me){

        echo "
        <div class='msg me'>
            $msg
        </div>
        ";

    }else{

        echo "
        <div class='msg them'>
            $msg
        </div>
        ";
    }
}
?>