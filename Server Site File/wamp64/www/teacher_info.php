<?php
include 'db_connection.php';
$teachers = [];

if (!$conn->connect_error) {
  $sql = "SELECT * FROM teacher ORDER BY joining_date ASC";
  $result = $conn->query($sql);

  if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $teachers[] = $row;
    }
  }

  $conn->close();
}

// Grouping helpers
function is_headmaster_only($designation) {
  $d = strtolower($designation);
  return strpos($d, 'headmaster') !== false && strpos($d, 'assistant headmaster') === false;
}

function is_assistant_headmaster($designation) {
  $d = strtolower($designation);
  return strpos($d, 'headmaster') !== false && strpos($d, 'assistant headmaster') !== false;
}

function filter_teachers($teachers, $service_status, $type) {
  return array_filter($teachers, function ($t) use ($service_status, $type) {
    if ($t['service_status'] !== $service_status) return false;

    $d = $t['designation'];

    switch ($type) {
      case 'headmaster':
        return is_headmaster_only($d);
      case 'assistant':
        return is_assistant_headmaster($d);
      case 'others':
        return !is_headmaster_only($d) && !is_assistant_headmaster($d);
    }

    return false;
  });
}

// Display Function
function display_teacher_table($teachers, $title) {
  if (count($teachers) === 0) return;

  echo "<h2 style='margin-top: 40px; text-align: center;'>$title</h2>";
  echo "<div style='overflow-x: auto;'><table class='teacher-table'>";
  echo "<thead>
          <tr>
            <th>Name</th>
            <th>Designation</th>
            <th>Qualification</th>
            <th>Teaching Subject</th>
            <th>Joining Date</th>
            <th>Birth Date</th>
            <th>Phone Number</th>
            <th>Email</th>
            <th>District</th>
          </tr>
        </thead>
        <tbody>";
  foreach ($teachers as $t) {
    echo "<tr>
            <td>" . htmlspecialchars($t['teacher_name']) . "</td>
            <td>" . htmlspecialchars($t['designation']) . "</td>
            <td>" . htmlspecialchars($t['qualification']) . "</td>
            <td>" . htmlspecialchars($t['teaching_subject']) . "</td>
            <td>" . htmlspecialchars($t['joining_date']) . "</td>
            <td>" . htmlspecialchars($t['date_of_birth']) . "</td>
            <td>" . htmlspecialchars($t['phone_number']) . "</td>
            <td>" . htmlspecialchars($t['email']) . "</td>
            <td>" . htmlspecialchars($t['district']) . "</td>
          </tr>";
  }
  echo "</tbody></table></div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<style>
    .teacher-table {
      width: 1200px;
      margin: 20px auto;
      border-collapse: collapse;
      table-layout: fixed;
      font-size: 14px;
    }

    .teacher-table th,
    .teacher-table td {
      border: 1px solid #ccc;
      padding: 8px;
      text-align: center;
      word-wrap: break-word;
      overflow-wrap: break-word;
      vertical-align: middle;
    }

    /* Fixed column widths */
    .teacher-table th:nth-child(1), .teacher-table td:nth-child(1) { width: 12%; }
    .teacher-table th:nth-child(2), .teacher-table td:nth-child(2) { width: 13%; }
    .teacher-table th:nth-child(3), .teacher-table td:nth-child(3) { width: 11%; }
    .teacher-table th:nth-child(4), .teacher-table td:nth-child(4) { width: 14%; }
    .teacher-table th:nth-child(5), .teacher-table td:nth-child(5) { width: 10%; }
    .teacher-table th:nth-child(6), .teacher-table td:nth-child(6) { width: 10%; }
    .teacher-table th:nth-child(7), .teacher-table td:nth-child(7) { width: 10%; }
    .teacher-table th:nth-child(8), .teacher-table td:nth-child(8) { width: 10%; }
    .teacher-table th:nth-child(9), .teacher-table td:nth-child(9) { width: 10%; }

    @media (max-width: 1300px) {
      .teacher-table {
        width: 100%;
        font-size: 13px;
      }
    }
  </style>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Teacher & Staff - Khulna University School</title>
  <link rel="stylesheet" href="nav.css" />
  <link rel="stylesheet" href="index.css" />
  <link rel="stylesheet" href="footer.css" />
 
</head>
<body>

  <!-- Navigation -->
  <?php include 'nav.php'; ?>

  <!-- Content -->
  <section class="message-section" style="flex-direction: column; align-items: center;">
    <h1>Teacher & Staff</h1>

    <?php
      display_teacher_table(filter_teachers($teachers, 'active', 'headmaster'), 'Headmaster');
      display_teacher_table(filter_teachers($teachers, 'active', 'assistant'), 'Assistant Headmaster');
      display_teacher_table(filter_teachers($teachers, 'active', 'others'), 'Working Teachers');

      display_teacher_table(filter_teachers($teachers, 'retired', 'headmaster'), 'Retired Headmaster');
      display_teacher_table(filter_teachers($teachers, 'retired', 'assistant'), 'Retired Assistant Headmaster');
      display_teacher_table(filter_teachers($teachers, 'retired', 'others'), 'Retired Teachers');
    ?>
  </section>

  <!-- Footer -->
  <?php include 'footer.php'; ?>

</body>
</html>
