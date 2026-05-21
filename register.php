<?php
include "db.php";

// Get role from URL
$role = isset($_GET['role']) ? $_GET['role'] : '';

if(isset($_POST['register'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    $role = $_POST['role'];

    $sql = "INSERT INTO users (name, email, password, role)
            VALUES ('$name','$email','$password','$role')";

    if($conn->query($sql)){
        
        // get last inserted user id
        $user_id = $conn->insert_id;

        // insert into faculty table
        if($role == 'faculty'){
            $conn->query("INSERT INTO faculty (user_id, department, designation) 
                          VALUES ('$user_id', '', '')");
        }

        // insert into student table
        if($role == 'student'){
            $conn->query("INSERT INTO students (user_id, cgpa, skills, interests) 
                          VALUES ('$user_id', NULL, '', '')");
        }

        echo "<script>alert('Registration Successful'); window.location='login.php?role=$role';</script>";
    } else {
        echo "Error: ".$conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Register</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body {
    margin: 0;
    font-family: Arial;
    background: linear-gradient(to right, #00113b, #dce6fe);
}

.container {
    width: 350px;
    margin: 80px auto;
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0,0,0,0.3);
}

h2 {
    text-align: center;
    color: #1f54de;
}

input {
    width: 93%;
    padding: 10px;
    margin: 8px 0;
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
    border: solid 2px;
    border-color: #1f54de;
    color: #1f54de;
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

.link a {
    color: red;
    text-decoration: none;
    font-weight: bold;
}

</style>
</head>

<body>

<div class="container">
<h2><i class="fa fa-user-plus"></i> Register</h2>

<form method="POST">

<input type="text" name="name" placeholder="Full Name" required>
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>

<!-- Hidden role -->
<input type="hidden" name="role" value="<?php echo $role; ?>">

<button name="register">Register</button>
</form>

<div class="link">
    Already have an account? 
    <a href="login.php?role=<?php echo $role; ?>">Login</a>
</div>

</div>

</body>
</html>