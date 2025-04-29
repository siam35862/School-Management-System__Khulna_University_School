<?php
include 'db_connection.php';

if (isset($_GET['student_id'], $_GET['class_name'], $_GET['group_'], $_GET['academic_year'])) {
    $student_id = $_GET['student_id'];
    $class_name = $_GET['class_name'];
    $group_ = $_GET['group_'];
    $academic_year = $_GET['academic_year'];
    
    // Get class_id first
    $class_sql = "SELECT class_id FROM class WHERE class_name = '$class_name' AND group_ = '$group_'";
    $class_result = $conn->query($class_sql);
    
    if ($class_result && $class_result->num_rows == 1) {
        $class_id = $class_result->fetch_assoc()['class_id'];
        
        // Now get st_ID
        $st_sql = "SELECT st_ID FROM student WHERE student_id = '$student_id' AND class_id = '$class_id' AND academic_year = '$academic_year'";
        $st_result = $conn->query($st_sql);
        
        if ($st_result && $st_result->num_rows == 1) {
            $st_ID = $st_result->fetch_assoc()['st_ID'];
            echo $st_ID;
        } else {
            echo "Error: Student not found";
        }
    } else {
        echo "Error: Class not found";
    }
} else {
    echo "Error: Missing parameters";
}
?>