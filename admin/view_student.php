<?php
include "../db.php";

$id = $_GET['id'];

$sql = "SELECT users.*, students.*
        FROM users
        JOIN students ON users.id = students.user_id
        WHERE users.id='$id'";

$data = $conn->query($sql)->fetch_assoc();
?>

<h2>Student Details</h2>

<p>Name: <?php echo $data['name']; ?></p>
<p>Email: <?php echo $data['email']; ?></p>
<p>CGPA: <?php echo $data['cgpa']; ?></p>
<p>Skills: <?php echo $data['skills']; ?></p>
<p>Interests: <?php echo $data['interests']; ?></p>