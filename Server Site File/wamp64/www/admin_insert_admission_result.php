<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo "Unauthorized access";
    exit();
}

// Database connection
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admission_id = $_POST['applicant_id'] ?? '';
    $marks = $_POST['marks'] ?? '';
    
    if (!$admission_id || !$marks) {
        echo "Required fields are missing";
        exit();
    }
    
    // Check if a record already exists
    $check_sql = "SELECT ar_id FROM admission_result WHERE admission_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $admission_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Update existing record
        $update_sql = "UPDATE admission_result SET marks = ? WHERE admission_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("di", $marks, $admission_id);
        
        if ($update_stmt->execute()) {
            echo "Admission result updated successfully";
        } else {
            echo "Error updating admission result: " . $conn->error;
        }
        $update_stmt->close();
    } else {
        // Insert new record
        $insert_sql = "INSERT INTO admission_result (admission_id, marks) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("id", $admission_id, $marks);
        
        if ($insert_stmt->execute()) {
            echo "Admission result added successfully";
        } else {
            echo "Error adding admission result: " . $conn->error;
        }
        $insert_stmt->close();
    }
    
    $check_stmt->close();
}

$conn->close();
?>