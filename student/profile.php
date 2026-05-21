<?php
if($_SESSION['role'] != 'student'){
    header("Location: ../login.php");
}

$user_id = $_SESSION['user_id'];

/* FETCH DATA */
$sql = "SELECT users.name, users.email, students.*
        FROM users
        LEFT JOIN students ON users.id = students.user_id
        WHERE users.id = '$user_id'";

$data = $conn->query($sql)->fetch_assoc();

/* UPDATE */
if(isset($_POST['update'])){
    $cgpa = $_POST['cgpa'];
    $skills = $_POST['skills'];
    $interests = $_POST['interests'];

    $check = $conn->query("SELECT * FROM students WHERE user_id='$user_id'");

    if($check->num_rows > 0){
        $conn->query("UPDATE students 
                      SET cgpa='$cgpa', skills='$skills', interests='$interests'
                      WHERE user_id='$user_id'");
    } else {
        $conn->query("INSERT INTO students (user_id, cgpa, skills, interests)
                      VALUES ('$user_id','$cgpa','$skills','$interests')");
    }

    echo "<script>
    window.location='dashboard.php?page=profile&success=1';
    </script>";
    exit();
}
?>

<?php if(isset($_GET['success'])){ ?>
<div class="success-msg">
    <i class="fa fa-check-circle"></i> Profile updated successfully!
</div>
<?php } ?>

<style>

/* MAIN WRAPPER */
.profile-wrapper{
    display:flex;
    gap:30px;
    flex-wrap:wrap;
}

/* PROFILE CARD */
.profile-view{
    flex:1;
    min-width:300px;
    background:white;
    color:#081c15;
    padding:25px;
    border-radius:12px;
    box-shadow:0 10px 25px rgba(0,0,0,0.3);
    text-align:center;
}

.profile-view i{
    font-size:60px;
    color:#1f54de;
    margin-bottom:10px;
}

.profile-view h3{
    margin:10px 0;
}

.profile-view p{
    margin:5px 0;
    font-size:14px;
}

/* EDIT CARD */
.profile-edit{
    flex:1;
    min-width:350px;
    background:white;
    color:#1f54de;
    padding:25px;
    border-radius:12px;
    box-shadow:0 10px 25px rgba(0,0,0,0.3);
}

/* TITLE */
.profile-edit h2{
    text-align:center;
    margin-bottom:20px;
    color:#1f54de;
}

/* INPUT GROUP */
.input-group{
    position:relative;
    margin-bottom:18px;
}

/* INPUT */
input, textarea{
    width:100%;
    padding:12px;
    border:1px solid #ccc;
    border-radius:6px;
    outline:none;
    transition:0.3s;
}

/* FOCUS EFFECT */
input:focus, textarea:focus{
    border-color:#1f54de;
    box-shadow:0 0 10px rgba(45,106,79,0.4);
}

/* BUTTON */
button{
    width:100%;
    padding:12px;
    border:none;
    border-radius:6px;
    background:#1f54de;
    color:white;
    font-size:15px;
    cursor:pointer;
    transition:0.3s;
}

button:hover{
    background:#1f54de;
    transform:scale(1.02);
}

/* SUCCESS MESSAGE */
.success-msg{
    background:#1f54de;
    color:white;
    padding:12px;
    border-radius:8px;
    margin-bottom:15px;
    text-align:center;
    animation:fadeIn 0.5s ease;
}

@keyframes fadeIn{
    from{opacity:0; transform:translateY(-10px);}
    to{opacity:1; transform:translateY(0);}
}

</style>

<div class="profile-wrapper">

<!-- PROFILE VIEW -->
<div class="profile-view">

<i class="fa fa-user-circle"></i>

<h3><?php echo $data['name']; ?></h3>
<p><?php echo $data['email']; ?></p>

<hr>

<p><b>CGPA:</b> <?php echo $data['cgpa'] ?: 'Not set'; ?></p>

<p><b>Skills:</b><br>
<?php echo $data['skills'] ?: 'Not added'; ?>
</p>

<p><b>Interests:</b><br>
<?php echo $data['interests'] ?: 'Not added'; ?>
</p>

</div>

<!-- EDIT FORM -->
<div class="profile-edit">

<h2><i class="fa fa-edit"></i> Edit Profile</h2>

<form method="POST">

<div class="input-group">
<input type="text" value="<?php echo $data['name']; ?>" disabled>
</div>

<div class="input-group">
<input type="email" value="<?php echo $data['email']; ?>" disabled>
</div>

<div class="input-group">
<input type="text" name="cgpa" placeholder="Enter CGPA"
value="<?php echo $data['cgpa']; ?>">
</div>

<div class="input-group">
<textarea name="skills" placeholder="Enter your skills"><?php echo $data['skills']; ?></textarea>
</div>

<div class="input-group">
<textarea name="interests" placeholder="Enter your interests"><?php echo $data['interests']; ?></textarea>
</div>

<button name="update">
<i class="fa fa-save"></i> Save Changes
</button>

</form>

</div>

</div>