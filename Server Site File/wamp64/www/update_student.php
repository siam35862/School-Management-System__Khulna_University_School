<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $st_ID = $_POST['st_ID'];
    $name = $_POST['name'];
    $dob = $_POST['dob'];
    $father = $_POST['father'];
    $mother = $_POST['mother'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $gender = $_POST['gender'];
    $presentAddress = $_POST['presentAddress'];
    $permAddress = $_POST['permAddress'];

    $pres_parts = explode(',', $presentAddress);
    $pres_village = trim($pres_parts[0] ?? '');
    $pres_post_code = trim($pres_parts[1] ?? '');
    $pres_upazila = trim($pres_parts[2] ?? '');
    $pres_zila = trim($pres_parts[3] ?? '');

    $perm_parts = explode(',', $permAddress);
    $perm_village = trim($perm_parts[0] ?? '');
    $perm_post_code = trim($perm_parts[1] ?? '');
    $perm_upazila = trim($perm_parts[2] ?? '');
    $perm_zila = trim($perm_parts[3] ?? '');

    $admissionQuery = "SELECT admission_id FROM student WHERE st_ID = '$st_ID'";
    $admissionResult = $conn->query($admissionQuery);
    if ($admissionResult->num_rows == 0) {
        http_response_code(404);
        exit();
    }
    $admissionRow = $admissionResult->fetch_assoc();
    $admission_id = $admissionRow['admission_id'];

    $updateAdmission = "UPDATE admission_form 
        SET applicant_name = ?, date_of_birth = ?, father_name = ?, mother_name = ?, 
            email = ?, mobile_number = ?, gender = ?, 
            village = ?, post_code = ?, upazila = ?, zila = ? 
        WHERE admission_id = ?";
    $stmt1 = $conn->prepare($updateAdmission);
    $stmt1->bind_param("sssssssssssi", $name, $dob, $father, $mother, $email, $mobile, $gender,
        $pres_village, $pres_post_code, $pres_upazila, $pres_zila, $admission_id);
    $stmt1->execute();

    $updateStudent = "UPDATE student 
        SET village = ?, post_code = ?, upazila = ?, zila = ? 
        WHERE st_ID = ?";
    $stmt2 = $conn->prepare($updateStudent);
    $stmt2->bind_param("ssssi", $perm_village, $perm_post_code, $perm_upazila, $perm_zila, $st_ID);
    $stmt2->execute();

    http_response_code(200); // Success
}
$conn->close();
?>
