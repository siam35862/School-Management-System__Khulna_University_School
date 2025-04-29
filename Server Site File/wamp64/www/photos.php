<?php include('nav.php'); ?>
<?php
// Include database connection
include 'db_connection.php';
$photos_message = "There are no photos for display.";
$has_photos = false;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Photos - Khulna University School</title>
  <link rel="stylesheet" href="nav.css">
  <link rel="stylesheet" href="footer.css">
  <style>
    /* Photo Gallery Styling */
    .gallery-container {
      max-width: 1200px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .gallery-title {
      text-align: center;
      color: #004080;
      font-size: 32px;
      margin-bottom: 30px;
      position: relative;
    }

    .gallery-title:after {
      content: "";
      display: block;
      width: 80px;
      height: 3px;
      background: #004080;
      margin: 10px auto;
    }

    .photo-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 40px;
    }

    .photo-item {
      overflow: hidden;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      position: relative;
      display: flex;
      flex-direction: column;
    }

    .photo-item:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 15px rgba(0, 0, 0, 0.15);
    }

    .photo-item img {
      width: 100%;
      height: 250px;
      object-fit: cover;
      display: block;
      transition: transform 0.5s ease;
    }

    .photo-item:hover img {
      transform: scale(1.05);
    }

    .photo-title {
      background: #f5f7fa;
      color: #004080;
      padding: 12px;
      text-align: center;
      font-weight: 500;
      border-top: 1px solid #e0e6ed;
    }

    /* Empty gallery message styling */
    .message-container {
      height: 60vh;
      display: flex;
      justify-content: center;
      align-items: center;
      text-align: center;
      font-size: 24px;
      color: #004080;
      font-weight: 300;
      background: #f5f7fa;
      border-radius: 8px;
      box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.05);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .photo-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
      }
      
      .photo-item img {
        height: 200px;
      }
    }

    @media (max-width: 480px) {
      .photo-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>

<div class="gallery-container">
  <h1 class="gallery-title">Our Photo Gallery</h1>
  
  <?php
  if (!$conn->connect_error) {
    $sql = "SELECT * FROM gallery WHERE type = 'photo'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
      // Photos found
      $has_photos = true;
      echo '<div class="photo-grid">';
      while ($row = $result->fetch_assoc()) {
        echo '<div class="photo-item">
          <img src="' . htmlspecialchars($row['file_name']) . '" alt="' . (isset($row['title']) ? htmlspecialchars($row['title']) : 'School Photo') . '">
          <div class="photo-title">' . (isset($row['title']) ? htmlspecialchars($row['title']) : 'School Event') . '</div>
        </div>';
      }
      echo '</div>';
    }
    $conn->close();
  }
  
  // Display message if no photos
  if (!$has_photos) {
    echo '<div class="message-container">
      <p>' . $photos_message . '</p>
    </div>';
  }
  ?>
</div>

<?php include('footer.php'); ?>

</body>
</html>