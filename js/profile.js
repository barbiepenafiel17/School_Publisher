/**
 * Profile Page JavaScript
 * Handles profile page specific functionality
 */

document.addEventListener('DOMContentLoaded', function() {
  // Profile modal functionality is handled inline in the profile.php file
  
  // Functions for profile image preview if needed
  function previewProfileImage(input, previewElement) {
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function(e) {
        document.querySelector(previewElement).src = e.target.result;
      };
      reader.readAsDataURL(input.files[0]);
    }
  }
  
  // Apply preview functionality to any file inputs if needed
  const profileInputs = document.querySelectorAll('input[type="file"][name="profile_picture"]');
  profileInputs.forEach(input => {
    input.addEventListener('change', function() {
      // Find the closest image element to preview the selected file
      const previewImg = this.closest('form').parentElement.querySelector('img');
      if (previewImg) {
        previewProfileImage(this, '#' + previewImg.id);
      }
    });
  });
});