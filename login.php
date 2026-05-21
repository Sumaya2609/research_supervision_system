<?php
session_start();
include "db.php";

// Get role from URL
$role = isset($_GET['role']) ? $_GET['role'] : '';

if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = md5($_POST['password']);

    $sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $result = $conn->query($sql);

    if($result->num_rows > 0){
        $user = $result->fetch_assoc();

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        if($user['role'] == 'admin'){
            header("Location: admin/dashboard.php");
        } elseif($user['role'] == 'faculty'){
            header("Location: faculty/dashboard.php");
        } else {
            header("Location: student/dashboard.php");
        }
    } else {
        echo "<script>alert('Invalid Login');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body {
    margin: 0;
    font-family: Arial;
    background: linear-gradient(to right, #00113b, #dce6fe);
}

.container {
    width: 350px;
    margin: 100px auto;
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0,0,0,0.3);
}

h2 {
    text-align: center;
    color: #1f54de;
}

.role {
    text-align: center;
    color: #1f54de;
    margin-bottom: 10px;
    font-weight: bold;
}

input {
    width: 94%;
    padding: 10px;
    margin: 8px 0;
    border: 1px solid #ccc;
}

button {
    width: 100%;
    padding: 10px;
    background: #1f54de;
    color: white;
    border: none;
    cursor: pointer;
}

button:hover {
    background: #fff;
    color: #1f54de;
    border: solid 2px;
    border-color: #1f54de;
}

.link {
    text-align: center;
    margin-top: 10px;
}

.link a {
    color: #1f54de;
    text-decoration: none;
    font-weight: bold;
}
.link a:hover {
    color: red;
    text-decoration: none;
    font-weight: bold;
}

</style>
</head>

<body>

<div class="container">
<h2><i class="fa fa-sign-in-alt"></i> Login</h2>

<div class="role">
<?php echo strtoupper($role); ?> PANEL
</div>

<form method="POST">
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>

<button name="login">Login</button>
</form>

<div class="link">
    Not registered? 
    <a href="register.php?role=<?php echo $role; ?>">Sign up</a>
</div>

</div>

</body>
</html>