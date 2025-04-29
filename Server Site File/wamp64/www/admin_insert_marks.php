<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['st_ID'], $_POST['subject_id'], $_POST['mark'])) {
    $st_ID = $_POST['st_ID'];
    $subject_id = $_POST['subject_id'];
    $mark = $_POST['mark'];
    
    // Begin transaction for insert/update
    $conn->begin_transaction();
    
    try {
        // Check if mark already exists
        $check_sql = "SELECT marks_id FROM marks WHERE st_ID = '$st_ID' AND subject_id = '$subject_id'";
        $check_result = $conn->query($check_sql);
        
        if ($check_result && $check_result->num_rows > 0) {
            // Update existing mark
            $update_sql = "UPDATE marks SET mark = '$mark' WHERE st_ID = '$st_ID' AND subject_id = '$subject_id'";
            $update_result = $conn->query($update_sql);
            
            if (!$update_result) {
                throw new Exception("Error updating mark: " . $conn->error);
            }
        } else {
            // Insert new mark
            $insert_sql = "INSERT INTO marks (mark, st_ID, subject_id) VALUES ('$mark', '$st_ID', '$subject_id')";
            $insert_result = $conn->query($insert_sql);
            
            if (!$insert_result) {
                throw new Exception("Error inserting mark: " . $conn->error);
            }
        }
        
        // If operation succeeded, commit the transaction
        $conn->commit();
        echo "<script>window.location.href = 'admin_dashboard.php';</script>";
    } catch (Exception $e) {
        // If operation failed, roll back the transaction
        $conn->rollback();
        echo "<script>alert('Error: " . $e->getMessage() . "'); window.location.href = 'admin_dashboard.php';</script>";
    }
} else {
    echo "<script>alert('Invalid data submitted. Please ensure you selected a subject and entered a mark.'); window.location.href = 'admin_dashboard.php';</script>";
}

$conn->close();
?>