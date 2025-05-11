<?php
include 'db_connect.php';

// Check if admin_notifications table exists
$result = $conn->query("SHOW TABLES LIKE 'admin_notifications'");
if ($result->num_rows > 0) {
  echo "Admin notifications table exists";
} else {
  echo "Admin notifications table does not exist";

  // Create the table if it doesn't exist
  $create_table_sql = "CREATE TABLE admin_notifications (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        message VARCHAR(255) NOT NULL,
        type VARCHAR(50) DEFAULT 'info',
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        link VARCHAR(255) DEFAULT NULL
    )";

  if ($conn->query($create_table_sql)) {
    echo "\nTable created successfully";

    // Add some sample notifications
    $sample_notifications = [
      "New article submission requires review",
      "User account registration pending approval",
      "System update available"
    ];

    foreach ($sample_notifications as $message) {
      $conn->query("INSERT INTO admin_notifications (message) VALUES ('$message')");
    }

    echo "\nSample notifications added";
  } else {
    echo "\nError creating table: " . $conn->error;
  }
}
?>