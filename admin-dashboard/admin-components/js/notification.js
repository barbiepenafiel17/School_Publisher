/**
 * Admin notifications JavaScript
 */
document.addEventListener('DOMContentLoaded', function() {
    // Notification dropdown toggle functionality
    const notificationDropdown = document.querySelector('#alertsDropdown');
    if (notificationDropdown) {
        // Mark notifications as read when dropdown is opened
        notificationDropdown.addEventListener('click', function(e) {
            // Prevent immediate closing of dropdown
            e.preventDefault();
            e.stopPropagation();
            
            // Get the dropdown menu
            const dropdownMenu = this.nextElementSibling;
            
            // Toggle the dropdown
            if (dropdownMenu.classList.contains('show')) {
                dropdownMenu.classList.remove('show');
            } else {
                dropdownMenu.classList.add('show');
                
                // Mark notifications as read when opened
                fetch('../mark_notification_read.php?all=1', {
                    method: 'GET',
                })
                .then(response => {
                    // Update the notification badge
                    updateNotificationCount();
                    
                    // Update UI to show all notifications as read
                    const unreadNotifications = dropdownMenu.querySelectorAll('.dropdown-item.unread');
                    unreadNotifications.forEach(item => {
                        item.classList.remove('unread');
                        item.classList.add('read');
                        const notificationText = item.querySelector('span');
                        if (notificationText) {
                            notificationText.classList.remove('font-weight-bold');
                            notificationText.classList.add('font-weight-normal');
                        }
                    });
                })
                .catch(error => {
                    console.error('Error marking notifications as read:', error);
                });
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdownMenu = document.querySelector('.dropdown-list');
            if (!e.target.closest('#alertsDropdown') && !e.target.closest('.dropdown-list') && 
                dropdownMenu && dropdownMenu.classList.contains('show')) {
                dropdownMenu.classList.remove('show');
            }
        });
        
        // Individual notification click handler
        const notificationItems = document.querySelectorAll('.dropdown-item[data-notification-id]');
        notificationItems.forEach(item => {
            item.addEventListener('click', function(e) {
                const notificationId = this.getAttribute('data-notification-id');
                
                // Mark this specific notification as read
                fetch(`../mark_notification_read.php?id=${notificationId}`, {
                    method: 'GET'
                }).catch(error => {
                    console.error('Error marking notification as read:', error);
                });
                
                // If the notification has a valid link (not just "#"), don't prevent default
                if (this.getAttribute('href') === '#') {
                    e.preventDefault();
                }
            });
        });
    }
    
    // Function to update notification count
    function updateNotificationCount() {
        fetch('../get_pending_count.php')
            .then(response => response.json())
            .then(data => {
                const badge = document.querySelector('.badge-counter');
                if (badge) {
                    badge.textContent = data.notifications > 0 ? data.notifications : '';
                }
            })
            .catch(error => {
                console.error('Error updating notification count:', error);
            });
    }
    
    // Update notification count periodically
    setInterval(updateNotificationCount, 60000); // Update every minute
});
