<?php
include 'db_final.php';
require_once 'helpers/db_helpers.php';

session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // ✅ assign before checking

if (!is_numeric($user_id)) {
    die("Invalid user ID.");
}

// Fetch user's full name
$query = "SELECT full_name FROM users WHERE id = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Error preparing the statement: {$conn->error}");
}

$stmt->bind_param("i", $user_id);

if (!$stmt->execute()) {
    die("Error executing the query: {$stmt->error}");
}

$stmt->bind_result($full_name);
$stmt->fetch();
$stmt->close();

// If user not found, destroy session
if (empty($full_name)) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Fetch saved articles for the current user
$query = "SELECT a.*, u.full_name, u.profile_picture, 
         (SELECT COUNT(*) FROM reactions WHERE article_id = a.id) as likes,
         (SELECT COUNT(*) FROM comments WHERE article_id = a.id) as comments
         FROM hidden_articles ha
         JOIN articles a ON ha.article_id = a.id
         JOIN users u ON a.user_id = u.id
         WHERE ha.user_id = ?
         ORDER BY ha.hidden_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$articles = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>DBCLM College</title>
    <link rel="stylesheet" href="global.css">
    <link rel="stylesheet" href="save_articles.css">
</head>

<body>

    <div class="layout">

        <header class="header">
            <?php require_once 'components/header.php'; ?>
        </header>

        <aside class="sidebar">
            <div>
                <ul>
                    <li>
                        <img src="finaluser.png" alt="Profile">
                        <span><a href="profile.php" style="text-decoration: none; color:black">My Profile</a></span>
                    </li>
                    <li>
                        <img src="finalsave.png" alt="Saved">
                        <span><a href="save_articles.php" style="text-decoration: none; color:black">Saved Articles</a></span>
                    </li>
                    <li class="active">
                    <img src="secret-file.png" alt="Saved">
                    <span><a href="hide.php" style="text-decoration: none; color:black">Hide Articles</a></span>
                    </li>
                </ul>
            </div>
        </aside>

        <main class="main-content">
            <div class="articles-container">
                <h1>Hide Articles</h1>
                <div class="post-feed" id="postFeed">
                    <?php if (!empty($articles)): ?>
                        <?php foreach ($articles as $row): ?>
                            <?php $articleId = $row['id']; ?>
                            <div class="post-card" data-article-id="<?= $articleId ?>">
                                <?php if (isset($_GET['status'])): ?>
                                    <?php if ($_GET['status'] == 'saved'): ?>
                                        <div class="alert success">Article saved successfully!</div>
                                    <?php elseif ($_GET['status'] == 'already_saved'): ?>
                                        <div class="alert warning">You have already saved this article.</div>
                                    <?php elseif ($_GET['status'] == 'error'): ?>
                                        <div class="alert error">An error occurred while saving the article. Please try again.</div>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <div class="post-card-header">
                                    <div class="post-header" >
                                    <div class="post-actions">
    <?php if (!in_array($articleId, $hidden_article_ids)): ?>
        <!-- Hide Button -->
        <form method="POST" action="hide_article.php">
            <input type="hidden" name="article_id" value="<?= $articleId ?>">
            <button type="submit" class="hide-article-btn">Hide</button>
        </form>
    <?php else: ?>
        <!-- Unhide Button -->
        <form method="POST" action="unhide_article.php">
            <input type="hidden" name="article_id" value="<?= $articleId ?>">
            <button type="submit" class="unhide-article-btn">Unhide</button>
        </form>
    <?php endif; ?>
</div>

                                        <img class="avatar"
                                            src="uploads/profile_pictures/<?= htmlspecialchars($row['profile_picture']) ?>"
                                            alt="User">
                                        <div class="post-header-info" style="margin-left:50px; margin-top:-45px;">
                                            <strong><?= htmlspecialchars($row['full_name']) ?></strong><br>
                                            <span
                                                class="post-date"><?= date("F j, Y | g:i A", strtotime($row['created_at'])) ?></span>
                                        </div>
                                    </div>

                                </div>

                                <div class="post-title" style="margin-top: 30px;"><strong><?= htmlspecialchars($row['title']) ?></strong></div>
                                <div class="post-content"><?= $row['abstract'] ?></div>

                                <?php if (!empty($row['featured_image'])): ?>
                                    <div class="post-image">
                                        <img src="<?= htmlspecialchars($row['featured_image']) ?>" alt="Article Image"
                                            class="responsive-img">
                                    </div>
                                <?php endif; ?>


                                
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No hide articles available yet.</p>
                    <?php endif; ?>
                </div>

            </div>

        </main>
        <footer class="footer">
            <?php require_once 'components/footer.php'; ?>
        </footer>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const bell = document.getElementById('notif-bell');
                const dropdown = document.getElementById('notif-dropdown');

                bell.addEventListener('click', function () {
                    dropdown.classList.toggle('hidden');

                    // If visible, mark all notifications as read via AJAX
                    if (!dropdown.classList.contains('hidden')) {
                        fetch('mark_notifications_read.php', {
                            method: 'POST'
                        })
                            .then(response => response.text())
                            .then(() => {
                                const badge = document.querySelector('.notif-badge');
                                if (badge) {
                                    badge.style.display = 'none'; // Hide the unread notification count
                                }
                            });
                    }
                });

                // Optional: close when clicking outside
                document.addEventListener('click', function (e) {
                    if (!dropdown.contains(e.target) && e.target !== bell) {
                        dropdown.classList.add('hidden');
                    }
                });
            });
        </script>
        <script>
            document.getElementById('notif-bell').addEventListener('click', function () {
                const dropdown = document.getElementById('notif-dropdown');
                const badge = document.querySelector('.notif-badge');

                // Toggle the dropdown
                dropdown.style.display = (dropdown.style.display === 'none' || dropdown.style.display === '') ? 'block' : 'none';

                // Hide the badge (but keep the notifications visible)
                if (dropdown.style.display === 'block' && badge) {
                    badge.style.display = 'none';

                    // Optionally tell the server the notifications were "seen"
                    fetch('clear_notifications.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'action=clear'
                    });
                }
            });

            // Hide dropdown when clicking outside
            window.addEventListener('click', function (e) {
                if (!document.querySelector('.notification-wrapper').contains(e.target)) {
                    document.getElementById('notif-dropdown').style.display = 'none';
                }
            });
        </script>
        <script>
            const articleInput = document.getElementById('articleInput');
            articleInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    articleInput.closest('form').submit();
                }
            });
        </script>
        <script>
            var modal = document.getElementById("editProfileModal");
            var btn = document.querySelector(".edit-button");
            var span = document.getElementsByClassName("close")[0];

            btn.onclick = function () {
                modal.style.display = "block";
            }

            span.onclick = function () {
                modal.style.display = "none";
            }

            window.onclick = function (event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
        </script>
        <script>
            function togglePassword() {
                const passwordInput = document.getElementById('password');
                if (passwordInput.type === "password") {
                    passwordInput.type = "text";
                } else {
                    passwordInput.type = "password";
                }
            }
        </script>


</body>

</html>