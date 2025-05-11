<?php
/**
 * Include this file in admin pages to setup notifications
 */

// Include notification functions
require_once 'helpers/notification_functions.php';

// JavaScript for handling notifications
echo '<script src="admin-dashboard/admin-components/js/notification.js"></script>';

// CSS for notification styling
echo '<link rel="stylesheet" href="admin-dashboard/admin-components/css/notification-styles.css">';
?>