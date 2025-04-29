<?php
include 'db_connection.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['class_name']) && isset($_GET['group_']) && isset($_GET['academic_year'])) {
    $class_name = $_GET['class_name'];
    $group_ = $_GET['group_'];
    $academic_year = $_GET['academic_year'];
    
    // Debug: Check received values
    echo "Received class_name: $class_name<br>";
    echo "Received group_: $group_<br>";
    echo "Received academic_year: $academic_year<br>";
    
    // Query to get class_id from class table
    $class_sql = "SELECT class_id FROM class WHERE class_name = '$class_name' AND group_ = '$group_'";
    $class_result = $conn->query($class_sql);
    
    // Debug: Check the query result
    if ($class_result && $class_result->num_rows == 1) {
        echo "Class found<br>";
        $class_row = $class_result->fetch_assoc();
        $class_id = $class_row['class_id'];
        
        // Query to get students from the student table based on class_id AND academic_year
        $student_sql = "SELECT student_id FROM student WHERE class_id = '$class_id' AND academic_year = '$academic_year'";
        $student_result = $conn->query($student_sql);
        
        // Debug: Check if students exist
        if ($student_result && $student_result->num_rows > 0) {
            echo "Students found<br>";
            
            // Add styles for the dropdown
            echo "<option value=''>Select Student ID</option>";
            while ($row = $student_result->fetch_assoc()) {
                echo "<option value='{$row['student_id']}'>{$row['student_id']}</option>";
            }
        } else {
            echo "No students found for $class_name and $group_ in academic year $academic_year<br>";
        }
    } else {
        echo "No class found for $class_name and $group_<br>";
    }
} elseif (isset($_GET['class_name']) && isset($_GET['group_'])) {
    // This branch is to fetch academic years when only class and group are selected
    $class_name = $_GET['class_name'];
    $group_ = $_GET['group_'];
    
    // Get class_id first
    $class_sql = "SELECT class_id FROM class WHERE class_name = '$class_name' AND group_ = '$group_'";
    $class_result = $conn->query($class_sql);
    
    if ($class_result && $class_result->num_rows == 1) {
        $class_row = $class_result->fetch_assoc();
        $class_id = $class_row['class_id'];
        
        // Get distinct academic years for this class_id
        $year_sql = "SELECT DISTINCT academic_year FROM student WHERE class_id = '$class_id' ORDER BY academic_year DESC";
        $year_result = $conn->query($year_sql);
        
        if ($year_result && $year_result->num_rows > 0) {
            echo "<option value=''>Select Academic Year</option>";
            while ($row = $year_result->fetch_assoc()) {
                echo "<option value='{$row['academic_year']}'>{$row['academic_year']}</option>";
            }
        } else {
            echo "<option value=''>No academic years found</option>";
        }
    } else {
        echo "<option value=''>No class found</option>";
    }
}

$conn->close();
?>