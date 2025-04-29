<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    exit();
}

// Get admission_id from POST
$admission_id = isset($_POST['admission_id']) ? intval($_POST['admission_id']) : 0;

if ($admission_id <= 0) {
    exit();
}

// Connect to database
include 'db_connection.php';

// Update marks to 0
$update_query = "UPDATE admission_result SET marks = 0 WHERE admission_id = ?";
$stmt = $conn->prepare($update_query);

if ($stmt) {
    $stmt->bind_param("i", $admission_id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
?>
