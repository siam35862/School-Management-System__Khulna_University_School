<?php
// Database connection
include 'db_connection.php';

$class_name = $_GET['class_name'] ?? '';
$group = $_GET['group_'] ?? '';

if ($class_name && $group) {
    // Query to get distinct admission years for the selected class and group
    $sql = "SELECT DISTINCT admission_year FROM admission_form 
            WHERE class_id = (SELECT class_id FROM class WHERE class_name = '$class_name' AND group_ = '$group')
            ORDER BY admission_year DESC";
    
    $result = $conn->query($sql);
    
    echo "<option value=''>Select Admission Year</option>";
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<option value='{$row['admission_year']}'>{$row['admission_year']}</option>";
        }
    }
}

$conn->close();
?>