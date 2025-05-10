<?php
include('includes/header.php');
include('includes/navbar.php');
include('includes/db.php');
include('includes/scripts.php');


?>
<div id="content-wrapper" class="d-flex flex-column">

    <!-- Main Content -->
    <div id="content">

        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

            <!-- Sidebar Toggle (Topbar) -->
            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                <i class="fa fa-bars"></i>
            </button>

            <!-- Topbar Navbar -->
            <ul class="navbar-nav ml-auto">

                <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                <li class="nav-item dropdown no-arrow d-sm-none">
                    <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-search fa-fw"></i>
                    </a>
                    <!-- Dropdown - Messages -->
                    <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                        aria-labelledby="searchDropdown">
                        <form class="form-inline mr-auto w-100 navbar-search">
                            <div class="input-group">
                                <input type="text" class="form-control bg-light border-0 small"
                                    placeholder="Search for..." aria-label="Search"
                                    aria-describedby="basic-addon2">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button">
                                        <i class="fas fa-search fa-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </li>

                <!-- Nav Item - Alerts -->
                <li class="nav-item dropdown no-arrow mx-1">
                    <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-bell fa-fw"></i>
                        <!-- Counter - Alerts -->
                        <span class="badge badge-danger badge-counter">
                            <?php
                            // Fetch unread notifications count
                            $unread_query = "SELECT COUNT(*) AS unread_count FROM admin_notifications WHERE is_read = 0";
                            $unread_result = $mysqli->query($unread_query);
                            $unread_count = $unread_result->fetch_assoc()['unread_count'] ?? 0;
                            echo $unread_count > 0 ? $unread_count : '';
                            ?>
                        </span>
                    </a>
                    <!-- Dropdown - Alerts -->
                    <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                        aria-labelledby="alertsDropdown">
                        <h6 class="dropdown-header">
                            Alerts Center
                        </h6>
                        <?php
                        // Fetch the latest notifications
                        $notifications_query = "SELECT * FROM admin_notifications ORDER BY created_at DESC LIMIT 5";
                        $notifications_result = $mysqli->query($notifications_query);

                        if ($notifications_result->num_rows > 0):
                            while ($notification = $notifications_result->fetch_assoc()):
                        ?>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-primary">
                                            <i class="fas fa-file-alt text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500"><?= date('F j, Y', strtotime($notification['created_at'])) ?></div>
                                        <span class="font-weight-bold"><?= htmlspecialchars($notification['message']) ?></span>
                                    </div>
                                </a>
                            <?php
                            endwhile;
                        else:
                            ?>
                            <a class="dropdown-item text-center small text-gray-500" href="#">No new notifications</a>
                        <?php endif; ?>
                        <a class="dropdown-item text-center small text-gray-500" href="all_notifications.php">Show All Alerts</a>
                    </div>
                </li>

                <div class="topbar-divider d-none d-sm-block"></div>

                <!-- Nav Item - User Information -->
                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="mr-2 d-none d-lg-inline text-gray-600 small">Administrator</span>
                        <img class="img-profile rounded-circle"
                            src="img/undraw_profile.svg">
                    </a>
                    <!-- Dropdown - User Information -->
                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                        aria-labelledby="userDropdown">
                        <div class="dropdown-divider"></div>
                         <a class="dropdown-item" href="/../logout.php">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                            Logout
                        </a>
                    </div>
                </li>

            </ul>

        </nav>
          <div class="container-fluid" style="width: 100%;">
    

    <div class="container" style="width: 100%; margin:20px">
      <div class="announcement-box" >
        <h2>Create New Announcement</h2>

        <?php
// Connect to your database
$pdo = new PDO("mysql:host=localhost;dbname=dbclm_college", "root", ""); // Change credentials as needed
?>

<form action="save_announcement.php" method="POST" enctype="multipart/form-data">
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

    <div class="actions">
      <button type="submit" name="action" value="cancel">Cancel</button>
      <button type="submit" name="action" value="publish">Publish</button>
    </div>
  </div>
</form>
      </div>
    </div>
  </div>
    </div>
</div>
