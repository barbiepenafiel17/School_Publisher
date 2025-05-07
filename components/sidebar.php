<?php
/**
 * Sidebar Component
 * Displays the sidebar with navigation links.
 */
?>

<!-- Sidebar Navigation Card -->
<div class="card sidebar-nav ">
  <ul>
    <li>
      <img src="finaluser.png" alt="Profile">
      <span><a href="profile.php" style="color: black;text-decoration: none;">My Profile</a></span>
    </li>
    <li>
      <img src="finalsave.png" alt="Saved">
      <span><a href="save_articles.php" style="color: black;text-decoration: none;">Saved Articles</span>
    </li>
    
  </ul>

</div>

<!-- Institutes Sort Card -->
<div class="card sidebar-nav">
  <h2 class="card-title">Institutes</h2>
  <ul class="checkbox-list">
    <li><input type="checkbox" class="institute-filter" value="All" checked> All Institutes</li>
    <li><input type="checkbox" class="institute-filter" value="IC"> Institute of Computing</li>

    <li><input type="checkbox" class="institute-filter" value="ILEGG"> Institute of Leadership Entrepreneurship, and
      Good Governance</li>
    <li><input type="checkbox" class="institute-filter" value="ITed"> Institute of Teacher
      Education</li>
    <li><input type="checkbox" class="institute-filter" value="IAAS"> Institute of
      Aquatic and Applied Sciences</li>
  </ul>
</div>

<!-- Sort Card -->
<div class="card sidebar-nav">
  <h2 class="card-title">Sort</h2>
  <ul class="checkbox-list">
    <li><input type="radio" name="sort" class="sort-option" value="new" checked> New Upload</li>
    <li><input type="radio" name="sort" class="sort-option" value="old"> Oldest Upload</li>
    <li><input type="radio" name="sort" class="sort-option" value="popular"> Popular</li>
  </ul>
</div>

<!-- School Announcement Card -->
<div class="card sidebar-nav">
  <h2 class="card-title">School Announcement</h2>

  <?php foreach ($latest_announcements as $row): ?>
    <?php
    $title = strtolower($row['title']);
    $class = '';

    if (strpos($title, 'dismissal') !== false) {
      $class = 'early-dismissal';
    } elseif (strpos($title, 'yearbook') !== false) {
      $class = 'yearbook';
    } elseif (strpos($title, 'summer') !== false || strpos($title, 'enrichment') !== false) {
      $class = 'summer';
    }
    ?>
    <div class="announcement <?= $class ?>">
      <strong><?= htmlspecialchars($row['title']) ?></strong>
      <p><?= nl2br(htmlspecialchars($row['content'])) ?></p>
    </div>
  <?php endforeach; ?>
</div>