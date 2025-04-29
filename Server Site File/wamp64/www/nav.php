<?php
include 'db_connection.php';

// Fetch school info
$schoolName = "Khulna University School";
$schoolAddress = "Khulna-9208";
$schoolLogo = "logo.png";
$schoolTitle = "Khulna University School";
$schoolAge = "N/A";

$infoSql = "SELECT school_name, address, image, established_year FROM school_info ORDER BY id DESC LIMIT 1";
$infoResult = $conn->query($infoSql);
if ($infoResult && $row = $infoResult->fetch_assoc()) {
  $schoolName = $row['school_name'];
  $schoolAddress = $row['address'];
  $schoolLogo = $row['image'] ?: "logo.png";
  $schoolTitle = $schoolName;
  if (!empty($row['established_year'])) {
    $schoolAge = date('Y') - $row['established_year'] . " years";
  }
}

// Fetch footer contact info
$address = $phone = $email = "Not available";
$footerSql = "SELECT address, phone, email FROM footer_info ORDER BY id DESC LIMIT 1";
$footerResult = $conn->query($footerSql);
if ($footerResult && $row = $footerResult->fetch_assoc()) {
  $address = $row["address"];
  $phone = $row["phone"];
  $email = $row["email"];
}

// Fetch counts
$teacherCount = 0;
$studentCount = 0;
$currentYear = date("Y");

$teacherSql = "SELECT COUNT(*) AS total FROM teacher WHERE service_status = 'active'";
$teacherResult = $conn->query($teacherSql);
if ($teacherResult && $row = $teacherResult->fetch_assoc()) {
  $teacherCount = $row['total'];
}

$studentSql = "SELECT COUNT(*) AS total FROM student WHERE academic_year = '$currentYear'";
$studentResult = $conn->query($studentSql);
if ($studentResult && $row = $studentResult->fetch_assoc()) {
  $studentCount = $row['total'];
}

// Fetch latest notice
$latestNoticeTitle = "No recent notice.";
$latestNoticeDescription = "";

$noticeSql = "SELECT notice_title, notice_description FROM notice ORDER BY notice_date DESC LIMIT 1";
$noticeResult = $conn->query($noticeSql);
if ($noticeResult && $row = $noticeResult->fetch_assoc()) {
  $latestNoticeTitle = htmlspecialchars($row['notice_title']);
  $latestNoticeDescription = nl2br(htmlspecialchars($row['notice_description']));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($schoolTitle) ?></title>
  <link rel="stylesheet" href="nav.css">
</head>
<body>

<header class="site-header">
  <div class="top-banner">
    <div class="logo-section">
      <img src="<?= htmlspecialchars($schoolLogo) ?>" alt="<?= htmlspecialchars($schoolName) ?> Logo">
      <div>
        <h2><?= htmlspecialchars($schoolName) ?></h2>
        <p><?= htmlspecialchars($schoolAddress) ?></p>
      </div>
    </div>
  </div>

  <nav class="main-nav">
    <ul class="nav-links">
      <li><a href="index.php">Home</a></li>
      <li><a href="#">About</a>
        <ul>
          <li><a href="glance.php">At a Glance</a></li>
          <li><a href="history.php">History</a></li>
        </ul>
      </li>
      <li><a href="teacher_info.php">Teacher & Staff</a></li>
      <li><a href="#">Admission</a>
        <ul>
          <li><a href="admission.php">Apply Now</a></li>
        </ul>
      </li>
      <li><a href="#">Students</a>
        <ul>
          <li><a href="student_count.php">Current Students Count</a></li>
          <li><a href="current_student_list.php">Current Students List</a></li>
          <li><a href="passed_student_list.php">Past Students List</a></li>
        </ul>
      </li>
      <li><a href="#">Exam Result</a>
        <ul>
          <li><a href="admission_result.php">Admission Result</a></li>
          <li><a href="internal_result.php">Internal Result</a></li>
        </ul>
      </li>
      <li><a href="#">Gallery</a>
        <ul>
          <li><a href="photos.php">Photos</a></li>
          <li><a href="videos.php">Videos</a></li>
        </ul>
      </li>
      <li><a href="#">School Glory</a>
        <ul>
          <li><a href="achievements.php">Achievements</a></li>
          <li><a href="events.php">Events</a></li>
        </ul>
      </li>
      <li><a href="notice.php">Notice</a></li>
      <li><a href="#contact">Contact</a></li>
      <li><a href="#">Login</a>
        <ul>
          <li><a href="student_login.php">Student</a></li>
          <li><a href="teacher_login.php">Teacher</a></li>
          <li><a href="admin_login.php">Admin</a></li>
        </ul>
      </li>
    </ul>
  </nav>
</header>

<!-- Latest Notice -->
<div class="latest-notice">
  <div class="scrolling-notice">
    <span><strong>Latest Notice:</strong> <?= $latestNoticeTitle ?> </span>
  </div>
</div>
</body>
