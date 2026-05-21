<?php
session_start();
include "../db.php";

if($_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

$sql = "SELECT users.id, users.name, users.email, faculty.department
        FROM users
        JOIN faculty ON users.id = faculty.user_id";

$result = $conn->query($sql);
?>

<h2>Faculty</h2>

<table border="1" cellpadding="10">
<tr>
<th>Name</th>
<th>Email</th>
<th>Department</th>
</tr>

<?php while($row = $result->fetch_assoc()){ ?>
<tr>
<td><?php echo $row['name']; ?></td>
<td><?php echo $row['email']; ?></td>
<td><?php echo $row['department']; ?></td>
</tr>
<?php } ?>

</table>