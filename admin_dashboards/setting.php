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
<script src="https://cdn.tailwindcss.com/3.4.16"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: "#4f46e5",
                    secondary: "#6366f1"
                },
                borderRadius: {
                    none: "0px",
                    sm: "4px",
                    DEFAULT: "8px",
                    md: "12px",
                    lg: "16px",
                    xl: "20px",
                    "2xl": "24px",
                    "3xl": "32px",
                    full: "9999px",
                    button: "8px",
                },
            },
        },
    };
</script>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link
    href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap"
    rel="stylesheet" />
<link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
    rel="stylesheet" />
<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" />
<style>
    :where([class^="ri-"])::before {
        content: "\f3c2";
    }

    body {
        font-family: 'Inter', sans-serif;
    }

    .toggle-checkbox:checked {
        right: 0;
        border-color: #4f46e5;
    }

    .toggle-checkbox:checked+.toggle-label {
        background-color: #4f46e5;
    }

    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
</style>

<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column" style="display: flex;">

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
            <!-- Main content area -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-6">


                <!-- Settings content -->
                <div class="grid grid-cols-1 gap-6">
                    <!-- Account Settings -->
                    <div class="bg-white rounded shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b">
                            <h2 class="text-lg font-medium text-gray-800">
                                Account Settings
                            </h2>
                        </div>
                        <div class="p-6 space-y-6">
                            <!-- Profile Information -->
                            <div class="space-y-4">
                                <h3 class="text-md font-medium text-gray-700">
                                    Profile Information
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label
                                            for="name"
                                            class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                        <input
                                            type="text"
                                            id="name"
                                            value="James Wilson"
                                            class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none" />
                                    </div>
                                    <div>
                                        <label
                                            for="email"
                                            class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                        <input
                                            type="email"
                                            id="email"
                                            value="james.wilson@example.com"
                                            class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none" />
                                    </div>
                                    <div>
                                        <label
                                            for="username"
                                            class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                                        <input
                                            type="text"
                                            id="username"
                                            value="jwilson"
                                            class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none" />
                                    </div>
                                    <div>
                                        <label
                                            for="bio"
                                            class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
                                        <textarea
                                            id="bio"
                                            rows="3"
                                            class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none">
Tech writer and digital marketing specialist with over 8 years of experience creating content for SaaS companies.</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Email Preferences -->
                            <div class="space-y-4 pt-4 border-t">
                                <h3 class="text-md font-medium text-gray-700">
                                    Email Preferences
                                </h3>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-800">
                                                Weekly Digest
                                            </h4>
                                            <p class="text-xs text-gray-500">
                                                Receive a weekly summary of your publishing activity
                                            </p>
                                        </div>
                                        <div
                                            class="relative inline-block w-10 mr-2 align-middle select-none">
                                            <input
                                                type="checkbox"
                                                id="weekly-digest"
                                                checked
                                                class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer right-0" />
                                            <label
                                                for="weekly-digest"
                                                class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-800">
                                                Article Performance
                                            </h4>
                                            <p class="text-xs text-gray-500">
                                                Get notified about significant changes in article
                                                performance
                                            </p>
                                        </div>
                                        <div
                                            class="relative inline-block w-10 mr-2 align-middle select-none">
                                            <input
                                                type="checkbox"
                                                id="article-performance"
                                                checked
                                                class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer" />
                                            <label
                                                for="article-performance"
                                                class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-800">
                                                Product Updates
                                            </h4>
                                            <p class="text-xs text-gray-500">
                                                Stay informed about new features and improvements
                                            </p>
                                        </div>
                                        <div
                                            class="relative inline-block w-10 mr-2 align-middle select-none">
                                            <input
                                                type="checkbox"
                                                id="product-updates"
                                                class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer" />
                                            <label
                                                for="product-updates"
                                                class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Password Change -->
                            <div class="space-y-4 pt-4 border-t">
                                <h3 class="text-md font-medium text-gray-700">Password</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label
                                            for="current-password"
                                            class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                        <input
                                            type="password"
                                            id="current-password"
                                            class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none" />
                                    </div>
                                    <div></div>
                                    <div>
                                        <label
                                            for="new-password"
                                            class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                        <input
                                            type="password"
                                            id="new-password"
                                            class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none" />
                                    </div>
                                    <div>
                                        <label
                                            for="confirm-password"
                                            class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                                        <input
                                            type="password"
                                            id="confirm-password"
                                            class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary focus:outline-none" />
                                    </div>
                                </div>
                                <div class="pt-2">
                                    <button
                                        class="px-4 py-2 bg-primary text-white rounded-button hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 whitespace-nowrap">
                                        Update Password
                                    </button>
                                </div>
                            </div>

                            
    </div>







</div>
<!-- /.container-fluid -->

</div>


</div>