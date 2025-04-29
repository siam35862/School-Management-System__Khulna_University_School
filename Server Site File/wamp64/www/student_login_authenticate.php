<?php
session_start();

include('nav.php'); // Call the navigation header

// Database connection
include 'db_connection.php';

// Get the submitted login details
$username = $_POST['username'];
$password = $_POST['password'];

// Query to check if the user exists in the student_login table
$sql = "SELECT * FROM student_login WHERE user_name = '$username' AND user_password = '$password'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $_SESSION['st_ID'] = $row['st_ID']; // Store the student ID in session
    $_SESSION['username'] = $username; // Store the username in session
    $_SESSION['password'] = $password; // Store the password (optional)
    header('Location: student.php');  // Redirect to student profile page
    exit();
} else {
    // If login failed, show error message
    echo "<div class='error-message' style='color: red; text-align: center;'>Incorrect username or password!</div>";
    echo "<br><a href='student_login.php' style='text-align: center; display: block;'>Go back</a>";
}

include('footer.php'); // Footer
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Profile</title>
    <link rel="stylesheet" href="nav.css">
    <link rel="stylesheet" href="footer.css">
</head>
</html>