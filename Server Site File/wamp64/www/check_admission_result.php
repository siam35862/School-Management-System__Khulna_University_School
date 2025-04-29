<?php
// Database connection
include 'db_connection.php';

$admission_id = $_GET['admission_id'] ?? '';
$response = ['exists' => false, 'marks' => ''];

if ($admission_id) {
    // Check if admission result exists for this admission_id
    $sql = "SELECT marks FROM admission_result WHERE admission_id = '$admission_id'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response['exists'] = true;
        $response['marks'] = $row['marks'];
    }
}

echo json_encode($response);
$conn->close();
?>