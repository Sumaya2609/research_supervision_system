<?php
$conn = new mysqli("localhost", "root", "", "research_system");

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}
?>