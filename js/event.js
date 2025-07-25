document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(button => {
    button.addEventListener('click', () => {
        const target = document.querySelector(button.getAttribute('data-bs-target'));
        const showText = button.querySelector('.show-map, .show-stream');
        const hideText = button.querySelector('.hide-map, .hide-stream');
        if (target.classList.contains('show')) {
            showText.classList.remove('d-none');
            hideText.classList.add('d-none');
        } else {
            showText.classList.add('d-none');
            hideText.classList.remove('d-none');
        }
    });
});
