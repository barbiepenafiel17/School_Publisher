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
$initialLimit = 3;
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
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        // This relies on a showToast(message, type) function being available globally.
        if (typeof showToast === 'function') {
          showToast('The article has been reported successfully.', 'success');
        } else {
          // Fallback if showToast is not defined
          alert('The article has been reported successfully.');
          console.error('showToast function not found. Please define it to use toast notifications.');
        }
      });
    </script>
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

    <main class="main-content">
      <!-- Post Box -->
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
          <form class="post-form " style="display: flex; " action="submit_article.php" method="POST">
            <input type="text" class="post-input" id="articleInput" name="title"
              placeholder="What's on your mind? Want to publish your own article?" required>
            <button type="submit" class="post-btn" style="margin-left:12px;">Submit</button>
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
                <!-- <div class="form-group full-width toggle-section">
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
                                    </div> -->
                <div class="form-actions">
                  <!-- <button type="button" class="draft-btn">Save as Draft</button> -->
                  <button type="submit" class="submit-btn">Submit for Review</button>
                </div>
              </form>
            </div>
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
  <script src="js/header.js"></script>
  <script src="js/newsfeed_v2.js"></script>
</body>

</html>