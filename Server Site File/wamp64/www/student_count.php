<?php 
$currentYear = date("Y"); 
include 'db_connection.php';

// Total Students (Current Year) 
$totalSql = "SELECT COUNT(*) AS total FROM student WHERE academic_year = '$currentYear'"; 
$totalResult = $conn->query($totalSql); 
$totalStudents = $totalResult->fetch_assoc()['total'] ?? 0;

// Get class_id to class_name mapping
$classMapping = [];
$mappingSql = "SELECT class_id, class_name FROM class";
$mappingResult = $conn->query($mappingSql);
while ($row = $mappingResult->fetch_assoc()) {
  $classMapping[$row['class_id']] = $row['class_name'];
}

// Class-wise Count for junior classes (Six, Seven, Eight)
$classCounts = []; 
$juniorClasses = ['Six', 'Seven', 'Eight'];
foreach ($juniorClasses as $className) {
  $sql = "SELECT COUNT(*) AS total 
          FROM student s
          JOIN class c ON s.class_id = c.class_id
          WHERE s.academic_year = '$currentYear' 
          AND c.class_name = '$className'";
  $result = $conn->query($sql);
  $row = $result->fetch_assoc();
  $classCounts[$className] = $row['total'] ?? 0; 
}

// Group-wise Count for senior classes (Nine and Ten) using group_ from class table
$groupCounts = []; 
$seniorClasses = ['Nine', 'Ten'];
foreach ($seniorClasses as $className) {
  $sql = "SELECT c.group_, COUNT(*) AS total
          FROM student s
          JOIN class c ON s.class_id = c.class_id
          WHERE s.academic_year = '$currentYear' 
          AND c.class_name = '$className'
          GROUP BY c.group_";
  $result = $conn->query($sql);
  $groupCounts[$className] = [];
  while ($row = $result->fetch_assoc()) {
    $group = $row['group_'] ?: 'Not Specified';
    $groupCounts[$className][$group] = $row['total'];
  } 
}

$conn->close(); 
?>

<!DOCTYPE html> 
<html lang="en"> 
<head>
  <meta charset="UTF-8">
  <title>Current Student Count</title>
  <link rel="stylesheet" href="nav.css">
  <link rel="stylesheet" href="footer.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
    }
    
    .count-container {
      max-width: 900px;
      margin: 40px auto;
      padding: 20px;
      background: #f9f9f9;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .count-container h2 {
      color: #004080;
      text-align: center;
      margin-bottom: 30px;
    }
    
    .count-section {
      margin-bottom: 30px;
    }
    
    .count-section h3 {
      margin-bottom: 10px;
      color: #222;
      border-bottom: 2px solid #004080;
      padding-bottom: 5px;
    }
    
    .count-list {
      list-style: none;
      padding-left: 20px;
    }
    
    .count-list li {
      margin: 5px 0;
      font-size: 16px;
    }
    
    @media (max-width: 768px) {
      .count-container {
        margin: 20px;
        padding: 15px;
      }
    }
  </style> 
</head> 
<body>

<?php include 'nav.php'; ?>

<div class="count-container">
  <h2>Student Count for Academic Year <?= $currentYear ?></h2>
  
  <div class="count-section">
    <h3>Total Students</h3>
    <ul class="count-list">
      <li><strong>Total:</strong> <?= $totalStudents ?></li>
    </ul>
  </div>
  
  <div class="count-section">
    <h3>Class-wise Count (Classes Six–Eight)</h3>
    <ul class="count-list">
      <?php foreach ($classCounts as $className => $count): ?>
        <li>Class <?= $className ?>: <?= $count ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
  
  <div class="count-section">
    <h3>Group-wise Count (Classes Nine & Ten)</h3>
    <?php foreach ($groupCounts as $className => $groups): ?>
      <strong>Class <?= $className ?>:</strong>
      <ul class="count-list">
        <?php if (empty($groups)): ?>
          <li>No students found</li>
        <?php else: ?>
          <?php foreach ($groups as $group => $count): ?>
            <li><?= htmlspecialchars($group) ?>: <?= $count ?></li>
          <?php endforeach; ?>
        <?php endif; ?>
      </ul>
    <?php endforeach; ?>
  </div>
</div>

<?php include 'footer.php'; ?>

</body> 
</html>