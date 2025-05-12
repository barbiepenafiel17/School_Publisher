/**
 * Newsfeed JavaScript
 * Handles all client-side rendering and interactions for the newsfeed
 */

// State management
const state = {
    currentOffset: window.initialData.currentOffset,
    sortOption: window.initialData.sortOption,
    filters: {
        institutes: ['All']
    },
    isLoading: false
};

// Utility functions
function escapeHtml(text) {
    if (!text) return '';
    return text.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
        hour12: true
    });
}

// Article form template
function getArticleFormTemplate() {
    return `
        <div class="form-container">
            <div class="form-header">
                <h2>Submit Your Article</h2>
                <button class="close-btn" onclick="toggleVisibility(document.getElementById('articleFormDropdown'))">✕</button>
            </div>
            <form action="submit_article.php" method="post" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label>Article Title</label>
                        <input type="text" name="title" required>
                    </div>
                </div>
                <div class="form-group full-width">
                    <label>Abstract</label>
                    <textarea name="abstract" rows="3"></textarea>
                </div>
                <div class="form-group full-width">
                    <label>Featured Image</label>
                    <div class="image-upload">
                        <div class="image-box">
                            <p>📷<br>Drag and drop your image here, or click to browse</p>
                            <small>Supported formats: JPG, PNG, GIF (Max size: 5MB)</small>
                            <input type="file" name="featured_image" hidden id="featuredImageInput">
                        </div>
                        <button type="button" class="select-button" id="selectImageButton">Select Image</button>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="submit-btn">Submit for Review</button>
                </div>
            </form>
        </div>
    `;
}

// Article template
function renderArticle(article) {
    return `
        <div class="post-card" data-article-id="${article.id}">
            <div class="post-card-header">
                <div class="post-header">
                    <img class="avatar" src="uploads/profile_pictures/${escapeHtml(article.profile_picture)}" alt="User">
                    <div>
                        <strong>${escapeHtml(article.full_name)}</strong><br>
                        <span class="post-date">${formatDate(article.created_at)}</span>
                    </div>
                </div>
                <div class="dropdown" style="display: inline-block; position: relative;">
                    <button class="dot-btn" onclick="toggleDropdown(this)">...</button>
                    <div class="dropdown-content" style="display: none; position: absolute; top: 0; right: 100%; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.2); z-index: 1;">
                        ${article.is_owner ? `
                            <form method="POST" action="delete_article.php" onsubmit="return confirm('Delete this article?');">
                                <input type="hidden" name="article_id" value="${article.id}">
                                <button type="submit" style="color: red; background: none; border: none; padding: 10px; width: 100%; text-align: left;">
                                    <i class="fa fa-trash-o" style="margin-right:10px;"></i>Delete
                                </button>
                            </form>
                        ` : ''}
                        <form method="POST" action="hide_article.php">
                            <input type="hidden" name="article_id" value="${article.id}">
                            <button type="submit" style="background: none; border: none; padding: 10px; width: 100%; text-align: left;">
                                <i class="fa fa-eye-slash" style="margin-right:10px;"></i>Hide
                            </button>
                        </form>
                        <form method="POST" action="report_article.php" onsubmit="return confirm('Report this article to admin?');">
                            <input type="hidden" name="article_id" value="${article.id}">
                            <button type="submit" style="background: none; border: none; padding: 10px; width: 100%; text-align: left;">
                                <i class="fa fa-exclamation-triangle" style="margin-right:10px; color:yellow;"></i>Report
                            </button>
                        </form>
                        <form method="POST" action="save_A.php">
                            <input type="hidden" name="article_id" value="${article.id}">
                            <button type="submit" style="background: none; border: none; padding: 10px; width: 100%; text-align: left;">
                                <i class="fa fa-bookmark" style="margin-right:10px;"></i>Save
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="post-title" style="font-family: Poppins; font-size:40px">
                <strong>${escapeHtml(article.title)}</strong>
            </div>
            <div class="post-content" style="font-family: Poppins; font-size:20px; text-align:justify">
                ${escapeHtml(article.abstract)}
            </div>

            ${article.featured_image ? `
                <div class="post-image">
                    <img src="${escapeHtml(article.featured_image)}" alt="Article Image" class="responsive-img" loading="lazy">
                </div>
            ` : ''}

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
        </div>
    `;
}

// Comment template
function renderComment(comment) {
    return `
        <div class="comment">
            <div class="comment-text">
                <strong>${escapeHtml(comment.commenter_name)}:</strong>
                ${escapeHtml(comment.comment_text)}
            </div>
            ${comment.reply_text ? `
                <div class="reply-text">
                    <em>Owner replied:</em> ${escapeHtml(comment.reply_text)}
                </div>
            ` : ''}
        </div>
    `;
}

// Event handlers
function toggleDropdown(button) {
    const dropdown = button.nextElementSibling;
    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
}

function toggleVisibility(element) {
    element.classList.toggle('hidden');
}

// Initialize the newsfeed
function initializeNewsfeed() {
    const postFeed = document.getElementById('postFeed');
    
    // Render initial articles
    if (window.initialData.articles.length > 0) {
        window.initialData.articles.forEach(article => {
            postFeed.insertAdjacentHTML('beforeend', renderArticle(article));
        });
        
        // Initialize event listeners for initial articles
        initializeDynamicElements();
        
        // Load comments for each article
        window.initialData.articles.forEach(article => {
            loadComments(article.id);
        });
    } else {
        postFeed.innerHTML = '<p>No articles available yet.</p>';
    }

    // Initialize article form
    const articleFormDropdown = document.getElementById('articleFormDropdown');
    if (articleFormDropdown) {
        articleFormDropdown.innerHTML = getArticleFormTemplate();
    }

    // Initialize event listeners
    initializeEventListeners();
}

// Load more articles
async function loadMoreArticles() {
    if (state.isLoading) return;
    
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const postFeed = document.getElementById('postFeed');
    
    if (!loadMoreBtn || !loadingSpinner || !postFeed) return;
    
    state.isLoading = true;
    loadMoreBtn.classList.add('disabled');
    loadMoreBtn.innerText = 'Loading...';
    loadingSpinner.style.display = 'block';
    
    try {
        const response = await fetch('load_more_articles.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                offset: state.currentOffset,
                limit: 5,
                sort: state.sortOption,
                filters: state.filters
            })
        });
        
        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }
        
        if (data.articles && data.articles.length > 0) {
            data.articles.forEach(article => {
                postFeed.insertAdjacentHTML('beforeend', renderArticle(article));
            });
            
            // Initialize event listeners for new articles
            initializeDynamicElements();
            
            // Load comments for new articles
            data.articles.forEach(article => {
                loadComments(article.id);
            });
            
            state.currentOffset += data.articles.length;
            
            if (!data.hasMore) {
                loadMoreBtn.style.display = 'none';
            } else {
                loadMoreBtn.innerText = 'Load More';
                loadMoreBtn.classList.remove('disabled');
            }
        } else {
            loadMoreBtn.innerText = 'No More Articles';
            loadMoreBtn.style.display = 'none';
        }
    } catch (error) {
        console.error('Error loading more articles:', error);
        loadMoreBtn.innerText = 'Error. Try Again';
        loadMoreBtn.classList.remove('disabled');
    } finally {
        state.isLoading = false;
        loadingSpinner.style.display = 'none';
    }
}

// Load comments for an article
async function loadComments(articleId) {
    try {
        const response = await fetch('get_comments.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `article_id=${articleId}`
        });
        
        const comments = await response.json();
        const commentList = document.getElementById(`comments-${articleId}`);
        
        if (!commentList) return;
        
        if (comments.length === 0) {
            commentList.innerHTML = '<p class="no-comments">No comments yet.</p>';
            return;
        }
        
        let html = '';
        comments.forEach(comment => {
            html += renderComment(comment);
        });
        commentList.innerHTML = html;
        
        // Update comment count
        const commentCount = document.getElementById(`comment-count-${articleId}`);
        if (commentCount) {
            commentCount.textContent = comments.length;
        }
    } catch (error) {
        console.error('Error loading comments:', error);
    }
}

// Handle comment submission
async function handleCommentSubmit(articleId, commentText) {
    try {
        const response = await fetch('post_comment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `article_id=${articleId}&comment_text=${encodeURIComponent(commentText)}`
        });
        
        const result = await response.json();
        if (result.success) {
            // Update comment count immediately
            const commentCount = document.getElementById(`comment-count-${articleId}`);
            if (commentCount) {
                commentCount.textContent = result.comment_count;
            }
            // Refresh comments
            await loadComments(articleId);
        }
        return result;
    } catch (error) {
        console.error('Error submitting comment:', error);
        return {success: false, error: error.message};
    }
}

// Track initialized forms
const initializedForms = new WeakSet();

// Initialize comment forms
function initializeCommentForms() {
    document.querySelectorAll('.comment-form').forEach(form => {
        // Remove any existing event listeners
        const newForm = form.cloneNode(true);
        form.parentNode.replaceChild(newForm, form);

        newForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const articleId = this.querySelector('input[name="article_id"]').value;
            const commentText = this.querySelector('input[name="comment_text"]').value;
            
            if (!commentText.trim()) return;
            
            try {
                const result = await handleCommentSubmit(articleId, commentText);
                if (result.success) {
                    this.querySelector('input[name="comment_text"]').value = '';
                }
            } catch (error) {
                console.error('Error posting comment:', error);
            }
        });
    });
}

// Handle like button click
async function handleLikeClick(articleId) {
    try {
        const response = await fetch('like_article.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ article_id: articleId })
        });
        
        const data = await response.json();
        if (data.likes !== undefined) {
            const likeCount = document.getElementById(`like-count-${articleId}`);
            if (likeCount) {
                likeCount.textContent = data.likes;
            }
        }
    } catch (error) {
        console.error('Error liking article:', error);
    }
}

// Initialize dynamic elements (like buttons, comment forms)
function initializeDynamicElements() {
    // Setup like buttons
    document.querySelectorAll('.like-btn').forEach(button => {
        // Remove any existing event listeners
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);

        newButton.addEventListener('click', function() {
            const articleId = this.getAttribute('data-article-id');
            if (articleId) {
                handleLikeClick(articleId);
            }
        });
    });

    // Initialize comment forms
    initializeCommentForms();
}

// Initialize event listeners
function initializeEventListeners() {
    // Publish button
    const publishBtn = document.querySelector('.publish-btn');
    const articleFormDropdown = document.getElementById('articleFormDropdown');
    if (publishBtn && articleFormDropdown) {
        publishBtn.addEventListener('click', () => toggleVisibility(articleFormDropdown));
    }

    // Image upload
    const selectImageButton = document.getElementById('selectImageButton');
    const featuredImageInput = document.getElementById('featuredImageInput');
    if (selectImageButton && featuredImageInput) {
        selectImageButton.addEventListener('click', () => featuredImageInput.click());
        featuredImageInput.addEventListener('change', (event) => {
            const fileName = event.target.files[0] ? event.target.files[0].name : 'No file chosen';
            selectImageButton.textContent = `Selected: ${fileName}`;
        });
    }

    // Load more button
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', loadMoreArticles);
    }

    // Infinite scroll
    window.addEventListener('scroll', () => {
        if (isScrollNearBottom() && !state.isLoading) {
            loadMoreArticles();
        }
    });

    // Initialize dynamic elements for initial articles
    initializeDynamicElements();
}

// Check if user has scrolled near bottom
function isScrollNearBottom() {
    return (window.innerHeight + window.scrollY) >= document.body.offsetHeight - 500;
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', initializeNewsfeed);
