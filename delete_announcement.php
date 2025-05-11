<?php
// Required headers
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Connect to database
  $pdo = new PDO("mysql:host=localhost;dbname=dbclm_college", "root", "");

  // Get announcement ID from POST data
  $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

  if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid announcement ID']);
    exit;
  }

  try {
    // Delete the announcement
    $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
    $result = $stmt->execute([$id]);

    if ($result) {
      echo json_encode(['success' => true, 'message' => 'Announcement deleted successfully']);
    } else {
      echo json_encode(['success' => false, 'message' => 'Failed to delete announcement']);
    }
  } catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
  }
} else {
  echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>