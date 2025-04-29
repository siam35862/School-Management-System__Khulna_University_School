<?php
include 'db_connection.php';

$id = $_POST['id'];
$school_name = $_POST['school_name'];
$established_year = $_POST['established_year'];
$short_history = $_POST['short_history'];
$full_history = $_POST['full_history'];
$address = $_POST['address'];

$imagePath = '';
if (!empty($_FILES['image']['name'])) {
    $targetDir = "uploads/";
    $imagePath = $targetDir . basename($_FILES["image"]["name"]);
    move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath);
}

$sql = "UPDATE school_info SET school_name='$school_name', established_year='$established_year',
        short_history='$short_history', full_history='$full_history', address='$address'" .
        ($imagePath ? ", image='$imagePath'" : "") . " WHERE id=$id";

$conn->query($sql);
$conn->close();
?>
