    // Add any JavaScript functionality here
    document.addEventListener('DOMContentLoaded', function () {
      // Modal functionality
      const modal = document.getElementById('announcementModal');
      const openModalBtn = document.getElementById('createAnnouncementBtn');
      const closeModalBtn = document.getElementById('closeAnnouncementModal');
      const cancelBtn = document.getElementById('cancelAnnouncementBtn');      // Open modal for creating new announcement
      openModalBtn.addEventListener('click', function () {
        // Reset form for new announcement
        document.getElementById('announcementForm').reset();

        // Make sure any hidden fields for editing are cleared
        const hiddenIdField = document.getElementById('announcement_id');
        if (hiddenIdField) {
          hiddenIdField.value = '';
        } else {
          // Create hidden field for announcement ID if it doesn't exist
          const idField = document.createElement('input');
          idField.type = 'hidden';
          idField.id = 'announcement_id';
          idField.name = 'announcement_id';
          document.getElementById('announcementForm').appendChild(idField);
        }

        // Set title and button text for create mode
        document.querySelector('#announcementModal .modal-header h2').textContent = 'Create New Announcement';
        document.querySelector('#announcementForm button[type="submit"]').textContent = 'Publish';

        modal.classList.add('show');
      });

      // Edit announcement button functionality
      document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function () {
          const announcementId = this.getAttribute('data-id');

          // Fetch announcement details to populate the edit form
          fetch(`get_announcement.php?id=${announcementId}`)
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                const announcement = data.data;

                // Get the form and reset it first
                const form = document.getElementById('announcementForm');
                form.reset();

                // Populate form fields
                form.elements['title'].value = announcement.title;
                form.elements['content'].value = announcement.content;

                // Set audience checkboxes
                const audiences = announcement.audience.split(',');
                document.querySelectorAll('input[name="audience[]"]').forEach(checkbox => {
                  checkbox.checked = audiences.includes(checkbox.value);
                });

                // Set notification checkbox
                form.elements['notify'].checked = announcement.notify == 1;

                // Set or create hidden field for announcement ID
                let hiddenIdField = document.getElementById('announcement_id');
                if (!hiddenIdField) {
                  hiddenIdField = document.createElement('input');
                  hiddenIdField.type = 'hidden';
                  hiddenIdField.id = 'announcement_id';
                  hiddenIdField.name = 'announcement_id';
                  form.appendChild(hiddenIdField);
                }
                hiddenIdField.value = announcement.id;

                // Update modal title and button text for edit mode
                document.querySelector('#announcementModal .modal-header h2').textContent = 'Edit Announcement';
                document.querySelector('#announcementForm button[type="submit"]').textContent = 'Update';

                // Show the modal
                modal.classList.add('show');
              } else {
                alert('Error: ' + data.message);
              }
            })
            .catch(error => {
              console.error('Error fetching announcement:', error);
              alert('An error occurred while fetching the announcement details.');
            });
        });
      });

      // Close modal with X button
      closeModalBtn.addEventListener('click', function () {
        modal.classList.remove('show');
      });

      // Close modal with Cancel button
      cancelBtn.addEventListener('click', function () {
        if (confirm("Are you sure you want to cancel? Any unsaved changes will be lost.")) {
          modal.classList.remove('show');
          document.getElementById('announcementForm').reset();
        }
      });

      // Close modal when clicking outside
      window.addEventListener('click', function (event) {
        if (event.target === modal) {
          modal.classList.remove('show');
        }
      });

      // View Announcement Modal functionality
      const viewModal = document.getElementById('viewAnnouncementModal');
      const closeViewModalBtn = document.getElementById('closeViewAnnouncementModal');
      const closeViewBtn = document.getElementById('closeViewBtn'); document.querySelectorAll('.btn-view').forEach(button => {
        button.addEventListener('click', function () {
          const announcementId = this.getAttribute('data-id');

          // Fetch announcement details from the server
          fetch(`get_announcement.php?id=${announcementId}`)
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                const announcement = data.data;
                document.getElementById('viewTitle').textContent = announcement.title;
                document.getElementById('viewDate').textContent = formatDate(announcement.created_at);
                document.getElementById('viewAudience').textContent = announcement.audience;
                document.getElementById('viewContent').textContent = announcement.content;
                viewModal.classList.add('show');
              } else {
                alert('Error: ' + data.message);
              }
            })
            .catch(error => {
              console.error('Error fetching announcement:', error);
              alert('An error occurred while fetching the announcement details.');
            });
        });
      });

      // Helper function to format date
      function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'long',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        });
      }

      // Close View Announcement Modal with X button
      closeViewModalBtn.addEventListener('click', function () {
        viewModal.classList.remove('show');
      });

      // Close View Announcement Modal with Close button
      closeViewBtn.addEventListener('click', function () {
        viewModal.classList.remove('show');
      });

      // Close View Announcement Modal when clicking outside
      window.addEventListener('click', function (event) {
        if (event.target === viewModal) {
          viewModal.classList.remove('show');
        }
      });

      // Delete Confirmation Modal functionality
      const deleteModal = document.getElementById('deleteAnnouncementModal');
      const closeDeleteModalBtn = document.getElementById('closeDeleteAnnouncementModal');
      const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
      const confirmDeleteBtn = document.getElementById('confirmDeleteBtn'); document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function () {
          const announcementId = this.getAttribute('data-id');
          const announcementTitle = this.closest('tr').querySelector('td:first-child').textContent;

          document.getElementById('deleteTitle').textContent = announcementTitle;

          deleteModal.classList.add('show');

          confirmDeleteBtn.onclick = function () {
            // Create form data for the delete request
            const formData = new FormData();
            formData.append('id', announcementId);

            // Send delete request to the server
            fetch('delete_announcement.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  alert('Announcement deleted successfully!');
                  // Refresh the page to show updated list
                  window.location.reload();
                } else {
                  alert('Error: ' + data.message);
                }
                deleteModal.classList.remove('show');
              })
              .catch(error => {
                console.error('Error deleting announcement:', error);
                alert('An error occurred while deleting the announcement.');
                deleteModal.classList.remove('show');
              });
          };
        });
      });

      // Close Delete Confirmation Modal with X button
      closeDeleteModalBtn.addEventListener('click', function () {
        deleteModal.classList.remove('show');
      });

      // Close Delete Confirmation Modal with Cancel button
      cancelDeleteBtn.addEventListener('click', function () {
        deleteModal.classList.remove('show');
      });

      // Close Delete Confirmation Modal when clicking outside
      window.addEventListener('click', function (event) {
        if (event.target === deleteModal) {
          deleteModal.classList.remove('show');
        }
      });
    });