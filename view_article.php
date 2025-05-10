<?php
include('includes/header.php');
include('includes/navbar.php');
include('includes/db.php');

// Check if the article ID is provided
if (!isset($_GET['article_id']) || !is_numeric($_GET['article_id'])) {
    echo "<div class='container mt-4'><p class='alert alert-danger'>Invalid article ID.</p></div>";
    include('includes/footer.php');
    exit();
}

$article_id = $_GET['article_id'];

// Fetch the article details
$query = "
    SELECT a.*, u.full_name, u.email 
    FROM articles a 
    JOIN users u ON a.user_id = u.id 
    WHERE a.id = ?
";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $article_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0):
    $article = $result->fetch_assoc();
?>
<div class="container mt-4">
    <h2>Article Details</h2>
    <p><strong>Title:</strong> <?= htmlspecialchars($article['title']); ?></p>
    <p><strong>Author:</strong> <?= htmlspecialchars($article['full_name']); ?> (<?= htmlspecialchars($article['email']); ?>)</p>
    <p><strong>Content:</strong></p>
    <p><?= nl2br(htmlspecialchars($article['content'])); ?></p>
</div>
<?php
else:
    echo "<div class='container mt-4'><p class='alert alert-danger'>Article not found.</p></div>";
endif;

$stmt->close();
$mysqli->close();

include('includes/footer.php');
?>