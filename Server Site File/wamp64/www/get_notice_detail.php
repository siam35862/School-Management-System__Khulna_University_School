<?php
if (!isset($_GET['notice_id'])) {
  echo "No notice selected.";
  exit;
}

$notice_id = intval($_GET['notice_id']);

// DB connection
include 'db_connection.php';

$stmt = $conn->prepare("SELECT notice_description FROM notice WHERE notice_id = ?");
$stmt->bind_param("i", $notice_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $row = $result->fetch_assoc()) {
  echo nl2br(htmlspecialchars($row['notice_description']));
} else {
  echo "Notice not found.";
}

$stmt->close();
$conn->close();
?>
