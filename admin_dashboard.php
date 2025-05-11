<?php
include('db_connect.php');

// Check if user is logged in as admin
session_start();
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//   header('Location: login.php');
//   exit;
// }

// Get statistics
$stats_query = "
    SELECT 
        COUNT(*) AS total, 
        SUM(status IN ('PENDING', 'SUBMITTED')) AS pending,
        SUM(status = 'APPROVED') AS approved,
        SUM(status = 'REJECTED') AS rejected
    FROM articles";
$stats_result = $conn->query($stats_query);

if ($stats_result && $stats_result->num_rows > 0) {
  $stats = $stats_result->fetch_assoc();
} else {
  // Fallback values in case of error or empty result
  $stats = [
    'total' => 0,
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0
  ];
}

// Get article data
$articles_query = "
    SELECT 
        articles.*, 
        users.full_name AS author_name, 
        users.email AS author_email, 
        users.institute AS author_institute,
        users.profile_picture,
        articles.featured_image
    FROM articles 
    JOIN users ON articles.user_id = users.id 
    ORDER BY articles.created_at DESC 
    LIMIT 10";
$articles_result = $conn->query($articles_query);

if ($articles_result && $articles_result->num_rows > 0) {
  $articles = $articles_result->fetch_all(MYSQLI_ASSOC);
} else {
  $articles = []; // fallback to empty array to avoid undefined variable
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DBCLM College - Articles Dashboard</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  <link rel="stylesheet" href="admin-dashboard\css\all_notification.css" />

  <!-- Custom CSS -->
  <link rel="stylesheet" href="admin_dashboard.css">
</head>

<body>

  <div class="layout-wrapper">
    <!-- Sidebar -->
    <?php
    $currentPage = 'notifications';
    include('admin-dashboard/admin-components/sidebar.php');
    ?>
    <!-- Main Content -->
    <main class="main-content">

      <?php
      $pageTitle = 'All Notifications';
      include('admin-dashboard/admin-components/header.php');
      ?>

      <div class="content">
        <!-- Stats Cards -->
        <div class="stats-container">
          <div class="stats-card total-articles">
            <div class="stats-header">
              <p>TOTAL SUBMISSIONS</p>
            </div>
            <div class="stats-body">
              <h2><?= $stats['total'] ?? 0 ?></h2>
              <div class="stats-icon">
                <i class="fas fa-file-alt"></i>
              </div>
            </div>
          </div>

          <div class="stats-card pending-reviews">
            <div class="stats-header">
              <p>PENDING REVIEWS</p>
            </div>
            <div class="stats-body">
              <h2><?= $stats['pending'] ?? 0 ?></h2>
              <div class="stats-icon">
                <i class="fas fa-clock"></i>
              </div>
            </div>
          </div>

          <div class="stats-card approved-articles">
            <div class="stats-header">
              <p>APPROVED ARTICLES</p>
            </div>
            <div class="stats-body">
              <h2><?= $stats['approved'] ?? 0 ?></h2>
              <div class="stats-icon">
                <i class="fas fa-check-circle"></i>
              </div>
            </div>
          </div>

          <div class="stats-card rejected-articles">
            <div class="stats-header">
              <p>REJECTED ARTICLES</p>
            </div>
            <div class="stats-body">
              <h2><?= $stats['rejected'] ?? 0 ?></h2>
              <div class="stats-icon">
                <i class="fas fa-times-circle"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Articles Table -->
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>ARTICLE</th>
                <th>AUTHOR</th>
                <th>INSTITUTE</th>
                <th>DATE</th>
                <th>STATUS</th>
                <th>ACTIONS</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($articles as $article): ?>
                <tr>
                  <td><?= htmlspecialchars($article['title']) ?></td>
                  <td>
                    <?= htmlspecialchars($article['author_name']) ?><br>
                    <a
                      href="mailto:<?= htmlspecialchars($article['author_email']) ?>"><?= htmlspecialchars($article['author_email']) ?></a>
                  </td>
                  <td><?= htmlspecialchars($article['author_institute'] ?? 'N/A') ?></td>
                  <td><?= date('M j, Y', strtotime($article['created_at'])) ?></td>
                  <td><span
                      class="status-badge <?= strtolower($article['status']) ?>"><?= strtoupper($article['status']) ?></span>
                  </td>
                  <td class="actions"> <button class="btn-view" onclick="openArticleModal(
                        <?= $article['id'] ?>, 
                        '<?= htmlspecialchars($article['title']) ?>', 
                        '<?= htmlspecialchars($article['author_name']) ?>', 
                        '<?= htmlspecialchars($article['author_email']) ?>', 
                        '<?= htmlspecialchars($article['author_institute'] ?? 'N/A') ?>', 
                        '<?= date('M j, Y', strtotime($article['created_at'])) ?>', 
                        '<?= htmlspecialchars($article['status']) ?>',
                        '<?= htmlspecialchars(addslashes($article['content'])) ?>',
                        '<?= htmlspecialchars($article['featured_image'] ?? '') ?>',
                        '<?= htmlspecialchars($article['profile_picture'] ?? 'profile.jpg') ?>'
                      )">View
                    </button>

                    <?php if ($article['status'] === 'PENDING'): ?>
                      <button class="btn-approve" type="button"
                        onclick="openApproveModal(<?= $article['id'] ?>)">Approve</button>
                      <button class="btn-reject" type="button"
                        onclick="openRejectModal(<?= $article['id'] ?>)">Reject</button>
                    <?php else: ?>
                      <button class="btn-done " type="button" disabled
                        onclick="approveArticle(<?= $article['id'] ?>)">Approve</button>
                      <button class="btn-done" type="button" disabled
                        onclick="openRejectModal(<?= $article['id'] ?>)">Reject</button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>


  <!-- Article Modal -->
  <div class="modal-overlay" id="articleModal">
    <div class="modal-container modal-lg">
      <div class="modal-header">
        <h2>Article Details</h2>
        <span class="modal-close" onclick="closeArticleModal()">&times;</span>
      </div>
      <div class="modal-body">
        <div class="article-header">
          <h2 id="modalArticleTitle" class="article-title"></h2>
          <span id="modalArticleStatus" class="status-badge"></span>
        </div>

        <div class="article-author-section">
          <div class="author-info">
            <img id="modalAuthorImage" src="" alt="Author" class="author-avatar">
            <div>
              <h4 id="modalArticleAuthor"></h4>
              <p id="modalArticleInstitute" class="author-institute"></p>
              <p class="article-date">Published on <span id="modalArticleDate"></span></p>
            </div>
          </div>
          <div class="author-contact">
            <a id="modalArticleEmailLink" href="" target="_blank"><i class="fas fa-envelope"></i> <span
                id="modalArticleEmail"></span></a>
          </div>
        </div>

        <div class="article-content-section"> <!-- Article Featured Image -->
          <div id="modalArticleImageContainer" class="article-image-container">
            <img id="modalArticleImage" src="" alt="Article Featured Image" class="article-image">
          </div>

          <!-- Article Content -->
          <div id="modalArticleContent" class="article-content"></div>
        </div>

        <div class="modal-actions">
          <a href="#" id="viewArticleLink" class="btn-edit"></a>
          <button type="button" class="btn-cancel" onclick="closeArticleModal()">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Reject Modal -->
  <div class="modal-overlay" id="rejectModal">
    <div class="modal-container">
      <div class="modal-header">
        <h2>Reject Article</h2>
        <span class="modal-close" onclick="closeRejectModal()">&times;</span>
      </div>
      <div class="modal-body">
        <form id="rejectForm" action="reject_article.php" method="POST">
          <input type="hidden" id="rejectArticleId" name="article_id">

          <div class="form-group mb-4">
            <label for="reason" class="mb-2">Reason for Rejection:</label>
            <textarea id="reason" name="reason" rows="4" class="form-control" required></textarea>
          </div>

          <div class="modal-actions">
            <button type="submit" class="btn-reject">Reject Article</button>
            <button type="button" class="btn-cancel" onclick="closeRejectModal()">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Approve Modal -->
  <div class="modal-overlay" id="approveModal">
    <div class="modal-container">
      <div class="modal-header">
        <h2>Approve Article</h2>
        <span class="modal-close" onclick="closeApproveModal()">&times;</span>
      </div>
      <div class="modal-body">
        <form id="approveForm" action="approve_article.php" method="POST">
          <input type="hidden" id="approveArticleId" name="article_id">

          <div class="form-group mb-4">
            <label for="approval_notes" class="mb-2">Approval Notes (Optional):</label>
            <textarea id="approval_notes" name="approval_notes" rows="4" class="form-control"
              placeholder="Add any notes or feedback for the author"></textarea>
          </div>

          <div class="modal-actions">
            <button type="submit" class="btn-approve">Approve Article</button>
            <button type="button" class="btn-cancel" onclick="closeApproveModal()">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

  <script src="admin-dashboard\js\admin_dashboard.js"></script>

</body>

</html>