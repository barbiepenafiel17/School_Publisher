<?php
include('db_connect.php');

// Check admin_notifications table structure
$result = $conn->query("SHOW CREATE TABLE admin_notifications");

if ($result && $result->num_rows > 0) {
  $row = $result->fetch_assoc();
  echo $row['Create Table'];
} else {
  echo "Table doesn't exist or couldn't be queried";
}
?>