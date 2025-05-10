<?php
require 'db.php'; // Include your database connection file

// Fetch the latest articles
$query = "SELECT id, title, abstract, featured_image FROM articles WHERE status = 'approved' ORDER BY created_at DESC LIMIT 6";
$result = $mysqli->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>DBCLM College</title>
  <link rel="stylesheet" href="landing.css">
</head>
<body>

  <link rel="stylesheet" href="header.css">

<header class="navbar">
  <div class="logo">
    <img src="FinalLogo.jpg" alt="DBCLM Logo">
  </div>
  <nav class="nav-links">
    <a href="landingpage.php">Home</a>
    <!-- <a href="#">Latest</a> -->
    <a href="aboutus.php">About</a>
    <a href="contactus.php">Contact</a>
  </nav>
  <div class="navbar-right">
    <div class="notification-wrapper">
      <img src="bell.jpg" alt="Notifications" class="icon-bell" id="notif-bell">
        
      </div>
      <div class="notification-user">
      <a href="login.php">Login</a>
        
      </div>
    </div>
    

    
  </div>
</header>

<!-- Add the header-specific JavaScript file -->
<script src="header.js"></script>
  <section class="banner">
    <img src="headers.png" alt="Library Banner">
    <button class="publish-btn" >
      <a href="login.php" style="text-decoration:none; color:black;">PUBLISH ARTICLE</a>
    </button>
  </section>

  <section class="latest-news">
    <p class="section-subtitle">THE LATEST</p>
    <h2>Featured News</h2>


<div class="grid-container">
  <?php if ($result->num_rows > 0): ?>
    <?php while ($article = $result->fetch_assoc()): ?>
      <div class="grid-item">
        <div class="article-card">
          <?php if (!empty($article['featured_image'])): ?>
            <img src="<?= htmlspecialchars($article['featured_image']); ?>" alt="Article Image" class="article-image">
          <?php endif; ?>
          <h3 class="article-title"><?= htmlspecialchars($article['title']); ?></h3>
          <p class="article-abstract"><?= htmlspecialchars($article['abstract']); ?></p>
          <a href="view_article.php?article_id=<?= $article['id']; ?>" class="read-more">Read More →</a>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>No articles available at the moment.</p>
  <?php endif; ?>
</div>


    <button class="view-all"><a href="login.php"style="text-decoration:none; color:black;">View All Articles →</a></button>
  </section>

  <section class="mission-section">
  <div class="mission-container">
    <div class="mission-image">
      <img src="mv.jpg" alt="Our Mission">
    </div>
    <div class="mission-text">
      <p class="mission-subtitle">ABOUT US</p>
      <h2 class="mission-title">Our Mission & Vision</h2>
      <p class="mission-description">
        Our mission is to provide a platform where students can express their ideas, enhance their writing skills, and share meaningful stories within the school community. We aim to foster creativity, critical thinking, and collaboration through student-led publishing. Our vision is to become a trusted and inspiring source of student-driven content that encourages communication, showcases talent, and strengthens the voice of the youth in our school.
      </p>
    </div>
  </div>
</section>

<section class="developers-section">
  <div class="developers-header">
    <p>From Vision to Reality</p>
    <h2>The Developers</h2>
  </div>

  <div class="developer-slider">
    

    <div class="developer-cards"><img src="marj.jpg" class="dev-card">
      <div class="dev-card"><img src="lai.jpg" class="dev-card"></div>
      <div class="dev-card"><img src="dona.jpg" class="dev-card"></div>
      <div class="dev-card"><img src="barb.jpg" class="dev-card"></div>
      <div class="dev-card"><img src="cheni.jpg" class="dev-card"></div>
    </div>

    
  </div>
</section>

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

</body>
</html>