<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../db.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student'){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ================= AJAX REQUEST ================= */

if(isset($_GET['fetch'])){

    $stmt = $conn->prepare("
    SELECT *
    FROM notifications
    WHERE user_id=?
    ORDER BY created_at DESC
    ");

    $stmt->bind_param("i",$user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    $notifications = [];

    while($row = $result->fetch_assoc()){
        $notifications[] = $row;
    }

    /* UNREAD COUNT */

    $countQuery = $conn->query("
    SELECT COUNT(*) as total
    FROM notifications
    WHERE user_id='$user_id'
    AND is_read=0
    ");

    $count = $countQuery->fetch_assoc()['total'];

    echo json_encode([
        'notifications' => $notifications,
        'count' => $count
    ]);

    exit();
}

/* ================= MARK AS READ ================= */

$conn->query("
UPDATE notifications
SET is_read=1
WHERE user_id='$user_id'
");
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Notifications</title>

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

/* ================= TOPBAR ================= */

.topbar{
    width:100%;
    background:white;
    padding:20px 30px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 4px 15px rgba(0,0,0,0.05);
    position:sticky;
    top:0;
    z-index:100;
}

.topbar h2{
    font-size:24px;
}

/* ================= BELL ================= */

.bell-area{
    position:relative;
}

.bell-btn{
    width:50px;
    height:50px;
    border-radius:50%;
    border:none;
    background:#2563eb;
    color:white;
    font-size:20px;
    cursor:pointer;
    transition:0.3s;
    position:relative;
}

.bell-btn:hover{
    background:#1d4ed8;
    transform:scale(1.05);
}

.badge{
    position:absolute;
    top:-5px;
    right:-5px;
    background:red;
    color:white;
    width:22px;
    height:22px;
    border-radius:50%;
    font-size:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:bold;
}

/* ================= CONTAINER ================= */

.container{
    width:90%;
    max-width:900px;
    margin:30px auto;
}

/* ================= CARD ================= */

.notification-card{
    background:white;
    padding:20px;
    border-radius:18px;
    margin-bottom:18px;
    box-shadow:0 5px 15px rgba(0,0,0,0.06);
    transition:0.3s;
    border-left:5px solid transparent;
}

.notification-card:hover{
    transform:translateY(-3px);
}

.unread{
    border-left:5px solid #f59e0b;
}

.notification-title{
    font-size:17px;
    font-weight:600;
    margin-bottom:8px;
}

.notification-message{
    color:#475569;
    line-height:1.6;
}

.time{
    margin-top:12px;
    font-size:13px;
    color:#94a3b8;
}

/* ================= EMPTY ================= */

.empty{
    background:white;
    padding:40px;
    text-align:center;
    border-radius:18px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}

.empty i{
    font-size:60px;
    color:#cbd5e1;
    margin-bottom:15px;
}

.empty p{
    color:#64748b;
}

/* ================= BACK BUTTON ================= */

.back-btn{
    background:#2563eb;
    color:white;
    text-decoration:none;
    padding:12px 18px;
    border-radius:10px;
    transition:0.3s;
}

.back-btn:hover{
    background:#1d4ed8;
}

/* ================= MOBILE ================= */

@media(max-width:768px){

    .topbar{
        flex-direction:column;
        gap:15px;
        align-items:flex-start;
    }

    .container{
        width:95%;
    }
}

</style>
</head>

<body>

<!-- ================= TOPBAR ================= -->

<div class="topbar">

<div>
<h2>
<i class="fa-solid fa-bell"></i>
Notifications
</h2>
</div>

<div style="display:flex;align-items:center;gap:15px;">

<a href="javascript:history.back()" class="back-btn">
<i class="fa-solid fa-arrow-left"></i>
Back
</a>

<div class="bell-area">

<button class="bell-btn" id="bellButton">

<i class="fa-solid fa-bell"></i>

<span class="badge" id="notificationCount">
0
</span>

</button>

</div>

</div>

</div>

<!-- ================= CONTAINER ================= -->

<div class="container">

<div id="notificationContainer"></div>

</div>

<!-- ================= AJAX ================= -->

<script>

function fetchNotifications(){

    fetch('notifications.php?fetch=1')

    .then(response => response.json())

    .then(data => {

        let container =
        document.getElementById('notificationContainer');

        let count =
        document.getElementById('notificationCount');

        count.innerText = data.count;

        if(data.count <= 0){
            count.style.display = 'none';
        }else{
            count.style.display = 'flex';
        }

        container.innerHTML = '';

        if(data.notifications.length === 0){

            container.innerHTML = `
            <div class="empty">

            <i class="fa-regular fa-bell-slash"></i>

            <h3>No Notifications</h3>

            <p>
            You currently have no notifications.
            </p>

            </div>
            `;

            return;
        }

        data.notifications.forEach(item => {

            container.innerHTML += `

            <div class="notification-card
            ${item.is_read == 0 ? 'unread' : ''}">

            <div class="notification-title">
            ${capitalize(item.type)}
            </div>

            <div class="notification-message">
            ${item.message}
            </div>

            <div class="time">
            <i class="fa-regular fa-clock"></i>
            ${item.created_at}
            </div>

            </div>

            `;
        });
    });
}

/* ================= CAPITALIZE ================= */

function capitalize(text){
    return text.charAt(0).toUpperCase() + text.slice(1);
}

/* ================= AUTO REFRESH ================= */

fetchNotifications();

setInterval(fetchNotifications, 5000);

</script>

</body>
</html>