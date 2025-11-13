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

  /*
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
  */
 
});