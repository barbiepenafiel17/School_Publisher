<?php
// Required headers
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  // Connect to database
  $pdo = new PDO("mysql:host=localhost;dbname=dbclm_college", "root", "");

  // Get announcement ID from GET data
  $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

  if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid announcement ID']);
    exit;
  }

  try {
    // Get the announcement details
    $stmt = $pdo->prepare("SELECT * FROM announcements WHERE id = ?");
    $stmt->execute([$id]);
    $announcement = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($announcement) {
      echo json_encode([
        'success' => true,
        'data' => $announcement
      ]);
    } else {
      echo json_encode(['success' => false, 'message' => 'Announcement not found']);
    }
  } catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
  }
} else {
  echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>