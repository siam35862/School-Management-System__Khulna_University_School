<?php
session_start();

// Database connection
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = $_POST['user_name'];
    $user_password = $_POST['user_password'];

    // Query to check the admin login
    $sql = "SELECT * FROM admin_login WHERE user_name = '$user_name' AND user_password = '$user_password'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        // Fetch the admin data
        $admin_data = $result->fetch_assoc();
        
        // Successful login - store admin ID in session
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['adl_id'] = $admin_data['adl_id']; // Store the admin ID
        
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}

$conn->close();
?>

<!-- admin_login.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="nav.css">
    <link rel="stylesheet" href="footer.css">
    <link rel="stylesheet" href="admin_login.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef1f4;
            margin: 0;
            padding: 0;
        }

        .login-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 80vh;
        }

        .login-container {
            width: 350px;
            background-color: #fff;
            padding: 30px 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 5px;
            color: #444;
            font-weight: 500;
        }

        input[type="text"],
        input[type="password"] {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
        }

        .buttons {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }

        button {
            padding: 10px 20px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .submit-btn {
            background-color: #0275d8;
            color: white;
        }

        .reset-btn {
            background-color: #d9534f;
            color: white;
        }
    </style>
</head>

<body>
    <?php include('nav.php'); ?>

    <div class="login-wrapper">
        <div class="login-container">
            <h2>Admin Login</h2>

            <?php if (isset($error))
                echo "<p class='error'>$error</p>"; ?>

            <form action="admin_login.php" method="post">
                <label for="user_name">Username:</label>
                <input type="text" id="user_name" name="user_name" required>

                <label for="user_password">Password:</label>
                <input type="password" id="user_password" name="user_password" required>

                <div class="buttons">
                    <button type="reset" class="reset-btn">Reset</button>
                    <button type="submit" class="submit-btn">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <?php include('footer.php'); ?>
</body>

</html>