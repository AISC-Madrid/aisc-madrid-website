
document.addEventListener('DOMContentLoaded', function () {
    // Language of the page script 
    const languageOptions = document.querySelectorAll('.language-option');
    const languageBtn = document.getElementById('languageDropdown');

    languageOptions.forEach(option => {
        option.addEventListener('click', (e) => {
            e.preventDefault();
            const selectedLang = option.dataset.lang.toUpperCase();
            languageBtn.textContent = selectedLang;
            console.log("Selected language:", selectedLang);

            // Change text based on selected language
            document.querySelectorAll('[data-en]').forEach(el => {
                el.textContent = el.dataset[selectedLang.toLowerCase()];
            });

            // Change placeholders
            const nameInput = document.getElementById("name");
            const emailInput = document.getElementById("email");
            if (nameInput) nameInput.placeholder = nameInput.dataset[selectedLang.toLowerCase()];
            if (emailInput) emailInput.placeholder = emailInput.dataset[selectedLang.toLowerCase()];

            // Store selected language
            localStorage.setItem('lang', selectedLang.toLowerCase());

            // Close dropdown if exists
            const dropdown = bootstrap.Dropdown.getOrCreateInstance(languageBtn);
            dropdown?.hide();
        });
    });

    // Load saved language from localStorage on first load
    const savedLang = localStorage.getItem('lang');
    if (savedLang) {
        document.querySelector(`[data-lang="${savedLang}"]`)?.click();
    }
});
