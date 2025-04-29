<?php
include 'db_connection.php';

$id = $_POST['id'];
$role = $_POST['role'];
$name = $_POST['name'];
$title = $_POST['title'];
$message = $_POST['message'];

$imagePath = '';
if (!empty($_FILES['image']['name'])) {
    $targetDir = "uploads/";
    $imagePath = $targetDir . basename($_FILES["image"]["name"]);
    move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath);
}

$sql = "UPDATE messages SET role='$role', name='$name', title='$title', message='$message'" .
        ($imagePath ? ", image='$imagePath'" : "") .
        ", created_at=NOW() WHERE id=$id";

$conn->query($sql);
$conn->close();
?>
