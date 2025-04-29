<?php
session_start();
include('nav.php');

// DB Connection
include 'db_connection.php';

// Check login
if (!isset($_SESSION['st_ID'])) {
    echo "Session expired. Please log in again.";
    exit();
}

$st_ID = $_SESSION['st_ID'];
// Event registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];
    $stmt = $conn->prepare("INSERT INTO student_event (st_ID, event_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $st_ID, $event_id);
    $stmt->execute();
    echo "<script> window.location.href='student.php';</script>";
    exit();
}

// Handle profile update (AJAX or post)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fields = ['name', 'dob', 'father', 'mother', 'email', 'mobile', 'gender', 'presentAddress', 'permAddress', 'user_name', 'user_password'];
    foreach ($fields as $f) {
        if (!isset($_POST[$f])) $_POST[$f] = '';
    }

    // Split addresses into parts
    list($pres_village, $pres_post_code, $pres_upazila, $pres_zila) = array_pad(explode(',', $_POST['presentAddress']), 4, '');
    list($perm_village, $perm_post_code, $perm_upazila, $perm_zila) = array_pad(explode(',', $_POST['permAddress']), 4, '');

    // Update admission_form table
    $stmt = $conn->prepare("UPDATE admission_form af
        JOIN student s ON af.admission_id = s.admission_id 
        SET af.applicant_name=?, af.date_of_birth=?, af.father_name=?, af.mother_name=?, af.email=?, af.mobile_number=?, af.gender=?,
            af.village=?, af.post_code=?, af.upazila=?, af.zila=?,
            s.village=?, s.post_code=?, s.upazila=?, s.zila=?
        WHERE s.st_ID=?");
    $stmt->bind_param("sssssssssssssssi",
        $_POST['name'], $_POST['dob'], $_POST['father'], $_POST['mother'], $_POST['email'],
        $_POST['mobile'], $_POST['gender'],
        trim($pres_village), trim($pres_post_code), trim($pres_upazila), trim($pres_zila),
        trim($perm_village), trim($perm_post_code), trim($perm_upazila), trim($perm_zila),
        $st_ID
    );
    $stmt->execute();

    // Update login credentials
    $stmt2 = $conn->prepare("UPDATE student_login SET user_name = ?, user_password = ? WHERE st_ID = ?");
    $stmt2->bind_param("ssi", $_POST['user_name'], $_POST['user_password'], $st_ID);
    $stmt2->execute();

    echo "success";
    exit();
}

// Fetch student profile
$sql = "SELECT af.applicant_name, af.applicant_id, af.date_of_birth, af.father_name, af.mother_name, 
               af.guardian_mobile_number, af.mobile_number, af.previous_school_name, af.class_id, af.admission_year, 
               af.email, af.gender, af.village AS pres_village, af.post_code AS pres_post_code, 
               af.upazila AS pres_upazila, af.zila AS pres_zila,
               s.village AS perm_village, s.post_code AS perm_post_code, 
               s.upazila AS perm_upazila, s.zila AS perm_zila,
               s.optional_subject, s.student_id, c.class_name, c.group_
        FROM admission_form af 
        JOIN student s ON af.admission_id = s.admission_id 
        JOIN class c ON s.class_id = c.class_id
        WHERE s.st_ID = '$st_ID'";
$result = $conn->query($sql);
if ($result->num_rows > 0) $student_info = $result->fetch_assoc();
else { echo "No student data found."; exit(); }

// Fetch login info
$login_result = $conn->query("SELECT user_name, user_password FROM student_login WHERE st_ID = '$st_ID'");
$login_info = ($login_result->num_rows > 0) ? $login_result->fetch_assoc() : ['user_name' => 'N/A', 'user_password' => 'N/A'];

// Subjects
$subjects_result = $conn->query("SELECT sub.subject_title, sub.subject_code 
    FROM student_subject ss 
    JOIN subject sub ON ss.subject_id = sub.subject_id 
    WHERE ss.st_ID = '$st_ID'");

// Attendance
$attendance_result = $conn->query("SELECT COUNT(*) AS total_days, 
    SUM(CASE WHEN attendance_status = 'Present' THEN 1 ELSE 0 END) AS present_days,
    SUM(CASE WHEN attendance_status = 'Late' THEN 1 ELSE 0 END) AS late_days,
    SUM(CASE WHEN attendance_status = 'Absent' THEN 1 ELSE 0 END) AS absent_days
    FROM attendance WHERE st_ID = '$st_ID'");
$attendance_data = $attendance_result->fetch_assoc();

// Events
$events_stmt = $conn->prepare("SELECT * FROM institution_event WHERE event_date > NOW() AND event_id NOT IN (SELECT event_id FROM student_event WHERE st_ID = ?)");
$events_stmt->bind_param("i", $st_ID);
$events_stmt->execute();
$events_result = $events_stmt->get_result();
// Participated events
$participated_events_stmt = $conn->prepare("SELECT e.title, e.category, e.event_date FROM institution_event e 
    JOIN student_event se ON e.event_id = se.event_id 
    WHERE se.st_ID = ?");
$participated_events_stmt->bind_param("i", $st_ID);
$participated_events_stmt->execute();
$participated_events_result = $participated_events_stmt->get_result();

// Achievements
$achievements_result = $conn->query("SELECT award_name, achievement_date, achievement_description FROM achievement WHERE st_ID = '$st_ID'");


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Profile</title>
    <link rel="stylesheet" href="nav.css">
    <link rel="stylesheet" href="footer.css">
    <style>
        body { font-family: 'Segoe UI'; background: #f9f9f9; margin: 0; }
        .container { padding: 30px; background: #fff; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .section { margin-bottom: 30px; padding: 20px; border-radius: 10px; background: #f4f9ff; }
        .section p, .section li { margin: 8px 0; font-size: 15px; }
        h2 { color: #004080; margin-bottom: 10px; }
        .info-label { font-weight: bold; color: #333; }
        .save-button { margin-top: 10px; padding: 10px 20px; background-color: #004080; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .save-button:hover { background-color: #002c5f; }
        .centered { text-align: center; }
    </style>
</head>
<body>
<div class="container">
    <form action="student_login.php" method="POST" style="text-align:right;">
        <button type="submit" class="save-button">Logout</button>
    </form>

    <div class="section centered">
        <h2>Student Profile</h2>
        <p><span class="info-label">Name:</span> <?= $student_info['applicant_name'] ?></p>
        <p><span class="info-label">Student ID:</span> <?= $student_info['student_id'] ?></p>
        <p><span class="info-label">Class:</span> <?= $student_info['class_name'] ?></p>
        <p><span class="info-label">Group:</span> <?= $student_info['group_'] ?></p>
    </div>

    <div class="section">
        <h2>Personal Information</h2>
        <p><span class="info-label">Name:</span> <span id="name"><?= $student_info['applicant_name'] ?></span></p>
        <p><span class="info-label">Date of Birth:</span> <span id="dob"><?= $student_info['date_of_birth'] ?></span></p>
        <p><span class="info-label">Father's Name:</span> <span id="father"><?= $student_info['father_name'] ?></span></p>
        <p><span class="info-label">Mother's Name:</span> <span id="mother"><?= $student_info['mother_name'] ?></span></p>
        <p><span class="info-label">Email:</span> <span id="email"><?= $student_info['email'] ?></span></p>
        <p><span class="info-label">Mobile:</span> <span id="mobile"><?= $student_info['mobile_number'] ?></span></p>
        <p><span class="info-label">Gender:</span> <span id="gender"><?= $student_info['gender'] ?></span></p>
        <p><span class="info-label">Present Address:</span> <span id="present-address"><?= $student_info['pres_village'] . ', ' . $student_info['pres_post_code'] . ', ' . $student_info['pres_upazila'] . ', ' . $student_info['pres_zila'] ?></span></p>
        <p><span class="info-label">Permanent Address:</span> <span id="perm-address"><?= $student_info['perm_village'] . ', ' . $student_info['perm_post_code'] . ', ' . $student_info['perm_upazila'] . ', ' . $student_info['perm_zila'] ?></span></p>
        <button id="edit-btn" class="save-button" onclick="enableEditing()">Edit</button>
        <button id="update-btn" class="save-button" onclick="updateData()" style="display:none;">Update</button>
    </div>

    <div class="section">
        <h2>Login Credentials</h2>
        <p><span class="info-label">Username:</span> <span id="login-username"><?= $login_info['user_name'] ?></span></p>
        <p><span class="info-label">Password:</span> <span id="login-password"><?= $login_info['user_password'] ?></span></p>
        <button id="edit-login-btn" class="save-button" onclick="enableLoginEditing()">Edit</button>
        <button id="update-login-btn" class="save-button" onclick="updateData()" style="display:none;">Update</button>
    </div>

    <div class="section">
        <h2>Academic Information</h2>
        <p><span class="info-label">Optional Subject:</span> <?= $student_info['optional_subject'] ?></p>
        <p><span class="info-label">Subjects:</span></p>
        <ul>
            <?php while ($subject = $subjects_result->fetch_assoc()) { ?>
                <li><?= $subject['subject_title'] ?> (<?= $subject['subject_code'] ?>)</li>
            <?php } ?>
        </ul>
    </div>

    <div class="section">
        <h2>Attendance</h2>
        <p><span class="info-label">Present:</span> <?= $attendance_data['present_days'] ?></p>
        <p><span class="info-label">Late:</span> <?= $attendance_data['late_days'] ?></p>
        <p><span class="info-label">Absent:</span> <?= $attendance_data['absent_days'] ?></p>
        <p><span class="info-label">Attendance %:</span>
            <?= ($attendance_data['total_days'] > 0) ? round(($attendance_data['present_days'] / $attendance_data['total_days']) * 100, 2) . '%' : 'N/A' ?>
        </p>
    </div>

    <div class="section">
        <h2>Upcoming Events</h2>
        <?php if ($events_result->num_rows > 0): ?>
            <form method="POST">
                <select name="event_id" required>
                    <option value="">Select Event</option>
                    <?php while ($event = $events_result->fetch_assoc()): ?>
                        <option value="<?= $event['event_id'] ?>"><?= $event['title'] ?> - <?= $event['event_date'] ?></option>
                    <?php endwhile; ?>
                </select>
                <input type="submit" class="save-button" value="Register">
            </form>
        <?php else: ?>
            <p>No upcoming events.</p>
        <?php endif; ?>
    </div>
    </div>

    <div class="section">
        <h2>Achievements</h2>
        <ul>
            <?php if ($achievements_result->num_rows > 0): while ($a = $achievements_result->fetch_assoc()): ?>
                <li><strong><?= $a['award_name'] ?></strong> - <?= $a['achievement_date'] ?><br><?= $a['achievement_description'] ?></li>
            <?php endwhile; else: echo "<p>No achievements found.</p>"; endif; ?>
        </ul>
    </div>
     <!-- Participated Events Section -->
     <div class="section">
        <h2>Participation in Events</h2>
        <?php if ($participated_events_result->num_rows > 0): ?>
            <ul>
                <?php while ($event = $participated_events_result->fetch_assoc()): ?>
                    <li>
                        <strong><?= $event['title'] ?> (<?= $event['category'] ?>)</strong> - <?= date('d M Y', strtotime($event['event_date'])) ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No events participated in yet.</p>
        <?php endif; ?>
    </div>

</div>

<?php include('footer.php'); ?>

<script>
function enableEditing() {
    ['name','dob','father','mother','email','mobile','gender','present-address','perm-address'].forEach(id => {
        document.getElementById(id).contentEditable = true;
    });
    document.getElementById('edit-btn').style.display = 'none';
    document.getElementById('update-btn').style.display = 'inline';
}

function enableLoginEditing() {
    document.getElementById('login-username').contentEditable = true;
    document.getElementById('login-password').contentEditable = true;
    document.getElementById('edit-login-btn').style.display = 'none';
    document.getElementById('update-login-btn').style.display = 'inline';
}

function updateData() {
    const data = {
        name: document.getElementById('name').innerText,
        dob: document.getElementById('dob').innerText,
        father: document.getElementById('father').innerText,
        mother: document.getElementById('mother').innerText,
        email: document.getElementById('email').innerText,
        mobile: document.getElementById('mobile').innerText,
        gender: document.getElementById('gender').innerText,
        presentAddress: document.getElementById('present-address').innerText,
        permAddress: document.getElementById('perm-address').innerText,
        user_name: document.getElementById('login-username').innerText,
        user_password: document.getElementById('login-password').innerText
    };

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "", true); // Post to same page
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
        if (xhr.status === 200 && xhr.responseText.includes("success")) {
        
            location.reload();
        } else {
            alert('Update failed.');
        }
    };

    let formBody = [];
    for (const key in data) {
        formBody.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
    }
    xhr.send(formBody.join('&'));
}
</script>
</body>
</html>
