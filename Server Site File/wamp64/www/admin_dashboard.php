<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in'] || !isset($_SESSION['adl_id'])) {
    header("Location: index.php");
    exit();
}

include 'db_connection.php';

// Fetch school info
$school_sql = "SELECT * FROM school_info WHERE id = 1";
$school_result = $conn->query($school_sql);
$school_row = $school_result->fetch_assoc();

// Fetch messages from principal and chairman
$message_sql = "SELECT * FROM messages WHERE id IN (1, 2)";
$message_result = $conn->query($message_sql);
$messages = [];
while ($row = $message_result->fetch_assoc()) {
    $messages[] = $row;
}

// Fetch admin login details - using adl_id from session
$adl_id = $_SESSION['adl_id'];
$admin_sql = "SELECT * FROM admin_login WHERE adl_id = '$adl_id'";
$admin_result = $conn->query($admin_sql);
$admin_row = $admin_result->fetch_assoc();



///Teacher Added////

// Function to generate random username and password
function generateRandomString($length = 8)
{
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $str = '';
    for ($i = 0; $i < $length; $i++) {
        $str .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $str;
}

// Initialize variables to hold submitted data
$display_name = "";
$display_username = "";
$display_password = "";
$submission_success = false;

// If form is submitted
if (isset($_POST['submit'])) {
    // Collect and sanitize data
    $teacher_name = $conn->real_escape_string(trim($_POST['teacher_name']));
    $date_of_birth = $conn->real_escape_string(trim($_POST['date_of_birth']));
    $joining_date = $conn->real_escape_string(trim($_POST['joining_date']));
    $designation = $conn->real_escape_string(trim($_POST['designation']));
    $teaching_subject = $conn->real_escape_string(trim($_POST['teaching_subject']));
    $phone_number = $conn->real_escape_string(trim($_POST['phone_number']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $qualification = $conn->real_escape_string(trim($_POST['qualification']));
    $district = $conn->real_escape_string(trim($_POST['district']));

    // Insert teacher data
    $insertTeacher = "INSERT INTO teacher (teacher_name, date_of_birth, joining_date, designation, teaching_subject, phone_number, email, qualification, district)
                      VALUES ('$teacher_name', '$date_of_birth', '$joining_date', '$designation', '$teaching_subject', '$phone_number', '$email', '$qualification', '$district')";

    if ($conn->query($insertTeacher) === TRUE) {
        // Get inserted teacher_ID
        $teacher_ID = $conn->insert_id;

        // Generate unique username and password
        do {
            $user_name = generateRandomString(6); // username length
            $user_password = generateRandomString(8); // password length

            $checkLogin = "SELECT * FROM teacher_login WHERE user_name = '$user_name' OR user_password = '$user_password'";
            $result = $conn->query($checkLogin);
        } while ($result->num_rows > 0); // Repeat if already exists

        // Insert into teacher_login
        $insertLogin = "INSERT INTO teacher_login (user_name, user_password, teacher_ID) 
                        VALUES ('$user_name', '$user_password', '$teacher_ID')";

        if ($conn->query($insertLogin) === TRUE) {
            // Store information in session to display after redirect
            $_SESSION['teacher_added'] = true;
            $_SESSION['display_name'] = $teacher_name;
            $_SESSION['display_username'] = $user_name;
            $_SESSION['display_password'] = $user_password;

            // Redirect to prevent form resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $error_message = "Failed to create login info. Error: " . $conn->error;
        }
    } else {
        $error_message = "Failed to add teacher. Error: " . $conn->error;
    }
}

// Check if we have a successful submission notification to display
if (isset($_SESSION['teacher_added']) && $_SESSION['teacher_added']) {
    $submission_success = true;
    $display_name = $_SESSION['display_name'];
    $display_username = $_SESSION['display_username'];
    $display_password = $_SESSION['display_password'];

    // Clear the session variables to prevent displaying the message again on refresh
    unset($_SESSION['teacher_added']);
    unset($_SESSION['display_name']);
    unset($_SESSION['display_username']);
    unset($_SESSION['display_password']);
}

// Handle edit form submission
if (isset($_POST['update_teacher'])) {
    $teacher_id = $_POST['teacher_id'];
    $teacher_name = $_POST['teacher_name'];
    $date_of_birth = $_POST['date_of_birth'];
    $joining_date = $_POST['joining_date'];
    $designation = $_POST['designation'];
    $service_status = $_POST['service_status'];
    $teaching_subject = $_POST['teaching_subject'];
    $phone_number = $_POST['phone_number'];
    $email = $_POST['email'];
    $qualification = $_POST['qualification'];
    $district = $_POST['district'];
    $user_name = $_POST['user_name'];
    $user_password = $_POST['user_password'];

    // Check if username already exists for another teacher
    $check_query = "SELECT * FROM teacher_login WHERE user_name = '$user_name' AND teacher_ID != '$teacher_id'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        echo "<script>alert('Username already exists!');</script>";
    } else {
        // Update teacher details
        $update_teacher_query = "UPDATE teacher SET 
            teacher_name = '$teacher_name',
            date_of_birth = '$date_of_birth',
            joining_date = '$joining_date',
            designation = '$designation',
            service_status = '$service_status',
            teaching_subject = '$teaching_subject',
            phone_number = '$phone_number',
            email = '$email',
            qualification = '$qualification',
            district = '$district'
            WHERE teacher_ID = '$teacher_id'";

        // Update teacher login details
        $update_login_query = "UPDATE teacher_login SET 
            user_name = '$user_name', 
            user_password = '$user_password' 
            WHERE teacher_ID = '$teacher_id'";

        $teacher_update_success = mysqli_query($conn, $update_teacher_query);
        $login_update_success = mysqli_query($conn, $update_login_query);

        if ($teacher_update_success && $login_update_success) {

        } else {
            echo "<script>alert('Error updating record: " . mysqli_error($conn) . "');</script>";
        }
    }
}


///Notices Section////

// Fetch notices
$notice_sql = "SELECT * FROM notice ORDER BY notice_date DESC";
$notice_result = $conn->query($notice_sql);

// Handle notice form submissions
if (isset($_POST['add_notice'])) {
    $notice_title = $conn->real_escape_string(trim($_POST['notice_title']));
    $notice_description = $conn->real_escape_string(trim($_POST['notice_description']));

    // Current date will be automatically inserted by MySQL for notice_date if it has DEFAULT CURRENT_TIMESTAMP
    $insert_notice = "INSERT INTO notice (notice_title, notice_description) VALUES ('$notice_title', '$notice_description')";

    if ($conn->query($insert_notice) === TRUE) {
        $notice_success = "Notice added successfully!";
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF'] . "?notice_success=1");
        exit();
    } else {
        $notice_error = "Failed to add notice. Error: " . $conn->error;
    }
}

if (isset($_POST['edit_notice'])) {
    $notice_id = $conn->real_escape_string($_POST['notice_id']);
    $notice_title = $conn->real_escape_string(trim($_POST['notice_title']));
    $notice_description = $conn->real_escape_string(trim($_POST['notice_description']));

    $update_notice = "UPDATE notice SET notice_title = '$notice_title', notice_description = '$notice_description' WHERE notice_id = '$notice_id'";

    if ($conn->query($update_notice) === TRUE) {
        $notice_success = "Notice updated successfully!";
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF'] . "?notice_success=2");
        exit();
    } else {
        $notice_error = "Failed to update notice. Error: " . $conn->error;
    }
}

if (isset($_GET['delete_notice'])) {
    $notice_id = $conn->real_escape_string($_GET['delete_notice']);

    $delete_notice = "DELETE FROM notice WHERE notice_id = '$notice_id'";

    if ($conn->query($delete_notice) === TRUE) {
        $notice_success = "Notice deleted successfully!";
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF'] . "?notice_success=3");
        exit();
    } else {
        $notice_error = "Failed to delete notice. Error: " . $conn->error;
    }
}

// Show success messages
if (isset($_GET['notice_success'])) {
    $success_code = $_GET['notice_success'];
    if ($success_code == 1) {
        $notice_success = "Notice added successfully!";
    } else if ($success_code == 2) {
        $notice_success = "Notice updated successfully!";
    } else if ($success_code == 3) {
        $notice_success = "Notice deleted successfully!";
    }
}

////////Achievement////////

// Fetch events for dropdown
$event_sql = "SELECT * FROM institution_event ORDER BY event_date DESC";
$event_result = $conn->query($event_sql);
$events = [];
while ($row = $event_result->fetch_assoc()) {
    $events[] = $row;
}

// Fetch students for dropdown
$student_sql = "SELECT s.st_ID, s.student_id, af.applicant_name, c.class_name, c.group_ 
                FROM student s 
                JOIN admission_form af ON s.admission_id = af.admission_id
                JOIN class c ON s.class_id = c.class_id
                ORDER BY af.applicant_name";
$student_result = $conn->query($student_sql);
$students = [];
while ($row = $student_result->fetch_assoc()) {
    $students[] = $row;
}

// Fetch teachers for dropdown
$teacher_sql = "SELECT teacher_ID, teacher_name, designation, phone_number FROM teacher ORDER BY teacher_name";
$teacher_result = $conn->query($teacher_sql);
$teachers = [];
while ($row = $teacher_result->fetch_assoc()) {
    $teachers[] = $row;
}

// Handle achievement form submission
if (isset($_POST['add_achievement'])) {
    // Collect and sanitize data
    $award_name = $conn->real_escape_string(trim($_POST['award_name']));
    $achievement_date = $conn->real_escape_string(trim($_POST['achievement_date']));
    $achievement_description = $conn->real_escape_string(trim($_POST['achievement_description']));

    // Determine if it's a student or teacher achievement
    $st_ID = isset($_POST['student_id']) && !empty($_POST['student_id']) ? $conn->real_escape_string($_POST['student_id']) : "NULL";
    $t_ID = isset($_POST['teacher_id']) && !empty($_POST['teacher_id']) ? $conn->real_escape_string($_POST['teacher_id']) : "NULL";
    $event_id = isset($_POST['event_id']) && !empty($_POST['event_id']) ? $conn->real_escape_string($_POST['event_id']) : "NULL";

    // Validate required fields
    $validation_error = false;

    if ($st_ID == "NULL" && $t_ID == "NULL") {
        $achievement_error = "Please select either a student or a teacher.";
        $validation_error = true;
    }

    if ($st_ID != "NULL" && $t_ID != "NULL") {
        $achievement_error = "You cannot select both a student and a teacher.";
        $validation_error = true;
    }

    if ($st_ID != "NULL" && $event_id == "NULL") {
        $achievement_error = "Please select an event for student achievement.";
        $validation_error = true;
    }

    // If validations pass, insert the achievement
    if (!$validation_error) {
        // Insert achievement data
        $insert_achievement = "INSERT INTO achievement (award_name, achievement_date, achievement_description, st_ID, t_ID, event_id) 
                             VALUES ('$award_name', '$achievement_date', '$achievement_description', " .
            ($st_ID == "NULL" ? "NULL" : "'$st_ID'") . ", " .
            ($t_ID == "NULL" ? "NULL" : "'$t_ID'") . ", " .
            ($event_id == "NULL" ? "NULL" : "'$event_id'") . ")";

        if ($conn->query($insert_achievement) === TRUE) {
            $achievement_success = "Achievement added successfully!";
            // Redirect to prevent form resubmission
            header("Location: " . $_SERVER['PHP_SELF'] . "?achievement_success=1");
            exit();
        } else {
            $achievement_error = "Failed to add achievement. Error: " . $conn->error;
        }
    }
}

// Fetch achievements to display
$achievement_sql = "SELECT a.*, 
                    s.student_id, af.applicant_name, c.class_name, c.group_,
                    t.teacher_name, t.designation, 
                    e.title as event_title, e.category as event_category
                    FROM achievement a
                    LEFT JOIN student s ON a.st_ID = s.st_ID
                    LEFT JOIN admission_form af ON s.admission_id = af.admission_id
                    LEFT JOIN class c ON s.class_id = c.class_id
                    LEFT JOIN teacher t ON a.t_ID = t.teacher_ID
                    LEFT JOIN institution_event e ON a.event_id = e.event_id
                    ORDER BY a.achievement_date DESC";
$achievement_result = $conn->query($achievement_sql);

// Show success messages
if (isset($_GET['achievement_success'])) {
    $success_code = $_GET['achievement_success'];
    if ($success_code == 1) {
        $achievement_success = "Achievement added successfully!";
    } else if ($success_code == 2) {
        $achievement_success = "Achievement updated successfully!";
    } else if ($success_code == 3) {
        $achievement_success = "Achievement deleted successfully!";
    }
}

// Handle delete achievement
if (isset($_GET['delete_achievement'])) {
    $achievement_id = $conn->real_escape_string($_GET['delete_achievement']);

    $delete_achievement = "DELETE FROM achievement WHERE achievement_id = '$achievement_id'";

    if ($conn->query($delete_achievement) === TRUE) {
        $achievement_success = "Achievement deleted successfully!";
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF'] . "?achievement_success=3");
        exit();
    } else {
        $achievement_error = "Failed to delete achievement. Error: " . $conn->error;
    }
}

// Handle edit achievement - show form with prefilled data
if (isset($_GET['edit_achievement'])) {
    $edit_id = $conn->real_escape_string($_GET['edit_achievement']);

    // Fetch the achievement data
    $edit_sql = "SELECT a.*, 
                 s.st_ID, t.teacher_ID, e.event_id
                 FROM achievement a
                 LEFT JOIN student s ON a.st_ID = s.st_ID
                 LEFT JOIN teacher t ON a.t_ID = t.teacher_ID
                 LEFT JOIN institution_event e ON a.event_id = e.event_id
                 WHERE a.achievement_id = '$edit_id'";

    $edit_result = $conn->query($edit_sql);

    if ($edit_result->num_rows > 0) {
        $edit_data = $edit_result->fetch_assoc();
    } else {
        // Achievement not found
        $achievement_error = "Achievement not found for editing.";
    }
}

// Handle update achievement
if (isset($_POST['update_achievement'])) {
    // Collect and sanitize data
    $achievement_id = $conn->real_escape_string($_POST['achievement_id']);
    $award_name = $conn->real_escape_string(trim($_POST['award_name']));
    $achievement_date = $conn->real_escape_string(trim($_POST['achievement_date']));
    $achievement_description = $conn->real_escape_string(trim($_POST['achievement_description']));

    // Determine if it's a student or teacher achievement
    $st_ID = isset($_POST['student_id']) && !empty($_POST['student_id']) ? $conn->real_escape_string($_POST['student_id']) : "NULL";
    $t_ID = isset($_POST['teacher_id']) && !empty($_POST['teacher_id']) ? $conn->real_escape_string($_POST['teacher_id']) : "NULL";
    $event_id = isset($_POST['event_id']) && !empty($_POST['event_id']) ? $conn->real_escape_string($_POST['event_id']) : "NULL";

    // Validate required fields
    $validation_error = false;

    if ($st_ID == "NULL" && $t_ID == "NULL") {
        $achievement_error = "Please select either a student or a teacher.";
        $validation_error = true;
    }

    if ($st_ID != "NULL" && $t_ID != "NULL") {
        $achievement_error = "You cannot select both a student and a teacher.";
        $validation_error = true;
    }

    if ($st_ID != "NULL" && $event_id == "NULL") {
        $achievement_error = "Please select an event for student achievement.";
        $validation_error = true;
    }

    // If validations pass, update the achievement
    if (!$validation_error) {
        // Update achievement data
        $update_achievement = "UPDATE achievement SET 
                              award_name = '$award_name', 
                              achievement_date = '$achievement_date', 
                              achievement_description = '$achievement_description', 
                              st_ID = " . ($st_ID == "NULL" ? "NULL" : "'$st_ID'") . ", 
                              t_ID = " . ($t_ID == "NULL" ? "NULL" : "'$t_ID'") . ", 
                              event_id = " . ($event_id == "NULL" ? "NULL" : "'$event_id'") . "
                              WHERE achievement_id = '$achievement_id'";

        if ($conn->query($update_achievement) === TRUE) {
            $achievement_success = "Achievement updated successfully!";
            // Redirect to prevent form resubmission
            header("Location: " . $_SERVER['PHP_SELF'] . "?achievement_success=2");
            exit();
        } else {
            $achievement_error = "Failed to update achievement. Error: " . $conn->error;
        }
    }
}




//upload photos or videos

// Check if there are any messages in the session
if (isset($_SESSION['message_']) && isset($_SESSION['messageType'])) {
    $message_ = $_SESSION['message_'];
    $messageType = $_SESSION['messageType'];

    // Clear the message after displaying it
    unset($_SESSION['message_']);
    unset($_SESSION['messageType']);
} else {
    $message_ = '';
    $messageType = '';
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $mediaType = isset($_POST['media_type']) ? $_POST['media_type'] : '';
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';

    // Validate input
    if (empty($mediaType) || !in_array($mediaType, ['photo', 'video'])) {
        $_SESSION['message_'] = "Please select a valid media type.";
        $_SESSION['messageType'] = "error";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } elseif (empty($title)) {
        $_SESSION['message_'] = "Please enter a title for the media.";
        $_SESSION['messageType'] = "error";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } elseif (!isset($_FILES['media_file']) || $_FILES['media_file']['error'] == UPLOAD_ERR_NO_FILE) {
        $_SESSION['message_'] = "Please select a file to upload.";
        $_SESSION['messageType'] = "error";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        // File handling
        $file = $_FILES['media_file'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileError = $file['error'];

        // Get file extension
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Define allowed extensions based on media type
        $allowedExtensions = [];
        if ($mediaType == 'photo') {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $maxFileSize = 50 * 1024 * 1024; // 5MB for photos
        } else { // video
            $allowedExtensions = ['mp4', 'avi', 'mov', 'wmv', 'webm'];
            $maxFileSize = 5000 * 1024 * 1024; // 50MB for videos
        }

        // Check if file type is allowed
        if (!in_array($fileExt, $allowedExtensions)) {
            $_SESSION['message_'] = "This file type is not allowed for " . $mediaType . ".";
            $_SESSION['messageType'] = "error";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } elseif ($fileError !== 0) {
            // Check specific upload errors
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => "The uploaded file exceeds the upload_max_filesize directive in php.ini.",
                UPLOAD_ERR_FORM_SIZE => "The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form.",
                UPLOAD_ERR_PARTIAL => "The uploaded file was only partially uploaded.",
                UPLOAD_ERR_NO_FILE => "No file was uploaded.",
                UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder.",
                UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
                UPLOAD_ERR_EXTENSION => "A PHP extension stopped the file upload."
            ];

            $errorMessage = isset($errorMessages[$fileError]) ? $errorMessages[$fileError] : "There was an error uploading the file.";
            $_SESSION['message_'] = $errorMessage;
            $_SESSION['messageType'] = "error";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } elseif ($fileSize > $maxFileSize) {
            $sizeInMB = $maxFileSize / (1024 * 1024);
            $_SESSION['message_'] = "File size is too large. Maximum size is " . $sizeInMB . "MB.";
            $_SESSION['messageType'] = "error";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            // Create unique filename
            $newFileName = uniqid('', true) . '.' . $fileExt;
            $uploadDir = 'uploads/' . $mediaType . 's/';

            // Create directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filePath = $uploadDir . $newFileName;

            // Move uploaded file
            if (move_uploaded_file($fileTmpName, $filePath)) {
                // Insert into database
                $sql = "INSERT INTO gallery (title, file_name, type) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);

                if ($stmt) {
                    $stmt->bind_param("sss", $title, $filePath, $mediaType);

                    if ($stmt->execute()) {
                        $_SESSION['message_'] = "Your " . $mediaType . " has been uploaded successfully!";
                        $_SESSION['messageType'] = "success";
                    } else {
                        $_SESSION['message_'] = "Database error: " . $conn->error;
                        $_SESSION['messageType'] = "error";
                    }

                    $stmt->close();
                } else {
                    $_SESSION['message_'] = "Database error: " . $conn->error;
                    $_SESSION['messageType'] = "error";
                }
            } else {
                $_SESSION['message_'] = "Failed to move the uploaded file.";
                $_SESSION['messageType'] = "error";
            }

            // Redirect after processing
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }
}
?>
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="nav.css">
    <link rel="stylesheet" href="footer.css">
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        html {
            scroll-behavior: smooth;
            /* For smooth scrolling to anchor */
        }

        /*main containter*/
        .container {
            padding: 30px;
            max-width: 1300px;
            margin: 0 auto;
        }

        /*message show and school info*/

        h1,
        h3 {
            text-align: center;
            color: #004080;
        }


        .message-block {
            margin-bottom: 40px;
        }

        form input[type="text"],
        form input[type="file"],
        form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 16px;
        }

        form textarea {
            height: 100px;
            resize: vertical;
        }

        /*update admin login info*/
        .update-form {
            margin-top: 30px;
        }

        .status {
            margin-top: 10px;
            color: green;
        }

        /*all form*/
        form {
            background: #f9f9f9;
            padding: 20px;
            margin-bottom: 40px;
            border-radius: 8px;
            box-shadow: 0 0 10px #ccc;
        }


        /* all button*/
        button {
            padding: 10px 20px;
            background-color: #0077cc;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
        }



        .error {
            color: red;
            margin-bottom: 15px;
        }

        /* for all success message*/
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
        }
        



        /*admission process start*/
        .admission-process-section {
            margin-bottom: 40px;
        }

        .applicant-list-container {
            margin-top: 20px;
            max-height: 500px;
            overflow-y: auto;
        }

        .applicant-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .applicant-table th,
        .applicant-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .applicant-table th {
            background-color: #f2f2f2;
            position: sticky;
            top: 0;
        }

        .applicant-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .admit-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }

        .admit-btn:hover {
            background-color: #45a049;
        }

        /*admission process end*/

        /*start techer edition and show*/
        h1 {
            color: #333;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .action-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            border-radius: 3px;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }

        .form-submit {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            width: 100%;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -10px;
            margin-left: -10px;
        }

        .form-col {
            flex: 0 0 50%;
            max-width: 50%;
            padding: 0 10px;
            box-sizing: border-box;
        }

        /*end techer edition and show*/

        /*start notice*/
        .notice-container {
            margin-top: 20px;
            max-height: 500px;
            overflow-y: auto;
        }

        .edit-btn,
        .delete-btn {
            padding: 5px 10px;
            margin: 2px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            color: white;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }

        .edit-btn {
            background-color: #2196F3;
        }

        .delete-btn {
            background-color: #f44336;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 5px;
        }

        .close-modal {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close-modal:hover,
        .close-modal:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        /* end notice*/

        /* start achievement*/

        .achievement-container {
            margin-top: 20px;
            max-height: 500px;
            overflow-y: auto;
        }

        .achievement-type {
            margin: 10px 0;
        }

        .achievement-type label {
            margin-right: 20px;
        }

        .delete-btn {
            padding: 5px 10px;
            margin: 2px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            color: white;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            background-color: #f44336;
        }

        select.input-field {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .edit-btn {
            padding: 5px 10px;
            margin: 2px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            color: white;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            background-color: #4CAF50;
        }

        .cancel-btn {
            padding: 10px 15px;
            margin-left: 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            color: white;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            background-color: #808080;
        }

        /* end achievement*/



        /*upload photos or videos*/

        .upload-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .upload-title {
            text-align: center;
            color: #004080;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        .radio-group {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }

        .radio-container {
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .radio-container input {
            margin-right: 8px;
        }

        input[type="text"],
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-button {
            background-color: #e9ecef;
            color: #333;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            display: inline-block;
            width: 100%;
            box-sizing: border-box;
            text-align: center;
        }

        .file-input-text {
            margin-top: 8px;
            color: #666;
            font-size: 14px;
        }

        button {
            background-color: #004080;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
            width: 100%;
        }

        button:hover {
            background-color: #002d5a;
        }

        .messages_ {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .preview-container {
            margin-top: 20px;
            text-align: center;
            display: none;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .preview-container img,
        .preview-container video {
            max-width: 100%;
            max-height: 300px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .custom-file-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }



        /*upload photos or videos end */



        

        /*logout section*/
        .logout-btn {
            background-color: #cc0000;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .logout-btn:hover {
            background-color: #990000;
        }
    </style>
</head>

<body>
    <?php include('nav.php'); ?>

    <div class="container">
        <h1><?php echo $school_row['school_name']; ?></h1>
        <p style="text-align:center;">Established Year: <?php echo $school_row['established_year']; ?></p>
        <h3>Short History</h3>
        <p style="text-align:center;"><?php echo $school_row['short_history']; ?></p>
        <h3>Full History</h3>
        <p style="text-align:center;"><?php echo $school_row['full_history']; ?></p>

        <form id="schoolForm" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $school_row['id']; ?>">
            <input type="text" name="school_name" value="<?php echo $school_row['school_name']; ?>" required>
            <input type="text" name="established_year" value="<?php echo $school_row['established_year']; ?>" required>
            <textarea name="short_history" required><?php echo $school_row['short_history']; ?></textarea>
            <textarea name="full_history" required><?php echo $school_row['full_history']; ?></textarea>
            <input type="text" name="address" value="<?php echo $school_row['address']; ?>" required>
            <input type="file" name="image">
            <button type="submit">Update School Info</button>
            <div class="status" id="school-status"></div>
        </form>

        <?php foreach ($messages as $message): ?>
            <div class="message-block">
                <h3><?php echo ucfirst($message['role']); ?>'s Message</h3>
                <form class="messageForm" data-id="<?php echo $message['id']; ?>" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $message['id']; ?>">
                    <input type="hidden" name="role" value="<?php echo $message['role']; ?>">
                    <input type="text" name="name" value="<?php echo $message['name']; ?>" required>
                    <input type="text" name="title" value="<?php echo $message['title']; ?>" required>
                    <textarea name="message" required><?php echo $message['message']; ?></textarea>
                    <input type="file" name="image">
                    <button type="submit">Update Message</button>
                    <div class="status"></div>
                </form>
            </div>
        <?php endforeach; ?>

        <!-- Admin Login Info Update Form -->
        <div class="update-form">
            <h3>Update Admin Login Info</h3>
            <form id="adminUpdateForm">
                <input type="hidden" name="adl_id" value="<?php echo $admin_row['adl_id']; ?>">
                <input type="text" name="user_name" value="<?php echo $admin_row['user_name']; ?>" required>
                <input type="text" name="user_password" value="<?php echo $admin_row['user_password']; ?>" required>
                <button type="submit">Update Login Info</button>
                <div class="status" id="admin-status"></div>
            </form>
        </div>

        <!-- This is the specific section of teacher.php that needs to be modified -->



        <!-- Display error message if any -->
        <?php if (isset($error_message)): ?>
            <div class="error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Marks Entry -->
        <h3 class="section-title">Marks Entry</h3>
        <form method="POST" action="admin_insert_marks.php">
            <label>Class:</label>
            <select name="class_name_mark" class="input-field" onchange="fetchAcademicYears('marks')" required>
                <option value="">Select Class</option>
                <?php
                $class_result = $conn->query("SELECT DISTINCT class_name FROM class");
                while ($row = $class_result->fetch_assoc()) {
                    echo "<option value='{$row['class_name']}'>{$row['class_name']}</option>";
                }
                ?>
            </select>

            <label>Group:</label>
            <select name="group_mark" class="input-field" onchange="fetchAcademicYears('marks')" required>
                <option value="">Select Group</option>
                <?php
                $group_result = $conn->query("SELECT DISTINCT group_ FROM class");
                while ($row = $group_result->fetch_assoc()) {
                    echo "<option value='{$row['group_']}'>{$row['group_']}</option>";
                }
                ?>
            </select>

            <label>Academic Year:</label>
            <select name="academic_year_mark" id="academicYearDropdownMarks" class="input-field"
                onchange="fetchStudentsForMarks()" required>
                <option value="">Select Academic Year</option>
            </select>

            <label>Student ID:</label>
            <select name="student_id_mark" id="studentDropdownMarks" class="input-field" onchange="fetchStudentID()"
                required>
                <option value="">Select Student</option>
            </select>

            <div id="subjectMarksSection">
                <!-- Subjects and marks fields will be loaded here -->
            </div>
        </form>


        <!-- Admission Result Entry -->
        <h3 class="section-title">Admission Result Entry</h3>
        <form method="POST" action="admin_insert_admission_result.php" id="admissionResultForm">
            <label>Class and Group:</label>
            <select name="class_group" class="input-field" onchange="fetchAdmissionYears()" required>
                <option value="">Select Class and Group</option>
                <?php
                $class_group_result = $conn->query("SELECT DISTINCT class_name, group_ FROM class WHERE class_name != 'Ten'");
                while ($row = $class_group_result->fetch_assoc()) {
                    echo "<option value='{$row['class_name']}_{$row['group_']}'>{$row['class_name']} - {$row['group_']}</option>";
                }
                ?>
            </select>

            <label>Admission Year:</label>
            <select name="admission_year" id="admissionYearDropdown" class="input-field" onchange="fetchApplicants()"
                required>
                <option value="">Select Admission Year</option>
            </select>

            <label>Applicant:</label>
            <select name="applicant_id" id="applicantDropdown" class="input-field" onchange="checkExistingMarks()"
                required>
                <option value="">Select Applicant</option>
            </select>

            <label>Marks:</label>
            <input type="number" name="marks" id="admissionMarks" class="input-field" min="0" max="100" required>

            <button type="submit" class="submit-btn">Submit</button>
            <div class="status" id="admission-result-status"></div>
        </form>

        <!-- Student Admission Process -->
        <h3 class="section-title">Student Admission Process</h3>
        <div class="admission-process-section">
            <form method="POST" action="" id="processAdmissionForm">
                <label>Class and Group:</label>
                <select name="admission_class_group" class="input-field" onchange="fetchEligibleApplicants()" required>
                    <option value="">Select Class and Group</option>
                    <?php
                    $class_group_result = $conn->query("SELECT DISTINCT class_name, group_ FROM class WHERE class_name != 'Ten'");
                    while ($row = $class_group_result->fetch_assoc()) {
                        echo "<option value='{$row['class_name']}_{$row['group_']}'>{$row['class_name']} - {$row['group_']}</option>";
                    }
                    ?>
                </select>

                <div id="applicantListContainer" class="applicant-list-container">
                    <!-- Eligible applicants will be shown here -->
                </div>
            </form>
        </div>

        <h1>Teacher Management System</h1>
        <h2>Add New Teacher</h2>
        <form method="POST">
            <label>Teacher Name:</label><br>
            <input type="text" name="teacher_name" required><br><br>

            <label>Date of Birth:</label><br>
            <input type="date" name="date_of_birth" required><br><br>

            <label>Joining Date:</label><br>
            <input type="date" name="joining_date" required><br><br>

            <label>Designation:</label><br>
            <input type="text" name="designation" required><br><br>

            <label>Teaching Subject:</label><br>
            <input type="text" name="teaching_subject" required><br><br>

            <label>Phone Number:</label><br>
            <input type="text" name="phone_number" required><br><br>

            <label>Email:</label><br>
            <input type="email" name="email" required><br><br>

            <label>Qualification:</label><br>
            <input type="text" name="qualification" required><br><br>

            <label>District:</label><br>
            <input type="text" name="district" required><br><br>

            <button type="submit" name="submit">Submit</button>
        </form>

        <!-- Display teacher login info if just added -->
        <?php if ($submission_success): ?>
            <div class="success-message">
                <h3>Teacher Successfully Added</h3>
                <p><strong>Teacher Name:</strong> <?php echo htmlspecialchars($display_name); ?></p>
                <p><strong>User Name:</strong> <?php echo htmlspecialchars($display_username); ?></p>
                <p><strong>Password:</strong> <?php echo htmlspecialchars($display_password); ?></p>
            </div>
        <?php endif; ?>



        <div id="teacher-list">
            <h2>Teacher List</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Date of Birth</th>
                        <th>Joining Date</th>
                        <th>Designation</th>
                        <th>Service Status</th>
                        <th>Teaching Subject</th>
                        <th>Phone Number</th>
                        <th>Email</th>
                        <th>Qualification</th>
                        <th>District</th>
                        <th>Username</th>
                        <th>Password</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Query to fetch teacher details with login information, sorted by joining_date
                    $query = "SELECT t.*, tl.user_name, tl.user_password, tl.tl_id
                              FROM teacher t
                              JOIN teacher_login tl ON t.teacher_ID = tl.teacher_ID
                              ORDER BY t.joining_date ASC";

                    $result = mysqli_query($conn, $query);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $row["teacher_name"] . "</td>";
                            echo "<td>" . $row["date_of_birth"] . "</td>";
                            echo "<td>" . $row["joining_date"] . "</td>";
                            echo "<td>" . $row["designation"] . "</td>";
                            echo "<td>" . $row["service_status"] . "</td>";
                            echo "<td>" . $row["teaching_subject"] . "</td>";
                            echo "<td>" . $row["phone_number"] . "</td>";
                            echo "<td>" . $row["email"] . "</td>";
                            echo "<td>" . $row["qualification"] . "</td>";
                            echo "<td>" . $row["district"] . "</td>";
                            echo "<td>" . $row["user_name"] . "</td>";
                            echo "<td>" . $row["user_password"] . "</td>";
                            echo "<td><button class='action-btn' onclick='openEditModal(\"" . $row["teacher_ID"] . "\", \"" .
                                $row["teacher_name"] . "\", \"" . $row["date_of_birth"] . "\", \"" . $row["joining_date"] . "\", \"" .
                                $row["designation"] . "\", \"" . $row["service_status"] . "\", \"" . $row["teaching_subject"] . "\", \"" .
                                $row["phone_number"] . "\", \"" . $row["email"] . "\", \"" . $row["qualification"] . "\", \"" .
                                $row["district"] . "\", \"" . $row["user_name"] . "\", \"" . $row["user_password"] . "\")'>Edit</button></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='13'>No teachers found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>


        <!-- Edit Teacher Modal -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeEditModal()">&times;</span>
                <h2>Edit Teacher Information</h2>
                <form id="editForm" method="post" action="">
                    <input type="hidden" id="teacher_id" name="teacher_id">
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="teacher_name">Teacher Name:</label>
                                <input type="text" id="teacher_name" name="teacher_name" required>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="date_of_birth">Date of Birth:</label>
                                <input type="date" id="date_of_birth" name="date_of_birth" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="joining_date">Joining Date:</label>
                                <input type="date" id="joining_date" name="joining_date" required>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="designation">Designation:</label>
                                <input type="text" id="designation" name="designation" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="service_status">Service Status:</label>
                                <input type="text" id="service_status" name="service_status" required>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="teaching_subject">Teaching Subject:</label>
                                <input type="text" id="teaching_subject" name="teaching_subject" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="phone_number">Phone Number:</label>
                                <input type="text" id="phone_number" name="phone_number" required>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="email">Email:</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="qualification">Qualification:</label>
                                <input type="text" id="qualification" name="qualification" required>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="district">District:</label>
                                <input type="text" id="district" name="district" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="user_name">Username:</label>
                                <input type="text" id="user_name" name="user_name" required>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="user_password">Password:</label>
                                <input type="text" id="user_password" name="user_password" required>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="update_teacher" class="form-submit">Update Teacher</button>
                </form>
            </div>
        </div>

        <h2>Notice Management</h2>

        <!-- Success/Error Messages -->
        <?php if (isset($notice_success)): ?>
            <div class="success-message">
                <?php echo $notice_success; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($notice_error)): ?>
            <div class="error">
                <?php echo $notice_error; ?>
            </div>
        <?php endif; ?>

        <!-- Add New Notice Form -->
        <h3>Add New Notice</h3>
        <form method="POST">
            <label>Notice Title:</label><br>
            <input type="text" name="notice_title" required><br><br>

            <label>Notice Description:</label><br>
            <textarea name="notice_description" rows="5" required></textarea><br><br>

            <button type="submit" name="add_notice">Add Notice</button>
        </form>

        <!-- Existing Notices -->
        <h3>Existing Notices</h3>
        <div class="notice-container">
            <?php if ($notice_result->num_rows > 0): ?>
                <table class="applicant-table">
                    <thead>
                        <tr>

                            <th>Title</th>
                            <th>Description</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $notice_result->fetch_assoc()): ?>
                            <tr>

                                <td><?php echo htmlspecialchars($row['notice_title']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($row['notice_description'])); ?></td>
                                <td><?php echo date('d M Y', strtotime($row['notice_date'])); ?></td>
                                <td>
                                    <button
                                        onclick="editNotice(<?php echo $row['notice_id']; ?>, '<?php echo addslashes(htmlspecialchars($row['notice_title'])); ?>', '<?php echo addslashes(htmlspecialchars($row['notice_description'])); ?>')"
                                        class="edit-btn">Edit</button>
                                    <a href="?delete_notice=<?php echo $row['notice_id']; ?>"
                                        onclick="return confirm('Are you sure you want to delete this notice?')"
                                        class="delete-btn">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No notices found.</p>
            <?php endif; ?>
        </div>

        <!-- Edit Notice Modal/Form -->
        <div id="editNoticeModal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close-modal" onclick="closeEditModal()">&times;</span>
                <h3>Edit Notice</h3>
                <form method="POST">
                    <input type="hidden" id="edit_notice_id" name="notice_id">

                    <label>Notice Title:</label><br>
                    <input type="text" id="edit_notice_title" name="notice_title" required><br><br>

                    <label>Notice Description:</label><br>
                    <textarea id="edit_notice_description" name="notice_description" rows="5"
                        required></textarea><br><br>

                    <button type="submit" name="edit_notice">Update Notice</button>
                </form>
            </div>
        </div>


        <!-- Achievement Management Section -->
        <h2>Achievement Management</h2>

        <!-- Success/Error Messages -->
        <?php if (isset($achievement_success)): ?>
            <div class="success-message">
                <?php echo $achievement_success; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($achievement_error)): ?>
            <div class="error">
                <?php echo $achievement_error; ?>
            </div>
        <?php endif; ?>

        <!-- Add New Achievement Form -->
        <h3>Add New Achievement</h3>
        <form method="POST" id="achievementForm">
            <label>Award Name:</label><br>
            <input type="text" name="award_name" required><br><br>

            <label>Achievement Date:</label><br>
            <input type="date" name="achievement_date" required><br><br>

            <label>Achievement Description:</label><br>
            <textarea name="achievement_description" rows="4" required></textarea><br><br>

            <div class="achievement-type">
                <label><input type="radio" name="achievement_type" value="student" checked> Student Achievement</label>
                <label><input type="radio" name="achievement_type" value="teacher"> Teacher Achievement</label>
            </div><br>

            <!-- Student Achievement Fields -->
            <div id="student-fields">
                <label>Student:</label><br>
                <select name="student_id" id="student-dropdown" class="input-field">
                    <option value="">Select Student</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student['st_ID']; ?>">
                            <?php echo $student['applicant_name'] . ' (ID: ' . $student['student_id'] . ', Class: ' . $student['class_name'] . ' ' . $student['group_'] . ')'; ?>
                        </option>
                    <?php endforeach; ?>
                </select><br><br>

                <label>Event:</label><br>
                <select name="event_id" id="event-dropdown" class="input-field">
                    <option value="">Select Event</option>
                    <?php foreach ($events as $event): ?>
                        <option value="<?php echo $event['event_id']; ?>">
                            <?php echo $event['title'] . ' (' . $event['category'] . ', ' . date('d M Y', strtotime($event['event_date'])) . ')'; ?>
                        </option>
                    <?php endforeach; ?>
                </select><br><br>
            </div>

            <!-- Teacher Achievement Fields -->
            <div id="teacher-fields" style="display: none;">
                <label>Teacher:</label><br>
                <select name="teacher_id" id="teacher-dropdown" class="input-field">
                    <option value="">Select Teacher</option>
                    <?php foreach ($teachers as $teacher): ?>
                        <option value="<?php echo $teacher['teacher_ID']; ?>">
                            <?php echo $teacher['teacher_name'] . ' (' . $teacher['designation'] . ', ' . $teacher['phone_number'] . ')'; ?>
                        </option>
                    <?php endforeach; ?>
                </select><br><br>
            </div>

            <button type="submit" name="add_achievement">Add Achievement</button>
        </form>

        <!-- Existing Achievements -->
        <h3>Existing Achievements</h3>
        <div class="achievement-container">
            <?php if ($achievement_result && $achievement_result->num_rows > 0): ?>
                <table class="applicant-table">
                    <thead>
                        <tr>

                            <th>Award Name</th>
                            <th>Date</th>
                            <th>Achieved By</th>
                            <th>Event</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $achievement_result->fetch_assoc()): ?>
                            <tr>

                                <td><?php echo htmlspecialchars($row['award_name']); ?></td>
                                <td><?php echo date('d M Y', strtotime($row['achievement_date'])); ?></td>
                                <td>
                                    <?php
                                    if (!empty($row['applicant_name'])) {
                                        echo htmlspecialchars($row['applicant_name']) . ' (Student ID: ' . $row['student_id'] . ')';
                                        echo '<br>Class: ' . $row['class_name'] . ' ' . $row['group_'];
                                    } else if (!empty($row['teacher_name'])) {
                                        echo htmlspecialchars($row['teacher_name']) . ' (' . $row['designation'] . ')';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if (!empty($row['event_title'])) {
                                        echo htmlspecialchars($row['event_title']) . ' (' . $row['event_category'] . ')';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td><?php echo nl2br(htmlspecialchars($row['achievement_description'])); ?></td>

                                <td>
                                    <a href="?edit_achievement=<?php echo $row['achievement_id']; ?>" class="edit-btn">Edit</a>
                                    <a href="?delete_achievement=<?php echo $row['achievement_id']; ?>"
                                        class="delete-btn">Delete</a>

                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No achievements found.</p>
            <?php endif; ?>

            <?php if (isset($edit_data)): ?>
                <h3>Edit Achievement</h3>
                <form method="POST" id="editAchievementForm">
                    <input type="hidden" name="achievement_id" value="<?php echo $edit_data['achievement_id']; ?>">

                    <label>Award Name:</label><br>
                    <input type="text" name="award_name" value="<?php echo htmlspecialchars($edit_data['award_name']); ?>"
                        required><br><br>

                    <label>Achievement Date:</label><br>
                    <input type="date" name="achievement_date" value="<?php echo $edit_data['achievement_date']; ?>"
                        required><br><br>

                    <label>Achievement Description:</label><br>
                    <textarea name="achievement_description" rows="4"
                        required><?php echo htmlspecialchars($edit_data['achievement_description']); ?></textarea><br><br>

                    <div class="achievement-type">
                        <label><input type="radio" name="achievement_type" value="student" <?php echo (!empty($edit_data['st_ID'])) ? 'checked' : ''; ?>> Student Achievement</label>
                        <label><input type="radio" name="achievement_type" value="teacher" <?php echo (!empty($edit_data['teacher_ID'])) ? 'checked' : ''; ?>> Teacher Achievement</label>
                    </div><br>

                    <!-- Student Achievement Fields -->
                    <div id="edit-student-fields" <?php echo (empty($edit_data['st_ID'])) ? 'style="display: none;"' : ''; ?>>
                        <label>Student:</label><br>
                        <select name="student_id" id="edit-student-dropdown" class="input-field">
                            <option value="">Select Student</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['st_ID']; ?>" <?php echo ($edit_data['st_ID'] == $student['st_ID']) ? 'selected' : ''; ?>>
                                    <?php echo $student['applicant_name'] . ' (ID: ' . $student['student_id'] . ', Class: ' . $student['class_name'] . ' ' . $student['group_'] . ')'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select><br><br>

                        <label>Event:</label><br>
                        <select name="event_id" id="edit-event-dropdown" class="input-field">
                            <option value="">Select Event</option>
                            <?php foreach ($events as $event): ?>
                                <option value="<?php echo $event['event_id']; ?>" <?php echo ($edit_data['event_id'] == $event['event_id']) ? 'selected' : ''; ?>>
                                    <?php echo $event['title'] . ' (' . $event['category'] . ', ' . date('d M Y', strtotime($event['event_date'])) . ')'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select><br><br>
                    </div>

                    <!-- Teacher Achievement Fields -->
                    <div id="edit-teacher-fields" <?php echo (empty($edit_data['teacher_ID'])) ? 'style="display: none;"' : ''; ?>>
                        <label>Teacher:</label><br>
                        <select name="teacher_id" id="edit-teacher-dropdown" class="input-field">
                            <option value="">Select Teacher</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?php echo $teacher['teacher_ID']; ?>" <?php echo ($edit_data['teacher_ID'] == $teacher['teacher_ID']) ? 'selected' : ''; ?>>
                                    <?php echo $teacher['teacher_name'] . ' (' . $teacher['designation'] . ', ' . $teacher['phone_number'] . ')'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select><br><br>
                    </div>

                    <button type="submit" name="update_achievement">Update Achievement</button>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="cancel-btn">Cancel</a>
                </form>
            <?php endif; ?>

        </div>

        <!-- upload photos or videos-->

        <!-- <div class="upload-container"> -->
        <h1 class="upload-title">Upload Photos & Videos</h1>

        <?php if (!empty($message_)): ?>
            <div class="messages_ <?php echo $messageType; ?>">
                <?php
                if (is_array($message_)) {
                    echo htmlspecialchars(implode(', ', $message_));
                } else {
                    echo htmlspecialchars($message_);
                }
                ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
            enctype="multipart/form-data">
            <div class="form-group">
                <label>Media Type:</label>
                <div class="radio-group">
                    <label class="radio-container">
                        <input type="radio" name="media_type" value="photo" checked> Photo
                    </label>
                    <label class="radio-container">
                        <input type="radio" name="media_type" value="video"> Video
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" placeholder="Enter a title for your media">
            </div>

            <div class="form-group">
                <label for="media_file">Select File:</label>
                <div class="file-input-wrapper">
                    <div class="file-input-button" id="file-input-label">Choose a file</div>
                    <input type="file" id="media_file" name="media_file" class="custom-file-input">
                </div>
                <div class="file-input-text" id="file-name">No file selected</div>
                <div id="fileTypeInfo">Allowed photo types: JPG, JPEG, PNG, GIF</div>
            </div>

            <div id="preview" class="preview-container"></div>

            <div class="form-group">
                <button type="submit">Upload</button>
            </div>
        </form>
        <!-- </div> -->

        <!-- Logout Button -->
        <button class="logout-btn" onclick="window.location.href='admin_login.php'">Logout</button>
    </div>




    <?php include('footer.php'); ?>

    <script>

        //update school info
        document.getElementById('schoolForm').addEventListener('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            fetch('update_school_info.php', {
                method: 'POST',
                body: formData
            }).then(response => response.text())
                .then(data => {
                    document.getElementById('school-status').innerText = "Updated successfully!";
                });
        });


        //update message
        document.querySelectorAll('.messageForm').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                let status = this.querySelector('.status');
                let formData = new FormData(this);
                fetch('update_message.php', {
                    method: 'POST',
                    body: formData
                }).then(response => response.text())
                    .then(data => {
                        status.innerText = "Updated successfully!";
                    });
            });
        });

        //update admin info
        document.getElementById('adminUpdateForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const status = document.getElementById('admin-status');
            const formData = new FormData(this);

            fetch('update_admin_info.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(data => {
                    if (data === "username_exists") {
                        status.innerText = "Username is not available. Please choose a different username.";
                        status.style.color = "red";
                    } else if (data === "success") {
                        status.innerText = "Admin login info updated successfully!";
                        status.style.color = "green";
                    } else {
                        status.innerText = "Error updating login info. Please try again.";
                        status.style.color = "red";
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    status.innerText = "An error occurred. Please try again.";
                    status.style.color = "red";
                });
        });

        //marks entry section
        function fetchAcademicYears(sectionType) {
            let className, group, dropdownId;

            if (sectionType === 'attendance') {
                className = document.querySelector("[name='class_name']").value;
                group = document.querySelector("[name='group_']").value;
                dropdownId = "academicYearDropdown";
            } else if (sectionType === 'marks') {
                className = document.querySelector("[name='class_name_mark']").value;
                group = document.querySelector("[name='group_mark']").value;
                dropdownId = "academicYearDropdownMarks";
            }

            if (className && group) {
                const xhr = new XMLHttpRequest();
                xhr.open("GET", `get_students_by_class_group.php?class_name=${className}&group_=${group}`, true);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        document.getElementById(dropdownId).innerHTML = xhr.responseText;
                    }
                };
                xhr.send();
            }
        }

        function fetchStudents() {
            const className = document.querySelector("[name='class_name']").value;
            const group = document.querySelector("[name='group_']").value;
            const academicYear = document.querySelector("[name='academic_year']").value;
            if (className && group && academicYear) {
                const xhr = new XMLHttpRequest();
                xhr.open("GET", `get_students_by_class_group.php?class_name=${className}&group_=${group}&academic_year=${academicYear}`, true);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        document.getElementById("studentDropdown").innerHTML = xhr.responseText;
                    }
                };
                xhr.send();
            }
        }

        function fetchStudentsForMarks() {
            const className = document.querySelector("[name='class_name_mark']").value;
            const group = document.querySelector("[name='group_mark']").value;
            const academicYear = document.querySelector("[name='academic_year_mark']").value;
            if (className && group && academicYear) {
                const xhr = new XMLHttpRequest();
                xhr.open("GET", `get_students_by_class_group.php?class_name=${className}&group_=${group}&academic_year=${academicYear}`, true);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        document.getElementById("studentDropdownMarks").innerHTML = xhr.responseText;
                    }
                };
                xhr.send();
            }
        }

        function fetchStudentID() {
            const studentId = document.querySelector("[name='student_id_mark']").value;
            const className = document.querySelector("[name='class_name_mark']").value;
            const group = document.querySelector("[name='group_mark']").value;
            const academicYear = document.querySelector("[name='academic_year_mark']").value;

            if (studentId && className && group && academicYear) {
                const xhr = new XMLHttpRequest();
                xhr.open("GET", `get_student_id.php?student_id=${studentId}&class_name=${className}&group_=${group}&academic_year=${academicYear}`, true);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        const stID = xhr.responseText;
                        if (stID && !isNaN(stID)) {
                            fetchSubjects(stID);
                        } else {
                            document.getElementById("subjectMarksSection").innerHTML = "Error: Could not find student ID";
                        }
                    }
                };
                xhr.send();
            }
        }

        function fetchSubjects(stID) {
            if (stID) {
                const xhr = new XMLHttpRequest();
                xhr.open("GET", "get_subjects_by_student.php?st_ID=" + stID, true);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        document.getElementById("subjectMarksSection").innerHTML = xhr.responseText;
                    }
                };
                xhr.send();
            }
        }

        //admission result entry section
        function fetchAdmissionYears() {
            const classGroupValue = document.querySelector("[name='class_group']").value;
            if (classGroupValue) {
                const [className, group] = classGroupValue.split('_');

                const xhr = new XMLHttpRequest();
                xhr.open("GET", `get_admission_years.php?class_name=${className}&group_=${group}`, true);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        document.getElementById("admissionYearDropdown").innerHTML = xhr.responseText;
                    }
                };
                xhr.send();
            }
        }

        function fetchApplicants() {
            const classGroupValue = document.querySelector("[name='class_group']").value;
            const admissionYear = document.querySelector("[name='admission_year']").value;

            if (classGroupValue && admissionYear) {
                const [className, group] = classGroupValue.split('_');

                const xhr = new XMLHttpRequest();
                xhr.open("GET", `get_applicants.php?class_name=${className}&group_=${group}&admission_year=${admissionYear}`, true);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        document.getElementById("applicantDropdown").innerHTML = xhr.responseText;
                    }
                };
                xhr.send();
            }
        }

        function checkExistingMarks() {
            const applicantId = document.querySelector("[name='applicant_id']").value;

            if (applicantId) {
                const xhr = new XMLHttpRequest();
                xhr.open("GET", `check_admission_result.php?admission_id=${applicantId}`, true);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.exists) {
                            document.getElementById("admissionMarks").value = response.marks;
                        } else {
                            document.getElementById("admissionMarks").value = "";
                        }
                    }
                };
                xhr.send();
            }
        }



        document.getElementById('admissionResultForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const form = this; // Store reference to the form

            fetch('admin_insert_admission_result.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(data => {
                    document.getElementById('admission-result-status').innerText = data;

                    // Reset form fields after successful submission
                    if (data.includes("successfully")) {
                        // Complete form reset
                        form.reset();

                        // Clear dropdowns that depend on other selections
                        document.getElementById("admissionYearDropdown").innerHTML = "<option value=''>Select Admission Year</option>";
                        document.getElementById("applicantDropdown").innerHTML = "<option value=''>Select Applicant</option>";

                        // Additional timeout to ensure UI updates
                        setTimeout(() => {
                            // Trigger change events on selects to ensure any dependent fields update
                            const event = new Event('change');
                            document.querySelector("[name='class_group']").dispatchEvent(event);
                        }, 100);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('admission-result-status').innerText = "Error occurred during submission";
                });
        });


        //student admission preocess
        function fetchEligibleApplicants() {
            const classGroupValue = document.querySelector("[name='admission_class_group']").value;

            if (classGroupValue) {
                const [className, group] = classGroupValue.split('_');
                const container = document.getElementById("applicantListContainer");
                container.innerHTML = "<p>Loading eligible applicants...</p>";

                const xhr = new XMLHttpRequest();
                xhr.open("GET", `get_eligible_applicants.php?class_name=${className}&group_=${group}`, true);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        container.innerHTML = xhr.responseText;
                    }
                };
                xhr.send();
            } else {
                document.getElementById("applicantListContainer").innerHTML = "";
            }
        }

        //teacher update
        // Get the modal
        var modal = document.getElementById("editModal");

        // Function to open the edit modal
        function openEditModal(teacherId, teacherName, dob, joiningDate, designation, serviceStatus, teachingSubject,
            phoneNumber, email, qualification, district, userName, userPassword) {
            document.getElementById("teacher_id").value = teacherId;
            document.getElementById("teacher_name").value = teacherName;
            document.getElementById("date_of_birth").value = formatDateForInput(dob);
            document.getElementById("joining_date").value = formatDateForInput(joiningDate);
            document.getElementById("designation").value = designation;
            document.getElementById("service_status").value = serviceStatus;
            document.getElementById("teaching_subject").value = teachingSubject;
            document.getElementById("phone_number").value = phoneNumber;
            document.getElementById("email").value = email;
            document.getElementById("qualification").value = qualification;
            document.getElementById("district").value = district;
            document.getElementById("user_name").value = userName;
            document.getElementById("user_password").value = userPassword;
            modal.style.display = "block";
        }

        // Function to format date for input field (YYYY-MM-DD)
        function formatDateForInput(dateStr) {
            if (!dateStr) return '';

            // Check if the date is already in YYYY-MM-DD format
            if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
                return dateStr;
            }

            // Parse the date string (assuming it's in a format like "MM/DD/YYYY" or "DD/MM/YYYY")
            const parts = dateStr.split(/[\/\-\.]/);

            // Try to determine the format and convert to YYYY-MM-DD
            if (parts.length === 3) {
                let year, month, day;

                // If the first part is 4 digits, assume YYYY-MM-DD
                if (parts[0].length === 4) {
                    year = parts[0];
                    month = parts[1].padStart(2, '0');
                    day = parts[2].padStart(2, '0');
                }
                // If the last part is 4 digits, assume MM/DD/YYYY or DD/MM/YYYY
                else if (parts[2].length === 4) {
                    year = parts[2];
                    // Make a best guess (this might not be accurate for all locales)
                    // Assuming MM/DD/YYYY for simplicity
                    month = parts[0].padStart(2, '0');
                    day = parts[1].padStart(2, '0');
                } else {
                    // Fallback to current date if format is unknown
                    const today = new Date();
                    year = today.getFullYear();
                    month = String(today.getMonth() + 1).padStart(2, '0');
                    day = String(today.getDate()).padStart(2, '0');
                }

                return `${year}-${month}-${day}`;
            }

            return '';
        }

        // Function to close the edit modal
        function closeEditModal() {
            modal.style.display = "none";
        }

        // Close the modal when clicking outside of it
        window.onclick = function (event) {
            if (event.target == modal) {
                closeEditModal();
            }
        }

        //Notice edition
        function editNotice(id, title, description) {
            // Decode HTML entities in title and description
            title = title.replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"');
            description = description.replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"');

            // Set form values
            document.getElementById('edit_notice_id').value = id;
            document.getElementById('edit_notice_title').value = title;
            document.getElementById('edit_notice_description').value = description;

            // Show modal
            document.getElementById('editNoticeModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editNoticeModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function (event) {
            const modal = document.getElementById('editNoticeModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        };

        //achievement
        // Toggle student/teacher fields based on selection
        document.addEventListener('DOMContentLoaded', function () {
            const achievementTypeRadios = document.querySelectorAll('input[name="achievement_type"]');
            const studentFields = document.getElementById('student-fields');
            const teacherFields = document.getElementById('teacher-fields');

            achievementTypeRadios.forEach(radio => {
                radio.addEventListener('change', function () {
                    if (this.value === 'student') {
                        studentFields.style.display = 'block';
                        teacherFields.style.display = 'none';
                        document.getElementById('teacher-dropdown').value = '';
                    } else {
                        studentFields.style.display = 'none';
                        teacherFields.style.display = 'block';
                        document.getElementById('student-dropdown').value = '';
                        document.getElementById('event-dropdown').value = '';
                    }
                });
            });

            // Form validation
            document.getElementById('achievementForm').addEventListener('submit', function (e) {
                const achievementType = document.querySelector('input[name="achievement_type"]:checked').value;

                if (achievementType === 'student') {
                    const studentId = document.getElementById('student-dropdown').value;
                    const eventId = document.getElementById('event-dropdown').value;

                    if (!studentId || !eventId) {
                        e.preventDefault();
                        alert('Please select both a student and an event for student achievement.');
                    }
                } else {
                    const teacherId = document.getElementById('teacher-dropdown').value;

                    if (!teacherId) {
                        e.preventDefault();
                        alert('Please select a teacher for teacher achievement.');
                    }
                }
            });
        });

        //upload photos or videos

        const photoRadio = document.querySelector('input[value="photo"]');
        const videoRadio = document.querySelector('input[value="video"]');
        const fileTypeInfo = document.getElementById('fileTypeInfo');
        const mediaFileInput = document.getElementById('media_file');
        const previewDiv = document.getElementById('preview');
        const fileNameDisplay = document.getElementById('file-name');
        const fileInputLabel = document.getElementById('file-input-label');

        photoRadio.addEventListener('change', updateFileInfo);
        videoRadio.addEventListener('change', updateFileInfo);

        function updateFileInfo() {
            if (photoRadio.checked) {
                fileTypeInfo.textContent = "Allowed photo types: JPG, JPEG, PNG, GIF (Max 5MB)";
            } else {
                fileTypeInfo.textContent = "Allowed video types: MP4, AVI, MOV, WMV, WEBM (Max 50MB)";
            }
        }

        // Preview functionality
        mediaFileInput.addEventListener('change', function (e) {
            const file = this.files[0];
            previewDiv.innerHTML = '';

            if (file) {
                // Update filename display
                fileNameDisplay.textContent = file.name;
                fileInputLabel.textContent = "Change file";

                previewDiv.style.display = 'block';

                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.onload = function () {
                        URL.revokeObjectURL(this.src);
                    }
                    previewDiv.appendChild(img);

                    // Auto-select photo radio if it's an image
                    photoRadio.checked = true;
                    updateFileInfo();
                }
                else if (file.type.startsWith('video/')) {
                    const video = document.createElement('video');
                    video.src = URL.createObjectURL(file);
                    video.controls = true;
                    previewDiv.appendChild(video);

                    // Auto-select video radio if it's a video
                    videoRadio.checked = true;
                    updateFileInfo();
                }
            } else {
                fileNameDisplay.textContent = "No file selected";
                fileInputLabel.textContent = "Choose a file";
                previewDiv.style.display = 'none';
            }
        });

        // Ensure the file input triggers correctly
        document.querySelector('.file-input-wrapper').addEventListener('click', function (e) {
            if (e.target !== mediaFileInput) {
                mediaFileInput.click();
            }
        });

        // Run initial update
        updateFileInfo();
    </script>
</body>

</html>

<?php $conn->close(); ?>