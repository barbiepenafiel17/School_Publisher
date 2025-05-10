<?php
include('includes/header.php');
include('includes/navbar.php');
include('includes/db.php');

// Fetch all notifications
$query = "SELECT * FROM admin_notifications ORDER BY created_at DESC";
$result = $mysqli->query($query);
?>

<div class="container mt-4">
    <h2>All Notifications</h2>
    <ul class="list-group">
        <?php while ($notification = $result->fetch_assoc()): ?>
            <li class="list-group-item">
                <strong><?= htmlspecialchars($notification['message']) ?></strong>
                <br>
                <small class="text-muted"><?= date('F j, Y, g:i A', strtotime($notification['created_at'])) ?></small>
                <?php if ($notification['type'] === 'report'): ?>
                    <a href="view_article.php?article_id=<?= $notification['reference_id']; ?>" class="btn btn-sm btn-primary mt-2">View Reported Article</a>
                <?php endif; ?>
            </li>
        <?php endwhile; ?>
    </ul>
</div>

<?php include('includes/footer.php'); ?>