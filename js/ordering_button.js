document.addEventListener('DOMContentLoaded', function() {
  const orderBtn = document.querySelector('#order-btn');
  const container = document.querySelector('.row.g-4');
  if (!orderBtn || !container) return;

  // Función para ordenar las tarjetas
  function sortCards(order) {
    const cards = Array.from(container.querySelectorAll('.event-card'));
    cards.sort((a, b) => {
      const da = parseInt(a.getAttribute('date')) || 0;
      const db = parseInt(b.getAttribute('date')) || 0;
      return order === 'desc' ? db - da : da - db;
    });
    cards.forEach(c => container.appendChild(c));
  }

  // Función para actualizar el ícono del botón
  function updateButton(order) {
    if (order === 'desc') {
      orderBtn.innerHTML = '<i class="bi bi-sort-down"></i>';
      orderBtn.title = 'Orden descendente';
    } else {
      orderBtn.innerHTML = '<i class="bi bi-sort-up"></i>';
      orderBtn.title = 'Orden ascendente';
    }
  }

  // Inicialización
  let currentOrder = orderBtn.getAttribute('data-order') || 'desc';
  updateButton(currentOrder);
  sortCards(currentOrder);

  // Evento de click
  orderBtn.addEventListener('click', function() {
    currentOrder = currentOrder === 'desc' ? 'asc' : 'desc';
    orderBtn.setAttribute('data-order', currentOrder);
    updateButton(currentOrder);
    sortCards(currentOrder);
  });
});
