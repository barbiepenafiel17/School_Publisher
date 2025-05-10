<?php
include('includes/header.php');
include('includes/navbar.php');
include('includes/db.php');
include('includes/scripts.php');
$user_stats_query = "
    SELECT 
        COUNT(*) AS total, 
        SUM(role = 'student') AS student,
        SUM(role = 'teacher') AS teacher
    FROM users
";

$user_stats_result = $mysqli->query($user_stats_query);

if ($user_stats_result && $user_stats_result->num_rows > 0) {
    $user_stats = $user_stats_result->fetch_assoc();
} else {
    // Fallback values in case of error or empty result
    $user_stats = [
        'total' => 0,
        'student' => 0,
        'teacher' => 0
    ];
}

// === Fetch latest 5 articles with author info ===
// Assuming $userId is the user's ID
$query = "UPDATE users SET last_activity = NOW() WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->close();
// This query updates the status based on the latest article submission
$update_status_query = "
    UPDATE users u
    LEFT JOIN (
        SELECT user_id, MAX(created_at) AS last_article
        FROM articles
        GROUP BY user_id
    ) a ON u.id = a.user_id
    SET u.status = 
        CASE
            WHEN a.last_article IS NOT NULL AND a.last_article >= NOW() - INTERVAL 7 DAY THEN 'active'
            WHEN a.last_article IS NOT NULL THEN 'inactive'
            ELSE 'pending'
        END;
";

$mysqli->query($update_status_query);


$users_query = "
    SELECT 
        users.id,
        users.full_name AS author_name, 
        users.email AS author_email, 
        users.created_at,
        users.role AS author_role,
        users.status,
        users.last_activity,
        MAX(articles.created_at) AS last_article_date
    FROM 
        users 
    LEFT JOIN 
        articles ON users.id = articles.user_id
    GROUP BY 
        users.id
    ORDER BY 
        users.created_at DESC
    LIMIT 5
";
$users_result = $mysqli->query($users_query);

if ($users_result && $users_result->num_rows > 0) {
    $rows = $users_result->fetch_all(MYSQLI_ASSOC);
} else {
    $rows = []; // fallback to empty array to avoid undefined variable
}



?>

<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">

    <!-- Main Content -->
    <div id="content">

        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

            <!-- Sidebar Toggle (Topbar) -->
            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                <i class="fa fa-bars"></i>
            </button>
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
        <!-- End of Topbar -->

        <!-- Begin Page Content -->
        <div class="container-fluid">

            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Article Management Dashboard</h1>

            </div>

            <!-- Content Row -->
            <div class="row">

                <!-- Total Users -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Users</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <h2><?= $user_stats['total'] ?? 0; ?></h2>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fa fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Students -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Students</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <h2><?= $user_stats['student'] ?? 0; ?></h2>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Teachers -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Teachers
                                    </div>
                                    <div class="row no-gutters align-items-center">
                                        <div class="col-auto">
                                            <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                                <h2><?= $user_stats['teacher'] ?? 0; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-id-card fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- New Users -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        New Users (Last 7 Days)</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <h2><?= $user_stats['new_users'] ?? 0; ?></h2>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fa fa-user-plus fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <table>
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
                                <td><span class="role <?= strtolower($row['author_role']) ?>">
                                        <?= strtoupper($row['author_role']) ?>
                                    </span></td>
                                <td><?= date('M j, Y', strtotime($row['created_at'])) ?></td>
                                <td>
                                    <span class="status <?= strtolower($row['status']) ?>">
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>

                                <!-- View Button -->
                                <td>
                                    <form action="view_user.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="user_id" value="<?= $row['id']; ?>">
                                        <button type="submit" class="view-btn btn btn-primary btn-sm">View</button>
                                        </form>
                            </tr>
                        <?php endforeach; ?></td>



                    </tbody>
                </table>
            </div>

            <!-- Content Row -->


        </div>
        <!-- /.container-fluid -->

    </div>
    <!-- End of Main Content -->

</div>
<!-- End of Page Wrapper -->