<?php
include('db_connect.php');

// Check if user is logged in as admin
session_start();
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//   header('Location: login.php');
//   exit;
// }

// Include notification functions
if (file_exists('helpers/notification_functions.php')) {
  include_once('helpers/notification_functions.php');
}

// Pagination settings
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get filter parameters
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';
$read_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';

// Build the query with filters
$query_conditions = [];
$params = [];
$param_types = "";

if (!empty($type_filter)) {
  $query_conditions[] = "type = ?";
  $params[] = $type_filter;
  $param_types .= "s";
}

if ($read_filter === 'read') {
  $query_conditions[] = "is_read = 1";
} elseif ($read_filter === 'unread') {
  $query_conditions[] = "is_read = 0";
}

if (!empty($date_filter)) {
  if ($date_filter === 'today') {
    $query_conditions[] = "DATE(created_at) = CURDATE()";
  } elseif ($date_filter === 'week') {
    $query_conditions[] = "created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
  } elseif ($date_filter === 'month') {
    $query_conditions[] = "created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
  }
}

// Build the where clause
$where_clause = count($query_conditions) > 0 ? " WHERE " . implode(" AND ", $query_conditions) : "";

// Count total notifications with filters
$count_query = "SELECT COUNT(*) AS total FROM admin_notifications" . $where_clause;
$count_stmt = $conn->prepare($count_query);

if (!empty($params)) {
  $count_stmt->bind_param($param_types, ...$params);
}

$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_notifications = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_notifications / $per_page);

// Get notifications with pagination and filters
$notifications_query = "SELECT * FROM admin_notifications" . $where_clause . " ORDER BY created_at DESC LIMIT ?, ?";
$stmt = $conn->prepare($notifications_query);

if (!empty($params)) {
  $params[] = $offset;
  $params[] = $per_page;
  $stmt->bind_param($param_types . "ii", ...$params);
} else {
  $stmt->bind_param("ii", $offset, $per_page);
}

$stmt->execute();
$notifications_result = $stmt->get_result();
$notifications = [];

while ($row = $notifications_result->fetch_assoc()) {
  $notifications[] = $row;
}

// Mark all as read if requested
if (isset($_GET['mark_all_read']) && $_GET['mark_all_read'] == 1) {
  if (function_exists('mark_all_notifications_read')) {
    mark_all_notifications_read();
  } else {
    $conn->query("UPDATE admin_notifications SET is_read = 1");
  }
  header('Location: all_notifications.php');
  exit;
}

// Delete notification if requested
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
  $delete_id = intval($_GET['delete']);
  $conn->query("DELETE FROM admin_notifications WHERE id = $delete_id");
  header('Location: all_notifications.php');
  exit;
}

// Get notification stats
$stats_query = "SELECT 
    COUNT(*) AS total,
    SUM(is_read = 0) AS unread,
    SUM(type = 'info') AS info,
    SUM(type = 'warning') AS warning,
    SUM(type = 'danger') AS danger,
    SUM(type = 'success') AS success
FROM admin_notifications";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DBCLM College - All Notifications</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  <!-- Admin Layout CSS -->



  <link rel="stylesheet" href="admin-dashboard\css\header.css">
  <link rel="stylesheet" href="admin-dashboard\css\sidebar.css">


  <!-- Custom CSS -->
  <link rel="stylesheet" href="admin-dashboard\css\all_notification.css" />
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

      <div class="content p-4">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
          <h1 class="h3 mb-0 text-gray-800">All Notifications</h1>
          <div>
            <a href="all_notifications.php?mark_all_read=1" class="btn btn-sm btn-primary">
              <i class="fas fa-check-double mr-1"></i>
            </a>
            <a href="admin_dashboard.php" class="btn btn-sm btn-secondary">
              <i class="fas fa-arrow-left mr-1"></i>
            </a>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
              <h3><?= number_format($stats['total']) ?></h3>
              <p>Total Notifications</p>
            </div>
          </div>
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
              <h3><?= number_format($stats['unread']) ?></h3>
              <p>Unread</p>
            </div>
          </div>
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
              <h3><?= number_format($stats['warning'] + $stats['danger']) ?></h3>
              <p>Important</p>
            </div>
          </div>
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
              <h3><?= number_format($stats['info'] + $stats['success']) ?></h3>
              <p>Informational</p>
            </div>
          </div>
        </div>


        <!-- Notifications List -->
        <?php if (empty($notifications)): ?>
          <div class="empty-state">
            <i class="fas fa-bell-slash"></i>
            <h5>No notifications found</h5>
            <p class="text-muted">There are no notifications matching your filters.</p>
          </div>
        <?php else: ?>
          <div class="notifications-list">
            <?php foreach ($notifications as $notification):
              $is_read = $notification['is_read'] ? '' : 'unread';
              $type = $notification['type'] ?: 'info';
              // Use reference_id if it exists
              $has_reference = !empty($notification['reference_id']);
              $link = $has_reference ? '#' : '#';

              // Determine link based on type
              if ($has_reference) {
                if ($type == 'article') {
                  $link = 'admin_dashboard.php?article=' . $notification['reference_id'];
                } else if ($type == 'user') {
                  $link = 'user_admin_dashboard.php?user=' . $notification['reference_id'];
                }
              }
              ?>
              <div class="notification-item <?= $is_read ?> <?= $type ?>">
                <div class="notification-header">
                  <span class="type-badge <?= $type ?>"><?= ucfirst($type) ?></span>
                  <span
                    class="notification-date"><?= date('F j, Y, g:i a', strtotime($notification['created_at'] ?? 'now')) ?></span>
                </div>
                <div class="notification-message">
                  <?= htmlspecialchars($notification['message']) ?>
                </div>
                <div class="notification-footer">
                  <div class="notification-status">
                    <?php if ($notification['is_read']): ?>
                      <span class="text-muted"><i class="fas fa-check"></i> Read</span>
                    <?php else: ?>
                      <span class="text-primary"><i class="fas fa-circle fa-sm"></i> Unread</span>
                    <?php endif; ?>
                  </div>
                  <div class="notification-actions">
                    <?php if ($link !== '#'): ?>
                      <a href="<?= $link ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> View
                      </a>
                    <?php endif; ?>
                    <?php if (!$notification['is_read']): ?>
                      <a href="mark_notification_read.php?id=<?= $notification['id'] ?>&return=all_notifications.php"
                        class="btn btn-sm btn-success">
                        <i class="fas fa-check"></i> Mark Read
                      </a>
                    <?php endif; ?>
                    <a href="all_notifications.php?delete=<?= $notification['id'] ?>" class="btn btn-sm btn-danger"
                      onclick="return confirm('Are you sure you want to remove this notification?')">
                      <i class="fas fa-trash"></i>
                    </a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Pagination -->
          <?php if ($total_pages > 1): ?>
            <nav aria-label="Notification pagination">
              <ul class="pagination">
                <?php if ($page > 1): ?>
                  <li class="page-item">
                    <a class="page-link"
                      href="?page=<?= $page - 1 ?>&type=<?= $type_filter ?>&status=<?= $read_filter ?>&date=<?= $date_filter ?>">
                      <i class="fas fa-chevron-left"></i>
                    </a>
                  </li>
                <?php endif; ?>

                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);

                if ($start_page > 1) {
                  echo '<li class="page-item"><a class="page-link" href="?page=1&type=' . $type_filter . '&status=' . $read_filter . '&date=' . $date_filter . '">1</a></li>';
                  if ($start_page > 2) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                  }
                }

                for ($i = $start_page; $i <= $end_page; $i++) {
                  $active = $i === $page ? 'active' : '';
                  echo '<li class="page-item ' . $active . '"><a class="page-link" href="?page=' . $i . '&type=' . $type_filter . '&status=' . $read_filter . '&date=' . $date_filter . '">' . $i . '</a></li>';
                }

                if ($end_page < $total_pages) {
                  if ($end_page < $total_pages - 1) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                  }
                  echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '&type=' . $type_filter . '&status=' . $read_filter . '&date=' . $date_filter . '">' . $total_pages . '</a></li>';
                }
                ?>

                <?php if ($page < $total_pages): ?>
                  <li class="page-item">
                    <a class="page-link"
                      href="?page=<?= $page + 1 ?>&type=<?= $type_filter ?>&status=<?= $read_filter ?>&date=<?= $date_filter ?>">
                      <i class="fas fa-chevron-right"></i>
                    </a>
                  </li>
                <?php endif; ?>
              </ul>
            </nav>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </main>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>