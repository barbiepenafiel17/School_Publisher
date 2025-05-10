<?php
require 'includes/db.php'; // Include your database connection file

// Check if the user ID is provided
if (!isset($_POST['user_id']) || !is_numeric($_POST['user_id'])) {
    echo "<p>Invalid user ID.</p>";
    exit();
}

$user_id = $_POST['user_id'];

// Fetch the user details
$query = "
    SELECT 
        full_name, 
        email, 
        role, 
        status, 
        created_at, 
        last_activity 
    FROM users 
    WHERE id = ?
";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "<p>User not found.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View User</title>
    <link rel="stylesheet" href="view_user.css"> <!-- Optional CSS file -->
</head>
<body>
    <div class="user-container">
        <h1>User Details</h1>
        <p><strong>Full Name:</strong> <?= htmlspecialchars($user['full_name']); ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></p>
        <p><strong>Role:</strong> <?= ucfirst(htmlspecialchars($user['role'])); ?></p>
        <p><strong>Status:</strong> <?= ucfirst(htmlspecialchars($user['status'])); ?></p>
        <p><strong>Created At:</strong> <?= date('F j, Y, g:i A', strtotime($user['created_at'])); ?></p>
        <p><strong>Last Activity:</strong> <?= $user['last_activity'] ? date('F j, Y, g:i A', strtotime($user['last_activity'])) : 'No activity recorded'; ?></p>
        <a href="index1.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</body>
</html>