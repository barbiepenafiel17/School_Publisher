<?php
include('includes/header.php');
include('includes/navbar.php');
include('includes/db.php');
include('includes/scripts.php');


$stats_query = "
    SELECT 
        COUNT(*) AS total, 
        SUM(status IN ('PENDING', 'SUBMITTED')) AS pending,
        SUM(status = 'APPROVED') AS approved,
        SUM(status = 'REJECTED') AS rejected
    FROM articles";
$stats_result = $mysqli->query($stats_query);
$stats = $stats_result->fetch_assoc();

$articles_query = "
    SELECT 
        articles.*, 
        users.full_name AS author_name, 
        users.email AS author_email, 
        users.institute AS author_institute 
    FROM articles 
    JOIN users ON articles.user_id = users.id 
    ORDER BY articles.created_at DESC 
    LIMIT 5";
$articles_result = $mysqli->query($articles_query);
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
        <!-- End of Topbar -->

        <!-- Begin Page Content -->
        <div class="container-fluid">

            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Article Management Dashboard</h1>

            </div>

            <!-- Content Row -->
            <div class="row">

                <!-- Total Submission -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Submission</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <h2><?= $stats['total'] ?></h2>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Reviews -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Pending Reviews</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <h2 id="pendingCount"><?= $stats['pending'] ?></h2>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Approve Articles -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Approved Articles
                                    </div>
                                    <div class="row no-gutters align-items-center">
                                        <div class="col-auto">
                                            <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                                <h2><?= $stats['approved'] ?></h2>
                                            </div>
                                        </div>
                                        <div class="col">

                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fa fa-check-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reject Article -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Rejected Articles</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <h2><?= $stats['rejected'] ?></h2>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fa fa-times-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <table style="width: 100%;
                background: white;
                border-collapse: collapse;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th>Author</th>
                            <th>Institutes</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $articles_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td>
                                    <?= htmlspecialchars($row['author_name']) ?><br>
                                    <a href="mailto:<?= htmlspecialchars($row['author_email']) ?>"><?= htmlspecialchars($row['author_email']) ?></a>
                                </td>
                                <td><?= htmlspecialchars($row['author_institute']) ?></td>
                                <td><?= date('M j, Y', strtotime($row['created_at'])) ?></td>
                                <td>
                                    <span class="status <?= strtolower($row['status']) ?>"><?= strtoupper($row['status']) ?></span>
                                </td>
                                <td class="actions">
                                    <form action="approve_article.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="article_id" value="<?= $row['id']; ?>">
                                        <button type="submit" class="accept-btn">Approve</button>
                                    </form>
                                    <form action="reject_article.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="article_id" value="<?= $row['id']; ?>">
                                        <button type="button" onclick="openModal(<?= $row['id']; ?>)" class="reject-btn">Reject</button>
                                    </form>


                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <!-- Modal for Rejecting Article -->
            <div id="rejectModal" class="modal" style="display:none;">
                <div class="modal-content">
                    <span onclick="closeModal()" class="close" style="margin-left:1120px">&times;</span>
                    <h2>Reject Article</h2>
                    <form action="reject_article.php" method="POST">
                        <input type="hidden" id="articleId" name="article_id">
                        <label for="reason">Reason for Rejection:</label>
                        <textarea id="reason" name="reason" rows="4" required></textarea>
                        <button type="submit" class="reject-btn">Reject Article</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
