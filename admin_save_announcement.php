<?php
$pdo = new PDO("mysql:host=localhost;dbname=dbclm_college", "root", ""); // Adjust credentials

// Check if it's an update (id exists) or a new announcement
$isUpdate = isset($_POST['announcement_id']) && !empty($_POST['announcement_id']);
$announcementId = $isUpdate ? intval($_POST['announcement_id']) : 0;

$title = $_POST['title'];
$content = $_POST['content'];
$audience = isset($_POST['audience']) ? implode(",", $_POST['audience']) : "all";
$notify = isset($_POST['notify']) ? 1 : 0;
$action = $_POST['action'];
$status = $action === 'publish' ? 'published' : ($action === 'draft' ? 'draft' : 'cancelled');

$upload_dir = "uploads/";
$attachment_name = "";

if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
    $file_tmp = $_FILES['attachment']['tmp_name'];
    $file_name = basename($_FILES['attachment']['name']);
    $target_path = $upload_dir . uniqid() . "_" . $file_name;

    if (move_uploaded_file($file_tmp, $target_path)) {
        $attachment_name = $target_path;
    }
}

// Save or update the announcement
if ($isUpdate) {
    // Update existing announcement
    $stmt = $pdo->prepare("UPDATE announcements 
                          SET title = ?, content = ?, audience = ?, notify = ?, status = ?
                          WHERE id = ?");

    $stmt->execute([
        $title,
        $content,
        $audience,
        $notify,
        $status,
        $announcementId
    ]);
} else {
    // Save new announcement
    $stmt = $pdo->prepare("INSERT INTO announcements (title, content, audience, notify, status, created_at)
    VALUES (?, ?, ?, ?, ?, NOW())");

    $stmt->execute([
        $title,
        $content,
        $audience,
        $notify,
        $status
    ]);
}

// Delete old announcements, keep only the 3 latest
$pdo->exec("
    DELETE FROM announcements
    WHERE id NOT IN (
        SELECT id FROM (
            SELECT id FROM announcements ORDER BY created_at DESC LIMIT 3
        ) AS keep_ids
    )
");

// Redirect
header("Location: admin_announcement.php");
exit;
?>