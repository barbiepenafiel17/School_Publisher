<?php
// Refactored to improve maintainability and modularity
require_once 'helpers/db_helpers.php';
require_once 'db_connect.php';
require_once 'filter_feed.php';

session_start();

// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
if (!is_numeric($user_id)) {
    die("Invalid user ID.");
}

// Fetch user info
$user = getUserInfo($pdo, $user_id);
if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$full_name = htmlspecialchars($user['full_name']);
$profile_picture = !empty($user['profile_picture']) ? 'uploads/profile_pictures/' . htmlspecialchars($user['profile_picture']) : 'uploads/profile_pictures/default_profile.png';

// Fetch unread notifications
$unreadNotifications = getUnreadNotifications($pdo, $user_id);
$notifCount = count($unreadNotifications);

// Fetch articles and announcements
$institutes = ['All'];
$sortOption = $_POST['sort'] ?? 'new'; // Get sort option from POST request or default to 'new'
$articles = getFilteredArticles($pdo, $institutes, $sortOption);
$latest_announcements = getLatestAnnouncements($pdo);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>DBCLM College</title>
    <link rel="stylesheet" href="global.css">
    <link rel="stylesheet" href="newsfeed.css">
</head>

<body>

    <div class="layout">

        <header class="header">
            <?php require_once 'components/header.php'; ?>
        </header>

        <aside class="sidebar">
            <?php require_once 'components/sidebar.php'; ?>
        </aside>

        <main class="main-content">
            <!-- Post Feeds -->
            <div>
                <div class="post-box">
                    <?php
                    // Display profile picture
                    if (!empty($profile_picture)) {
                        $profile_picture = htmlspecialchars($profile_picture);
                    } else {
                        // Default profile picture if none uploaded
                        $profile_picture = 'default_profile.png'; // You can put a nice default image here
                    }
                    ?>


                    <div class="post-box-input">
                        <img src="<?php echo $profile_picture; ?>" alt="User" class="avatar">
                        <form class="post-form" action="submit_article.php" method="POST">
                            <input type="text" class="post-input" id="articleInput" name="title"
                                placeholder="What's on your mind? Want to publish your own article?" required>
                        </form>

                    </div>


                    <div class="center-button">
                        <button class="publish-btn"><strong>PUBLISH ARTICLE</strong> ✎</button>

                        <!-- Dropdown Article Form -->
                        <div id="articleFormDropdown" class="form-dropdown hidden">
                            <div class="form-container">
                                <div class="form-header">
                                    <h2>Submit Your Article</h2>
                                    <button class="close-btn"
                                        onclick="document.getElementById('articleFormDropdown').classList.add('hidden')">✕</button>

                                </div>
                                <form action="submit_article.php" method="post" enctype="multipart/form-data">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Article Title</label>
                                            <input type="text" name="title" required>
                                        </div>
                                    </div>
                                    <div class="form-group full-width">
                                        <label>Abstract</label>
                                        <textarea name="abstract" rows="3"></textarea>
                                    </div>

                                    <div class="form-group full-width">
                                        <label>Featured Image</label>
                                        <div class="image-upload">
                                            <div class="image-box">
                                                <p>📷<br>Drag and drop your image here, or click to browse</p>
                                                <small>Supported formats: JPG, PNG, GIF (Max size: 5MB)</small>
                                                <input type="file" name="featured_image" hidden id="featuredImageInput">
                                            </div>
                                            <button type="button" class="select-button" id="selectImageButton">Select
                                                Image</button>
                                        </div>
                                    </div>
                                    <div class="form-group full-width toggle-section">
                                        <label>Additional Options</label>
                                        <div class="toggles">
                                            <div class="toggle-item">
                                                <label class="switch">
                                                    <span>💬 Allow comments</span>
                                                    <input type="checkbox" name="comments" checked>
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                            <div class="toggle-item">
                                                <label class="switch">
                                                    <span>🔒 Make article private</span>
                                                    <input type="checkbox" name="private">
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                            <div class="toggle-item">
                                                <label class="switch">
                                                    <span>🔔 Email notifications for comments</span>
                                                    <input type="checkbox" name="notifications" checked>
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-actions">
                                        <button type="button" class="draft-btn">Save as Draft</button>
                                        <button type="submit" class="submit-btn">Submit for Review</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="post-feed" id="postFeed">
                    <?php if (!empty($articles)): ?>
                        <?php foreach ($articles as $row): ?>
                            <?php $articleId = $row['id']; ?>
                            <div class="post-card" data-article-id="<?= $articleId ?>">
                                <div class="dropdown" style="display: inline-block; position: relative;">

                                    <div class="dropdown-content"
                                        style="display: none; position: absolute; top: 0; left: 100%; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.2); z-index: 1;">
                                        <!-- Delete -->
                                        <form method="POST" action="delete_article.php"
                                            onsubmit="return confirm('Delete this article?');">
                                            <input type="hidden" name="article_id" value="<?= $articleId ?>">
                                            <button type="submit"
                                                style="color: red; background: none; border: none; padding: 10px; width: 100%; text-align: left;">Delete</button>
                                        </form>

                                        <!-- Hide -->
                                        <form method="POST" action="hide_article.php">
                                            <input type="hidden" name="article_id" value="<?= $articleId ?>">
                                            <button type="submit"
                                                style="background: none; border: none; padding: 10px; width: 100%; text-align: left;">Hide</button>
                                        </form>

                                        <!-- Report -->
                                        <form method="POST" action="report_article.php"
                                            onsubmit="return confirm('Report this article to admin?');">
                                            <input type="hidden" name="article_id" value="<?= $articleId ?>">
                                            <button type="submit"
                                                style="background: none; border: none; padding: 10px; width: 100%; text-align: left;">Report</button>
                                        </form>
                                    </div>
                                </div>

                                <div class="post-card-header">
                                    <div class="post-header">
                                        <img class="avatar"
                                            src="uploads/profile_pictures/<?= htmlspecialchars($row['profile_picture']) ?>"
                                            alt="User">
                                        <div>
                                            <strong><?= htmlspecialchars($row['full_name']) ?></strong><br>
                                            <span
                                                class="post-date"><?= date("F j, Y | g:i A", strtotime($row['created_at'])) ?></span>
                                        </div>
                                    </div>
                                    <button class="dot-btn" onclick="toggleDropdown(this)">...</button>
                                </div>

                                <div class="post-title"><strong><?= htmlspecialchars($row['title']) ?></strong></div>
                                <div class="post-content"><?= htmlspecialchars($row['abstract']) ?></div>

                                <?php if (!empty($row['featured_image'])): ?>
                                    <div class="post-image">
                                        <img src="<?= htmlspecialchars($row['featured_image']) ?>" alt="Article Image"
                                            class="responsive-img">
                                    </div>
                                <?php endif; ?>

                                <div class="post-actions">
                                    <button class="like-btn" data-article-id="<?= $articleId ?>">
                                        👍 <span class="like-count"
                                            id="like-count-<?= $articleId ?>"><?= $row['likes'] ?></span>
                                    </button>
                                    <button class="comment-btn">
                                        💬 <span class="comment-count"
                                            id="comment-count-<?= $articleId ?>"><?= $row['comments'] ?></span>
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
                                        <input class="comment-box" type="text" name="comment_text"
                                            placeholder="Write a comment..." required>
                                        <button class="post-form-button" type="submit">Post</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No approved articles available yet.</p>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Post Feeds -->
        </main>

        <footer class="footer">
            <?php require_once 'components/footer.php'; ?>
        </footer>
    </div>



    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/newsfeed.js"></script>
    <script src="js/database_helper.js"></script>

</body>

</html>