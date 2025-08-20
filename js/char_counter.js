  const reasonInput = document.getElementById('reason');
  const charCount = document.getElementById('char-count');

  // Initialize character count on page load
  charCount.textContent = reasonInput.value.length + ' / 1000 characters';

  reasonInput.addEventListener('input', () => {
    const len = reasonInput.value.length;
    charCount.textContent = len + ' / 1000 characters';
  });