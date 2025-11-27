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
    // INICIALIZACIÓN DE ELEMENTOS GLOBALES
    // ------------------------------------------
    const container = document.querySelector('.project-group.row.g-4');
    const projectCards = document.querySelectorAll('.project-card');
    const orderBtn = document.querySelector('#order-btn');

    // Salir si no hay proyectos o contenedor (lo cual es crítico para los filtros)
    if (!container || projectCards.length === 0) return;
    
    
    // ------------------------------------------
    // 2. PROJECT ORDERING BUTTON (Botón de Ordenación)
    // ------------------------------------------

    // Función para ordenar las tarjetas
    function sortCards(order) {
        const cards = Array.from(container.querySelectorAll('.project-card'));
        cards.sort((a, b) => {
            const da = parseInt(a.getAttribute('date')) || 0;
            const db = parseInt(b.getAttribute('date')) || 0;
            return order === 'desc' ? db - da : da - db;
        });
        cards.forEach(c => container.appendChild(c));
    }

    // Función para actualizar el ícono del botón
    function updateOrderButton(order) {
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
    // 3. PROJECT CATEGORY FILTERING (Filtro por Categoría con Dropdown)
    // ------------------------------------------

    const filterButtons = document.querySelectorAll('.filter-btn');
    const categoryFilterDropdownBtn = document.querySelector('#categoryFilterDropdown');

    filterButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            
            e.preventDefault(); 
            
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            const categoryToFilter = this.getAttribute('data-filter');
            const selectedText = this.textContent.trim();
            
            categoryFilterDropdownBtn.textContent = selectedText;

            projectCards.forEach(card => {
                const cardClasses = card.classList;
                const requiredClass = 'cat-' + categoryToFilter;
                
                // Check if the card should be visible
                const isMatch = (categoryToFilter === 'all' || cardClasses.contains(requiredClass));

                if (isMatch) {
                    // Si debe mostrarse:
                    card.classList.remove('hidden');
                    card.style.display = 'flex'; 
                } else {
                    // Si debe ocultarse:
                    card.classList.add('hidden');
                    card.style.display = 'flex'; 
                }
            });
            
            // Mantenemos la ordenación
            sortCards(currentOrder); 
        });
    });

});