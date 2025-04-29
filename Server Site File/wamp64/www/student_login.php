<?php
session_start();
include('nav.php'); // Call the navigation header
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Login</title>
    <link rel="stylesheet" href="nav.css">
    <link rel="stylesheet" href="footer.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
        }

        .login-box {
            width: 300px;
            padding: 20px 25px;
            margin: auto;
            background: #fff;
            text-align: center;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .login-box h2 {
            margin-bottom: 15px;
            font-size: 20px;
            color: #004080;
        }

        .login-box label {
            font-size: 14px;
            font-weight: bold;
            display: block;
            text-align: left;
            margin-top: 10px;
            color: #333;
        }

        .login-box input[type="text"],
        .login-box input[type="password"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
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

        footer {
            margin-top: auto;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Student Login</h2>
        <form action="student_login_authenticate.php" method="POST">
            <label>Username:</label>
            <input type="text" name="username" required>

            <label>Password:</label>
            <input type="password" name="password" required>
            <div class="buttons">
            <button type="reset" class="reset-btn">Reset</button>
            <button type="submit" class="submit-btn">Submit</button>
        </div>
           
        </form>
       
       
        
    </div>

    <?php include('footer.php'); ?>  <!-- Footer -->
</body>
</html>
