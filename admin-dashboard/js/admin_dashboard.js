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

// Pagination functionality for the articles table
function setupPagination() {
  const table = document.querySelector('.data-table');
  if (!table) return; // Exit if table doesn't exist
  
  const tableRows = table.querySelectorAll('tbody tr');
  let rowsPerPage = 5; // Default number of rows per page
  let currentPage = 1;
  
  // If no rows, don't set up pagination
  if (tableRows.length === 0) return;
  
  // Create pagination container
  const paginationContainer = document.createElement('div');
  paginationContainer.className = 'pagination-container';
  table.parentNode.insertBefore(paginationContainer, table.nextSibling);
  
  // Function to calculate total pages
  function calculateTotalPages() {
    return Math.ceil(tableRows.length / rowsPerPage);
  }
  
  // Function to generate page number buttons
  function generatePageButtons(totalPages, currentPage) {
    // Only show a limited number of page buttons
    const maxButtons = 5;
    let buttons = '';
    
    if (totalPages <= maxButtons) {
      // If total pages is less than or equal to maxButtons, show all pages
      for (let i = 1; i <= totalPages; i++) {
        buttons += `<button class="page-number ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
      }
    } else {
      // Always show first page
      buttons += `<button class="page-number ${1 === currentPage ? 'active' : ''}" data-page="1">1</button>`;
      
      // Calculate start and end page numbers to show
      let startPage = Math.max(2, currentPage - Math.floor(maxButtons / 2) + 1);
      let endPage = Math.min(totalPages - 1, startPage + maxButtons - 3);
      
      if (startPage > 2) {
        buttons += `<span class="page-ellipsis">...</span>`;
      }
      
      // Add middle page buttons
      for (let i = startPage; i <= endPage; i++) {
        buttons += `<button class="page-number ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
      }
      
      if (endPage < totalPages - 1) {
        buttons += `<span class="page-ellipsis">...</span>`;
      }
      
      // Always show last page
      if (totalPages > 1) {
        buttons += `<button class="page-number ${totalPages === currentPage ? 'active' : ''}" data-page="${totalPages}">${totalPages}</button>`;
      }
    }
    
    return buttons;
  }
  
  // Create pagination controls
  function renderPagination() {
    const totalPages = calculateTotalPages();
    
    // Adjust current page if needed after changing rows per page
    if (currentPage > totalPages) {
      currentPage = totalPages;
    }
    
    // Build pagination HTML
    let paginationHTML = `
      <div class="pagination">
        <div class="rows-per-page">
          <label for="rowsPerPage">Rows per page:</label>
          <select id="rowsPerPage">
            <option value="5" ${rowsPerPage === 5 ? 'selected' : ''}>5</option>
            <option value="10" ${rowsPerPage === 10 ? 'selected' : ''}>10</option>
            <option value="25" ${rowsPerPage === 25 ? 'selected' : ''}>25</option>
            <option value="50" ${rowsPerPage === 50 ? 'selected' : ''}>50</option>
            <option value="${tableRows.length}" ${rowsPerPage === tableRows.length ? 'selected' : ''}>All</option>
          </select>
        </div>
        
        <button id="prevPage" ${currentPage === 1 ? 'disabled' : ''}>
          <i class="fas fa-chevron-left"></i> Previous
        </button>
        
        <div class="page-numbers">
          ${generatePageButtons(totalPages, currentPage)}
        </div>
        
        <button id="nextPage" ${currentPage === totalPages ? 'disabled' : ''}>
          Next <i class="fas fa-chevron-right"></i>
        </button>
        
        <span class="page-info">(${tableRows.length} total items)</span>
      </div>
    `;
    
    // Update the pagination container
    paginationContainer.innerHTML = paginationHTML;
    
    // Add event listeners to pagination buttons
    document.getElementById('prevPage').addEventListener('click', () => {
      if (currentPage > 1) {
        currentPage--;
        updateTable();
        renderPagination();
      }
    });
    
    document.getElementById('nextPage').addEventListener('click', () => {
      if (currentPage < totalPages) {
        currentPage++;
        updateTable();
        renderPagination();
      }
    });
    
    // Add event listeners to page number buttons
    document.querySelectorAll('.page-number').forEach(button => {
      button.addEventListener('click', function() {
        const pageNumber = parseInt(this.getAttribute('data-page'));
        if (pageNumber !== currentPage) {
          currentPage = pageNumber;
          updateTable();
          renderPagination();
        }
      });
    });
    
    // Add event listener to rows per page selector
    document.getElementById('rowsPerPage').addEventListener('change', function() {
      rowsPerPage = parseInt(this.value);
      currentPage = 1; // Reset to first page when changing rows per page
      updateTable();
      renderPagination();
    });
  }
  
  // Function to update table based on current page
  function updateTable() {
    const startIndex = (currentPage - 1) * rowsPerPage;
    const endIndex = startIndex + rowsPerPage;
    
    tableRows.forEach((row, index) => {
      if (index >= startIndex && index < endIndex) {
        row.style.display = ''; // Show row
      } else {
        row.style.display = 'none'; // Hide row
      }
    });
  }
  
  // Initialize table and pagination
  updateTable();
  renderPagination();
}

// Initialize pagination when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  setupPagination();
});