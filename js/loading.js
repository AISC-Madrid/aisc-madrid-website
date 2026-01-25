    // Handle form submission to show loading overlay
    document.querySelector('form[action="processing/recruiting.php"]').addEventListener('submit', function () {
      document.getElementById('loadingOverlay').style.display = 'flex';
    });