<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Events - Khulna University School</title>
  <link rel="stylesheet" href="nav.css">
  <link rel="stylesheet" href="footer.css">
  <style>
    .events-container {
      max-width: 1000px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .event {
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 15px;
      cursor: pointer;
      background-color: #f9f9f9;
      transition: background-color 0.3s;
    }

    .event:hover {
      background-color: #eef5ff;
    }

    .event-title {
      font-size: 18px;
      font-weight: bold;
      color: #004080;
    }

    .event-date {
      font-weight: normal;
      font-size: 14px;
      color: #777;
      margin-left: 8px;
    }

    .event-category {
      font-size: 14px;
      color: #555;
      margin-top: 4px;
    }

    .event-description {
      display: none;
      margin-top: 10px;
      color: #333;
      line-height: 1.5;
    }

    .organizers {
      margin-top: 10px;
    }

    .organizers p {
      margin: 2px 0;
    }
  </style>
  <script>
    function toggleEvent(el) {
      const desc = el.querySelector('.event-description');
      desc.style.display = (desc.style.display === 'block') ? 'none' : 'block';
    }
  </script>
</head>
<body>

<?php include 'nav.php'; ?>

<div class="events-container">
  <h2 style="text-align:center; color:#004080;">School Events</h2>

  <?php
  include 'db_connection.php';
  if ($conn->connect_error) {
    echo "<p style='color:red;text-align:center;'>Database connection failed.</p>";
  } else {
    // Step 1: Get all events
    $sqlEvents = "SELECT * FROM institution_event ORDER BY event_date DESC";
    $resultEvents = $conn->query($sqlEvents);

    if ($resultEvents && $resultEvents->num_rows > 0) {
      while ($event = $resultEvents->fetch_assoc()) {
        $eventID = $event['event_id'];
        $title = htmlspecialchars($event['title']);
        $category = htmlspecialchars($event['category']);
        $eventDate = date("d M, Y", strtotime($event['event_date']));
        $description = nl2br(htmlspecialchars($event['event_description']));

        // Step 2: Get organizers for this event
        $organizers = [];
        $sqlOrg = "SELECT t.teacher_name, t.email 
                   FROM teacher_event te
                   JOIN teacher t ON te.t_ID = t.teacher_ID
                   WHERE te.event_id = $eventID";
        $resOrg = $conn->query($sqlOrg);
        if ($resOrg && $resOrg->num_rows > 0) {
          while ($org = $resOrg->fetch_assoc()) {
            $name = htmlspecialchars($org['teacher_name']);
            $email = htmlspecialchars($org['email']);
            $organizers[] = "$name ($email)";
          }
        }

        // Step 3: Count student participants
        $sqlCount = "SELECT COUNT(*) as count FROM student_event WHERE event_id = $eventID";
        $resCount = $conn->query($sqlCount);
        $studentCount = 0;
        if ($resCount && $countRow = $resCount->fetch_assoc()) {
          $studentCount = $countRow['count'];
        }

        // Display
        echo "
        <div class='event' onclick='toggleEvent(this)'>
          <div class='event-title'>$title <span class='event-date'>($eventDate)</span></div>
          <div class='event-category'>$category</div>
          <div class='event-description'>
            $description
            <br><br>
            <div class='organizers'><strong>Organizers:</strong><br>";
              if (count($organizers) > 0) {
                foreach ($organizers as $o) {
                  echo "<p>- $o</p>";
                }
              } else {
                echo "<p>- N/A</p>";
              }
        echo "
            </div>
            <br>
            <strong>Total Student Participation:</strong> $studentCount
          </div>
        </div>
        ";
      }
    } else {
      echo "<p style='text-align:center;'>No events found.</p>";
    }

    $conn->close();
  }
  ?>
</div>

<?php include 'footer.php'; ?>

</body>
</html>
