/**
 * Modified newsfeed_v2.js to work with standard PHP layout
 * but render only the articles section dynamically
 */

let isInitialized = false;

// Initialize the application when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    if (isInitialized) return;
    isInitialized = true;

    // Initialize the articles display
    renderArticleFeed();

    // Initialize event listeners
    initializeAllEventListeners();

    // Load comments for initial articles
    window.initialData.articles.forEach(article => {
        loadComments(article.id);
    });
});

// State management
const state = {
    currentOffset: window.initialData.currentOffset,
    sortOption: window.initialData.sortOption,
    filters: {
        institutes: ['All']
    },
    isLoading: false,
    user: window.initialData.user,
    articles: window.initialData.articles,
    notifications: window.initialData.notifications,
    announcements: window.initialData.announcements
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

// Helper function to replace newlines with <br>
function nl2br(str) {
    if (!str) return '';
    return str.replace(/\n/g, '<br>');
}

// Render only the article feed
function renderArticleFeed() {
    const postFeed = document.getElementById('postFeed');

    if (!postFeed) return;

    if (state.articles.length === 0) {
        postFeed.innerHTML = '<p>No articles available yet.</p>';
        return;
    }

    postFeed.innerHTML = state.articles.map(article => renderArticle(article)).join('');
}

// Component-based rendering functions
const components = {
    

    // Sidebar component 
    renderSidebar() {
        return `
            <div class="card sidebar-nav">
                <ul>
                    <li>
                        <img src="finaluser.png" alt="Profile">
                        <span><a href="profile.php" style="color: black;text-decoration: none;">My Profile</a></span>
                    </li>
                    <li>
                        <img src="finalsave.png" alt="Saved">
                        <span><a href="save_articles.php" style="color: black;text-decoration: none;">Saved Articles</a></span>
                    </li>
                </ul>
            </div>
            ${this.renderInstitutesFilter()}
            ${this.renderSortOptions()}
            ${this.renderAnnouncements()}
        `;
    },

    // Institutes filter component
    renderInstitutesFilter() {
        return `
            <div class="card sidebar-nav">
                <h2 class="card-title">Institutes</h2>
                <ul class="checkbox-list">
                    <li><input type="checkbox" class="institute-filter" value="All" checked> All Institutes</li>
                    <li><input type="checkbox" class="institute-filter" value="IC"> Institute of Computing</li>
                    <li><input type="checkbox" class="institute-filter" value="ILEGG"> Institute of Leadership Entrepreneurship, and Good Governance</li>
                    <li><input type="checkbox" class="institute-filter" value="ITed"> Institute of Teacher Education</li>
                    <li><input type="checkbox" class="institute-filter" value="IAAS"> Institute of Aquatic and Applied Sciences</li>
                </ul>
            </div>
        `;
    },

    // Sort options component
    renderSortOptions() {
        return `
            <div class="card sidebar-nav">
                <h2 class="card-title">Sort</h2>
                <ul class="checkbox-list">
                    <li><input type="radio" name="sort" class="sort-option" value="new" ${state.sortOption === 'new' ? 'checked' : ''}> New Upload</li>
                    <li><input type="radio" name="sort" class="sort-option" value="old" ${state.sortOption === 'old' ? 'checked' : ''}> Oldest Upload</li>
                    <li><input type="radio" name="sort" class="sort-option" value="popular" ${state.sortOption === 'popular' ? 'checked' : ''}> Popular</li>
                </ul>
            </div>
        `;
    },

    // Announcements component
    renderAnnouncements() {
        return `
            <div class="card sidebar-nav">
                <h2 class="card-title">School Announcement</h2>
                ${state.announcements.map(announcement => {
            let announcementClass = '';
            const title = announcement.title.toLowerCase();

            if (title.includes('dismissal')) {
                announcementClass = 'early-dismissal';
            } else if (title.includes('yearbook')) {
                announcementClass = 'yearbook';
            } else if (title.includes('summer') || title.includes('enrichment')) {
                announcementClass = 'summer';
            }

            return `
                        <div class="announcement ${announcementClass}">
                            <strong>${escapeHtml(announcement.title)}</strong>
                            <p>${nl2br(escapeHtml(announcement.content))}</p>
                        </div>
                    `;
        }).join('')}
            </div>
        `;
    },

    // Main content component
    renderMainContent() {
        return `
            ${this.renderPostBox()}
            <div id="postFeed">${this.renderArticles()}</div>
            ${this.renderLoadMore()}
        `;
    },

    // Post box component
    renderPostBox() {
        const profilePicture = !state.user.profile_picture ?
            'uploads/profile_pictures/default_profile.png' :
            `uploads/profile_pictures/${state.user.profile_picture}`;

        return `
            <div class="post-box">
                <div class="post-box-input">
                    <img src="${profilePicture}" alt="User" class="avatar">
                    <form id="quickPostForm" class="post-form" style="display: flex;">
                        <input type="text" class="post-input" id="articleInput" name="title"
                            placeholder="What's on your mind? Want to publish your own article?" required>
                        <button type="submit" class="post-btn" style="margin-left:12px;">Submit</button>
                    </form>
                </div>

                <div class="center-button">
                    <button class="publish-btn" id="publishBtn"><strong>PUBLISH ARTICLE</strong> ✎</button>
                    <div id="articleFormDropdown" class="form-dropdown hidden">
                        ${getArticleFormTemplate()}
                    </div>
                </div>
            </div>
        `;
    },

    // Articles component
    renderArticles() {
        if (state.articles.length === 0) {
            return '<p>No articles available yet.</p>';
        }

        return state.articles.map(article => renderArticle(article)).join('');
    },

    // Load more component
    renderLoadMore() {
        return `
            <div class="load-more-container" id="loadMoreContainer">
                <button id="loadMoreBtn" class="load-more-btn">Load More</button>
                <div id="loadingSpinner" class="loading-spinner" style="display: none;">
                    <div class="spinner"></div>
                </div>
            </div>
        `;
    },

    // Footer component
    renderFooter() {
        return `
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
        `;
    }
};

// Article form template
function getArticleFormTemplate() {
    return `
        <div class="form-container">
            <div class="form-header">
                <h2>Submit Your Article</h2>
                <button type="button" class="close-btn" id="closeFormBtn">✕</button>
            </div>
            <form id="articleForm" action="submit_article.php" method="post" enctype="multipart/form-data">
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
                    <img class="avatar" src="uploads/profile_pictures/${escapeHtml(article.profile_picture || 'default_profile.png')}" alt="User">
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
            ` : ''}            <div class="post-actions">
                <button class="like-btn ${article.user_liked ? 'liked' : ''}" data-article-id="${article.id}">
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
                
                <form class="comment-form" data-article-id="${article.id}">
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

// Helper function to replace newlines with <br>
function nl2br(str) {
    return str.replace(/\n/g, '<br>');
}

// Render the entire application
// function renderApp() {
//     const appContainer = document.getElementById('app');

//     appContainer.innerHTML = `
//         <header class="header">
//             ${components.renderHeader()}
//         </header>

//         <aside class="sidebar">
//             ${components.renderSidebar()}
//         </aside>

//         <main class="main-content">
//             ${components.renderMainContent()}
//         </main>

//         <footer class="footer">
//             ${components.renderFooter()}
//         </footer>
//     `;

//     // Hide the loading spinner
//     document.getElementById('loading').style.display = 'none';

//     // Initialize all event listeners after rendering
//     initializeAllEventListeners();

//     // Load comments for initial articles
//     state.articles.forEach(article => {
//         loadComments(article.id);
//     });
// }

// Initialize all event listeners
function initializeAllEventListeners() {
 

    // User menu dropdown
    document.getElementById('userMenuButton')?.addEventListener('click', toggleUserMenu);

    // Close user menu when clicking outside
    document.addEventListener('click', function (e) {
        const userDropdown = document.querySelector('.user-dropdown');
        const userMenu = document.getElementById('user-menu');
        if (userDropdown && userMenu && !userDropdown.contains(e.target) && userMenu.style.display === 'block') {
            userMenu.style.display = 'none';
        }
    });

    // Institute filters
    document.querySelectorAll('.institute-filter').forEach(checkbox => {
        checkbox.addEventListener('change', handleFilterChange);
    });

    // Sort options
    document.querySelectorAll('.sort-option').forEach(radio => {
        radio.addEventListener('change', handleSortChange);
    });

    // Publish button
    document.getElementById('publishBtn')?.addEventListener('click', () => {
        const dropdown = document.getElementById('articleFormDropdown');
        toggleVisibility(dropdown);
    });

    // Close form button
    document.getElementById('closeFormBtn')?.addEventListener('click', () => {
        const dropdown = document.getElementById('articleFormDropdown');
        toggleVisibility(dropdown);
    });

    // Article form submit
    document.getElementById('articleForm')?.addEventListener('submit', handleArticleSubmit);

    // Quick post form
    document.getElementById('quickPostForm')?.addEventListener('submit', handleQuickPost);

    // Load more button
    document.getElementById('loadMoreBtn')?.addEventListener('click', loadMoreArticles);

    // Initialize image upload
    const selectImageButton = document.getElementById('selectImageButton');
    const featuredImageInput = document.getElementById('featuredImageInput');
    if (selectImageButton && featuredImageInput) {
        selectImageButton.addEventListener('click', () => featuredImageInput.click());
        featuredImageInput.addEventListener('change', (event) => {
            const fileName = event.target.files[0] ? event.target.files[0].name : 'No file chosen';
            selectImageButton.textContent = `Selected: ${fileName}`;
        });
    }

    // Initialize interactive elements for articles
    initializeInteractiveElements();

    // Initialize infinite scroll
    window.removeEventListener('scroll', handleScroll);
    window.addEventListener('scroll', handleScroll);
}

function handleScroll() {
    if (isScrollNearBottom() && !state.isLoading) {
        loadMoreArticles();
    }
}


// Initialize article interactions
function initializeInteractiveElements() {
    // Initialize like buttons
    document.querySelectorAll('.like-btn').forEach(button => {
        // Remove any existing event listeners
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);

        newButton.addEventListener('click', function () {
            const articleId = this.getAttribute('data-article-id');
            if (articleId) {
                handleLikeClick(articleId);
            }
        });
    });

    // Initialize comment forms
    document.querySelectorAll('.comment-form').forEach(form => {
        // Remove any existing event listeners
        const newForm = form.cloneNode(true);
        form.parentNode.replaceChild(newForm, form);

        newForm.addEventListener('submit', async function (e) {
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

// Toggle dropdown
function toggleDropdown(button) {
    const dropdown = button.nextElementSibling;
    dropdown.style.display = dropdown.style.display === 'none' || dropdown.style.display === '' ? 'block' : 'none';

    // Close all other dropdowns
    document.querySelectorAll('.dropdown-content').forEach(dd => {
        if (dd !== dropdown && dd.style.display === 'block') {
            dd.style.display = 'none';
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function closeDropdown(e) {
        if (!dropdown.contains(e.target) && e.target !== button && dropdown.style.display === 'block') {
            dropdown.style.display = 'none';
            document.removeEventListener('click', closeDropdown);
        }
    });
}

// Toggle visibility
function toggleVisibility(element) {
    if (element) {
        element.classList.toggle('hidden');
    }
}

// Toggle user menu
function toggleUserMenu() {
    const userMenu = document.getElementById('user-menu');
    if (userMenu) {
        userMenu.style.display = userMenu.style.display === 'block' ? 'none' : 'block';
    }
}

// Filter change handler
async function handleFilterChange() {
    const selectedInstitutes = [];
    document.querySelectorAll('.institute-filter:checked').forEach(checkbox => {
        selectedInstitutes.push(checkbox.value);
    });

    state.filters.institutes = selectedInstitutes;
    await refreshArticles();
}

// Sort change handler
async function handleSortChange() {
    state.sortOption = this.value;
    await refreshArticles();
}

// Article submission handler
async function handleArticleSubmit(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    try {
        const response = await fetch('submit_article.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            // Close the form
            toggleVisibility(document.getElementById('articleFormDropdown'));

            // Clear form
            form.reset();
            document.getElementById('selectImageButton').textContent = 'Select Image';

            // Show success message
            showMessage('Article submitted successfully!', 'success');

            // Refresh articles
            refreshArticles();
        } else {
            showMessage('Error submitting article: ' + result.error, 'error');
        }
    } catch (error) {
        console.error('Error submitting article:', error);
        showMessage('An error occurred. Please try again.', 'error');
    }
}

// Quick post handler
async function handleQuickPost(e) {
    e.preventDefault();

    const title = document.getElementById('articleInput').value;

    if (!title.trim()) return;

    try {
        const response = await fetch('submit_article.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `title=${encodeURIComponent(title)}`
        });

        const result = await response.json();

        if (result.success) {
            // Clear input
            document.getElementById('articleInput').value = '';

            // Show success message
            showMessage('Article submitted successfully!', 'success');

            // Refresh articles
            refreshArticles();
        } else {
            showMessage('Error submitting article: ' + result.error, 'error');
        }
    } catch (error) {
        console.error('Error submitting article:', error);
        showMessage('An error occurred. Please try again.', 'error');
    }
}

// Refresh articles
async function refreshArticles() {
    state.currentOffset = 0;

    try {
        const response = await fetch('filter_feed.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                institutes: state.filters.institutes,
                sort: state.sortOption
            })
        });

        const articles = await response.json();

        state.articles = articles;

        // Re-render just the article feed
        const postFeed = document.getElementById('postFeed');
        postFeed.innerHTML = components.renderArticles();

        // Re-initialize interactive elements
        initializeInteractiveElements();

        // Load comments for each article
        articles.forEach(article => {
            loadComments(article.id);
        });
    } catch (error) {
        console.error('Error refreshing articles:', error);
        showMessage('Error loading articles. Please refresh the page.', 'error');
    }
}

// Add this function to check if we're near the bottom
function isScrollNearBottom() {
    const scrollPosition = window.innerHeight + window.scrollY;
    const pageHeight = document.body.offsetHeight;
    const buffer = 500; // Buffer zone before bottom

    return scrollPosition >= pageHeight - buffer;
}

// Add scroll event listener
window.addEventListener('scroll', () => {
    if (isScrollNearBottom() && !state.isLoading && !document.querySelector('.load-more-btn.end-reached')) {
        loadMoreArticles();
    }
});

// Add this function to handle the back to top action
function handleBackToTop() {
    // Reload the current page
    window.location.reload();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function loadMoreArticles() {
    if (state.isLoading || document.querySelector('.load-more-btn.end-reached')) return;

    const loadMoreBtn = document.getElementById('loadMoreBtn');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const postFeed = document.getElementById('postFeed');

    if (!loadMoreBtn || !loadingSpinner || !postFeed) return;

    // Check if we have articles first
    if (state.articles.length === 0) {
        // No articles, show Back to Top button
        loadMoreBtn.innerText = 'Back To Top';
        loadMoreBtn.classList.remove('disabled');
        loadMoreBtn.classList.add('end-reached');

        // Replace the button with a new one to prevent multiple handlers
        const newButton = loadMoreBtn.cloneNode(true);
        loadMoreBtn.parentNode.replaceChild(newButton, loadMoreBtn);

        newButton.addEventListener('click', handleBackToTop);
        return;
    }

    // If we have articles, proceed with loading more
    state.isLoading = true;
    loadMoreBtn.classList.add('disabled');
    loadMoreBtn.innerText = 'Loading...';
    loadingSpinner.style.display = 'block';

    try {
        // Fetch the next set of articles from the server
        const response = await fetch('filter_feed.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                institutes: state.filters.institutes,
                sort: state.sortOption,
                offset: state.currentOffset,
                limit: 3
            })
        });

        const data = await response.json();

        if (data.length > 0) {
            // Filter out any articles that are already in state.articles
            const newArticles = data.filter(newArticle => 
                !state.articles.some(existingArticle => existingArticle.id === newArticle.id)
            );

            if (newArticles.length > 0) {
                // Add is_owner flag to each article
                newArticles.forEach(article => {
                    article.is_owner = (article.user_id == state.user.id);
                });

                // Add articles to state
                state.articles = [...state.articles, ...newArticles];

                // Create a temporary container for the new articles
                const tempContainer = document.createElement('div');
                newArticles.forEach(article => {
                    tempContainer.insertAdjacentHTML('beforeend', renderArticle(article));
                });

                // Append all new articles to the feed
                const postFeedContent = tempContainer.innerHTML;
                postFeed.insertAdjacentHTML('beforeend', postFeedContent);

                // Initialize interactive elements for new articles
                initializeInteractiveElements();

                // Load comments for new articles
                newArticles.forEach(article => {
                    loadComments(article.id);
                });

                state.currentOffset += newArticles.length;
                loadMoreBtn.innerText = 'Load More';
                loadMoreBtn.classList.remove('disabled');
            } else {
                // No new articles to add, show Back to Top
                loadMoreBtn.innerText = 'Back To Top';
                loadMoreBtn.classList.remove('disabled');
                loadMoreBtn.classList.add('end-reached');

                // Replace the button with a new one to prevent multiple handlers
                const newButton = loadMoreBtn.cloneNode(true);
                loadMoreBtn.parentNode.replaceChild(newButton, loadMoreBtn);

                newButton.addEventListener('click', handleBackToTop);
            }
        } else {
            // No more articles to load
            loadMoreBtn.innerText = 'Back To Top';
            loadMoreBtn.classList.remove('disabled');
            loadMoreBtn.classList.add('end-reached');

            // Replace the button with a new one to prevent multiple handlers
            const newButton = loadMoreBtn.cloneNode(true);
            loadMoreBtn.parentNode.replaceChild(newButton, loadMoreBtn);

            newButton.addEventListener('click', handleBackToTop);
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
                commentCount.textContent = parseInt(commentCount.textContent) + 1;
            }
            // Refresh comments
            await loadComments(articleId);
        }
        return result;
    } catch (error) {
        console.error('Error submitting comment:', error);
        return { success: false, error: error.message };
    }
}

// Handle like button click
async function handleLikeClick(articleId) {
    try {
        // Support both JSON and form data
        const response = await fetch('like_article.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ article_id: articleId })
        });

        const data = await response.json();

        if (data.success) {
            // Update like count
            const likeCount = document.getElementById(`like-count-${articleId}`);
            if (likeCount) {
                likeCount.textContent = data.likes;
            }

            // Update button appearance based on action returned from server
            const likeBtn = document.querySelector(`.like-btn[data-article-id="${articleId}"]`);
            if (likeBtn) {
                likeBtn.classList.toggle('liked', data.action === 'liked');
            }

            // Update the article in state to reflect the new like count and liked state
            const articleIndex = state.articles.findIndex(article => article.id == articleId);
            if (articleIndex !== -1) {
                state.articles[articleIndex].likes = data.likes;
                state.articles[articleIndex].user_liked = (data.action === 'liked');
            }
        } else {
            console.error('Error liking article:', data.error);
            showMessage('Error processing like: ' + data.error, 'error');
        }
    } catch (error) {
        console.error('Error liking article:', error);
        showMessage('Error processing like. Please try again.', 'error');
    }
}

// Show message to user
function showMessage(message, type = 'info') {
    // Remove any existing messages
    const existingMessages = document.querySelectorAll('.alert');
    existingMessages.forEach(msg => msg.remove());

    // Create message element
    const messageEl = document.createElement('div');
    messageEl.className = `alert alert-${type}`;
    messageEl.textContent = message;

    // Insert at top of main content
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        mainContent.insertBefore(messageEl, mainContent.firstChild);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            messageEl.remove();
        }, 5000);
    }
}

// Event listener for toggling the publish form
// don't mid this thing
const publishBtn = document.querySelector('.publish-btn');
const dropdownForm = document.getElementById('articleFormDropdown');
if (publishBtn && dropdownForm) {
    publishBtn.addEventListener('click', () => toggleVisibility(dropdownForm));
}
