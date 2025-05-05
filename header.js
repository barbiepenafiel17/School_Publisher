/**
 * Header Component JavaScript
 * Handles notification system and user menu functionality
 */

document.addEventListener('DOMContentLoaded', function () {
  // Notification dropdown functionality
  const bell = document.getElementById('notif-bell');
  const dropdown = document.getElementById('notif-dropdown');

  if (bell && dropdown) {
    bell.addEventListener('click', function () {
      // Toggle display between 'none' and 'block'
      dropdown.style.display = dropdown.style.display === 'none' || dropdown.style.display === '' ? 'block' : 'none';

      // If visible, mark all notifications as read via AJAX
      if (dropdown.style.display === 'block') {
        fetch('mark_notification_read.php', {
          method: 'POST'
        })
          .then(response => response.text())
          .then(() => {
            const badge = document.querySelector('.notif-badge');
            if (badge) {
              badge.style.display = 'none';
            }
          });
      }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
      if (!dropdown.contains(e.target) && e.target !== bell && dropdown.style.display === 'block') {
        dropdown.style.display = 'none';
      }
    });
  }

  // User dropdown toggle
  window.toggleUserMenu = function () {
    const userMenu = document.getElementById('user-menu');
    if (userMenu) {
      if (userMenu.style.display === 'none' || userMenu.style.display === '') {
        userMenu.style.display = 'block';
      } else {
        userMenu.style.display = 'none';
      }
    }
  };

  // Close user menu when clicking outside
  document.addEventListener('click', function (e) {
    const userDropdown = document.querySelector('.user-dropdown');
    const userMenu = document.getElementById('user-menu');
    if (userDropdown && userMenu && !userDropdown.contains(e.target) && userMenu.style.display === 'block') {
      userMenu.style.display = 'none';
    }
  });
});

