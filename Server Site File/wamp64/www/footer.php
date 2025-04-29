<?php
include 'db_connection.php';
// Footer contact info
$sql = "SELECT address, phone, email FROM footer_info ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $address = $row["address"];
  $phone = $row["phone"];
  $email = $row["email"];
} else {
  $address = $phone = $email = "Not available";
}

// Teacher Count
$teacherCount = 0;
$teacherSql = "SELECT COUNT(*) AS total FROM teacher WHERE service_status = 'active'";
$teacherResult = $conn->query($teacherSql);
if ($teacherResult && $row = $teacherResult->fetch_assoc()) {
  $teacherCount = $row['total'];
}

// Student Count (current year)
$currentYear = date("Y");
$studentCount = 0;
$studentSql = "SELECT COUNT(*) AS total FROM student WHERE academic_year = '$currentYear'";
$studentResult = $conn->query($studentSql);
if ($studentResult && $row = $studentResult->fetch_assoc()) {
  $studentCount = $row['total'];
}

// School Age
$schoolAge = "Not available";
$ageSql = "SELECT established_year FROM school_info ORDER BY id DESC LIMIT 1";
$ageResult = $conn->query($ageSql);
if ($ageResult && $row = $ageResult->fetch_assoc()) {
  $establishedYear = $row['established_year'];
  $schoolAge = ($currentYear - $establishedYear) . " years";
}


?>
<!-- Footer Section -->
<footer class="footer" id="contact">
  <div class="footer-container">

    <!-- First row: Teacher, Student, School Age -->
    <div class="footer-item">
      <p><strong>Teachers:</strong></p>
      <p><?= $teacherCount ?></p>
    </div>
    <div class="footer-item">
      <p><strong>Students:</strong></p>
      <p><?= $studentCount ?></p>
    </div>
    <div class="footer-item">
      <p><strong>School Age:</strong></p>
      <p><?= $schoolAge ?></p>
    </div>

  </div>

  <div class="footer-container">

    <!-- Second row: Address, Phone, Email -->
    <div class="footer-item">
      <p><strong>Address:</strong></p>
      <p><?= htmlspecialchars($address) ?></p>
    </div>
    <div class="footer-item">
      <p><strong>Phone:</strong></p>
      <p><a href="tel:<?= htmlspecialchars($phone) ?>"><?= htmlspecialchars($phone) ?></a></p>
    </div>
    <div class="footer-item">
      <p><strong>Email:</strong></p>
      <p><a href="mailto:<?= htmlspecialchars($email) ?>"><?= htmlspecialchars($email) ?></a></p>
    </div>

  </div>
  
  <!-- New row: Quick Links -->
  <div class="footer-container">
    <div class="footer-item quick-links">
      <p><strong>Quick Links:</strong></p>
      <ul>
        <li><a href="https://www.facebook.com/share/16WGFtXHyn/" target="_blank">Facebook Page</a></li>
      </ul>
    </div>
  </div>

  <div class="footer-container">
    <p>&copy; <?= date("Y") ?> Khulna University School. All rights reserved.</p>
  </div>
</footer>