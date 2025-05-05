<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php'; // PDO connection ($pdo)

$user_id = $_SESSION['user_id'];
if (!is_numeric($user_id)) {
    die("Invalid user ID.");
}

// Fetch user info
$stmt = $pdo->prepare("SELECT full_name, profile_picture FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$full_name = htmlspecialchars($user['full_name']);
$profile_picture = !empty($user['profile_picture']) ? 'uploads/profile_pictures/' . htmlspecialchars($user['profile_picture']) : 'uploads/profile_pictures/default_profile.png';

// Fetch latest unread notification
$notifStmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = :user_id AND is_read = 0 ORDER BY created_at DESC LIMIT 1");
$notifStmt->execute(['user_id' => $user_id]);
$unreadNotifications = $notifStmt->fetchAll(PDO::FETCH_ASSOC);
$notifCount = count($unreadNotifications);

// Fetch articles
$articleStmt = $pdo->query("
    SELECT a.*, u.full_name, u.profile_picture,
        (SELECT COUNT(*) FROM reactions WHERE article_id = a.id AND reaction_type = 'like') AS likes,
        (SELECT COUNT(*) FROM comments WHERE article_id = a.id) AS comments
    FROM articles a
    JOIN users u ON a.user_id = u.id
    WHERE a.status = 'approved'
    ORDER BY a.created_at DESC
");
$articles = $articleStmt->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>DBCLM College</title>
  <link rel="stylesheet" href="contactus.css">
</head>
<body>

<header class="header">
            <?php require_once 'components/header.php'; ?>
        </header>

  <div class="contact-section">
    <a href="contactus.php" class="contact-button">Contact Us</a>
    <h1 class="contact-title">Get in Touch</h1>
    <p class="contact-subtext">Have questions or need more information? We’re here to help.</p>
    <p class="contact-subtext">Reach out to our team for assistance.</p>
  </div>

  <div class="contact-info-section">
    <h2>Contact Information</h2>
    <p class="description">
      Feel free to reach us through any of our following channels.<br>
      Our team is ready to assist you with any questions or inquiries you may have.
    </p>

    <div class="contact-item">
      <img src="gps.png" alt="Address Icon" class="icon">
      <div>
        <h3>Address</h3>
        <p>LAB No 20A, 1234 Academic Lane<br>
           Panabo City, MA 02145<br>
           Davao del Norte, Philippines</p>
      </div>
    </div>

    <div class="contact-item">
      <img src="phone-call.png" alt="Phone Icon" class="icon">
      <div>
        <h3>Phone</h3>
        <p>+63123456789</p>
      </div>
    </div>

    <div class="contact-item">
      <img src="email.png" alt="Email Icon" class="icon">
      <div>
        <h3>Email</h3>
        <p>speechforge@gmail.com</p>
      </div>
    </div>

    <div class="contact-item">
      <img src="clock.png" alt="Hours Icon" class="icon">
      <div>
        <h3>Hours</h3>
        <p>Open 9:30 AM every weekdays</p>
      </div>
    </div>
  </div>


  <footer class="site-footer">
  <div class="footer-container">
    <div class="footer-about">
      <p>Keeping the community<br>informed and connected.</p>
    </div>
    <div class="footer-links">
      <h4>Quick Links</h4>
      <ul>
        <li>Home</li>
        <li>Latest</li>
        <li>About</li>
        <li>Contact us</li>
      </ul>
    </div>
    <div class="footer-links">
      <h4>Categories</h4>
      <ul>
        <li>Academics</li>
        <li>Sports</li>
        <li>Arts and Culture</li>
        <li>Faculty Spotlight</li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <p>© 2025 Speechforge. All rights reserved.</p>
  </div>
</footer>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// Toggle publish form
const publishBtn = document.querySelector('.publish-btn');
const dropdownForm = document.getElementById('articleFormDropdown');
const selectImageButton = document.getElementById('selectImageButton');
const featuredImageInput = document.getElementById('featuredImageInput');

publishBtn.addEventListener('click', () => {
    dropdownForm.classList.toggle('hidden');
});

selectImageButton.addEventListener('click', () => {
    featuredImageInput.click();
});

featuredImageInput.addEventListener('change', (event) => {
    const fileName = event.target.files[0] ? event.target.files[0].name : 'No file chosen';
    selectImageButton.textContent = `Selected: ${fileName}`;
});

// Likes and comments handling
$(document).ready(function() {
  $('.like-btn').click(function() {
    const articleId = $(this).data('article-id');
    const likeBtn = $(this);

    $.post('like_article.php', { article_id: articleId }, function(response) {
        if (response.likes !== undefined) {
            $('#like-count-' + articleId).text(response.likes);
        }
    }, 'json');
});

$('.comment-box').keypress(function(e) {
    if (e.which === 13) {
        const articleId = $(this).data('article-id');
        const commentText = $(this).val();
        const commentList = $('#comments-' + articleId);

        if (commentText.trim() !== '') {
            $.post('submit_comment.php', {
                article_id: articleId,
                comment: commentText
            }, function(response) {
                if (response.comment_html) {
                    commentList.append('<div class="comment">' + response.comment_html + '</div>');

                    // Increment comment count in UI
                    let countEl = $('#comment-count-' + articleId);
                    countEl.text(parseInt(countEl.text()) + 1);
                }
            }, 'json');
            $(this).val('');
        }
    }
});
});

</script>
<script>document.addEventListener('DOMContentLoaded', function () {
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
    window.addEventListener('click', function(e) {
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
    const dropdowns = document.querySelectorAll(".dropdown");
    dropdowns.forEach(dropdown => {
        const button = dropdown.querySelector(".dot-btn");
        const content = dropdown.querySelector(".dropdown-content");

        button.addEventListener("click", function(e) {
            e.stopPropagation();
            document.querySelectorAll(".dropdown-content").forEach(c => {
                if (c !== content) c.style.display = "none";
            });
            content.style.display = content.style.display === "block" ? "none" : "block";
        });

        document.addEventListener("click", function() {
            content.style.display = "none";
        });
    });
</script>
  </body>
</html>