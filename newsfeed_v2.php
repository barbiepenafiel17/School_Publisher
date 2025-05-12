<?php
// Refactored to improve maintainability and modularity with full client-side rendering
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

// Fetch latest announcements
$latest_announcements = getLatestAnnouncements($pdo);
?>
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
  <!-- Application Shell - Everything will be rendered by JavaScript -->
  <div id="app" class="layout">
    <div id="loading" class="loading-spinner">
      <div class="spinner"></div>
    </div>
  </div>

  <!-- Pass initial data to JavaScript -->
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