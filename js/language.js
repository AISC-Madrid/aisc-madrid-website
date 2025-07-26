document.addEventListener('DOMContentLoaded', function () {
    // Language of the page script 
    const languageOptions = document.querySelectorAll('.language-option');
    const languageBtn = document.getElementById('languageDropdown');

    const articleId = "content/events/event1/article1"; // Cambia esto si tienes más de un artículo

    function loadArticle(articleId, lang) {
        fetch(`${articleId}.${lang}.json`)
            .then(response => {
                if (!response.ok) throw new Error("Error loading article");
                return response.json();
            })
            .then(data => {
                const titleEl = document.getElementById("article-title");
                const bodyEl = document.getElementById("article-body");
                const dateEl = document.getElementById("article-date");
                if (titleEl) titleEl.textContent = data.title || "Título no disponible";
                if (bodyEl) bodyEl.innerHTML = data.body || "Contenido no disponible"; 
                if (dateEl) dateEl.innerHTML = data.date || "Fecha no disponible";
            })
            .catch(error => {
                console.error("Error loading article:", error);
            });
    }

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

            // Load article from JSON
            loadArticle(articleId, selectedLang.toLowerCase());

            // Store selected language
            localStorage.setItem('lang', selectedLang.toLowerCase());

        });
    });

    // Load saved language from localStorage on first load
    const savedLang = localStorage.getItem('lang');
    if (savedLang) {
        document.querySelector(`[data-lang="${savedLang}"]`)?.click();
        loadArticle(articleId, savedLang);
    } else {
        loadArticle(articleId, "en"); // Idioma por defecto si no hay nada guardado
    }
});
