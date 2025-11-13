document.addEventListener('DOMContentLoaded', function () { 
    'use strict';

    
    // ------------------------------------------
    // 1. BOOTSTRAP FORM VALIDATION (Keep as is)
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
    // 2. PROJECT FILTERING (Refactored)
    // ------------------------------------------

    const filterButtons = document.querySelectorAll('.project-filter-btn');
    
    // Map status keys (from data-filter) to their corresponding DOM elements
    // NOTE: This targets the individual project items (e.g., the <div> containing the card)
    const projectsMap = {
        'wish': document.querySelectorAll('.project-wish'),
        'current': document.querySelectorAll('.project-current'),
        'finished': document.querySelectorAll('.project-finished'),
        'paused': document.querySelectorAll('.project-paused'),
    };
    
    // Helper function to hide/show groups and set active button
    const applyFilter = (filterKey) => {
        let activeIndex = -1;

        // Loop through all project groups
        Object.keys(projectsMap).forEach((key, index) => {
            const displayStyle = (key === filterKey) ? 'block' : 'none';
            projectsMap[key].forEach(element => {
                element.style.display = displayStyle;
            });
            
            // Identify the index of the current filter button for styling
            if (key === filterKey) {
                // Find the index of the button corresponding to the filterKey
                filterButtons.forEach((btn, idx) => {
                    if (btn.dataset.filter === filterKey) {
                        activeIndex = idx;
                    }
                });
            }
        });
        
        // Update active button styling
        filterButtons.forEach(b => b.classList.remove('active'));
        if (activeIndex !== -1) {
            filterButtons[activeIndex].classList.add('active');
        }
    };
    
    // Initialize: Show 'current' projects by default
    applyFilter('current'); 
    
    // Attach single event listener to all buttons
    filterButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const filterKey = btn.dataset.filter; // Get the 'data-filter' attribute value (e.g., 'wish', 'current')
            applyFilter(filterKey);
        });
    });


    // ------------------------------------------
    // 3. PROJECT ORDERING BUTTON
    // ------------------------------------------
    const orderBtn = document.querySelector('#order-btn');
    const container = document.querySelector('.row.g-4');
    if (!orderBtn || !container) return;

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
