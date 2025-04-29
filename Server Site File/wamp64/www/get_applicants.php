<?php
// Database connection
include 'db_connection.php';

$class_name = $_GET['class_name'] ?? '';
$group = $_GET['group_'] ?? '';
$admission_year = $_GET['admission_year'] ?? '';

if ($class_name && $group && $admission_year) {
    // Query to get applicants for the selected class, group, and admission year
    $sql = "SELECT admission_id, applicant_name, applicant_id FROM admission_form 
            WHERE class_id = (SELECT class_id FROM class WHERE class_name = '$class_name' AND group_ = '$group')
            AND admission_year = '$admission_year'
            ORDER BY applicant_name";
    
    $result = $conn->query($sql);
    
    echo "<option value=''>Select Applicant</option>";
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<option value='{$row['admission_id']}'>{$row['applicant_name']} - {$row['applicant_id']}</option>";
        }
    }
}

$conn->close();
?>