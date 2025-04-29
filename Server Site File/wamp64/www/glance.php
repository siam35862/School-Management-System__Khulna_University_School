<?php
// Database connection
include 'db_connection.php';

$shortHistory = "Not available";

if (!$conn->connect_error) {
  $sql = "SELECT short_history FROM school_info ORDER BY id DESC LIMIT 1";
  $result = $conn->query($sql);
  if ($result && $row = $result->fetch_assoc()) {
    $shortHistory = nl2br(htmlspecialchars($row['short_history']));
  }
  $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>At a Glance - Khulna University School</title>
  <link rel="stylesheet" href="nav.css">
  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="footer.css">
</head>
<body>

  <!-- Include Navigation (with latest notice) -->
  <?php include 'nav.php'; ?>

  <!-- Short History Content -->
  <section class="message-section" style="flex-direction: column; align-items: center;">
    <h2>At a Glance</h2>
    <p style="max-width: 800px; text-align: justify;"><?= $shortHistory ?></p>
  </section>

  <!-- Include Footer -->
  <?php include 'footer.php'; ?>

</body>
</html>
