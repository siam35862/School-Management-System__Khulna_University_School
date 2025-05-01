<link rel="stylesheet" href="nav.css">
<link rel="stylesheet" href="footer.css">
<?php
session_start();

// Connect to the database
include 'db_connection.php';

$username__ = $_POST['username'] ?? '';
$password__ = $_POST['password'] ?? '';

// Validate login
$stmt = $conn->prepare("SELECT st_ID FROM student_login WHERE user_name=? AND user_password=?");
$stmt->bind_param("ss", $username__, $password__);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    include 'nav.php';
    echo "<p style='color:red; text-align:center;'>Invalid login credentials!</p>";
    
    include 'footer.php';
    exit;
}

$row = $result->fetch_assoc();
$st_ID = $row['st_ID'];

// Fetch student, admission, and group info
$stmt = $conn->prepare("
    SELECT s.*, a.applicant_name, a.father_name, a.mother_name, a.date_of_birth, c.group_, c.class_name
    FROM student s
    JOIN admission_form a ON s.admission_id = a.admission_id
    JOIN class c ON s.class_id = c.class_id
    WHERE s.st_ID = ?
");
$stmt->bind_param("i", $st_ID);
$stmt->execute();
$studentResult = $stmt->get_result();
$student = $studentResult->fetch_assoc();

// Fetch school name
$schoolResult = $conn->query("SELECT school_name FROM school_info LIMIT 1");
$schoolRow = $schoolResult->fetch_assoc();
$school_name = $schoolRow['school_name'] ?? 'School Name';

// Define student variables
$class_id = $student['class_id'] ?? '';
$class_name = $student['class_name'] ?? '';
$optional_subject = ($class_name == 'Eight') ? 'AGRICULTURE STUDIES' : ($student['optional_subject'] ?? '');
$group = $student['group_'] ?? 'N/A';
$academic_year = $student['academic_year'] ?? '';
$student_id = $student['student_id'] ?? '';

// GPA calculation functions
function calculateGPA($mark) {
    if ($mark >= 80) return 5.0;
    elseif ($mark >= 70) return 4.0;
    elseif ($mark >= 60) return 3.5;
    elseif ($mark >= 50) return 3.0;
    elseif ($mark >= 40) return 2.0;
    elseif ($mark >= 33) return 1.0;
    else return 0.0;
}

function calculateGrade($gpa) {
    if ($gpa >= 5.00) return 'A+';
    elseif ($gpa >= 4.00) return 'A';
    elseif ($gpa >= 3.50) return 'A-';
    elseif ($gpa >= 3.00) return 'B';
    elseif ($gpa >= 2.00) return 'C';
    elseif ($gpa >= 1.00) return 'D';
    else return 'F';
}

// Subject classification
$additional_titles = [
    'PHYSICAL EDUCATION AND HEALTH',
    'ARTS AND CRAFTS',
    'WORK AND LIFE ORIENTED EDUCATION',
    'PHYSICAL EDUCATION, HEALTH & SPORTS',
    'CAREER EDUCATION'
];

$subjects = [];
$additional_subjects = [];
$optional_subject_data = null;
$total_points = 0;
$total_subjects = 0;
$optional_points = 0;
$total_marks = 0;
$additional_subject_marks = 0;

// Fetch all subjects for the class with marks (if any)
$stmt = $conn->prepare("
    SELECT s.subject_id, s.subject_title, s.subject_code, m.mark AS mark
    FROM subject s
    LEFT JOIN marks m ON s.subject_id = m.subject_id AND m.st_ID = ?
    WHERE s.class_id = ?
    GROUP BY s.subject_id, s.subject_title, s.subject_code
");
$stmt->bind_param("ii", $st_ID, $class_id);
$stmt->execute();
$marksResult = $stmt->get_result();

// Process each subject
while ($row = $marksResult->fetch_assoc()) {
    $subject = $row['subject_title'];
    $subject_code = $row['subject_code'];
    $mark = is_null($row['mark']) ? 0 : round($row['mark'], 2);
    $gpa = calculateGPA($mark);
    $is_optional = ($subject === $optional_subject);
    $is_additional_subject = in_array(strtoupper($subject), $additional_titles);

    if ($is_additional_subject) {
        $additional_subject_marks += $mark;
        $additional_subjects[] = [
            'subject' => $subject,
            'code' => $subject_code,
            'mark' => $mark,
            'gpa' => $gpa
        ];
    } elseif ($is_optional) {
        if ($gpa > 2.0) {
            $optional_points = max(0, $gpa - 2.0);
        }
        $optional_subject_data = [
            'subject' => $subject,
            'code' => $subject_code,
            'mark' => $mark,
            'gpa' => $gpa
        ];
        $total_marks += $mark;
    } else {
        $total_marks += $mark;
        $total_points += $gpa;
        $total_subjects++;
        $subjects[] = [
            'subject' => $subject,
            'code' => $subject_code,
            'mark' => $mark,
            'gpa' => $gpa
        ];
    }
}

// Final GPA and status
$gpa_without_optional = $total_subjects > 0 ? $total_points / $total_subjects : 0;
$gpa_with_optional = min(5.0, $gpa_without_optional + $optional_points / $total_subjects);
$total_marks += $additional_subject_marks;
$exam_type = ($class_id == 10) ? 'TEST' : 'ANNUAL';
$has_fail_in_main = false;
foreach ($subjects as $sub) {
    if ($sub['gpa'] == 0.0) {
        $has_fail_in_main = true;
        break;
    }
}
$status = $has_fail_in_main ? 'FAILED' : 'PASSED';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Internal Result</title>
    <link rel="stylesheet" href="nav.css">
    <link rel="stylesheet" href="footer.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .result-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; color: #007BFF; margin-bottom: 10px; }
        .info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5px 20px;
            margin: 20px 0;
            font-size: 14px;
        }
        .info p { margin: 0; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 8px;
            border: 1px solid #999;
            text-align: center;
            font-size: 12px;
        }
        .print-btn {
            text-align: center;
            margin-top: 30px;
        }
        .print-btn button {
            padding: 10px 20px;
            font-size: 14px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        @media print {
            nav, footer, .print-btn {
                display: none !important;
            }
            .result-container {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
<body>

<?php include 'nav.php'; ?>

<div class="result-container">
    <h2><?php echo htmlspecialchars($school_name); ?></h2>
    <h2>Student Internal Result</h2>

    <div class="info">
        <p><strong>Student ID:</strong> <?php echo htmlspecialchars($student_id); ?></p>
        <p><strong>Student Name:</strong> <?php echo htmlspecialchars($student['applicant_name']); ?></p>
        <p><strong>Father's Name:</strong> <?php echo htmlspecialchars($student['father_name']); ?></p>
        <p><strong>Mother's Name:</strong> <?php echo htmlspecialchars($student['mother_name']); ?></p>
        <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($student['date_of_birth']); ?></p>
        <p><strong>School:</strong> <?php echo htmlspecialchars($school_name); ?></p>
        <p><strong>Class:</strong> <?php echo htmlspecialchars($class_name); ?></p>
        <p><strong>Group:</strong> <?php echo htmlspecialchars($group); ?></p>
        <p><strong>Academic Year:</strong> <?php echo htmlspecialchars($academic_year); ?></p>
        <p><strong>Exam Type:</strong> <?php echo htmlspecialchars($exam_type); ?></p>
        <p><strong>Total Marks:</strong> <?php echo number_format($total_marks, 2); ?></p>
        <p><strong>GPA (without optional):</strong> <?php echo number_format($gpa_without_optional, 2); ?></p>
        <p><strong>GPA (with optional):</strong> <?php echo number_format($gpa_with_optional, 2); ?></p>
        <p><strong>Status:</strong> <span style="color: <?php echo $status === 'PASSED' ? 'green' : 'red'; ?>;"><?php echo $status; ?></span></p>
    </div>

    <table>
        <tr style="background-color: #f0f0f0;">
            <th>Subject</th>
            <th>Code</th>
            <th>Mark</th>
            <th>GP</th>
            <th>Grade</th>
        </tr>
        <?php foreach ($subjects as $sub): ?>
        <tr>
            <td><?php echo htmlspecialchars($sub['subject']); ?></td>
            <td><?php echo htmlspecialchars($sub['code']); ?></td>
            <td><?php echo number_format($sub['mark'], 2); ?></td>
            <td><?php echo number_format($sub['gpa'], 2); ?></td>
            <td><?php echo calculateGrade($sub['gpa']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <?php if ($optional_subject_data): ?>
        <h3>Optional Subject</h3>
        <table>
            <tr style="background-color: #f0f0f0;">
                <th>Subject</th>
                <th>Code</th>
                <th>Mark</th>
                <th>GP</th>
                <th>Grade</th>
            </tr>
            <tr>
                <td><?php echo htmlspecialchars($optional_subject_data['subject']); ?></td>
                <td><?php echo htmlspecialchars($optional_subject_data['code']); ?></td>
                <td><?php echo number_format($optional_subject_data['mark'], 2); ?></td>
                <td><?php echo number_format($optional_subject_data['gpa'], 2); ?></td>
                <td><?php echo calculateGrade($optional_subject_data['gpa']); ?></td>
            </tr>
        </table>
    <?php endif; ?>

    <?php if (count($additional_subjects) > 0): ?>
        <h3>Additional Subjects</h3>
        <table>
            <tr style="background-color: #f0f0f0;">
                <th>Subject</th>
                <th>Code</th>
                <th>Mark</th>
                <th>GP</th>
                <th>Grade</th>
            </tr>
            <?php foreach ($additional_subjects as $sub): ?>
            <tr>
                <td><?php echo htmlspecialchars($sub['subject']); ?></td>
                <td><?php echo htmlspecialchars($sub['code']); ?></td>
                <td><?php echo number_format($sub['mark'], 2); ?></td>
                <td><?php echo number_format($sub['gpa'], 2); ?></td>
                <td><?php echo calculateGrade($sub['gpa']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <div class="print-btn" style="display: flex; justify-content: center; gap: 20px;">
        <form action="internal_result.php"  onsubmit="sessionStorage.clear();">
           
            <button type="submit" style="background-color:#007bff;">🔍 Search Again</button>
        </form>
        <button onclick="window.print()">🖨️ Print / Save as PDF</button>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
