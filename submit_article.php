<?php
session_start();

require_once 'db_connect.php';
require_once 'helpers/db_helpers.php';

// Redirect if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $upload_path = null;

        // Handle file upload
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === 0) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_name = basename($_FILES['featured_image']['name']);
            $file_tmp = $_FILES['featured_image']['tmp_name'];
            $file_size = $_FILES['featured_image']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($file_ext, $allowed_types) && $file_size <= 5 * 1024 * 1024) {
                $new_file_name = uniqid('img_', true) . '.' . $file_ext;
                $upload_path = $upload_dir . $new_file_name;

                if (!move_uploaded_file($file_tmp, $upload_path)) {
                    $upload_path = null; // Fallback to null if upload fails
                }
            }
        }

        // Prepare form data
        $title = $_POST['title'] ?? '';
        $abstract = $_POST['abstract'] ?? '';
        $content = $_POST['content'] ?? '';
        $comments_enabled = isset($_POST['comments']) ? 1 : 0;
        $notifications = isset($_POST['notifications']) ? 1 : 0;

        // Insert article into the database
        $stmt = $pdo->prepare(
            "INSERT INTO articles (user_id, title, abstract, content, featured_image, comments_enabled,  notifications, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'PENDING')"
        );
        $stmt->execute([$user_id, $title, $abstract, $content, $upload_path, $comments_enabled, $notifications]);

        header("Location: copy_newsfeed_v1.php?submitted=1");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
