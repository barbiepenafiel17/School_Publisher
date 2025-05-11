<?php
require_once 'helpers/db_helpers.php';
require_once 'db_connect.php';

session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
  echo json_encode(['error' => 'Not authenticated']);
  exit();
}

// Get parameters
$offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
$limit = isset($_POST['limit']) ? intval($_POST['limit']) : 5;
$sortOption = isset($_POST['sort']) ? $_POST['sort'] : 'new';

// Validate parameters
if ($offset < 0 || $limit <= 0 || $limit > 20) {
  echo json_encode(['error' => 'Invalid parameters']);
  exit();
}

// Get institutes from the session or use default
$institutes = isset($_SESSION['filtered_institutes']) ? $_SESSION['filtered_institutes'] : ['All'];

// Fetch paginated articles
$articles = getFilteredArticlesPaginated($pdo, $institutes, $sortOption, $limit, $offset);

// Start output buffering to capture HTML
ob_start();

if (!empty($articles)) {
  foreach ($articles as $row) {
    $articleId = $row['id'];
    // Include the article HTML directly here
    ?>
    <div class="post-card" data-article-id="<?= $articleId ?>">
      <div class="post-card-header">
        <div class="post-header">
          <img class="avatar" src="uploads/profile_pictures/<?= htmlspecialchars($row['profile_picture']) ?>" alt="User">
          <div>
            <strong><?= htmlspecialchars($row['full_name']) ?></strong><br>
            <span class="post-date"><?= date("F j, Y | g:i A", strtotime($row['created_at'])) ?></span>
          </div>
        </div>
        <div class="dropdown" style="display: inline-block; position: relative;">
          <button class="dot-btn" onclick="toggleDropdown(this)">...</button>
          <div class="dropdown-content"
            style="display: none; position: absolute; top: 0; right: 100%; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.2); z-index: 1;">
            <?php if ($row['user_id'] == $_SESSION['user_id']): ?>
              <!-- Delete -->
              <form method="POST" action="delete_article.php" onsubmit="return confirm('Delete this article?');">
                <input type="hidden" name="article_id" value="<?= $articleId ?>">
                <button type="submit"
                  style="color: red; background: none; border: none; padding: 10px; width: 100%; text-align: left;"><i
                    class="fa fa-trash-o" style="margin-right:10px;"></i>Delete</button>
              </form>
            <?php endif ?>
            <!-- Hide -->
            <form method="POST" action="hide_article.php">
              <input type="hidden" name="article_id" value="<?= $articleId ?>">
              <button type="submit" style="background: none; border: none; padding: 10px; width: 100%; text-align: left;"><i
                  class="fa fa-eye-slash" style="margin-right:10px;"></i>Hide</button>
            </form>

            <!-- Report -->
            <form method="POST" action="report_article.php" onsubmit="return confirm('Report this article to admin?');">
              <input type="hidden" name="article_id" value="<?= $articleId ?>">
              <button type="submit" style="background: none; border: none; padding: 10px; width: 100%; text-align: left;"><i
                  class="fa fa-exclamation-triangle" style="margin-right:10px; color:yellow;"></i>Report</button>
            </form>
            <!-- Save -->
            <form method="POST" action="save_A.php">
              <input type="hidden" name="article_id" value="<?= $articleId ?>">
              <button type="submit" style="background: none; border: none; padding: 10px; width: 100%; text-align: left;"><i
                  class="fa fa-bookmark" style="margin-right:10px;"></i>Save</button>
            </form>
          </div>
        </div>
      </div>

      <div class="post-title" style="font-family: Poppins; font-size:40px">
        <strong><?= htmlspecialchars($row['title']) ?></strong></div>
      <div class="post-content" style="font-family: Poppins; font-size:20px; text-align:justify">
        <?= htmlspecialchars($row['abstract']) ?></div>

      <?php if (!empty($row['featured_image'])): ?>
        <div class="post-image">
          <img src="<?= htmlspecialchars($row['featured_image']) ?>" alt="Article Image" class="responsive-img"
            loading="lazy">
        </div>
      <?php endif; ?>

      <div class="post-actions">
        <button class="like-btn" data-article-id="<?= $articleId ?>">
          👍 <span class="like-count" id="like-count-<?= $articleId ?>"><?= $row['likes'] ?></span>
        </button>
        <button class="comment-btn">
          💬 <span class="comment-count" id="comment-count-<?= $articleId ?>"><?= $row['comments'] ?></span>
        </button>
      </div>

      <div class="post-comments">
        <div class="comment-list" id="comments-<?= $articleId ?>">
          <?php $comments = getCommentsForArticle($pdo, $articleId); ?>
          <?php if (!empty($comments)): ?>
            <?php foreach ($comments as $comment): ?>
              <div class="comment">
                <div class="comment-text">
                  <strong><?= htmlspecialchars($comment['commenter_name']) ?>:</strong>
                  <?= htmlspecialchars($comment['comment_text']) ?>
                </div>
                <?php if (!empty($comment['reply_text'])): ?>
                  <div class="reply-text">
                    <em>Owner replied:</em> <?= htmlspecialchars($comment['reply_text']) ?>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="no-comments">No comments yet.</p>
          <?php endif; ?>
        </div>

        <form method="POST" action="post_comment.php" class="comment-form">
          <input type="hidden" name="article_id" value="<?= $articleId ?>">
          <input class="comment-box" type="text" name="comment_text" placeholder="Write a comment..." required>
          <button class="post-form-button" type="submit">Post</button>
        </form>
      </div>
    </div>
    <?php
  }
}
$html = ob_get_clean();

echo json_encode([
  'html' => $html,
  'count' => count($articles),
  'hasMore' => count($articles) == $limit
]);
?>