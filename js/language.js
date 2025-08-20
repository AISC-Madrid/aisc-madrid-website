function decodeHtml(html) {
    const txt = document.createElement("textarea");
    txt.innerHTML = html;
    return txt.value;
}

document.addEventListener('DOMContentLoaded', function () {
    const languageOptions = document.querySelectorAll('.language-option');
    const languageBtn = document.getElementById('languageDropdown');

    const changeLanguage = (lang) => {
        if (languageBtn) languageBtn.textContent = lang.toUpperCase();
        document.querySelectorAll('[data-en]').forEach(el => {
            const html = el.dataset[lang]; // e.g., "&lt;strong&gt;Hello&lt;/strong&gt;"
            if (html) el.innerHTML = html; // now <strong> works
        });
        localStorage.setItem('lang', lang);
    };

    languageOptions.forEach(option => {
        option.addEventListener('click', e => {
            e.preventDefault();
            changeLanguage(option.dataset.lang.toLowerCase());
        });
    });

    const savedLang = localStorage.getItem('lang');
    if (savedLang) changeLanguage(savedLang);
});
