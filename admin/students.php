<?php
session_start();
include "../db.php";

if($_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

// delete
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $conn->query("DELETE FROM users WHERE id='$id'");
}

$sql = "SELECT users.id, users.name, users.email, students.cgpa
        FROM users
        JOIN students ON users.id = students.user_id";

$result = $conn->query($sql);
?>

<h2>Students</h2>

<table border="1" cellpadding="10">
<tr>
<th>Name</th>
<th>Email</th>
<th>CGPA</th>
<th>Action</th>
</tr>

<?php while($row = $result->fetch_assoc()){ ?>
<tr>
<td><?php echo $row['name']; ?></td>
<td><?php echo $row['email']; ?></td>
<td><?php echo $row['cgpa']; ?></td>

<td>
<a href="view_student.php?id=<?php echo $row['id']; ?>">View</a> |
<a href="?delete=<?php echo $row['id']; ?>">Delete</a>
</td>
</tr>
<?php } ?>

</table>