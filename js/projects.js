document.addEventListener('DOMContentLoaded', function () { 
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

  // Event filter script 
  // Project category filter: handles showing/hiding projects by status (wish, current, finished, paused) when filter buttons are clicked
  const filterButtons = document.querySelectorAll('.project-filter-btn');
  const wishProjects = document.querySelectorAll('.project-wish');
  const currentProjects = document.querySelectorAll('.project-current');
  const finishedProjects = document.querySelectorAll('.project-finished');
  const pausedProjects = document.querySelectorAll('.project-paused');
  // Mostrar current por defecto
  wishProjects.forEach(e => e.style.display = 'none');
  finishedProjects.forEach(e => e.style.display = 'none');
  pausedProjects.forEach(e => e.style.display = 'none');
  currentProjects.forEach(e => e.style.display = 'block');
  filterButtons[1].classList.add('active'); // El primero activo por defecto

  filterButtons[0].addEventListener('click', () => {
    // Mostrar wish
    wishProjects.forEach(e => e.style.display = 'block');
    currentProjects.forEach(e => e.style.display = 'none');
    finishedProjects.forEach(e => e.style.display = 'none');
    pausedProjects.forEach(e => e.style.display = 'none');
    // Cambiar botón activo
    filterButtons.forEach(b => b.classList.remove('active'));
    filterButtons[0].classList.add('active');
  });

  filterButtons[1].addEventListener('click', () => {
    // Mostrar current
    wishProjects.forEach(e => e.style.display = 'none');
    currentProjects.forEach(e => e.style.display = 'block');
    finishedProjects.forEach(e => e.style.display = 'none');
    pausedProjects.forEach(e => e.style.display = 'none');
    // Cambiar botón activo
    filterButtons.forEach(b => b.classList.remove('active'));
    filterButtons[1].classList.add('active');
  });

  
  filterButtons[2].addEventListener('click', () => {
    // Mostrar finished
    wishProjects.forEach(e => e.style.display = 'none');
    currentProjects.forEach(e => e.style.display = 'none');
    finishedProjects.forEach(e => e.style.display = 'block');
    pausedProjects.forEach(e => e.style.display = 'none');
    // Cambiar botón activo
    filterButtons.forEach(b => b.classList.remove('active'));
    filterButtons[2].classList.add('active');
  });

  
  filterButtons[3].addEventListener('click', () => {
    // Mostrar paused
    wishProjects.forEach(e => e.style.display = 'none');
    currentProjects.forEach(e => e.style.display = 'none');
    finishedProjects.forEach(e => e.style.display = 'none');
    pausedProjects.forEach(e => e.style.display = 'block');
    // Cambiar botón activo
    filterButtons.forEach(b => b.classList.remove('active'));
    filterButtons[3].classList.add('active');
  });
});