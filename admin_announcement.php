<?php
include('db_connect.php');

// Check if user is logged in as admin
session_start();
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//   header('Location: login.php');
//   exit;
// }

// Connect to database for announcements
$pdo = new PDO("mysql:host=localhost;dbname=dbclm_college", "root", "");

// Fetch any existing announcements (if needed)
$announcements_query = "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5";
try {
  $announcements_result = $pdo->query($announcements_query);
  $announcements = $announcements_result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $announcements = []; // fallback to empty array if table doesn't exist yet
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DBCLM College - Announcements</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="admin_announcement.css">
</head>

<body>

  <div class="layout-wrapper">
    <!-- Sidebar -->
    <?php require_once 'admin-dashboard\admin-components\sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">

      <!-- Header/Navigation -->
      <?php require_once 'admin-dashboard\admin-components\header.php'; ?>


      <div class="content">
        <!-- Announcements Header with Create Button -->
        <div class="announcements-header">
          <h2>Manage Announcements</h2>
          <button id="createAnnouncementBtn" class="btn-create">
            <i class="fas fa-plus-circle"></i> Create New Announcement
          </button>
        </div>

        <!-- Recent Announcements Section -->
        <div class="announcement-box">
          <h2>Recent Announcements</h2>
          <?php if (!empty($announcements)): ?>
            <div class="table-container">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>TITLE</th>
                    <th>DATE</th>
                    <th>AUDIENCE</th>
                    <th>ACTION</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($announcements as $announcement): ?>
                    <tr>
                      <td><?= htmlspecialchars($announcement['title']) ?></td>
                      <td><?= date('M j, Y', strtotime($announcement['created_at'])) ?></td>
                      <td><?= htmlspecialchars($announcement['audience']) ?></td>
                      <td>
                        <button class="btn-view" data-id="<?= $announcement['id'] ?>">View</button>
                        <button class="btn-edit" data-id="<?= $announcement['id'] ?>">Edit</button>
                        <button class="btn-delete" data-id="<?= $announcement['id'] ?>">Delete</button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="empty-state">
              <i class="fas fa-bullhorn fa-3x"></i>
              <p>No announcements yet</p>
              <p class="empty-subtext">Create your first announcement by clicking the "Create New Announcement" button
                above</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </main>
  </div>
  <!-- Create Announcement Modal -->
  <div class="modal-overlay" id="announcementModal">
    <div class="modal-container">
      <div class="modal-header">
        <h2>Create New Announcement</h2>
        <span class="modal-close" id="closeAnnouncementModal">&times;</span>
      </div>
      <div class="modal-body">
        <form action="admin_save_announcement.php" method="POST" enctype="multipart/form-data" id="announcementForm">
          <div class="announcement-form">
            <label>Title</label>
            <input type="text" name="title" required placeholder="Enter announcement title" />

            <label>Content</label>
            <textarea name="content" required placeholder="Write your announcement content here..."></textarea>

            <label>Target Audience</label>
            <div class="checkbox-group">
              <label><input type="checkbox" name="audience[]" value="students" /> Students</label>
              <label><input type="checkbox" name="audience[]" value="teacher" /> Teacher</label>
            </div>

            <div class="notify">
              <label><input type="checkbox" name="notify" value="1" /> Send notification to users</label>
            </div>

            <div class="modal-actions">
              <button type="button" class="btn-cancel" id="cancelAnnouncementBtn">Cancel</button>
              <button type="submit" class="btn-publish" name="action" value="publish">Publish</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- View Announcement Modal -->
  <div class="modal-overlay" id="viewAnnouncementModal">
    <div class="modal-container">
      <div class="modal-header">
        <h2>Announcement Details</h2>
        <span class="modal-close" id="closeViewAnnouncementModal">&times;</span>
      </div>
      <div class="modal-body">
        <div class="announcement-details">
          <h3 id="viewTitle"></h3>
          <div class="announcement-meta">
            <span id="viewDate"></span> | <span id="viewAudience"></span>
          </div>
          <div class="announcement-content">
            <p id="viewContent"></p>
          </div>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn-cancel" id="closeViewBtn">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal-overlay" id="deleteAnnouncementModal">
    <div class="modal-container">
      <div class="modal-header">
        <h2>Confirm Delete</h2>
        <span class="modal-close" id="closeDeleteAnnouncementModal">&times;</span>
      </div>
      <div class="modal-body">
        <div class="delete-confirmation">
          <i class="fas fa-exclamation-triangle text-warning fa-2x mb-4"></i>
          <p>Are you sure you want to delete this announcement? This action cannot be undone.</p>
          <h4 id="deleteTitle"></h4>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn-cancel" id="cancelDeleteBtn">Cancel</button>
          <button type="button" class="btn-delete" id="confirmDeleteBtn">Delete</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js">
  </script>

  <script src="admin-dashboard\js\admin_announcement.js"></script>
</body>

</html>