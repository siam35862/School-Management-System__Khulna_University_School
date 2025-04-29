<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
file_put_contents('debug.log', print_r($_POST, true), FILE_APPEND);

include 'db_connection.php';

if (isset($_POST['class_name']) && isset($_POST['group_'])) {
    $class_name = $_POST['class_name'];
    $group_ = $_POST['group_'];
    $year = date('Y');

    $class_sql = "SELECT class_id FROM class WHERE class_name = '$class_name' AND group_ = '$group_'";
    $class_result = $conn->query($class_sql);

    if ($class_result && $class_result->num_rows == 1) {
        $class_row = $class_result->fetch_assoc();
        $class_id = $class_row['class_id'];

        $student_sql = "SELECT student_id FROM student WHERE class_id = '$class_id' AND academic_year = '$year'";
        $student_result = $conn->query($student_sql);

        if ($student_result->num_rows > 0) {
            echo "<option value=''>Select Student</option>";
            while ($row = $student_result->fetch_assoc()) {
                echo "<option value='{$row['student_id']}'>{$row['student_id']}</option>";
            }
        } else {
            echo "<option value=''>No students found</option>";
        }
    } else {
        echo "<option value=''>Class not found</option>";
    }
} else {
    echo "<option value=''>Invalid request</option>";
}
?>
