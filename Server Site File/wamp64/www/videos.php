<?php include('nav.php'); ?>
<?php
// Include database connection
include 'db_connection.php';
$videos_message = "There are no videos for display.";
$video_count = 0;

if (!$conn->connect_error) {
  $sql = "SELECT * FROM gallery WHERE type = 'video'";
  $result = $conn->query($sql);
  
  if ($result && $result->num_rows > 0) {
    // Videos found
    $videos_message = "";
    $video_count = $result->num_rows;
  }
  $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Videos - Khulna University School</title>
  <link rel="stylesheet" href="nav.css">
  <link rel="stylesheet" href="footer.css">
  <style>
    :root {
      --primary-color: #4e54c8;
      --secondary-color: #8f94fb;
      --accent-color: #ff6b6b;
      --light-color: #f0f5ff;
      --dark-color: #2c3e50;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, var(--light-color), #ffffff);
      margin: 0;
      padding: 0;
      color: var(--dark-color);
    }
    
    .page-title {
      background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
      color: white;
      padding: 2rem 0;
      text-align: center;
      margin: 0 0 2rem 0;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .page-title h1 {
      margin: 0;
      font-size: 2.5rem;
      letter-spacing: 1px;
    }
    
    .page-title p {
      margin: 0.5rem 0 0;
      font-size: 1.1rem;
      opacity: 0.9;
    }
    
    .video-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px 3rem;
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
      gap: 2rem;
      justify-content: center;
    }
    
    .video-card {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
      transition: transform 0.3s, box-shadow 0.3s;
      display: flex;
      flex-direction: column;
    }
    
    .video-wrapper {
      position: relative;
      width: 100%;
      border-bottom: 3px solid var(--accent-color);
      background-color: #000;
    }
    
    .video-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }
    
    .video-card video {
      width: 100%;
      height: auto;
      display: block;
      vertical-align: middle;
    }
    
    .video-info {
      padding: 1rem;
    }
    
    .video-title {
      font-weight: 600;
      font-size: 1.1rem;
      margin: 0 0 0.5rem 0;
      color: var(--primary-color);
    }
    
    .video-date {
      color: #777;
      font-size: 0.9rem;
    }
    
    .message-container {
      height: 60vh;
      display: flex;
      justify-content: center;
      align-items: center;
      text-align: center;
      font-size: 1.2rem;
      color: var(--primary-color);
      background-color: rgba(255, 255, 255, 0.8);
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
      margin: 2rem auto;
      max-width: 600px;
      padding: 2rem;
    }
    
    .message-container img {
      max-width: 120px;
      margin-bottom: 1.5rem;
    }
    
    .count-badge {
      background: var(--accent-color);
      color: white;
      padding: 0.3rem 1rem;
      border-radius: 20px;
      font-size: 0.9rem;
      font-weight: 600;
      display: inline-block;
      margin-left: 1rem;
    }
  </style>
</head>
<body>

<!-- Page Title Section -->
<div class="page-title">
  <h1>School Video Gallery <span class="count-badge"><?= $video_count ?> Videos</span></h1>
  <p>Watch our latest school events, performances and activities</p>
</div>

<!-- Main Content Section -->
<?php if (empty($videos_message)): ?>
  <div class="video-container">
    <?php
    // Reconnect to get the videos
    include 'db_connection.php';
    $sql = "SELECT * FROM gallery WHERE type = 'video' ORDER BY upload_date DESC";
    $result = $conn->query($sql);
    
    while ($row = $result->fetch_assoc()) {
      $title = isset($row['title']) ? $row['title'] : 'School Video';
      $date = isset($row['upload_date']) ? date('F j, Y', strtotime($row['upload_date'])) : '';
      
      echo '<div class="video-card">
              <div class="video-wrapper">
                <video controls preload="metadata">
                  <source src="' . htmlspecialchars($row['file_name']) . '" type="video/mp4">
                  Your browser does not support the video tag.
                </video>
              </div>
              <div class="video-info">
                <div class="video-title">' . htmlspecialchars($title) . '</div>
                <div class="video-date">' . $date . '</div>
              </div>
            </div>';
    }
    $conn->close();
    ?>
  </div>
<?php else: ?>
  <div class="message-container">
    <div>
      <img src="images/video-placeholder.svg" alt="No videos" onerror="this.src='images/placeholder.png'">
      <p><?= $videos_message ?></p>
      <p>Check back soon for new uploads!</p>
    </div>
  </div>
<?php endif; ?>

<?php include('footer.php'); ?>

</body>
</html>