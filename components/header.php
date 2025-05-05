<?php
/**
 * Header Component
 * Displays the navigation bar and user profile elements.
 */

// Ensure variables are defined
$notifCount = $notifCount ?? 0;
$full_name = $full_name ?? 'Guest';
$unreadNotifications = $unreadNotifications ?? [];
?>
<!-- Add the header-specific CSS file -->
<link rel="stylesheet" href="header.css">

<header class="navbar">
  <div class="logo">
    <img src="FinalLogo.jpg" alt="DBCLM Logo">
  </div>
  <nav class="nav-links">
    <a href="newsfeed.php">Home</a>
    <!-- <a href="#">Latest</a> -->
    <a href="aboutus.php">About</a>
    <a href="contactus.php">Contact</a>
  </nav>
  <div class="navbar-right">
    <div class="notification-wrapper">
      <img src="bell.jpg" alt="Notifications" class="icon-bell" id="notif-bell">

      <?php if ($notifCount > 0): ?>
        <span class="notif-badge"><?php echo $notifCount; ?></span>
      <?php endif; ?>

      <div class="notif-dropdown" id="notif-dropdown">
        <?php
        if ($notifCount > 0) {
          foreach ($unreadNotifications as $notif) {
            echo '<div class="notif-item">' .
              htmlspecialchars($notif['message'] ?? '') .
              '<br><small style="color:gray;">' . date('M d, Y H:i', strtotime($notif['created_at'])) . '</small>' .
              '</div>';
          }
        } else {
          echo '<div class="notif-item">No new notifications</div>';
        }
        ?>
      </div>
    </div>

    <!-- User Menu Dropdown Profile and Logout -->
    <div class="user-dropdown">
      <button class="user-label" onclick="toggleUserMenu()">
        <?php echo htmlspecialchars($full_name ?? ''); ?> &#x25BC;
      </button>
      <div id="user-menu" class="dropdown-content">
        <!-- <a href="profile.php">Profile</a> -->
        <a href="logout.php">Logout</a>
      </div>
    </div>
  </div>
</header>

<!-- Add the header-specific JavaScript file -->
<script src="header.js"></script>