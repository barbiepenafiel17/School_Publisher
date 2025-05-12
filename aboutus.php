<?php
// Refactored to improve maintainability and modularity
require_once 'helpers/db_helpers.php';
require_once 'db_connect.php';
require_once 'filter_feed.php';

session_start();

// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
if (!is_numeric($user_id)) {
    die("Invalid user ID.");
}

// Fetch user info
$user = getUserInfo($pdo, $user_id);
if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$full_name = htmlspecialchars($user['full_name']);
$profile_picture = !empty($user['profile_picture']) ? 'uploads/profile_pictures/' . htmlspecialchars($user['profile_picture']) : 'uploads/profile_pictures/default_profile.png';

// Fetch unread notifications
$unreadNotifications = getUnreadNotifications($pdo, $user_id);
$notifCount = count($unreadNotifications);

// Fetch articles and announcements
$institutes = ['All'];
$sortOption = $_POST['sort'] ?? 'new'; // Get sort option from POST request or default to 'new'
$articles = getFilteredArticles($pdo, $institutes, $sortOption);
$latest_announcements = getLatestAnnouncements($pdo);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>DBCLM College</title>
    <link rel="stylesheet" href="aboutus.css">
</head>

<body>
    <header class="header">
        <?php require_once 'components/header.php'; ?>
    </header>



    <div class="container">
        <h1>From thought to page—your platform <br> for meaningful <br> publishing. <br>—</h1>
        <p class="subheading">
            To curate and share thoughtful, high-quality articles <br> that inform, inspire, and engage readers across
            <br> diverse topics and perspectives.
        </p>

        <div class="cards-1">
            <div class="card-pic">
                <img src="laffy.png" alt="Laptop and coffee">
            </div>
            <div class="card1-pic">
                <img src="OAT.png" alt="Group of people collaborating">
                <!-- <div class="code-icon">&lt;&gt;</div> -->
                <button class="code-icon">&lt;&gt;</button>
                <div class="text-overlay">
                    <h3>What we believe in</h3>
                    <p>We believe in empowering creators to share <br> ideas that matter. Our tools are built for <br>
                        clarity, reliability, and purposeful publishing.</p>
                </div>
            </div>
            <div class="card2-pic">
                <img src="eavav.png" alt="Person coding">
            </div>
        </div>
    </div>

    <section class="foundation">
        <h1>The Foundation of our Success</h1>
        <p class="subtitle">Clarity and Consistency</p>
        <div class="features">
            <div class="feature-card">
                <img src="set.png" alt="Real-Time Updates">
                <h3>Real-Time System<br>Updates</h3>
            </div>
            <div class="feature-card">
                <img src="set.png" alt="Seamless Collaboration">
                <h3>Seamless<br>Collaboration</h3>
            </div>
            <div class="feature-card">
                <img src="paper.png" alt="Article Workflow">
                <h3>Article<br>Workflow</h3>
            </div>
        </div>
    </section>

    <section class="team-section">
        <h1>Meet our team of creators,<br>designers and problem solvers</h1>
        <p class="subtitle">Get to know the people who lead</p>

        <div class="team-carousel">


            <div class="team-member">
                <img src="lai.jpg">
                <p>Laiza Pueblo</p>
            </div>
            <div class="team-member">
                <img src="barb.jpg">
                <p>Barbie Peñafiel</p>
            </div>
            <div class="team-member">
                <img src="dona.jpg">
                <p>Donna Meg Eran</p>
            </div>
            <div class="team-member">
                <img src="cheni.jpg">
                <p> Chenybabes Dalogdug </p>
            </div>
            <div class="team-member">
                <img src="marj.jpg">
                <p>Marjorie Casilao</p>
            </div>


        </div>

        <div class="join-section">
            <div class="left">
                <h2>Join our team, the one<br>with the master touch</h2>
            </div>
            <div class="right">
                <p class="description">We believe in inspiring talent to reach their fullest potential and creating
                    opportunities that drive growth and success.</p>

            </div>
        </div>
    </section>


    <footer class="footer">
        <?php require_once 'components/footer.php'; ?>
    </footer>

</body>

</html>