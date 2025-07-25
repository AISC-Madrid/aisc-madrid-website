  document.addEventListener('DOMContentLoaded', function () {
(() => {
      'use strict';
      const forms = document.querySelectorAll('.needs-validation');

      Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }
          form.classList.add('was-validated');
        }, false);
      });
    })();
  const params = new URLSearchParams(window.location.search);
  const error = params.get('error');
  if (error) {
    let message = '';
    switch (error) {
      case 'validation':
        message = 'Por favor, completa todos los campos correctamente.';
        break;
      case 'duplicate':
        message = 'Este correo ya está registrado.';
        break;
      case 'insert':
        message = 'Hubo un error al guardar tus datos. Inténtalo de nuevo.';
        break;
      case 'connection':
        message = 'No se pudo conectar a la base de datos.';
        break;
      default:
        message = 'Ha ocurrido un error inesperado.';
    }

    const formSection = document.getElementById('form-error');
    if (formSection) {
      const alertDiv = document.createElement('div');
      alertDiv.className = 'alert alert-danger';
      alertDiv.innerText = message;
      formSection.prepend(alertDiv);
    }

    // Eliminar el parámetro de la URL después de mostrarlo
    window.history.replaceState({}, document.title, window.location.pathname + '#get-involved');
  }


// Event filter script 
  const buttons = document.querySelectorAll('.event-filter-btn');
  const pastEvents = document.querySelectorAll('.event-past');
  const futureEvents = document.querySelectorAll('.event-future');

  // Mostrar futuros por defecto
  pastEvents.forEach(e => e.style.display = 'none');
  futureEvents.forEach(e => e.style.display = 'block');
  buttons[0].classList.add('active'); // El primero activo por defecto

  buttons[0].addEventListener('click', () => {
    // Mostrar futuros
    pastEvents.forEach(e => e.style.display = 'none');
    futureEvents.forEach(e => e.style.display = 'block');
    // Cambiar botón activo
    buttons.forEach(b => b.classList.remove('active'));
    buttons[0].classList.add('active');
  });

  buttons[1].addEventListener('click', () => {
    // Mostrar pasados
    pastEvents.forEach(e => e.style.display = 'block');
    futureEvents.forEach(e => e.style.display = 'none');
    // Cambiar botón activo
    buttons.forEach(b => b.classList.remove('active'));
    buttons[1].classList.add('active');
  });




// Language of the page script 
  const languageOptions = document.querySelectorAll('.language-option');
  const languageBtn = document.getElementById('languageDropdown');

  languageOptions.forEach(option => {
    option.addEventListener('click', (e) => {
      e.preventDefault();
      const selectedLang = option.dataset.lang.toUpperCase();
      languageBtn.textContent = selectedLang;

      //Change language of the page
      document.querySelectorAll('[data-en]').forEach(el => {
        el.textContent = el.dataset[selectedLang.toLowerCase()];
      });

      ///Store selected language in localStorage
      localStorage.setItem('lang', selectedLang.toLowerCase());

      const dropdown = bootstrap.Dropdown.getOrCreateInstance(languageBtn);
dropdown.hide();
    });
  });

  //Load saved language from localStorage if exists
  const savedLang = localStorage.getItem('lang');
  if (savedLang) {
    document.querySelector(`[data-lang="${savedLang}"]`)?.click();
  }






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