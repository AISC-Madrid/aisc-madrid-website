document.addEventListener('DOMContentLoaded', function () { 
    'use strict';
    
    // ------------------------------------------
    // 1. BOOTSTRAP FORM VALIDATION (Se mantiene)
    // ------------------------------------------
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

    
    // ------------------------------------------
    // 2. PROJECT ORDERING BUTTON (Botón de Ordenación)
    // ------------------------------------------
    const orderBtn = document.querySelector('#order-btn');
    const container = document.querySelector('.project-group.row.g-4');
    
    // Si no existen, salimos para evitar errores.
    if (!orderBtn || !container) return;

    const projectCards = document.querySelectorAll('.project-card'); // Elementos a filtrar y ordenar
    
    // Función para ordenar las tarjetas
    function sortCards(order) {
        const cards = Array.from(container.querySelectorAll('.project-card'));
        cards.sort((a, b) => {
            const da = parseInt(a.getAttribute('date')) || 0;
            const db = parseInt(b.getAttribute('date')) || 0;
            // Retorna db - da para descendente, da - db para ascendente
            return order === 'desc' ? db - da : da - db;
        });
        cards.forEach(c => container.appendChild(c));
    }

    // Función para actualizar el ícono del botón
    function updateOrderButton(order) {
        // Asegúrate de tener los iconos de Bootstrap (`bi-sort-down`, `bi-sort-up`) disponibles
        if (order === 'desc') {
            orderBtn.innerHTML = '<i class="bi bi-sort-down"></i>';
            orderBtn.title = 'Orden descendente';
        } else {
            orderBtn.innerHTML = '<i class="bi bi-sort-up"></i>';
            orderBtn.title = 'Orden ascendente';
        }
    }

    // Inicialización del orden
    let currentOrder = orderBtn.getAttribute('data-order') || 'desc';
    updateOrderButton(currentOrder);
    sortCards(currentOrder);

    // Evento de click para ordenar
    orderBtn.addEventListener('click', function() {
        currentOrder = currentOrder === 'desc' ? 'asc' : 'desc';
        orderBtn.setAttribute('data-order', currentOrder);
        updateOrderButton(currentOrder);
        sortCards(currentOrder);
    });


    // ------------------------------------------
    // 3. PROJECT CATEGORY FILTERING (Filtro por Categoría)
    // ------------------------------------------

    const filterButtons = document.querySelectorAll('.filter-btn');
    // 'projectCards' ya está definido en la Sección 2

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // 1. Desactivar todos los botones de categoría y activar el actual
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            // 2. Obtener la categoría a filtrar
            const categoryToFilter = this.getAttribute('data-filter');

            // 3. Iterar sobre todas las tarjetas de proyecto para aplicar el filtro
            projectCards.forEach(card => {
                const cardClasses = card.classList;

                if (categoryToFilter === 'all') {
                    // Si es 'Todas', eliminamos la clase 'hidden' para mostrar la tarjeta
                    card.classList.remove('hidden'); 
                } else {
                    // La clase de la categoría a buscar es 'cat-SLUG'
                    const requiredClass = 'cat-' + categoryToFilter;
                    
                    if (cardClasses.contains(requiredClass)) {
                        // Si coincide, eliminamos 'hidden'
                        card.classList.remove('hidden'); 
                    } else {
                        // Si no coincide, añadimos 'hidden'
                        card.classList.add('hidden'); 
                    }
                }
            });
            // Opcional: Reaplicar la ordenación después de filtrar para mantener el orden de la vista
            sortCards(currentOrder); 
        });
    });

});