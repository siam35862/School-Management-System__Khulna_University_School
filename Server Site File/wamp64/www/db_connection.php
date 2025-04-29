<?php
// db_connection.php

$servername = "localhost";
$username = "root";
$password = "";
$database = "school_management_system";

try {
    $conn = new mysqli($servername, $username, $password, $database);
} catch (mysqli_sql_exception $e) {
    // Show a proper HTML page with center message
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Database Connection Error</title>
        <style>
            body {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                background-color: #f8d7da;
                font-family: Arial, sans-serif;
            }
            .message-box {
                text-align: center;
                background: white;
                padding: 30px;
                border: 2px solid #f5c2c7;
                border-radius: 10px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
            .message-box h1 {
                color: #721c24;
                margin-bottom: 10px;
            }
            .message-box p {
                color: #721c24;
                font-size: 18px;
            }
        </style>
    </head>
    <body>
        <div class='message-box'>
            <h1>Database Not Found</h1>
            <p>Please contact the administrator.</p>
        </div>
    </body>
    </html>";
    exit();
}
?>
