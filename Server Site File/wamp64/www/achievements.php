<?php
// Include header and navigation
include('nav.php');

include 'db_connection.php';
// Fetch achievements data from the database with student name
$sql = "SELECT a.achievement_id, a.award_name, a.achievement_date, a.achievement_description, a.st_ID, a.t_ID, a.event_id, 
        t.teacher_name, s.student_id,c.class_name,c.group_, af.applicant_name, t.phone_number AS teacher_phone, t.email AS teacher_email
        FROM achievement a
        LEFT JOIN teacher t ON a.t_ID = t.teacher_ID
        LEFT JOIN student s ON a.st_ID = s.st_ID
        LEFT JOIN admission_form af ON s.admission_id = af.admission_id
        LEFT JOIN class c ON c.class_id=s.class_id
        ORDER BY a.achievement_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Achievements - Khulna University School</title>
  <link rel="stylesheet" href="nav.css">
  <link rel="stylesheet" href="footer.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f4f4f9;
    }

    .achievements-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    h2 {
      text-align: center;
      color: #333;
      margin-bottom: 30px;
    }

    .achievement-item {
      background-color: #f9f9f9;
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .award-name {
      font-size: 24px;
      font-weight: bold;
      color: #4CAF50;
    }

    .achievement-item p {
      font-size: 16px;
      color: #555;
      line-height: 1.6;
    }

    .toggle-description {
      background-color: #4CAF50;
      color: white;
      padding: 10px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      margin-top: 10px;
      transition: background-color 0.3s;
    }

    .toggle-description:hover {
      background-color: #45a049;
    }

    .achievement-description {
      margin-top: 15px;
      padding: 10px;
      background-color: #f1f1f1;
      border-left: 4px solid #4CAF50;
      font-size: 14px;
      line-height: 1.6;
      color: #333;
    }

    .achievement-item .teacher-info,
    .achievement-item .student-info {
      margin-top: 15px;
      font-size: 14px;
      color: #777;
    }

    .teacher-info a,
    .student-info a {
      color: #4CAF50;
      text-decoration: none;
    }

    .teacher-info a:hover,
    .student-info a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<!-- Achievements Section -->
<div class="achievements-container">
  <h2>Achievements</h2>

  <?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="achievement-item">
        <h3 class="award-name"><?= htmlspecialchars($row['award_name']) ?></h3>
        <p><strong>Honoured by:</strong> 
          <?php
            if ($row['t_ID']) {
              echo "Teacher: " . htmlspecialchars($row['teacher_name']);
            } else {
              echo "Student: " . htmlspecialchars($row['applicant_name']);
            }
          ?>
        </p>
        <p><strong>Date:</strong> <?= date("F j, Y", strtotime($row['achievement_date'])) ?></p>

        <button class="toggle-description">Show Description</button>

        <div class="achievement-description" style="display: none;">
          <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($row['achievement_description'])) ?></p>
          
          <?php if ($row['t_ID']): ?>
            <p class="teacher-info">
              <strong>Teacher Contact:</strong> 
              <a href="mailto:<?= htmlspecialchars($row['teacher_email']) ?>"><?= htmlspecialchars($row['teacher_email']) ?></a> | 
              <a href="tel:<?= htmlspecialchars($row['teacher_phone']) ?>"><?= htmlspecialchars($row['teacher_phone']) ?></a>
            </p>
          <?php else: ?>
            <p class="student-info"><strong>Student ID:</strong> <?= htmlspecialchars($row['student_id']) ?></p>
            <p class="student-info"><strong>Class:</strong> <?= htmlspecialchars($row['class_name']) ?></p>
            <p class="student-info"><strong>Group:</strong> <?= htmlspecialchars($row['group_']) ?></p>
          <?php endif; ?>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>No achievements found.</p>
  <?php endif; ?>
</div>

<!-- Footer Section (appears after achievements) -->
<?php include('footer.php'); ?>

<script>
  // Toggle the achievement description visibility
  document.querySelectorAll('.toggle-description').forEach(button => {
    button.addEventListener('click', function() {
      const description = this.nextElementSibling;
      description.style.display = description.style.display === 'none' ? 'block' : 'none';
      this.textContent = description.style.display === 'none' ? 'Show Description' : 'Hide Description';
    });
  });
</script>

</body>
</html>

<?php
// Close database connection
$conn->close();
?>
