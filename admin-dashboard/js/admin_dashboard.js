// Modal functions for Article
    function openArticleModal(id, title, author, email, institute, date, status, content, imagePath, authorImage) {
      // Set text content
      document.getElementById('modalArticleTitle').textContent = title;
      document.getElementById('modalArticleAuthor').textContent = author;
      document.getElementById('modalArticleEmail').textContent = email;
      document.getElementById('modalArticleEmailLink').href = 'mailto:' + email;
      document.getElementById('modalArticleInstitute').textContent = institute;
      document.getElementById('modalArticleDate').textContent = date;

      // Set content with HTML formatting
      document.getElementById('modalArticleContent').innerHTML = content;

      // Set author profile picture
      const authorImageElement = document.getElementById('modalAuthorImage');
      if (authorImage && authorImage !== '') {
        authorImageElement.src = 'uploads/' + authorImage;
      } else {
        authorImageElement.src = 'profile.jpg'; // Default image
      }      // Set article image if available
      const articleImageContainer = document.getElementById('modalArticleImageContainer');
      const articleImageElement = document.getElementById('modalArticleImage');

      if (imagePath && imagePath !== '') {
        // Check if the path already includes the directory prefix
        if (imagePath.startsWith('uploads/')) {
          articleImageElement.src = imagePath;
        } else {
          articleImageElement.src = 'uploads/' + imagePath;
        }
        articleImageContainer.style.display = 'block';
      } else {
        articleImageContainer.style.display = 'none';
      }

      // Set status with appropriate styling
      const statusElement = document.getElementById('modalArticleStatus');
      statusElement.textContent = status;
      statusElement.className = 'status-badge ' + status.toLowerCase();

      // Set view/edit link
      document.getElementById('viewArticleLink').href = 'admin_dashboards/view_article.php?id=' + id;

      // Show modal
      document.getElementById('articleModal').classList.add('show');
    }

    function closeArticleModal() {
      document.getElementById('articleModal').classList.remove('show');
    }

    // Reject Modal functions
    function openRejectModal(articleId) {
      document.getElementById('rejectArticleId').value = articleId;
      document.getElementById('rejectModal').classList.add('show');
    }

    function closeRejectModal() {
      document.getElementById('rejectModal').classList.remove('show');
    }

    // Approve Modal functions
    function openApproveModal(articleId) {
      document.getElementById('approveArticleId').value = articleId;
      document.getElementById('approveModal').classList.add('show');
    }

    function closeApproveModal() {
      document.getElementById('approveModal').classList.remove('show');
    }    // Approve modal functions
    function openApproveModal(articleId) {
      document.getElementById('approveArticleId').value = articleId;
      document.getElementById('approveModal').classList.add('show');
    }

    function closeApproveModal() {
      document.getElementById('approveModal').classList.remove('show');
    }

    // Legacy approve article function (kept for backward compatibility)
    function approveArticle(articleId) {
      openApproveModal(articleId);
    }

    // Close modals if clicked outside
    window.addEventListener('click', function (event) {
      const articleModal = document.getElementById('articleModal');
      const rejectModal = document.getElementById('rejectModal');
      const approveModal = document.getElementById('approveModal');

      if (event.target === articleModal) {
        closeArticleModal();
      }

      if (event.target === rejectModal) {
        closeRejectModal();
      }

      if (event.target === approveModal) {
        closeApproveModal();
      }
    });