/**
 * Database Helper JavaScript
 * Contains client-side functions for database operations
 */

const DatabaseHelper = {
  /**
   * Mark notifications as read
   * @returns {Promise} Promise representing the completion of the operation
   */
  markNotificationsAsRead: function() {
    return fetch('mark_notification_read.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      }
    })
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.text();
    })
    .then(() => {
      // Update UI to reflect read notifications
      const badge = document.querySelector('.notif-badge');
      if (badge) {
        badge.style.display = 'none';
      }
    })
    .catch(error => {
      console.error('Error marking notifications as read:', error);
    });
  },

  /**
   * Like an article
   * @param {number} articleId - The ID of the article to like
   * @returns {Promise} Promise representing the completion of the operation
   */
  likeArticle: function(articleId) {
    return fetch('like_article.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'article_id=' + encodeURIComponent(articleId)
    })
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    })
    .then(data => {
      // Update the like count in the UI
      const likeCountElement = document.getElementById('like-count-' + articleId);
      if (likeCountElement) {
        likeCountElement.textContent = data.likes;
      }
      return data;
    })
    .catch(error => {
      console.error('Error liking article:', error);
    });
  },

  /**
   * Post a comment on an article
   * @param {number} articleId - The ID of the article
   * @param {string} commentText - The comment text
   * @returns {Promise} Promise representing the completion of the operation
   */
  postComment: function(articleId, commentText) {
    const formData = new FormData();
    formData.append('article_id', articleId);
    formData.append('comment_text', commentText);

    return fetch('post_comment.php', {
      method: 'POST',
      body: formData
    })
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    })
    .then(data => {
      // Update the comments section in the UI
      this.loadComments(articleId);
      return data;
    })
    .catch(error => {
      console.error('Error posting comment:', error);
    });
  },

  /**
   * Load comments for an article
   * @param {number} articleId - The ID of the article
   * @returns {Promise} Promise representing the completion of the operation
   */
  loadComments: function(articleId) {
    return fetch('get_comments.php?article_id=' + encodeURIComponent(articleId))
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    })
    .then(comments => {
      // Update the comments section in the UI
      const commentsContainer = document.getElementById('comments-' + articleId);
      if (commentsContainer) {
        if (comments.length === 0) {
          commentsContainer.innerHTML = '<p class="no-comments">No comments yet.</p>';
        } else {
          let html = '';
          comments.forEach(comment => {
            html += `
              <div class="comment">
                <div class="comment-text">
                  <strong>${comment.commenter_name}:</strong>
                  ${comment.comment_text}
                </div>
                ${comment.reply_text ? `
                  <div class="reply-text">
                    <em>Owner replied:</em> ${comment.reply_text}
                  </div>
                ` : ''}
              </div>
            `;
          });
          commentsContainer.innerHTML = html;
        }

        // Update comment count
        const commentCountElement = document.getElementById('comment-count-' + articleId);
        if (commentCountElement) {
          commentCountElement.textContent = comments.length;
        }
      }
      return comments;
    })
    .catch(error => {
      console.error('Error loading comments:', error);
    });
  }
};

/**
 * Initialize all database-related functionality when the DOM is loaded
 */
document.addEventListener('DOMContentLoaded', function() {
  // Setup notification handling
  const bell = document.getElementById('notif-bell');
  if (bell) {
    bell.addEventListener('click', function() {
      const dropdown = document.getElementById('notif-dropdown');
      if (dropdown) {
        dropdown.classList.toggle('visible');
        
        // If visible, mark all notifications as read
        if (dropdown.classList.contains('visible')) {
          DatabaseHelper.markNotificationsAsRead();
        }
      }
    });
  }

  // Setup like buttons
  document.querySelectorAll('.like-btn').forEach(button => {
    button.addEventListener('click', function() {
      const articleId = this.getAttribute('data-article-id');
      if (articleId) {
        DatabaseHelper.likeArticle(articleId);
      }
    });
  });

  // Setup comment forms
  // document.querySelectorAll('.comment-form').forEach(form => {
  //   form.addEventListener('submit', function(e) {
  //     e.preventDefault();
  //     const articleId = this.querySelector('input[name="article_id"]').value;
  //     const commentText = this.querySelector('input[name="comment_text"]').value;
      
  //     if (articleId && commentText) {
  //       DatabaseHelper.postComment(articleId, commentText)
  //         .then(() => {
  //           // Clear the comment input after posting
  //           this.querySelector('input[name="comment_text"]').value = '';
  //         });
  //     }
  //   });
  // });
});