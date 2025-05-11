<link rel="stylesheet" href="admin-dashboard/admin-components/css/notification-styles.css">


<header class="header">
  <div class="header-title">
    <h1>Article Management Dashboard
    </h1>
  </div>
  <div class="header-actions">
    <div class="notifications dropdown">
      <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-bs-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-bell fa-fw"></i>
        <!-- Counter - Alerts -->
        <span class="badge badge-danger badge-counter">
          <?php
          // Fetch unread notifications count
          $unread_query = "SELECT COUNT(*) AS unread_count FROM admin_notifications WHERE is_read = 0";
          $unread_result = $conn->query($unread_query);
          $unread_count = $unread_result->fetch_assoc()['unread_count'] ?? 0;
          echo $unread_count > 0 ? $unread_count : '';
          ?>
        </span>
      </a>
      <!-- Dropdown - Alerts -->
      <div class="dropdown-list dropdown-menu dropdown-menu-end shadow animated--grow-in"
        aria-labelledby="alertsDropdown">
        <h6 class="dropdown-header">
          Alerts Center
        </h6>
        <?php
        // Fetch the latest notifications
        $notifications_query = "SELECT * FROM admin_notifications ORDER BY created_at DESC LIMIT 5";
        $notifications_result = $conn->query($notifications_query);

        if ($notifications_result && $notifications_result->num_rows > 0):
          while ($notification = $notifications_result->fetch_assoc()):
            $link = $notification['link'] ?? '#';
            $type = $notification['type'] ?? 'info';
            $is_read = $notification['is_read'] == 1;
            $created_at = new DateTime($notification['created_at']);
            $formatted_date = $created_at->format('M d, Y g:i A');

            // Determine icon based on notification type
            $icon_class = 'fa-file-alt';
            $bg_class = 'bg-primary';

            switch ($type) {
              case 'success':
                $icon_class = 'fa-check';
                $bg_class = 'bg-success';
                break;
              case 'warning':
                $icon_class = 'fa-exclamation-triangle';
                $bg_class = 'bg-warning';
                break;
              case 'danger':
                $icon_class = 'fa-exclamation-circle';
                $bg_class = 'bg-danger';
                break;
              case 'info':
              default:
                $icon_class = 'fa-info-circle';
                $bg_class = 'bg-primary';
                break;
            }
            ?>
            <a class="dropdown-item d-flex align-items-center <?= $is_read ? 'read' : 'unread' ?>" href="<?= $link ?>"
              data-notification-id="<?= $notification['id'] ?>">
              <div class="mr-3">
                <div class="icon-circle <?= $bg_class ?>">
                  <i class="fas <?= $icon_class ?> text-white"></i>
                </div>
              </div>
              <div>
                <div class="small text-gray-500">
                  <?= $formatted_date ?>
                </div>
                <style>
                  .truncate {
                    display: inline-block;
                    max-width: 200px;
                    /* Adjust the width */
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                  }
                </style>
                <span class="truncate  <?= $is_read ? 'font-weight-normal' : 'font-weight-bold' ?>">
                  <?= htmlspecialchars($notification['message']) ?>
                </span>
              </div>
            </a>
            <?php
          endwhile;
        else:
          ?>
          <a class="dropdown-item text-center small text-gray-500" href="#">No new notifications</a> <?php endif; ?>
        <a class="dropdown-item text-center small text-gray-500" href="all_notifications.php">Show All
          Notifications</a>
      </div>
    </div>
    <div class="user-info dropdown">
      <div class="dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <span class="user-name">Administrator</span>
        <img src="admin_dashboards/img/undraw_profile.svg" alt="User Profile" class="user-avatar">
      </div>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">

        <hr class="dropdown-divider">
        </li>
        <li><a class="dropdown-item" href="logout.php"><i
              class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i> Logout</a></li>
      </ul>
    </div>
  </div>
</header>