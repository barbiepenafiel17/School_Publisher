    // Modal functions
    function openUserModal(userId, name, email, role, status, date, profilePicture) {
      document.getElementById('modalUserName').textContent = name;
      document.getElementById('modalUserEmail').textContent = email;
      document.getElementById('modalUserDate').textContent = date;

      // Set role with appropriate styling
      const roleElement = document.getElementById('modalUserRole');
      roleElement.textContent = role.toUpperCase();
      roleElement.className = 'role-badge ' + role.toLowerCase();

      // Set status with appropriate styling
      const statusElement = document.getElementById('modalUserStatus');
      statusElement.textContent = status.charAt(0).toUpperCase() + status.slice(1);
      statusElement.className = 'status-badge ' + status.toLowerCase();

      // Handle profile picture
      const avatarImg = document.getElementById('modalUserAvatar');
      const defaultAvatar = document.getElementById('modalUserDefaultAvatar');

      if (profilePicture && profilePicture !== 'null' && profilePicture !== '') {
        avatarImg.src = profilePicture;
        avatarImg.style.display = 'block';
        defaultAvatar.style.display = 'none';
      } else {
        avatarImg.style.display = 'none';
        defaultAvatar.style.display = 'block';
      }

      // Set edit link
      document.getElementById('editUserLink').href = 'admin_dashboards/view_user.php?user_id=' + userId;

      // Show modal
      document.getElementById('userModal').classList.add('show');
    }

    function closeUserModal() {
      document.getElementById('userModal').classList.remove('show');
    }

    // Close modal if clicked outside
    window.addEventListener('click', function (event) {
      const modal = document.getElementById('userModal');
      if (event.target === modal) {
        closeUserModal();
      }
    });
  