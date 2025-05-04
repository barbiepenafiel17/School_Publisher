/**
 * Newsfeed JavaScript
 * Handles interactive elements on the newsfeed page:
 * - Article publishing form
 * - Like functionality
 * - Comment submission
 * - Notifications
 */

// Utility function to toggle visibility of an element
function toggleVisibility(element) {
    element.classList.toggle('hidden');
}

// Utility function to send a POST request
async function postData(url, data) {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
    });
    return response.json();
}

// Event listener for toggling the publish form
const publishBtn = document.querySelector('.publish-btn');
const dropdownForm = document.getElementById('articleFormDropdown');
if (publishBtn && dropdownForm) {
    publishBtn.addEventListener('click', () => toggleVisibility(dropdownForm));
}

// Event listener for selecting an image
const selectImageButton = document.getElementById('selectImageButton');
const featuredImageInput = document.getElementById('featuredImageInput');
if (selectImageButton && featuredImageInput) {
    selectImageButton.addEventListener('click', () => featuredImageInput.click());
    featuredImageInput.addEventListener('change', (event) => {
        const fileName = event.target.files[0] ? event.target.files[0].name : 'No file chosen';
        selectImageButton.textContent = `Selected: ${fileName}`;
    });
}

// Toggle dropdown for article options
function toggleDropdown(button) {
    const dropdown = button.nextElementSibling;
    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
}

// Likes and comments handling
$(document).ready(function () {
    $('.like-btn').click(async function () {
        const articleId = $(this).data('article-id');
        const response = await postData('like_article.php', { article_id: articleId });
        if (response.likes !== undefined) {
            $(`#like-count-${articleId}`).text(response.likes);
        }
    });

    $('.comment-box').keypress(async function (e) {
        if (e.which === 13) {
            const articleId = $(this).data('article-id');
            const commentText = $(this).val();
            const response = await postData('submit_comment.php', { article_id: articleId, comment: commentText });
            if (response.success) {
                $(this).val(''); // Clear the input box
                // Optionally, reload comments
            }
        }
    });
});

// User menu toggle
function toggleUserMenu() {
    var menu = document.getElementById("user-menu");
    menu.style.display = (menu.style.display === "block") ? "none" : "block";
}

// Close dropdown if clicked outside
window.onclick = function (e) {
    if (!e.target.matches('.user-label')) {
        var menu = document.getElementById("user-menu");
        if (menu && menu.style.display === "block") {
            menu.style.display = "none";
        }
    }
}

// Notification handling
const bell = document.getElementById('notif-bell');
const dropdown = document.getElementById('notif-dropdown');

bell.addEventListener('click', function () {
    toggleVisibility(dropdown);

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
window.addEventListener('click', function (e) {
    if (!dropdown.contains(e.target) && e.target !== bell) {
        dropdown.classList.add('hidden');
    }
});

/**
 * Combined Filtering and Sorting System
 * This section handles both institute filtering and sorting together
 * to provide a coherent filtering experience
 */

// Store the current filter state
let currentFilters = {
    institutes: ['All'],
    sort: 'new'
};

// Function to apply both filters
function applyFilters() {
    fetch('filter_feed.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(currentFilters)
    })
        .then(response => response.json())
        .then(articles => {
            // Update the UI with filtered articles
            const postFeed = document.querySelector('#postFeed');
            postFeed.innerHTML = '';

            if (articles.length === 0) {
                postFeed.innerHTML = '<p>No articles found matching your criteria.</p>';
                return;
            }

            articles.forEach(article => {
                const articleHtml = renderArticle(article);
                postFeed.innerHTML += articleHtml;
            });

            // Re-initialize event handlers
            initArticleEvents();
        })
        .catch(error => {
            console.error("Error updating articles:", error);
        });
}

// Initialize event listeners when the DOM is loaded
document.addEventListener('DOMContentLoaded', function () {
    // Institute filter event listeners
    document.querySelectorAll('.institute-filter').forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            currentFilters.institutes = Array.from(document.querySelectorAll('.institute-filter:checked')).map(cb => cb.value);
            applyFilters();
        });
    });

    // Sort option event listeners
    document.querySelectorAll('.sort-option').forEach(radio => {
        radio.addEventListener('change', () => {
            currentFilters.sort = document.querySelector('.sort-option:checked').value;
            applyFilters();
        });
    });
});

/**
 * Helper function to render a single article
 * @param {Object} article - The article data object
 * @returns {string} HTML representation of the article
 */
function renderArticle(article) {
    const date = new Date(article.created_at);
    const formattedDate = date.toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
        hour12: true
    });

    // Build article HTML
    let html = `
    <div class="post-card" data-article-id="${article.id}">
      <div class="dropdown" style="display: inline-block; position: relative;">
        <div class="dropdown-content" style="display: none; position: absolute; top: 0; left: 100%; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.2); z-index: 1;">`;

    if (article.is_owner) {
        html += `
      <form method="POST" action="delete_article.php" onsubmit="return confirm('Delete this article?');">
        <input type="hidden" name="article_id" value="${article.id}">
        <button type="submit" style="color: red; background: none; border: none; padding: 10px; width: 100%; text-align: left;">Delete</button>
      </form>`;
    }

    html += `
      <form method="POST" action="hide_article.php">
        <input type="hidden" name="article_id" value="${article.id}">
        <button type="submit" style="background: none; border: none; padding: 10px; width: 100%; text-align: left;">Hide</button>
      </form>
      <form method="POST" action="report_article.php" onsubmit="return confirm('Report this article to admin?');">
        <input type="hidden" name="article_id" value="${article.id}">
        <button type="submit" style="background: none; border: none; padding: 10px; width: 100%; text-align: left;">Report</button>
      </form>
    </div>
  </div>

  <div class="post-card-header">
    <div class="post-header">
      <img class="avatar" src="uploads/profile_pictures/${escapeHtml(article.profile_picture)}" alt="User">
      <div>
        <strong>${escapeHtml(article.full_name)}</strong><br>
        <span class="post-date">${formattedDate}</span>
      </div>
    </div>
    <button class="dot-btn" onclick="toggleDropdown(this)">...</button>
  </div>

  <div class="post-title"><strong>${escapeHtml(article.title)}</strong></div>
  <div class="post-content">${escapeHtml(article.abstract)}</div>`;

    if (article.featured_image) {
        html += `
    <div class="post-image">
      <img src="${escapeHtml(article.featured_image)}" alt="Article Image" class="responsive-img">
    </div>`;
    }

    html += `
  <div class="post-actions">
    <button class="like-btn" data-article-id="${article.id}">
      👍 <span class="like-count" id="like-count-${article.id}">${article.likes}</span>
    </button>
    <button class="comment-btn">
      💬 <span class="comment-count" id="comment-count-${article.id}">${article.comments}</span>
    </button>
  </div>

  <div class="post-comments">
    <div class="comment-list" id="comments-${article.id}">
      <p class="no-comments">Loading comments...</p>
    </div>
    
    <form method="POST" action="post_comment.php" class="comment-form">
      <input type="hidden" name="article_id" value="${article.id}">
      <input class="comment-box" type="text" name="comment_text" placeholder="Write a comment..." required>
      <button class="post-form-button" type="submit">Post</button>
    </form>
  </div>
</div>`;

    return html;
}

/**
 * Helper function to escape HTML to prevent XSS attacks
 * @param {string} text - The text to escape
 * @returns {string} Escaped HTML text
 */
function escapeHtml(text) {
    if (!text) return '';
    return text.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

/**
 * Function to re-initialize event handlers for dynamically added content
 * This ensures that all interactive elements work after articles are loaded
 */
function initArticleEvents() {
    // Like buttons
    document.querySelectorAll('.like-btn').forEach(button => {
        button.addEventListener('click', () => {
            const articleId = button.getAttribute('data-article-id');
            fetch('like_article.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ article_id: articleId })
            })
                .then(response => response.json())
                .then(data => {
                    document.querySelector(`#like-count-${articleId}`).textContent = data.likes;
                });
        });
    });

    // Load comments for each article
    document.querySelectorAll('.post-card').forEach(card => {
        const articleId = card.getAttribute('data-article-id');
        fetch('get_comments.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `article_id=${articleId}`
        })
            .then(response => response.json())
            .then(comments => {
                const commentList = document.querySelector(`#comments-${articleId}`);
                commentList.innerHTML = '';

                if (comments.length === 0) {
                    commentList.innerHTML = '<p class="no-comments">No comments yet.</p>';
                    return;
                }

                comments.forEach(comment => {
                    let commentHtml = `
          <div class="comment">
            <div class="comment-text">
              <strong>${escapeHtml(comment.commenter_name)}:</strong> 
              ${escapeHtml(comment.comment_text)}
            </div>`;

                    if (comment.reply_text) {
                        commentHtml += `
            <div class="reply-text">
              <em>Owner replied:</em> ${escapeHtml(comment.reply_text)}
            </div>`;
                    }

                    commentHtml += `</div>`;
                    commentList.innerHTML += commentHtml;
                });
            });
    });
}