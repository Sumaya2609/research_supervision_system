```php
<?php
include "db.php";

// Get role from URL
$role = isset($_GET['role']) ? $_GET['role'] : '';

$message = "";

if(isset($_POST['register'])){

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = md5($_POST['password']);
    $role = $_POST['role'];

    /* ================= CHECK EMAIL ================= */

    $check = $conn->query("
    SELECT id
    FROM users
    WHERE email='$email'
    ");

    if($check->num_rows > 0){

        $message = "Email already exists! Please use another email.";

    }else{

        $sql = "
        INSERT INTO users
        (name, email, password, role)
        VALUES
        ('$name','$email','$password','$role')
        ";

        if($conn->query($sql)){

            // get last inserted user id
            $user_id = $conn->insert_id;

            /* ================= FACULTY ================= */

            if($role == 'faculty'){

                $conn->query("
                INSERT INTO faculty
                (user_id, department, designation)
                VALUES
                ('$user_id', '', '')
                ");
            }

            /* ================= STUDENT ================= */

            if($role == 'student'){

                $conn->query("
                INSERT INTO students
                (user_id, cgpa, skills, interests)
                VALUES
                ('$user_id', NULL, '', '')
                ");
            }

            echo "
            <script>
            alert('Registration Successful');
            window.location='login.php?role=$role';
            </script>
            ";

            exit();

        }else{

            $message = "Registration failed!";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Register</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

body{
    margin:0;
    font-family:Arial;
    background:linear-gradient(to right,#00113b,#dce6fe);
}

.container{
    width:350px;
    margin:80px auto;
    background:white;
    padding:25px;
    border-radius:14px;
    box-shadow:0 0 15px rgba(0,0,0,0.3);
}

h2{
    text-align:center;
    color:#1f54de;
    margin-bottom:20px;
}

input{
    width:100%;
    padding:12px;
    margin:10px 0;
    border:1px solid #cbd5e1;
    border-radius:8px;
    outline:none;
}

input:focus{
    border-color:#1f54de;
}

button{
    width:100%;
    padding:12px;
    background:#1f54de;
    color:white;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-size:15px;
    transition:0.3s;
}

button:hover{
    background:white;
    border:2px solid #1f54de;
    color:#1f54de;
}

.link{
    text-align:center;
    margin-top:15px;
}

.link a{
    color:#1f54de;
    text-decoration:none;
    font-weight:bold;
}

.error{
    background:#fee2e2;
    color:#dc2626;
    padding:12px;
    border-radius:8px;
    margin-bottom:15px;
    text-align:center;
    font-weight:600;
}

</style>

</head>

<body>

<div class="container">

<h2>

<i class="fa fa-user-plus"></i>

Register

</h2>

<?php if($message != ""){ ?>

<div class="error">

<?= $message; ?>

</div>

<?php } ?>

<form method="POST">

<input
type="text"
name="name"
placeholder="Full Name"
required>

<input
type="email"
name="email"
placeholder="Email"
required>

<input
type="password"
name="password"
placeholder="Password"
required>

<!-- Hidden role -->

<input
type="hidden"
name="role"
value="<?php echo $role; ?>">

<button name="register">

Register

</button>

</form>

<div class="link">

Already have an account?

<a href="login.php?role=<?php echo $role; ?>">

Login

</a>

</div>

</div>

</body>
</html>
```
