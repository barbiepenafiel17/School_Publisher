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

// Get initial articles (first batch)
$initialLimit = 5;
$institutes = ['All'];
$sortOption = 'new';
$articles = getFilteredArticlesPaginated($pdo, $institutes, $sortOption, $initialLimit, 0);

// Add is_owner flag to articles
foreach ($articles as &$article) {
    $article['is_owner'] = ($article['user_id'] == $_SESSION['user_id']);
}

// Fetch unread notifications
$unreadNotifications = getUnreadNotifications($pdo, $user_id);
$notifCount = count($unreadNotifications);

$full_name = htmlspecialchars($user['full_name']);
$profile_picture = !empty($user['profile_picture']) ? 'uploads/profile_pictures/' . htmlspecialchars($user['profile_picture']) : 'uploads/profile_pictures/default_profile.png';

// Fetch articles and announcements
$latest_announcements = getLatestAnnouncements($pdo);
?>
<?php if (isset($_GET['status'])): ?>
    <?php if ($_GET['status'] == 'reported'): ?>
        <div class="alert alert-success">The article has been reported successfully.</div>
    <?php elseif ($_GET['status'] == 'error'): ?>
        <div class="alert alert-danger">An error occurred while reporting the article. Please try again.</div>
    <?php endif; ?>
<?php endif; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>DBCLM College</title>
    <link rel="stylesheet" href="global.css">
    <link rel="stylesheet" href="newsfeed.css">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>

<body>

    <div class="layout">

        <header class="header">
            <?php require_once 'components/header.php'; ?>
        </header>

        <aside class="sidebar">
            <?php require_once 'components/sidebar.php'; ?>
        </aside>

        <main class="main-content"> <!-- Post Box -->
            <div class="post-box">
                <div class="post-box-input">
                    <img src="<?= !empty($user['profile_picture']) ? 'uploads/profile_pictures/' . htmlspecialchars($user['profile_picture']) : 'uploads/profile_pictures/default_profile.png' ?>"
                        alt="User" class="avatar">
                    <form id="quickPostForm" class="post-form" style="display: flex;">
                        <input type="text" class="post-input" id="articleInput" name="title"
                            placeholder="What's on your mind? Want to publish your own article?" required>
                        <button type="submit" class="post-btn" style="margin-left:12px;">Submit</button>
                    </form>
                </div>

                <div class="center-button">
                    <button class="publish-btn" id="publishBtn"><strong>PUBLISH ARTICLE</strong> ✎</button>
                    <div id="articleFormDropdown" class="form-dropdown hidden">
                        <!-- Article form will be rendered by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Post Feed - This will be populated by JavaScript -->
            <div id="postFeed"></div>

            <!-- Load More -->
            <div class="load-more-container" id="loadMoreContainer">
                <button id="loadMoreBtn" class="load-more-btn">Load More</button>
                <div id="loadingSpinner" class="loading-spinner" style="display: none;">
                    <div class="spinner"></div>
                </div>
            </div>
        </main>

        <footer class="footer">
            <?php require_once 'components/footer.php'; ?>
        </footer>
    </div> <!-- Pass initial data to JavaScript -->
    <script>
        window.initialData = {
            user: <?= json_encode($user) ?>,
            articles: <?= json_encode($articles) ?>,
            currentOffset: <?= $initialLimit ?>,
            sortOption: '<?= $sortOption ?>',
            notifications: {
                count: <?= $notifCount ?>,
                items: <?= json_encode($unreadNotifications) ?>
            },
            announcements: <?= json_encode($latest_announcements) ?>
        };
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/newsfeed_v2.js"></script>

</body>

</html>