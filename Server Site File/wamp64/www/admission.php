<?php
session_start();

include 'nav.php';

include 'db_connection.php';

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Using prepared statement for class query - MODIFIED THIS PART TO INCLUDE group_
$classOptions = '';
$classStmt = $conn->prepare("SELECT class_id, class_name, group_ FROM class WHERE class_name IN (?, ?, ?, ?)");
$six = "Six";
$seven = "Seven";
$eight = "Eight";
$nine = "Nine";
$classStmt->bind_param("ssss", $six, $seven, $eight, $nine);
$classStmt->execute();
$classResult = $classStmt->get_result();

if ($classResult && $classResult->num_rows > 0) {
    while ($row = $classResult->fetch_assoc()) {
        // Modified to show both class name and group name in the dropdown
        // Add the group_ value as a data attribute
        $groupInfo = !empty($row['group_']) ? " - {$row['group_']}" : "";
        $groupValue = !empty($row['group_']) ? $row['group_'] : "";
        $classOptions .= "<option value='{$row['class_id']}' data-group='{$groupValue}'>{$row['class_name']} {$groupInfo}</option>";
    }
}
$classStmt->close();

$showForm = true;
$successMessage = '';
$errorMessage = '';

// Display success message from session if exists
if (isset($_SESSION['success_message'])) {
    $showForm = false;
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Form submission processing
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errorMessage = "Form submission error: Invalid security token.";
    } else {
        // Sanitize inputs
        $name = htmlspecialchars(trim($_POST["student_name"] ?? ''));
        $father = htmlspecialchars(trim($_POST["father_name"] ?? ''));
        $mother = htmlspecialchars(trim($_POST["mother_name"] ?? ''));
        $dob = htmlspecialchars(trim($_POST["dob"] ?? ''));
        $gender = htmlspecialchars(trim($_POST["gender"] ?? ''));
        $class_id = filter_var($_POST["class"] ?? '', FILTER_SANITIZE_NUMBER_INT);
        $lastSchool = htmlspecialchars(trim($_POST["last_school"] ?? ''));
        $guardianPhone = htmlspecialchars(trim($_POST["guardian_phone"] ?? ''));
        $village = htmlspecialchars(trim($_POST["village"] ?? ''));
        $postcode = htmlspecialchars(trim($_POST["post_code"] ?? ''));
        $upazila = htmlspecialchars(trim($_POST["upazila"] ?? ''));
        $zila = htmlspecialchars(trim($_POST["zila"] ?? ''));
        $phone = htmlspecialchars(trim($_POST["phone"] ?? ''));
        $email = filter_var($_POST["email"] ?? '', FILTER_SANITIZE_EMAIL);
        $year = date("Y");
        
        // Get group and optional subject
        $group = isset($_POST["group"]) ? htmlspecialchars(trim($_POST["group"])) : "Not Applicable";
        $optional_subject = isset($_POST["optional_subject"]) ? strtoupper(htmlspecialchars(trim($_POST["optional_subject"]))) : "NOT APPLICABLE";

        // Validate required fields
        $errors = [];
        if (empty($name)) $errors[] = "Student name is required";
        if (empty($father)) $errors[] = "Father's name is required";
        if (empty($mother)) $errors[] = "Mother's name is required";
        if (empty($dob)) $errors[] = "Date of birth is required";
        if (empty($gender)) $errors[] = "Gender is required";
        if (empty($class_id)) $errors[] = "Class selection is required";
        if (empty($guardianPhone)) $errors[] = "Guardian phone number is required";
        if (empty($village)) $errors[] = "Village is required";
        if (empty($postcode)) $errors[] = "Post code is required";
        if (empty($upazila)) $errors[] = "Upazila is required";
        if (empty($zila)) $errors[] = "Zila is required";
        if (empty($phone)) $errors[] = "Phone number is required";

        // Format validation
        if (!empty($phone) && !preg_match("/^01[0-9]{9}$/", $phone)) {
            $errors[] = "Phone number must be 11 digits and start with '01'";
        }
        if (!empty($guardianPhone) && !preg_match("/^01[0-9]{9}$/", $guardianPhone)) {
            $errors[] = "Guardian phone number must be 11 digits and start with '01'";
        }
        if (!empty($postcode) && !preg_match("/^[0-9]{4}$/", $postcode)) {
            $errors[] = "Post code must be exactly 4 digits";
        }
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }

        if (count($errors) > 0) {
            $errorMessage = "Please correct the following errors:<br>" . implode("<br>", $errors);
        } else {
            $applicant_id = 'adm' . substr($phone, -9);
            
            // Check if this applicant_id already exists
            $checkStmt = $conn->prepare("SELECT applicant_id FROM admission_form WHERE applicant_id = ?");
            $checkStmt->bind_param("s", $applicant_id);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                $errorMessage = "An application with this phone number already exists. Please use a different phone number.";
            } else {
                try {
                    $stmt = $conn->prepare("INSERT INTO admission_form (applicant_name, applicant_id, date_of_birth, father_name, mother_name, guardian_mobile_number, mobile_number, previous_school_name, class_id, admission_year, email, gender, village, post_code, upazila, zila, group_, optional_subject) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssssssississssss", $name, $applicant_id, $dob, $father, $mother, $guardianPhone, $phone, $lastSchool, $class_id, $year, $email, $gender, $village, $postcode, $upazila, $zila, $group, $optional_subject);
                    
                    if ($stmt->execute()) {
                        // Use POST-REDIRECT-GET pattern to prevent form resubmission
                        $_SESSION['success_message'] = "Your application was submitted successfully!<br>Your Applicant ID is: <strong>$applicant_id</strong>";
                        header("Location: ".$_SERVER['PHP_SELF']);
                        exit;
                    } else {
                        $errorMessage = "Error submitting your application: " . $stmt->error;
                    }
                    $stmt->close();
                } catch (mysqli_sql_exception $e) {
                    $errorMessage = "Database error: " . $e->getMessage();
                }
                $checkStmt->close();
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Admission Form</title>
  <link rel="stylesheet" href="footer.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f7f7f7;
    }
    h2 {
      text-align: center;
      margin-top: 30px;
    }
    form {
      width: 700px;
      margin: 40px auto;
      background-color: #ffffff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    .form-row {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
    }
    label {
      width: 220px;
      font-weight: bold;
    }
    input[type="text"],
    input[type="number"],
    input[type="date"],
    input[type="email"],
    select {
      flex: 1;
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    fieldset {
      border: none;
      margin: 0;
      padding: 0;
    }
    .address-section .form-row {
      margin-left: 20px;
    }
    .radio-group {
      display: flex;
      gap: 20px;
      align-items: center;
    }
    .radio-group label {
      width: auto;
      font-weight: normal;
    }
    .buttons {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        button {
            padding: 10px 20px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .submit-btn {
            background-color: #0275d8;
            color: white;
        }
        .reset-btn {
            background-color: #d9534f;
            color: white;
        }
    .success-box {
      width: 600px;
      margin: 50px auto;
      padding: 30px;
      background-color: #e6ffe6;
      border: 2px solid #00cc00;
      text-align: center;
      border-radius: 10px;
      font-size: 18px;
    }
    .error-box {
      width: 600px;
      margin: 20px auto;
      padding: 15px;
      background-color: #ffebeb;
      border: 2px solid #ff3333;
      text-align: center;
      border-radius: 10px;
      font-size: 16px;
      color: #cc0000;
    }
    #group-section, #optional-subject-section {
      display: none;
    }
    .required-field::after {
      content: " *";
      color: red;
    }
    .form-info {
      width: 700px;
      margin: 0 auto 20px;
      padding: 10px;
      text-align: center;
      color: #555;
    }
    .form-info span {
      color: red;
      font-weight: bold;
    }
  </style>
</head>
<body>

<h2>Student Admission Form</h2>

<div class="form-info">Fields marked with <span>*</span> are required</div>

<?php if (!empty($errorMessage)): ?>
  <div class="error-box">
    <?= $errorMessage ?>
  </div>
<?php endif; ?>

<?php if ($showForm): ?>
<form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" novalidate>
  <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
  
  <div class="form-row">
    <label class="required-field">Student Name:</label>
    <input type="text" name="student_name" required value="<?= isset($_POST['student_name']) ? htmlspecialchars($_POST['student_name']) : '' ?>">
  </div>

  <div class="form-row">
    <label class="required-field">Father's Name:</label>
    <input type="text" name="father_name" required value="<?= isset($_POST['father_name']) ? htmlspecialchars($_POST['father_name']) : '' ?>">
  </div>

  <div class="form-row">
    <label class="required-field">Mother's Name:</label>
    <input type="text" name="mother_name" required value="<?= isset($_POST['mother_name']) ? htmlspecialchars($_POST['mother_name']) : '' ?>">
  </div>

  <div class="form-row">
    <label class="required-field">Date of Birth:</label>
    <input type="date" name="dob" required value="<?= isset($_POST['dob']) ? $_POST['dob'] : '' ?>">
  </div>

  <div class="form-row">
    <label class="required-field">Gender:</label>
    <div class="radio-group">
      <label><input type="radio" name="gender" value="male" required <?= (isset($_POST['gender']) && $_POST['gender'] == 'male') ? 'checked' : '' ?>> Male</label>
      <label><input type="radio" name="gender" value="female" <?= (isset($_POST['gender']) && $_POST['gender'] == 'female') ? 'checked' : '' ?>> Female</label>
    </div>
  </div>

  <div class="form-row">
    <label class="required-field">Aspirants Class:</label>
    <select name="class" id="class-select" required>
      <option value="">--Select Class--</option>
      <?= $classOptions ?>
    </select>
  </div>

  <!-- Group section is hidden but still included for form submission -->
  <div class="form-row" id="group-section" style="display: none;">
    <label class="required-field">Group:</label>
    <select name="group" id="group-select">
      <option value="">--Select Group--</option>
      <option value="Science" <?= (isset($_POST['group']) && $_POST['group'] == 'Science') ? 'selected' : '' ?>>Science</option>
      <option value="Humanities" <?= (isset($_POST['group']) && $_POST['group'] == 'Humanities') ? 'selected' : '' ?>>Humanities</option>
      <option value="Commerce" <?= (isset($_POST['group']) && $_POST['group'] == 'Commerce') ? 'selected' : '' ?>>Commerce</option>
    </select>
  </div>

  <div class="form-row" id="optional-subject-section">
    <label class="required-field">Optional Subject:</label>
    <select name="optional_subject" id="optional-subject">
      <option value="">--Select Optional Subject--</option>
    </select>
  </div>

  <div class="form-row">
    <label>School Last Attended:</label>
    <input type="text" name="last_school" value="<?= isset($_POST['last_school']) ? htmlspecialchars($_POST['last_school']) : '' ?>">
  </div>

  <div class="form-row">
    <label class="required-field">Guardian Phone Number:</label>
    <input type="text" name="guardian_phone" required placeholder="Format: 01XXXXXXXXX" pattern="^01[0-9]{9}$" value="<?= isset($_POST['guardian_phone']) ? htmlspecialchars($_POST['guardian_phone']) : '' ?>">
  </div>

  <fieldset class="address-section">
    <legend style="font-weight:bold; padding: 10px 0;">Present Address</legend>
    <div class="form-row">
      <label class="required-field">Village:</label>
      <input type="text" name="village" required value="<?= isset($_POST['village']) ? htmlspecialchars($_POST['village']) : '' ?>">
    </div>
    <div class="form-row">
      <label class="required-field">Post Code:</label>
      <input type="text" name="post_code" required placeholder="4-digit code" pattern="^[0-9]{4}$" value="<?= isset($_POST['post_code']) ? htmlspecialchars($_POST['post_code']) : '' ?>">
    </div>
    <div class="form-row">
      <label class="required-field">Upazila:</label>
      <input type="text" name="upazila" required value="<?= isset($_POST['upazila']) ? htmlspecialchars($_POST['upazila']) : '' ?>">
    </div>
    <div class="form-row">
      <label class="required-field">Zila:</label>
      <input type="text" name="zila" required value="<?= isset($_POST['zila']) ? htmlspecialchars($_POST['zila']) : '' ?>">
    </div>
  </fieldset>

  <div class="form-row">
    <label class="required-field">Phone Number:</label>
    <input type="text" name="phone" required placeholder="Format: 01XXXXXXXXX" pattern="^01[0-9]{9}$" value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
  </div>

  <div class="form-row">
    <label>Email:</label>
    <input type="email" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
  </div>

  <div class="buttons">
            <button type="reset" class="reset-btn">Reset</button>
            <button type="submit" class="submit-btn">Submit</button>
        </div>
</form>
<?php else: ?>
  <div class="success-box">
    <?= $successMessage ?>
    <br><br>
    <a href="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>"><button>Submit Another Application</button></a>
  </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const classSelect = document.getElementById('class-select');
  const groupSection = document.getElementById('group-section');
  const optionalSubjectSection = document.getElementById('optional-subject-section');
  const groupSelect = document.getElementById('group-select');
  const optionalSubjectSelect = document.getElementById('optional-subject');
  
  // Always keep group section hidden but use its value
  groupSection.style.display = 'none';
  
  // Set initial state based on any previously selected class
  if (classSelect.value && classSelect.options[classSelect.selectedIndex].text.includes('Nine')) {
    // Extract the group from the selected option's data attribute
    const selectedOption = classSelect.options[classSelect.selectedIndex];
    const groupValue = selectedOption.getAttribute('data-group');
    groupSelect.value = groupValue;
    
    optionalSubjectSection.style.display = 'flex';
    optionalSubjectSelect.required = true;
    updateOptionalSubjects();
  }
  
  classSelect.addEventListener('change', function() {
    const selectedOptionText = classSelect.options[classSelect.selectedIndex].text;
    const selectedOption = classSelect.options[classSelect.selectedIndex];
    const groupValue = selectedOption.getAttribute('data-group');
    
    // Set the group value from the class selection
    groupSelect.value = groupValue;
    
    if (selectedOptionText.includes('Nine')) {
      // Keep group hidden but set its value
      optionalSubjectSection.style.display = 'flex';
      optionalSubjectSelect.required = true;
      updateOptionalSubjects();
    } else {
      optionalSubjectSection.style.display = 'none';
      optionalSubjectSelect.required = false;
      optionalSubjectSelect.innerHTML = '<option value="">--Select Optional Subject--</option>';
    }
  });
  
  function updateOptionalSubjects() {
    const selectedGroup = groupSelect.value;
    let options = '<option value="">--Select Optional Subject--</option>';
    
    if (selectedGroup === 'Science') {
      options += '<option value="BIOLOGY">Biology</option>';
      options += '<option value="HIGHER MATHEMATICS">Higher Mathematics</option>';
    } else if (selectedGroup === 'Humanities') {
      options += '<option value="AGRICULTURE STUDIES">Agriculture Studies</option>';
      
    } else if (selectedGroup === 'Commerce') {
      options += '<option value="AGRICULTURE STUDIES">Agriculture Studies</option>';
      
    }
    
    optionalSubjectSelect.innerHTML = options;
    
    // If there's a previously selected optional subject, try to reselect it
    <?php if(isset($_POST['optional_subject'])): ?>
    const previousValue = "<?= $_POST['optional_subject'] ?>";
    for(let i = 0; i < optionalSubjectSelect.options.length; i++) {
      if(optionalSubjectSelect.options[i].value === previousValue) {
        optionalSubjectSelect.options[i].selected = true;
        break;
      }
    }
    <?php endif; ?>
  }
  
  // Input validation helpers
  const phoneInputs = document.querySelectorAll('input[name="phone"], input[name="guardian_phone"]');
  phoneInputs.forEach(input => {
    input.addEventListener('input', function() {
      // Only allow numbers
      this.value = this.value.replace(/[^0-9]/g, '');
      
      // Max length of 11
      if (this.value.length > 11) {
        this.value = this.value.slice(0, 11);
      }
      
      // If not starting with 01, show warning
      if (this.value.length >= 2 && !this.value.startsWith('01')) {
        this.setCustomValidity('Phone number must start with 01');
      } else {
        this.setCustomValidity('');
      }
    });
  });
  
  const postcodeInput = document.querySelector('input[name="post_code"]');
  postcodeInput.addEventListener('input', function() {
    // Only allow numbers
    this.value = this.value.replace(/[^0-9]/g, '');
    
    // Max length of 4
    if (this.value.length > 4) {
      this.value = this.value.slice(0, 4);
    }
    
    // Validate length
    if (this.value.length > 0 && this.value.length !== 4) {
      this.setCustomValidity('Post code must be exactly 4 digits');
    } else {
      this.setCustomValidity('');
    }
  });
  
  // Set initial selected value for class if it was previously selected
  <?php if(isset($_POST['class'])): ?>
  const previousClass = "<?= $_POST['class'] ?>";
  for(let i = 0; i < classSelect.options.length; i++) {
    if(classSelect.options[i].value === previousClass) {
      classSelect.options[i].selected = true;
      break;
    }
  }
  <?php endif; ?>
});
</script>

<?php include 'footer.php'; ?>
</body>
</html>