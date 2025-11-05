document.addEventListener("DOMContentLoaded", () => {
  const buttons = Array.from(document.querySelectorAll('.filters button'));
  const cards = Array.from(document.querySelectorAll('[data-type]'));
  const activeFilters = new Set();

  if (!buttons.length) return;

  buttons.forEach(btn => {
    btn.dataset.filter = (btn.dataset.filter || '').toLowerCase();
    if (btn.classList.contains('active')) activeFilters.add(btn.dataset.filter);
  });

  cards.forEach(c => {
    if (c.dataset.type) c.dataset.type = c.dataset.type.toLowerCase();
  });

  const getBtnByFilter = (f) => buttons.find(b => b.dataset.filter === f);

  function applyFilters() {
    if (activeFilters.size === 0 || activeFilters.has('all')) {
      cards.forEach(c => c.style.display = '');
      return;
    }

    cards.forEach(card => {
      const types = (card.dataset.type || '').split(/\s+/).filter(Boolean);
      const visible = Array.from(activeFilters).some(f => types.includes(f));
      card.style.display = visible ? '' : 'none';
    });
  }

  function handleAllPressed(allBtn) {
    buttons.forEach(b => {
      if (b !== allBtn) {
        b.classList.remove('active');
        activeFilters.delete(b.dataset.filter);
      }
    });
    allBtn.classList.add('active');
    activeFilters.clear();
    activeFilters.add('all');
  }

  buttons.forEach(btn => {
    btn.addEventListener('click', () => {
      const filter = btn.dataset.filter;

      if (filter === 'all') {
        handleAllPressed(btn);
        applyFilters();
        return;
      }

      if (btn.classList.contains('active')) {
        btn.classList.remove('active');
        activeFilters.delete(filter);
      } else {
        btn.classList.add('active');
        activeFilters.add(filter);
      }

      const allBtn = getBtnByFilter('all');
      const anySpecificActive = buttons
        .filter(b => b.dataset.filter !== 'all')
        .some(b => b.classList.contains('active'));

      if (anySpecificActive) {
        if (allBtn) {
          allBtn.classList.remove('active');
          activeFilters.delete('all');
        }
      } else {
        if (allBtn) {
          allBtn.classList.add('active');
          activeFilters.clear();
          activeFilters.add('all');
        }
      }

      applyFilters();
    });
  });

  if (activeFilters.size === 0) {
    const ab = getBtnByFilter('all');
    if (ab) {
      ab.classList.add('active');
      activeFilters.clear();
      activeFilters.add('all');
    }
  }
});
