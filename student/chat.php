<?php
session_start();
include "../db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ================= MATCHED FACULTY ================= */

$users = $conn->query("
SELECT DISTINCT users.id, users.name
FROM applications
JOIN topics ON applications.topic_id = topics.topic_id
JOIN faculty ON topics.faculty_id = faculty.faculty_id
JOIN users ON faculty.user_id = users.id
JOIN students ON applications.student_id = students.student_id
WHERE students.user_id='$user_id'
AND applications.status='approved'
");
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Student Chat</title>

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
    overflow:hidden;
}

/* ================= MAIN CONTAINER ================= */

.container{
    display:flex;
    height:100vh;
}

/* ================= SIDEBAR ================= */

.users{
    width:320px;
    background:white;
    border-right:1px solid #e2e8f0;
    display:flex;
    flex-direction:column;
    box-shadow:5px 0 20px rgba(0,0,0,0.04);
}

/* ================= SIDEBAR HEADER ================= */

.users-header{
    padding:22px;
    background:#2563eb;
    color:white;
    display:flex;
    align-items:center;
    justify-content:space-between;
}

.users-header h2{
    font-size:22px;
}

.back-btn{
    background:rgba(255,255,255,0.2);
    border:none;
    color:white;
    width:40px;
    height:40px;
    border-radius:12px;
    cursor:pointer;
    transition:0.3s;
}

.back-btn:hover{
    background:rgba(255,255,255,0.3);
}

/* ================= SEARCH ================= */

.search-box{
    padding:18px;
    border-bottom:1px solid #f1f5f9;
}

.search-box input{
    width:100%;
    padding:13px 15px;
    border:none;
    background:#f1f5f9;
    border-radius:12px;
    outline:none;
    font-size:14px;
}

/* ================= USER LIST ================= */

.user-list{
    flex:1;
    overflow-y:auto;
}

/* ================= USER BOX ================= */

.user-box{
    display:flex;
    align-items:center;
    gap:14px;
    padding:16px 20px;
    cursor:pointer;
    transition:0.3s;
    border-bottom:1px solid #f8fafc;
}

.user-box:hover{
    background:#eff6ff;
}

.user-box.active{
    background:#dbeafe;
}

/* ================= AVATAR ================= */

.avatar{
    width:52px;
    height:52px;
    border-radius:50%;
    background:#2563eb;
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:bold;
    font-size:18px;
    box-shadow:0 5px 15px rgba(37,99,235,0.25);
}

.user-info h4{
    color:#1e293b;
    margin-bottom:4px;
}

.user-info p{
    color:#64748b;
    font-size:13px;
}

/* ================= CHAT AREA ================= */

.chat-area{
    flex:1;
    display:flex;
    flex-direction:column;
    background:#eef2ff;
}

/* ================= CHAT HEADER ================= */

.chat-header{
    background:white;
    padding:18px 25px;
    display:flex;
    align-items:center;
    gap:15px;
    box-shadow:0 2px 10px rgba(0,0,0,0.04);
    z-index:10;
}

.chat-avatar{
    width:50px;
    height:50px;
    border-radius:50%;
    background:#2563eb;
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:bold;
    font-size:18px;
}

.chat-user-info h3{
    color:#1e293b;
}

.chat-user-info p{
    color:#10b981;
    font-size:13px;
}

/* ================= MESSAGES ================= */

.messages{
    flex:1;
    overflow-y:auto;
    padding:25px;
    display:flex;
    flex-direction:column;
    gap:12px;
}

/* ================= MESSAGE ================= */

.msg{
    max-width:65%;
    padding:14px 18px;
    border-radius:18px;
    line-height:1.5;
    font-size:14px;
    box-shadow:0 4px 12px rgba(0,0,0,0.06);
    animation:fadeIn 0.2s ease;
}

.me{
    background:#2563eb;
    color:white;
    align-self:flex-end;
    border-bottom-right-radius:5px;
}

.them{
    background:white;
    color:#1e293b;
    align-self:flex-start;
    border-bottom-left-radius:5px;
}

@keyframes fadeIn{
    from{
        opacity:0;
        transform:translateY(10px);
    }
    to{
        opacity:1;
        transform:translateY(0);
    }
}

/* ================= INPUT AREA ================= */

.input-box{
    background:white;
    padding:18px 20px;
    display:flex;
    align-items:center;
    gap:15px;
    box-shadow:0 -2px 10px rgba(0,0,0,0.04);
}

.input-box input{
    flex:1;
    border:none;
    background:#f1f5f9;
    padding:15px 18px;
    border-radius:16px;
    outline:none;
    font-size:14px;
}

.send-btn{
    width:55px;
    height:55px;
    border:none;
    border-radius:16px;
    background:#2563eb;
    color:white;
    font-size:18px;
    cursor:pointer;
    transition:0.3s;
    box-shadow:0 5px 15px rgba(37,99,235,0.25);
}

.send-btn:hover{
    background:#1d4ed8;
    transform:scale(1.05);
}

/* ================= EMPTY ================= */

.empty-chat{
    margin:auto;
    text-align:center;
    color:#94a3b8;
}

.empty-chat i{
    font-size:80px;
    margin-bottom:20px;
}

/* ================= MOBILE ================= */

@media(max-width:900px){

    .users{
        width:100px;
    }

    .user-info,
    .search-box{
        display:none;
    }

    .chat-user-info p{
        display:none;
    }
}

@media(max-width:700px){

    .container{
        flex-direction:column;
    }

    .users{
        width:100%;
        height:200px;
    }

    .chat-area{
        height:calc(100vh - 200px);
    }
}

</style>

</head>

<body>

<div class="container">

<!-- ================= SIDEBAR ================= -->

<div class="users">

<div class="users-header">

<h2>
<i class="fa-solid fa-comments"></i>
Chats
</h2>

<button class="back-btn"
onclick="history.back()">

<i class="fa-solid fa-arrow-left"></i>

</button>

</div>

<!-- SEARCH -->

<!-- <div class="search-box">

<input
type="text"
id="searchUser"
placeholder="Search faculty..."
onkeyup="filterUsers()"
>

</div> -->

<!-- USER LIST -->

<div class="user-list" id="userList">

<?php while($u = $users->fetch_assoc()){ ?>

<div class="user-box"

data-name="<?php echo strtolower($u['name']); ?>"

onclick="openChat(
<?php echo $u['id']; ?>,
'<?php echo htmlspecialchars($u['name']); ?>',
this
)">

<div class="avatar">
<?php echo strtoupper(substr($u['name'],0,1)); ?>
</div>

<div class="user-info">

<h4>
<?php echo htmlspecialchars($u['name']); ?>
</h4>

<p>Faculty Member</p>

</div>

</div>

<?php } ?>

</div>

</div>

<!-- ================= CHAT AREA ================= -->

<div class="chat-area">

<!-- HEADER -->

<div class="chat-header">

<div class="chat-avatar" id="chatAvatar">
?
</div>

<div class="chat-user-info">

<h3 id="chatName">
Select a Faculty
</h3>



</div>

</div>

<!-- CHAT -->

<div class="messages" id="chatBox">

<div class="empty-chat">

<i class="fa-regular fa-comments"></i>

<h2>No Conversation Selected</h2>

<p>
Choose a faculty member to start chatting.
</p>

</div>

</div>

<!-- INPUT -->

<div class="input-box">

<input
type="text"
id="msg"
placeholder="Type your message..."
onkeypress="handleEnter(event)"
>

<button class="send-btn"
onclick="sendMsg()">

<i class="fa-solid fa-paper-plane"></i>

</button>

</div>

</div>

</div>

<script>

let receiver = 0;

/* ================= OPEN CHAT ================= */

function openChat(id,name,el){

    receiver = id;

    document.getElementById("chatName").innerText = name;

    document.getElementById("chatAvatar")
    .innerText = name.charAt(0).toUpperCase();

    document.querySelectorAll(".user-box")
    .forEach(u => u.classList.remove("active"));

    el.classList.add("active");

    loadChat();
}

/* ================= LOAD CHAT ================= */

function loadChat(){

    if(receiver == 0) return;

    fetch("../chat/load_chat.php?user_id=" + receiver)

    .then(res => res.text())

    .then(data => {

        let box = document.getElementById("chatBox");

        box.innerHTML = data;

        box.scrollTop = box.scrollHeight;
    });
}

/* ================= SEND MESSAGE ================= */

function sendMsg(){

    let msg = document.getElementById("msg").value;

    if(msg.trim() == "" || receiver == 0){
        return;
    }

    fetch("../chat/send_message.php",{

        method:"POST",

        headers:{
            "Content-Type":
            "application/x-www-form-urlencoded"
        },

        body:
        "message=" +
        encodeURIComponent(msg) +
        "&receiver_id=" +
        receiver

    })

    .then(res => res.text())

    .then(() => {

        document.getElementById("msg").value = "";

        loadChat();
    });
}

/* ================= ENTER KEY ================= */

function handleEnter(e){

    if(e.key === "Enter"){
        sendMsg();
    }
}

/* ================= AUTO REFRESH ================= */

setInterval(loadChat, 100000);

/* ================= SEARCH USERS ================= */

function filterUsers(){

    let input =
    document.getElementById("searchUser")
    .value.toLowerCase();

    let users =
    document.querySelectorAll(".user-box");

    users.forEach(user => {

        let name =
        user.getAttribute("data-name");

        if(name.includes(input)){
            user.style.display = "flex";
        }else{
            user.style.display = "none";
        }
    });
}

</script>

</body>
</html>