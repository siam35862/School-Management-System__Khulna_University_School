<?php 
include 'db_connection.php';
$currentYear = date("Y");

// Fetch class list
$classOptions = [];
$classSql = "SELECT class_id, group_, class_name FROM class ORDER BY class_id";
$classResult = $conn->query($classSql);
while ($row = $classResult->fetch_assoc()) {
  $classOptions[$row['class_id']] = [
    'name' => $row['class_name'],
    'group' => $row['group_']
  ];
}

// Process form
$selectedClass = $_POST['class'] ?? '';
$selectedGroup = $_POST['group'] ?? '';
$students = [];

if ($_SERVER["REQUEST_METHOD"] === "POST" && $selectedClass) {
  $classId = intval($selectedClass);
  $group = $conn->real_escape_string($selectedGroup);
  
  $sql = "SELECT s.student_id, s.zila, af.applicant_name, af.mobile_number, c.group_, c.class_name
          FROM student s
          JOIN admission_form af ON s.admission_id = af.admission_id
          JOIN class c ON s.class_id = c.class_id
          WHERE s.class_id = $classId
          AND s.academic_year = '$currentYear'
          ORDER BY s.student_id ASC";
  $students = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Current Student List</title>
  <link rel="stylesheet" href="nav.css">
  <link rel="stylesheet" href="footer.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
    }
    
    .content {
      max-width: 1000px;
      margin: 30px auto;
      padding: 20px;
    }
    
    .form-box {
      background: #f0f0f0;
      padding: 20px;
      border-radius: 10px;
      margin-bottom: 30px;
    }
    
    select, button {
      padding: 8px 12px;
      margin-right: 10px;
      font-size: 16px;
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    
    table, th, td {
      border: 1px solid #ccc;
    }
    
    th, td {
      padding: 10px;
      text-align: center;
    }
    
    th {
      background-color: #004080;
      color: white;
    }
    
    .class-header {
      font-size: 18px;
      font-weight: bold;
      margin-bottom: 10px;
    }
    
    @media (max-width: 768px) {
      .form-box {
        display: flex;
        flex-direction: column;
      }
      select, button {
        margin-bottom: 10px;
        width: 100%;
      }
    }
  </style>
</head>
<body>

<?php include 'nav.php'; ?>

<div class="content">
  <h2>Current Student List (<?= $currentYear ?>)</h2>
  
  <form method="POST" class="form-box">
    <label for="class">Select Class:</label>
    <select name="class" id="class" required onchange="toggleGroupBox(this.value)">
      <option value="">--Select Class--</option>
      <?php foreach ($classOptions as $id => $classInfo): ?>
        <option value="<?= $id ?>" <?= ($selectedClass == $id ? 'selected' : '') ?>>
          <?= $classInfo['name'] ?> <?= $classInfo['group'] ? '- '.$classInfo['group'] : '' ?>
        </option>
      <?php endforeach; ?>
    </select>
    
    <button type="submit">Show Students</button>
  </form>
  
  <?php if ($students && $students->num_rows > 0): 
    $firstRow = $students->fetch_assoc();
    $students->data_seek(0); // Reset to first row
    ?>
    
    <div class="class-header">
      <?= htmlspecialchars($firstRow['class_name']) ?> 
      <?= $firstRow['group_'] ? '- '.htmlspecialchars($firstRow['group_']) : '' ?>
    </div>
    
    <table>
      <tr>
        <th>Student Name</th>
        <th>Student ID</th>
        <th>Mobile Number</th>
        <th>Hometown (Zila)</th>
      </tr>
      <?php while ($row = $students->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['applicant_name']) ?></td>
          <td><?= htmlspecialchars($row['student_id']) ?></td>
          <td><?= htmlspecialchars($row['mobile_number']) ?></td>
          <td><?= htmlspecialchars($row['zila']) ?></td>
        </tr>
      <?php endwhile; ?>
    </table>
  <?php elseif ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
    <p>No students found for the selected class and group.</p>
  <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
  
</body>
</html>