<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit();
}

// Get parameters
$admission_id = isset($_GET['admission_id']) ? intval($_GET['admission_id']) : 0;
$class_name = isset($_GET['class_name']) ? $_GET['class_name'] : '';
$group = isset($_GET['group_']) ? $_GET['group_'] : '';

// Validate admission_id
if ($admission_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid admission ID"]);
    exit();
}

// Connect to database
include 'db_connection.php';

// Update the marks to 0 for the specified admission_id
$update_query = "UPDATE admission_result SET marks = 0 WHERE admission_id = ?";
$stmt = $conn->prepare($update_query);

if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Error preparing statement: " . $conn->error]);
    exit();
}

$stmt->bind_param("i", $admission_id);
$result = $stmt->execute();

$stmt->close();
$conn->close();

// Redirect to the admin dashboard
header("Location: admin_dashboard.php");
exit();
?>
