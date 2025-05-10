<!-- Bootstrap core JavaScript-->
<script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="vendor/chart.js/Chart.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="js/demo/chart-area-demo.js"></script>
    <script src="js/demo/chart-pie-demo.js"></script>


   <script>
    document.getElementById('alertsDropdown').addEventListener('click', function () {
        fetch('mark_notifications_read.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the notification counter
                const badgeCounter = document.querySelector('.badge-counter');
                badgeCounter.textContent = '';
            }
        })
        .catch(error => console.error('Error:', error));
    });
</script>

  <script>
    function openModal(articleId) {
      const modal = document.getElementById('rejectModal');
      document.getElementById('articleId').value = articleId;
      modal.style.display = 'block';
    }

    function closeModal() {
      const modal = document.getElementById('rejectModal');
      modal.style.display = 'none';
    }

    function openViewModal(articleId) {
      const modal = document.getElementById('viewModal');
      const articleDetails = document.getElementById('articleDetails');

      // Clear previous content
      articleDetails.innerHTML = '<p>Loading...</p>';

      // Fetch article details via AJAX
      fetch(`view_article.php?article_id=${articleId}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            articleDetails.innerHTML = `
                    <h3>${data.article.title}</h3>
                    <p><strong>Author:</strong> ${data.article.author_name}</p>
                    <p><strong>Institute:</strong> ${data.article.author_institute}</p>
                    <p><strong>Content:</strong></p>
                    <p>${data.article.content}</p>
                `;
          } else {
            articleDetails.innerHTML = '<p>Error loading article details.</p>';
          }
        })
        .catch(error => {
          console.error('Error fetching article details:', error);
          articleDetails.innerHTML = '<p>Error loading article details.</p>';
        });

      modal.style.display = 'block';
    }

    function closeViewModal() {
      const modal = document.getElementById('viewModal');
      modal.style.display = 'none';
    }
    
    $user_id = $_SESSION['user_id']; // assuming this is the admin ID
    $sql = "SELECT * FROM admin_notifications WHERE reference_id = ? ORDER BY created_at DESC LIMIT 5";

  </script>
  <script src="js/newsfeed.js"></script>
  <script src="js/database_helper.js"></script>

<script>
    function deleteUser(userId) {
        if (confirm("Are you sure you want to delete this user? This action cannot be undone.")) {
            fetch('delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: userId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("User deleted successfully.");
                    // Optionally, remove the row from the table
                    document.querySelector(`button[onclick="deleteUser(${userId})"]`).closest('tr').remove();
                } else {
                    alert("Failed to delete user: " + data.message);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("An error occurred while deleting the user.");
            });
        }
    }
</script>
