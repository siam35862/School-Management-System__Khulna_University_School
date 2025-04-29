<?php
session_start();

// Display success or error messages for event creation
$event_success_message = '';
$event_error_message = '';

if (isset($_SESSION['event_success'])) {
    $event_success_message = $_SESSION['event_success'];
    unset($_SESSION['event_success']);
}

if (isset($_SESSION['event_error'])) {
    $event_error_message = $_SESSION['event_error'];
    unset($_SESSION['event_error']);
}

if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: teacher_login.php");
    exit();
}

if (!isset($_SESSION['teacher_ID'])) {
    header("Location: teacher_login.php");
    exit();
}

$teacher_ID = $_SESSION['teacher_ID'];

//database connection
include 'db_connection.php';

// Fetch teacher data
$sql = "SELECT teacher_name, email, phone_number, qualification, date_of_birth, district, designation, joining_date, service_status, teaching_subject 
        FROM teacher WHERE teacher_ID = '$teacher_ID'";
$result = $conn->query($sql);
$teacher = $result && $result->num_rows == 1 ? $result->fetch_assoc() : null;

$login_sql = "SELECT user_name, user_password FROM teacher_login WHERE teacher_ID = '$teacher_ID'";
$login_result = $conn->query($login_sql);
$login = $login_result && $login_result->num_rows == 1 ? $login_result->fetch_assoc() : null;

if (!$teacher || !$login) {
    echo "Teacher details or login credentials not found!";
    exit();
}

// Update profile
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['teacher_name'])) {
    $teacher_name = $_POST['teacher_name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $qualification = $_POST['qualification'];
    $date_of_birth = $_POST['date_of_birth'];
    $hometown = $_POST['hometown'];
    $user_name = $_POST['user_name'];
    $user_password = $_POST['user_password'];

    $update_login_sql = "UPDATE teacher_login SET user_name = '$user_name', user_password = '$user_password' WHERE teacher_ID = '$teacher_ID'";
    $update_teacher_sql = "UPDATE teacher SET teacher_name = '$teacher_name', email = '$email', phone_number = '$phone_number',
                            qualification = '$qualification', date_of_birth = '$date_of_birth', district = '$hometown'
                            WHERE teacher_ID = '$teacher_ID'";

    if ($conn->query($update_login_sql) && $conn->query($update_teacher_sql)) {
        header("Location: teacher.php");
        exit();
    } else {
        echo "Error updating teacher details: " . $conn->error;
    }
}

// Handle attendance
if (isset($_POST['class_name'], $_POST['group_'], $_POST['academic_year'], $_POST['student_id'], $_POST['attendance_status'])) {
    $class_name = $_POST['class_name'];
    $group_ = $_POST['group_'];
    $student_id = $_POST['student_id'];
    $attendance_status = $_POST['attendance_status'];
    $attendance_date = date('Y-m-d');
    $academic_year = $_POST['academic_year'];

    $class_id_sql = "SELECT class_id FROM class WHERE class_name = '$class_name' AND group_ = '$group_'";
    $class_result = $conn->query($class_id_sql);
    if ($class_result && $class_result->num_rows == 1) {
        $class_id = $class_result->fetch_assoc()['class_id'];

        $st_sql = "SELECT st_ID FROM student WHERE student_id = '$student_id' AND class_id = '$class_id' AND academic_year = '$academic_year'";
        $st_result = $conn->query($st_sql);
        if ($st_result && $st_result->num_rows == 1) {
            $st_ID = $st_result->fetch_assoc()['st_ID'];

            $insert_sql = "INSERT INTO attendance (st_ID, class_id, attendance_status, attendance_date)
                           VALUES ('$st_ID', '$class_id', '$attendance_status', '$attendance_date')";
            if ($conn->query($insert_sql)) {
                echo "Attendance updated successfully.";
            } else {
                echo "Error inserting attendance: " . $conn->error;
            }
        } else {
            echo "Student not found for selected class, group, and year.";
        }
    } else {
        echo "Class and group not found.";
    }
}

// Handle event creation with PRG pattern
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_event'])) {
    // Validate all required fields are present
    if (
        empty($_POST['event_title']) || empty($_POST['event_category']) ||
        empty($_POST['event_date']) || empty($_POST['event_description'])
    ) {
        $_SESSION['event_error'] = "All fields are required!";
    } else {
        $event_title = $_POST['event_title'];
        $event_category = $_POST['event_category'];
        $event_date = $_POST['event_date'];
        $event_description = $_POST['event_description'];

        // Insert into institution_event table
        $insert_event_sql = "INSERT INTO institution_event (title, category, event_date, event_description) 
                            VALUES ('$event_title', '$event_category', '$event_date', '$event_description')";

        if ($conn->query($insert_event_sql)) {
            // Get the newly created event_id
            $event_id = $conn->insert_id;

            // Insert into teacher_event table to associate teacher with event
            $insert_teacher_event_sql = "INSERT INTO teacher_event (t_ID, event_id) 
                                        VALUES ('$teacher_ID', '$event_id')";

            if ($conn->query($insert_teacher_event_sql)) {
                $_SESSION['event_success'] = "Event created successfully!";
            } else {
                $_SESSION['event_error'] = "Error linking teacher to event: " . $conn->error;
            }
        } else {
            $_SESSION['event_error'] = "Error creating event: " . $conn->error;
        }
    }
    
    // Redirect after form submission to prevent resubmission
    header("Location: teacher.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Teacher Profile</title>
    <link rel="stylesheet" href="nav.css">
    <link rel="stylesheet" href="footer.css">
    <style>
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
        }

        .input-field {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
        }

        .btn {
            width: 20%;
            padding: 10px;
            margin-top: 10px;
            color: white;
            background-color: #0275d8;
        }

        .large-dropdown {
            font-size: 16px;
            padding: 10px;
        }

        .section-title {
            margin-top: 30px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            color: #2c3e50;
        }

        .logout-btn {
            padding: 8px 16px;
            background-color: crimson;
            color: white;
            border: none;
            cursor: pointer;
        }
    </style>
    <script>
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
    </script>
</head>

<body>
    <?php include 'nav.php'; ?>

    <div class="container">
        <h2>Welcome to Khulna University School</h2>

        <!-- Logout Button -->
        <div style="text-align: right; margin-bottom: 20px;">
            <form method="post">
                <input type="submit" name="logout" value="Logout" class="logout-btn">
            </form>
        </div>

        <!-- Teacher Profile -->
        <h3 class="section-title">Teacher Profile</h3>
        <form method="POST">
            <label>User Name:</label>
            <input type="text" name="user_name" value="<?= htmlspecialchars($login['user_name']) ?>" class="input-field"
                required>

            <label>Password:</label>
            <input type="text" name="user_password" value="<?= htmlspecialchars($login['user_password']) ?>"
                class="input-field" required>

            <label>Name:</label>
            <input type="text" name="teacher_name" value="<?= htmlspecialchars($teacher['teacher_name']) ?>"
                class="input-field" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($teacher['email']) ?>" class="input-field"
                required>

            <label>Phone Number:</label>
            <input type="text" name="phone_number" value="<?= htmlspecialchars($teacher['phone_number']) ?>"
                class="input-field" required>

            <label>Qualification:</label>
            <input type="text" name="qualification" value="<?= htmlspecialchars($teacher['qualification']) ?>"
                class="input-field" required>

            <label>Date of Birth:</label>
            <input type="date" name="date_of_birth" value="<?= htmlspecialchars($teacher['date_of_birth']) ?>"
                class="input-field" required>

            <label>Hometown (District):</label>
            <input type="text" name="hometown" value="<?= htmlspecialchars($teacher['district']) ?>" class="input-field"
                required>

            <label>Designation:</label>
            <input type="text" value="<?= htmlspecialchars($teacher['designation']) ?>" class="input-field" disabled>

            <label>Joining Date:</label>
            <input type="date" value="<?= htmlspecialchars($teacher['joining_date']) ?>" class="input-field" disabled>

            <label>Service Status:</label>
            <input type="text" value="<?= htmlspecialchars($teacher['service_status']) ?>" class="input-field" disabled>

            <label>Teaching Subject:</label>
            <input type="text" value="<?= htmlspecialchars($teacher['teaching_subject']) ?>" class="input-field"
                disabled>

            <input type="submit" class="btn" value="Save & Update">
        </form>

        <!-- Attendance Entry -->
        <h3 class="section-title">Attendance Entry</h3>
        <form method="POST">
            <label>Class:</label>
            <select name="class_name" class="input-field" onchange="fetchAcademicYears('attendance')" required>
                <option value="">Select Class</option>
                <?php
                $class_result = $conn->query("SELECT DISTINCT class_name FROM class");
                while ($row = $class_result->fetch_assoc()) {
                    echo "<option value='{$row['class_name']}'>{$row['class_name']}</option>";
                }
                ?>
            </select>

            <label>Group:</label>
            <select name="group_" class="input-field" onchange="fetchAcademicYears('attendance')" required>
                <option value="">Select Group</option>
                <?php
                $group_result = $conn->query("SELECT DISTINCT group_ FROM class");
                while ($row = $group_result->fetch_assoc()) {
                    echo "<option value='{$row['group_']}'>{$row['group_']}</option>";
                }
                ?>
            </select>

            <label>Academic Year:</label>
            <select name="academic_year" id="academicYearDropdown" class="input-field" onchange="fetchStudents()"
                required>
                <option value="">Select Academic Year</option>
            </select>

            <label>Student ID:</label>
            <select name="student_id" id="studentDropdown" class="input-field large-dropdown" required>
                <option value="">Select Student</option>
            </select>

            <label>Attendance Status:</label>
            <select name="attendance_status" class="input-field" required>
                <option value="Present">Present</option>
                <option value="Absent">Absent</option>
                <option value="Late">Late</option>
            </select>

            <input type="submit" class="btn" value="Update Attendance">
        </form>

        <!-- Marks Entry -->
        <h3 class="section-title">Marks Entry</h3>
        <form method="POST" action="insert_marks.php">
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

        <!-- Event Creation -->
        <h3 class="section-title">Create Event</h3>
        <?php 
        if ($event_success_message) {
            echo "<p style='color: green; margin-top: 10px;'>$event_success_message</p>";
        }
        if ($event_error_message) {
            echo "<p style='color: red; margin-top: 10px;'>$event_error_message</p>";
        }
        ?>
        <form method="POST" action="">
            <label>Event Title:</label>
            <input type="text" name="event_title" class="input-field" required>

            <label>Event Category:</label>
            <input type="text" name="event_category" class="input-field" required>

            <label>Event Date:</label>
            <input type="date" name="event_date" class="input-field" required>

            <label>Event Description:</label>
            <textarea name="event_description" class="input-field" rows="4" required></textarea>

            <input type="submit" name="create_event" class="btn" value="Create Event">
        </form>

        <!-- Display Teacher's Events -->
        <h3 class="section-title">My Events</h3>
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse: collapse; margin-top: 10px;">
                <tr style="background-color: #f2f2f2;">
                    <th style="padding: 10px; border: 1px solid #ddd;">Title</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Category</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Date</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Description</th>
                </tr>
                <?php
                // Fetch events created by this teacher
                $events_sql = "SELECT ie.title, ie.category, ie.event_date, ie.event_description 
                      FROM institution_event ie 
                      JOIN teacher_event te ON ie.event_id = te.event_id 
                      WHERE te.t_ID = '$teacher_ID' 
                      ORDER BY ie.event_date DESC";
                $events_result = $conn->query($events_sql);

                if ($events_result && $events_result->num_rows > 0) {
                    while ($event = $events_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($event['title']) . "</td>";
                        echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($event['category']) . "</td>";
                        echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($event['event_date']) . "</td>";
                        echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($event['event_description']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align: center; padding: 10px;'>No events found</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>

</html>