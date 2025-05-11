<?php
include('db_connect.php');

// Check if user is logged in as admin
session_start();
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//   header('Location: login.php');
//   exit;
// }

$user_stats_query = "
    SELECT 
        COUNT(*) AS total, 
        SUM(role = 'student') AS student,
        SUM(role = 'teacher') AS teacher,
        SUM(created_at >= NOW() - INTERVAL 7 DAY) AS new_users
    FROM users
";

$user_stats_result = $conn->query($user_stats_query);

if ($user_stats_result && $user_stats_result->num_rows > 0) {
  $user_stats = $user_stats_result->fetch_assoc();
} else {
  // Fallback values in case of error or empty result
  $user_stats = [
    'total' => 0,
    'student' => 0,
    'teacher' => 0,
    'new_users' => 0
  ];
}

// Fetch all users with their details
$users_query = "
    SELECT 
        users.id,
        users.full_name AS author_name, 
        users.email AS author_email, 
        users.created_at,
        users.role AS author_role,
        users.status,
        users.profile_picture
    FROM 
        users 
    ORDER BY 
        users.created_at DESC
";
$users_result = $conn->query($users_query);

if ($users_result && $users_result->num_rows > 0) {
  $rows = $users_result->fetch_all(MYSQLI_ASSOC);
} else {
  $rows = []; // fallback to empty array to avoid undefined variable
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DBCLM College - Dashboard</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  <link rel="stylesheet" href="admin-dashboard\css\all_notification.css" />

  <!-- Custom CSS -->
  <link rel="stylesheet" href="user_admin_dashboard.css">
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
        <!-- Stats Cards -->
        <div class="stats-container">
          <div class="stats-card total-users">
            <div class="stats-header">
              <p>TOTAL USERS</p>
            </div>
            <div class="stats-body">
              <h2><?= $user_stats['total'] ?? 0 ?></h2>
              <div class="stats-icon">
                <i class="fas fa-users"></i>
              </div>
            </div>
          </div>

          <div class="stats-card students">
            <div class="stats-header">
              <p>STUDENTS</p>
            </div>
            <div class="stats-body">
              <h2><?= $user_stats['student'] ?? 0 ?></h2>
              <div class="stats-icon">
                <i class="fas fa-user-graduate"></i>
              </div>
            </div>
          </div>

          <div class="stats-card teachers">
            <div class="stats-header">
              <p>TEACHERS</p>
            </div>
            <div class="stats-body">
              <h2><?= $user_stats['teacher'] ?? 0 ?></h2>
              <div class="stats-icon">
                <i class="fas fa-chalkboard-teacher"></i>
              </div>
            </div>
          </div>

          <div class="stats-card new-users">
            <div class="stats-header">
              <p>NEW USERS (LAST 7 DAYS)</p>
            </div>
            <div class="stats-body">
              <h2><?= $user_stats['new_users'] ?? 0 ?></h2>
              <div class="stats-icon">
                <i class="fas fa-user-plus"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Users Table -->
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>NAME</th>
                <th>EMAIL</th>
                <th>ROLE</th>
                <th>DATE</th>
                <th>STATUS</th>
                <th>ACTION</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td><?= htmlspecialchars($row['author_name']) ?></td>
                  <td><?= htmlspecialchars($row['author_email']) ?></td>
                  <td><span
                      class="role-badge <?= strtolower($row['author_role']) ?>"><?= strtoupper($row['author_role']) ?></span>
                  </td>
                  <td><?= date('M j, Y', strtotime($row['created_at'])) ?></td>
                  <td><span class="status-badge <?= strtolower($row['status']) ?>"><?= ucfirst($row['status']) ?></span>
                  </td>
                  <td>
                    <button class="btn-view"
                      onclick="openUserModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['author_name']) ?>', '<?= htmlspecialchars($row['author_email']) ?>', '<?= htmlspecialchars($row['author_role']) ?>', '<?= htmlspecialchars($row['status']) ?>', '<?= date('M j, Y', strtotime($row['created_at'])) ?>', '<?= htmlspecialchars($row['profile_picture'] ?? '') ?>')">View</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>

  <!-- User Modal -->
  <div class="modal-overlay" id="userModal">
    <div class="modal-container">
      <div class="modal-header">
        <h2>User Details</h2>
        <span class="modal-close" onclick="closeUserModal()">&times;</span>
      </div>
      <div class="modal-body">
        <div class="user-info-section">
          <div class="user-avatar-large">
            <img id="modalUserAvatar" src="" alt="User Profile" class="user-profile-image">
            <div id="modalUserDefaultAvatar" class="user-default-avatar">
              <i class="fas fa-user-circle"></i>
            </div>
          </div>
          <div class="user-details">
            <h3 id="modalUserName"></h3>
            <span id="modalUserRole" class="role-badge"></span>
            <span id="modalUserStatus" class="status-badge"></span>
          </div>
        </div>

        <div class="user-details-grid">
          <div class="detail-item">
            <span class="detail-label">Email</span>
            <span class="detail-value" id="modalUserEmail"></span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Joined Date</span>
            <span class="detail-value" id="modalUserDate"></span>
          </div>
        </div>

        <div class="modal-actions">
          <a href="#" id="editUserLink" class="btn-edit">Edit User</a>

        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

  <script src="admin-dashboard\js\user_admin_dashboard.js"></script>
</body>

</html>