<?php
// update_class.php - Backend handler for class update

// Set header to return JSON response
header('Content-Type: application/json');

// Database connection parameters
include 'db_connection.php';
// Response array
$response = [
    'success' => false,
    'message' => ''
];

// Check if the request is POST and action is update_class
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_class') {
    
    // Validate required fields
    if (!isset($_POST['class_id']) || !isset($_POST['room_number']) || !isset($_POST['total_seat'])) {
        $response['message'] = 'Missing required fields';
        echo json_encode($response);
        exit;
    }
    
    // Sanitize inputs
    $class_id = filter_var($_POST['class_id'], FILTER_SANITIZE_NUMBER_INT);
    $room_number = filter_var($_POST['room_number'], FILTER_SANITIZE_STRING);
    $total_seat = filter_var($_POST['total_seat'], FILTER_SANITIZE_NUMBER_INT);
    
    // Validate inputs
    if (empty($class_id) || empty($room_number) || empty($total_seat)) {
        $response['message'] = 'Invalid input data';
        echo json_encode($response);
        exit;
    }
    

    
    // Check connection
    if ($conn->connect_error) {
        $response['message'] = 'Database connection failed';
        echo json_encode($response);
        exit;
    }
    
    // Prepare and execute update query
    $stmt = $conn->prepare("UPDATE class SET room_number = ?, total_seat = ? WHERE class_id = ?");
    $stmt->bind_param("sii", $room_number, $total_seat, $class_id);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Class information updated successfully!';
    } else {
        $response['message'] = 'Error updating class information: ' . $conn->error;
    }
    
    // Close connection
    $stmt->close();
    $conn->close();
    
} else {
    $response['message'] = 'Invalid request';
}

// Return response as JSON
echo json_encode($response);
?>