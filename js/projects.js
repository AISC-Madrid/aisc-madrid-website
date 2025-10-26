document.addEventListener('DOMContentLoaded', function () {
  const buttons = document.querySelectorAll('.project-filter-btn');
  const groups = {
    wish: document.querySelector('.project-group.wish'),
    current: document.querySelector('.project-group.current'),
    finished: document.querySelector('.project-group.finished'),
    paused: document.querySelector('.project-group.paused'),
  };

  const showGroup = (filter) => {
    Object.keys(groups).forEach(key => {
      const el = groups[key];
      if (!el) return;
      el.style.display = (filter === key) ? 'block' : 'none';
    });
  };

  // default, show all or a specific group
  showGroup('wish'); // or 'all' with small tweak
  buttons.forEach(btn => {
    btn.addEventListener('click', () => {
      const filter = btn.dataset.filter;
      showGroup(filter);
      buttons.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
    });
  });
}); 