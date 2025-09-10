// public/js/darkmode.js
(function () {
  const html = document.documentElement;
  const key = 'toylab_dark';
  function apply(state) {
    if (state) html.classList.add('dark');
    else html.classList.remove('dark');
  }
  const saved = localStorage.getItem(key);
  apply(saved === '1');

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-toggle-darkmode]');
    if (!btn) return;
    const newState = !html.classList.contains('dark');
    apply(newState);
    localStorage.setItem(key, newState ? '1' : '0');
  }, { passive: true });
})();
