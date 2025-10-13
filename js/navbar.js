document.addEventListener('DOMContentLoaded', function () {
  const navbarCollapse = document.getElementById('navbarNav');
  const navbarToggler = document.querySelector('.navbar-toggler');
  const languageDropdown = document.getElementById('languageDropdown');
  const languageMenu = languageDropdown?.nextElementSibling;

  // 1. Evitar que hacer clic en el botón o menú de idioma cierre el navbar
  [languageDropdown, ...document.querySelectorAll('.language-option')].forEach(el => {
    el.addEventListener('click', function (e) {
      e.stopPropagation(); // Evita que se propague y cierre el menú
    });
  });

  // 2. Cerrar navbar al hacer clic fuera (excepto si fue en el botón del idioma o su menú)
  document.addEventListener('click', function (event) {
    const isOpen = navbarCollapse.classList.contains('show');
    const clickedInsideNavbar = navbarCollapse.contains(event.target);
    const clickedToggle = navbarToggler.contains(event.target);
    const clickedLanguage = languageDropdown.contains(event.target) || languageMenu.contains(event.target);

    if (isOpen && !clickedInsideNavbar && !clickedToggle && !clickedLanguage) {
      const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
      bsCollapse?.hide();
    }
  });

  // 3. Cerrar navbar al hacer clic en enlaces normales (excepto los de idioma)
  navbarCollapse.querySelectorAll('a.nav-link, a.btn').forEach(link => {
    link.addEventListener('click', (e) => {
      if (
        e.target.closest('.dropdown-menu') ||
        e.target.closest('#languageDropdown')
      ) return;

      const isOpen = navbarCollapse.classList.contains('show');
      if (isOpen) {
        const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
        bsCollapse?.hide();
      }
    });
  });
});