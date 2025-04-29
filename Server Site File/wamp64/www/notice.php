<?php
// Connect to database
include 'db_connection.php';

$notices = [];
$sql = "SELECT notice_id, notice_title, notice_date FROM notice ORDER BY notice_date DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $notices[] = $row;
  }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Notice Board</title>
  <link rel="stylesheet" href="nav.css">
  <link rel="stylesheet" href="footer.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      padding-top: 150px;
      margin: 0;
    }

    .notice-container {
      max-width: 900px;
      margin: 0 auto;
      padding: 20px;
    }

    .notice-item {
      padding: 15px;
      border: 1px solid #ccc;
      border-radius: 8px;
      margin-bottom: 10px;
      background-color: #f9f9f9;
      cursor: pointer;
    }

    .notice-title {
      color: #004080;
      font-weight: bold;
      font-size: 18px;
    }

    .notice-date {
      font-size: 14px;
      color: #666;
      margin-top: 5px;
    }

    .notice-description {
      margin-top: 10px;
      padding-top: 10px;
      border-top: 1px solid #ddd;
      color: #222;
    }
  </style>
</head>
<body>

<?php include 'nav.php'; ?>


  <div class="notice-container">
    <h2>All Notices</h2>
    <?php foreach ($notices as $notice): ?>
      <div class="notice-item" data-id="<?= $notice['notice_id'] ?>">
        <div class="notice-title"><?= htmlspecialchars($notice['notice_title']) ?></div>
        <div class="notice-date"><?= htmlspecialchars($notice['notice_date']) ?></div>
        <div class="notice-description" id="desc-<?= $notice['notice_id'] ?>" style="display: none;"></div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php include 'footer.php'; ?>

  <script>
    document.querySelectorAll('.notice-item').forEach(item => {
      item.addEventListener('click', () => {
        const id = item.getAttribute('data-id');
        const desc = document.getElementById('desc-' + id);

        // If already visible, just toggle
        if (desc.style.display === 'block') {
          desc.style.display = 'none';
          desc.innerHTML = '';
          return;
        }

        // Hide all others
        document.querySelectorAll('.notice-description').forEach(d => {
          d.style.display = 'none';
          d.innerHTML = '';
        });

        // Fetch and show the content
        fetch('get_notice_detail.php?notice_id=' + id)
          .then(response => response.text())
          .then(data => {
            desc.innerHTML = data;
            desc.style.display = 'block';
          });
      });
    });
  </script>

</body>
</html>
