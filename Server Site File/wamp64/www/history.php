<?php
// Connect to the database
include 'db_connection.php';

// Set default values
$establishedYear = "Not available";
$shortHistory = "Not available";
$fullHistory = "Not available";

// Fetch data if connection is successful
if (!$conn->connect_error) {
  $sql = "SELECT established_year, short_history, full_history FROM school_info ORDER BY id DESC LIMIT 1";
  $result = $conn->query($sql);

  if ($result && $row = $result->fetch_assoc()) {
    $establishedYear = htmlspecialchars($row['established_year']);
    $shortHistory = nl2br(htmlspecialchars($row['short_history']));
    
    // Replace [year] placeholder with actual year
    $fullHistoryRaw = str_replace("[year]", $establishedYear, $row['full_history']);
    $fullHistory = nl2br(htmlspecialchars($fullHistoryRaw));
  }

  $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>School History - Khulna University School</title>
  <link rel="stylesheet" href="nav.css">
  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="footer.css">
</head>
<body>

  <!-- Include Navigation (with latest notice) -->
  <?php include 'nav.php'; ?>

  <!-- School History Content -->
  <section class="message-section" style="flex-direction: column; align-items: center;">
    <h2>About the School</h2>
    <p><strong>Established:</strong> <?= $establishedYear ?></p>

   
    <p style="max-width: 800px; text-align: justify;"><?= $fullHistory ?></p>
  </section>

  <!-- Include Footer -->
  <?php include 'footer.php'; ?>

</body>
</html>
