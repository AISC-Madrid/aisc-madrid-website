document.querySelectorAll('textarea[maxlength]').forEach(textarea => {
  const max = textarea.maxLength;

  const charCount = textarea.parentElement.querySelector('.form-text');

  charCount.textContent = `${textarea.value.length} / ${max} characters`;

  // Update count on input
  textarea.addEventListener('input', () => {
    charCount.textContent = `${textarea.value.length} / ${max} characters`;
  });
});
