<?php
// Database connection settings
include 'db_connection.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch messages from the database
$sql = "SELECT * FROM messages ORDER BY id ASC";
$result = $conn->query($sql);

$messages = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Khulna University School</title>
  <link rel="stylesheet" href="nav.css">
  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="footer.css">
</head>
<body>

  <!-- Include Navigation -->
  <?php include 'nav.php'; ?>


  <!-- Message Section -->
  <section class="message-section" id="message-container">
    <?php if (empty($messages)): ?>
      <div class="loading">No messages available.</div>
    <?php else: ?>
      <?php foreach ($messages as $message): ?>
        <div class="message-box">
          <img src="<?= htmlspecialchars($message['image']); ?>" alt="<?= htmlspecialchars($message['role']); ?> Photo">
          <h3><?= htmlspecialchars($message['title']); ?></h3>
          <p><?= nl2br(htmlspecialchars($message['message'])); ?></p>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>

  <!-- Include Footer -->
  <?php include 'footer.php'; ?>

</body>
</html>
