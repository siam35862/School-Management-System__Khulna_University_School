<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: index.php");
    exit();
}

//database connection
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $adl_id = $_POST['adl_id'];
    $user_name = $_POST['user_name'];
    $user_password = $_POST['user_password'];
    
    // Check if the username already exists for another admin
    $check_sql = "SELECT * FROM admin_login WHERE user_name = ? AND adl_id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $user_name, $adl_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Username already exists
        echo "username_exists";
    } else {
        // Username is available, proceed with update
        $update_sql = "UPDATE admin_login SET user_name = ?, user_password = ? WHERE adl_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssi", $user_name, $user_password, $adl_id);
        
        if ($update_stmt->execute()) {
            echo "success";
        } else {
            echo "error";
        }
    }
    
    $check_stmt->close();
    $conn->close();
}
?>