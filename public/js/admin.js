// public/js/admin.js
document.addEventListener('DOMContentLoaded', function () {
  /* ====== Dark mode (mesmo storage do site) ====== */
  var root = document.documentElement;
  var storeKey = 'toylab:dark';
  try {
    if (localStorage.getItem(storeKey) === '1') root.classList.add('dark');
  } catch (_) {}
  document.addEventListener('click', function (e) {
    var t = e.target.closest('[data-toggle-darkmode]');
    if (!t) return;
    var on = root.classList.toggle('dark');
    try { localStorage.setItem(storeKey, on ? '1' : '0'); } catch (_) {}
  });

  /* ====== Menu mobile ====== */
  var nav      = document.querySelector('.admin-nav');
  var toggle   = document.querySelector('[data-admin-toggle]');
  var linksBox = document.querySelector('[data-admin-links]');
  var backdrop = document.getElementById('adminBackdrop');
  if (!nav || !toggle || !linksBox) return;

  var mql = window.matchMedia('(min-width:901px)');

  function applyLayout() {
    if (mql.matches) {
      // Desktop: menu SEMPRE visível e fechado
      linksBox.hidden = false;
      nav.classList.remove('open');
      if (backdrop) backdrop.hidden = true;
      document.body.classList.remove('no-scroll');
      toggle.setAttribute('aria-expanded', 'false');
    } else {
      // Mobile: começa fechado
      linksBox.hidden = true;
      nav.classList.remove('open');
      if (backdrop) backdrop.hidden = true;
      document.body.classList.remove('no-scroll');
      toggle.setAttribute('aria-expanded', 'false');
    }
  }

  function openMenu() {
    if (mql.matches) return; // no desktop não abre painel
    nav.classList.add('open');
    linksBox.hidden = false;
    if (backdrop) backdrop.hidden = false;
    document.body.classList.add('no-scroll');
    toggle.setAttribute('aria-expanded', 'true');
  }

  function closeMenu() {
    nav.classList.remove('open');
    linksBox.hidden = true;
    if (backdrop) backdrop.hidden = true;
    document.body.classList.remove('no-scroll');
    toggle.setAttribute('aria-expanded', 'false');
  }

  function toggleMenu() {
    if (mql.matches) return; // no desktop o botão não faz nada
    (linksBox.hidden ? openMenu : closeMenu)();
  }

  // Eventos
  toggle.addEventListener('click', function (e) { e.preventDefault(); toggleMenu(); });
  if (backdrop) backdrop.addEventListener('click', closeMenu);
  linksBox.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', closeMenu); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeMenu(); });

  // Estado inicial + quando muda de tamanho
  applyLayout();
  if (mql.addEventListener) mql.addEventListener('change', applyLayout);
  else mql.addListener(applyLayout);
});
