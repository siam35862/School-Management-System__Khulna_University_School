<?php include('nav.php'); ?>
<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo "Unauthorized access";
    exit();
}

include 'db_connection.php';

// Get admission_id from URL
$admission_id = $_GET['admission_id'] ?? 0;

if (!$admission_id) {
    echo "Invalid admission ID";
    exit();
}

// First, check if this application has already been processed
$check_query = "SELECT * FROM student WHERE admission_id = '$admission_id'";
$check_result = $conn->query($check_query);
if ($check_result->num_rows > 0) {
    echo "This applicant has already been admitted.";
    exit();
}

// Get admission form details
$form_query = "SELECT * FROM admission_form WHERE admission_id = '$admission_id'";
$form_result = $conn->query($form_query);

if ($form_result->num_rows == 0) {
    echo "Application not found";
    exit();
}

$applicant_data = $form_result->fetch_assoc();
$class_id = $applicant_data['class_id'];
$admission_year = $applicant_data['admission_year'];
$applicant_name = $applicant_data['applicant_name'];
$optional_subject = $applicant_data['optional_subject'];

// If form submitted, process the admission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $village = $conn->real_escape_string($_POST['village'] ?? $applicant_data['village']);
    $post_code = $conn->real_escape_string($_POST['post_code'] ?? $applicant_data['post_code']);
    $upazila = $conn->real_escape_string($_POST['upazila'] ?? $applicant_data['upazila']);
    $zila = $conn->real_escape_string($_POST['zila'] ?? $applicant_data['zila']);
    
    // Current date for admission_date
    $admission_date = date('Y-m-d');
    
    // Generate student_id
    $student_id_query = "SELECT MAX(student_id) as max_id FROM student 
                         WHERE class_id = '$class_id' AND academic_year = '$admission_year'";
    $student_id_result = $conn->query($student_id_query);
    $student_id_data = $student_id_result->fetch_assoc();
    
    if ($student_id_data['max_id']) {
        $student_id = $student_id_data['max_id'] + 1;
    } else {
        $student_id = ($admission_year * 100) + 1;
    }
    
    $conn->begin_transaction();
    
    try {
        $insert_student = "INSERT INTO student 
                          (student_id, admission_date, class_id, academic_year, admission_id, 
                           optional_subject, village, post_code, upazila, zila)
                          VALUES 
                          ('$student_id', '$admission_date', '$class_id', '$admission_year', '$admission_id', 
                           '$optional_subject', '$village', '$post_code', '$upazila', '$zila')";
        
        if (!$conn->query($insert_student)) {
            throw new Exception("Error inserting student: " . $conn->error);
        }
        
        $st_ID = $conn->insert_id;
        
        $base_username = strtolower(preg_replace('/[^a-z0-9]/i', '', $applicant_name));
        $username = $base_username . $student_id;
        $password = 'pass' . $student_id;
        
        $username_check = true;
        $counter = 1;
        
        while ($username_check) {
            $check_username = "SELECT * FROM student_login WHERE user_name = '$username'";
            $username_result = $conn->query($check_username);
            
            if ($username_result->num_rows == 0) {
                $username_check = false;
            } else {
                $username = $base_username . $student_id . $counter;
                $counter++;
            }
        }
        
        $insert_login = "INSERT INTO student_login (user_name, user_password, st_ID)
                         VALUES ('$username', '$password', '$st_ID')";
        
        if (!$conn->query($insert_login)) {
            throw new Exception("Error creating login: " . $conn->error);
        }
        
        $subjects_query = "SELECT subject_id FROM subject WHERE class_id = '$class_id'";
        $subjects_result = $conn->query($subjects_query);
        
        if ($subjects_result->num_rows > 0) {
            while ($subject = $subjects_result->fetch_assoc()) {
                $subject_id = $subject['subject_id'];
                
                $insert_subject = "INSERT INTO student_subject (st_ID, subject_id)
                                  VALUES ('$st_ID', '$subject_id')";
                
                if (!$conn->query($insert_subject)) {
                    throw new Exception("Error assigning subject: " . $conn->error);
                }
            }
        }
        
        $conn->commit();
        
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="nav.css">
            <link rel="stylesheet" href="footer.css">
            <title>Admission Success</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 20px;
                }
                .success-container {
                    max-width: 600px;
                    margin: 0 auto;
                    background: #e8f5e9;
                    padding: 30px;
                    border-radius: 8px;
                    text-align: center;
                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                }
                h2 {
                    color: #2e7d32;
                    margin-bottom: 20px;
                }
                p {
                    font-size: 18px;
                    margin: 10px 0;
                }
                .important {
                    color: #d32f2f;
                    font-weight: bold;
                }
                .btn {
                    display: inline-block;
                    margin-top: 20px;
                    background-color: #2196F3;
                    color: white;
                    padding: 12px 20px;
                    text-decoration: none;
                    border-radius: 4px;
                    font-size: 16px;
                }
                .btn:hover {
                    background-color: #0b7dda;
                }
            </style>
        </head>
        <body>
            <div class="success-container">
                <h2>Student Admitted Successfully!</h2>
                <p><strong>Student ID:</strong> <?php echo $student_id; ?></p>
                <p><strong>Student Name:</strong> <?php echo htmlspecialchars($applicant_name); ?></p>
                <p><strong>Username:</strong> <?php echo $username; ?></p>
                <p><strong>Password:</strong> <?php echo $password; ?></p>
                <p class="important">Please note down these credentials for future use.</p>
                <a href="admin_dashboard.php" class="btn">Back to Dashboard</a>
            </div>
            <?php include('footer.php'); ?>
        </body>
        </html>
        <?php
        exit();
        
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}

// Display form to collect address information
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="nav.css">
    <link rel="stylesheet" href="footer.css">
    <title>Student Admission</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        h2 {
            color: #004080;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn-submit {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-submit:hover {
            background-color: #45a049;
        }
        .applicant-info {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Student Admission</h2>
        
        <div class="applicant-info">
            <h3>Applicant Information</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($applicant_data['applicant_name']); ?></p>
            <p><strong>Applicant ID:</strong> <?php echo htmlspecialchars($applicant_data['applicant_id']); ?></p>
            <p><strong>Father's Name:</strong> <?php echo htmlspecialchars($applicant_data['father_name']); ?></p>
            <p><strong>Mother's Name:</strong> <?php echo htmlspecialchars($applicant_data['mother_name']); ?></p>
        </div>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="village">Village:</label>
                <input type="text" id="village" name="village" value="<?php echo htmlspecialchars($applicant_data['village']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="post_code">Post Code:</label>
                <input type="text" id="post_code" name="post_code" value="<?php echo htmlspecialchars($applicant_data['post_code']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="upazila">Upazila:</label>
                <input type="text" id="upazila" name="upazila" value="<?php echo htmlspecialchars($applicant_data['upazila']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="zila">Zila:</label>
                <input type="text" id="zila" name="zila" value="<?php echo htmlspecialchars($applicant_data['zila']); ?>" required>
            </div>
            
            <button type="submit" class="btn-submit">Confirm Admission</button>
        </form>
    </div>
    <?php include('footer.php'); ?>
</body>
</html>

<?php $conn->close(); ?>
