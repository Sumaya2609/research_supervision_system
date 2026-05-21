<?php
session_start();
include "../db.php";

$user_id = $_SESSION['user_id'];

/* get ids */
$stu = $conn->query("SELECT student_id FROM students WHERE user_id='$user_id'")->fetch_assoc();
$student_id = $stu['student_id'];

if(isset($_POST['rate'])){
    $faculty_id = $_POST['faculty_id'];
    $rating = $_POST['rating'];
    $feedback = $_POST['feedback'];

    $conn->query("
    INSERT INTO ratings(student_id,faculty_id,rating,feedback)
    VALUES('$student_id','$faculty_id','$rating','$feedback')
    ");
}
?>

<h2>Rate Faculty</h2>

<form method="POST">

<input type="number" name="faculty_id" placeholder="Faculty ID" required><br><br>

<select name="rating">
<option>5</option>
<option>4</option>
<option>3</option>
<option>2</option>
<option>1</option>
</select><br><br>

<textarea name="feedback" placeholder="Feedback"></textarea><br><br>

<button name="rate">Submit</button>

</form>