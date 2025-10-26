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
    
    // Map status names to their corresponding element NodeLists
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
            
            // Track the index of the active button for styling
            if (key === filterKey) {
                activeIndex = index;
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
});
