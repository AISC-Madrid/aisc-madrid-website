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
            const html = el.dataset[lang]; // e.g., "<strong>Hello</strong>"
            if (html) el.innerHTML = decodeHtml(html); // now <strong> works
        });

        // note: we can only translate leaf nodes. So for elements whose children aren't just 
        // text, the text to be translated in <span> and the span should include the 
        // translation key instead
        // eg:
        // <ol translation-key="title"> List title 
        //   <li translation-key="elem"> List element </li>
        // ...
        // </ol>
        // should be:
        // <ol> <span translation-key="title"> List title </span>
        //   <li translation-key="elem"> List element </li>
        // ...
        // </ol>
        fetch(`/translations/${lang}.json`)
            .then(res => {
                if (res.ok) return res.json();
                throw new Error(`Translations for ${lang} not found or failed to load`);
            })
            .then(translations => {
                document.querySelectorAll('[translation-key]').forEach(el => {
                    const key = el.getAttribute('translation-key');
                    if (translations && translations[key]) {
                        el.innerHTML = decodeHtml(translations[key]); // <strong> works here too
                    }
                });
            })
            .catch(err => console.warn('Translation warning:', err));

        localStorage.setItem('lang', lang);
    };

    languageOptions.forEach(option => {
        option.addEventListener('click', e => {
            e.preventDefault();
            changeLanguage(option.dataset.lang.toLowerCase());
        });
    });

    // Default to 'es' if no language is saved
    if (!localStorage.getItem('lang')) {
        localStorage.setItem('lang', 'es');
    }
    changeLanguage(localStorage.getItem('lang'));
});
