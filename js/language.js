function decodeHtml(html) {
    const txt = document.createElement("textarea");
    txt.innerHTML = html;
    return txt.value;
}

document.addEventListener('DOMContentLoaded', function () {
    const languageOptions = document.querySelectorAll('.language-option');
    const languageBtn = document.getElementById('languageDropdown');

    // Helper functions for cookies
    function setCookie(name, value, days) {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }

    function getCookie(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for(let i=0;i < ca.length;i++) {
            let c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    }

    const changeLanguage = (lang) => {
        if (languageBtn) languageBtn.textContent = lang.toUpperCase();
        document.querySelectorAll('[data-en]').forEach(el => {
            const html = el.dataset[lang]; // e.g., "&lt;strong&gt;Hello&lt;/strong&gt;"
            if (html) el.innerHTML = decodeHtml(html); // now <strong> works
        });
        localStorage.setItem('lang', lang);
        setCookie('lang', lang, 365);
    };

    languageOptions.forEach(option => {
        option.addEventListener('click', e => {
            e.preventDefault();
            changeLanguage(option.dataset.lang.toLowerCase());
        });
    });

    const savedLang = localStorage.getItem('lang') || getCookie('lang') || 'es';
    changeLanguage(savedLang);
});
