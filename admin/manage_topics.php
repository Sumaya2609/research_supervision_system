<?php
session_start();
include "../db.php";

// approve
if(isset($_GET['approve'])){
    $id = $_GET['approve'];
    $conn->query("UPDATE topics SET status='approved' WHERE topic_id='$id'");
}

// reject
if(isset($_GET['reject'])){
    $id = $_GET['reject'];
    $conn->query("UPDATE topics SET status='rejected' WHERE topic_id='$id'");
}

$sql = "SELECT topics.*, users.name
        FROM topics
        JOIN faculty ON topics.faculty_id = faculty.faculty_id
        JOIN users ON faculty.user_id = users.id";

$result = $conn->query($sql);
?>

<h2>Manage Topics</h2>

<table border="1" cellpadding="10">
<tr>
<th>Title</th>
<th>Faculty</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php while($row = $result->fetch_assoc()){ ?>
<tr>
<td><?php echo $row['title']; ?></td>
<td><?php echo $row['name']; ?></td>
<td><?php echo $row['status']; ?></td>

<td>
<a href="?approve=<?php echo $row['topic_id']; ?>">Approve</a> |
<a href="?reject=<?php echo $row['topic_id']; ?>">Reject</a>
</td>
</tr>
<?php } ?>

</table>