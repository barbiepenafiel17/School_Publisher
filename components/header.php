<?php
/**
 * Header Component
 * Displays the navigation bar and user profile elements.
 */

// Ensure variables are defined
$notifCount = $notifCount ?? 0;
$full_name = $full_name ?? 'Guest';
?>
<header class="navbar">
  <div class="logo">
    <img src="FinalLogo.jpg" alt="DBCLM Logo">
  </div>
  <nav class="nav-links">
    <a href="newsfeed.php">Home</a>
    <a href="#">Latest</a>
    <a href="aboutus.php">About</a>
    <a href="contactus.php">Contact</a>
  </nav>
  <div class="navbar-right">
    <div class="notification-wrapper" style="position: relative;">
      <img src="bell.jpg" alt="Notifications" class="icon-bell" id="notif-bell" style="cursor:pointer;">

      <?php if ($notifCount > 0): ?>
        <span class="notif-badge"> <?php echo $notifCount; ?> </span>
      <?php endif; ?>

      <div class="notif-dropdown" id="notif-dropdown" style="display:none;">
        <?php
        if ($notifCount > 0) {
          foreach ($unreadNotifications as $notif) {
            echo '<div class="notif-item">' .
              htmlspecialchars($notif['message'] ?? '') .
              '<br><small>' . date('M d, Y H:i', strtotime($notif['created_at'])) . '</small>' .
              '</div>';
          }
        } else {
          echo '<div class="notif-item">No new notifications</div>';
        }
        ?>
      </div>
    </div>

    <div class="user-dropdown" style="position: relative; display: inline-block;">
      <button class="user-label" onclick="toggleUserMenu()" style="background: none; border: none; cursor: pointer;">
        <?php echo htmlspecialchars($full_name ?? ''); ?> &#x25BC;
      </button>
      <div id="user-menu" class="dropdown-content"
        style="display: none; position: absolute; right: 0; background-color: white; min-width: 120px; box-shadow: 0px 8px 16px rgba(0,0,0,0.2); z-index: 1;">
        <a href="profile.php"
          style="display: block; padding: 8px 16px; text-decoration: none; color: black;">Profile</a>
        <a href="logout.php" style="display: block; padding: 8px 16px; text-decoration: none; color: black;">Logout</a>
      </div>
    </div>
  </div>
</header>