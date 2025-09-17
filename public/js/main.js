// public/js/main.js


/* ========== NAV HAMBURGUER ========== */
(() => {
  const nav      = document.querySelector('[data-nav]');
  if (!nav) return;

  const toggle   = nav.querySelector('[data-nav-toggle]');
  const menu     = nav.querySelector('#primary-menu');
  const backdrop = document.querySelector('[data-nav-backdrop]');

  const open = () => {
    nav.classList.add('open');
    toggle?.setAttribute('aria-expanded', 'true');
    document.body.classList.add('no-scroll');
    if (backdrop) backdrop.style.display = 'block';
  };
  const close = () => {
    nav.classList.remove('open');
    toggle?.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('no-scroll');
    if (backdrop) backdrop.style.display = 'none';
  };

  toggle?.addEventListener('click', () => {
    nav.classList.contains('open') ? close() : open();
  });
  backdrop?.addEventListener('click', close);
  menu?.addEventListener('click', (e) => {
    if (e.target.closest('a')) close();        // fecha ao clicar num link
  });

  // Se redimensionar para desktop, garante fechamento
  const mq = window.matchMedia('(min-width: 721px)');
  (mq.addEventListener ? mq.addEventListener : mq.addListener).call(mq, 'change', (e) => { if (e.matches) close(); });
})();

// Filtros, busca, lightbox, toasts, infinito + skeletons
(function () {
  const grid = document.querySelector('[data-products-grid]');
  if (!grid) return;

  const $ = (s) => document.querySelector(s);
  let offset = parseInt(grid.getAttribute('data-offset') || '0', 10);
  const limit = parseInt(grid.getAttribute('data-limit') || '12', 10);
  let loading = false, done = false;

  // Lê estado atual (URL + input)
  function getState() {
    const u = new URL(location.href);
    return {
      category:    u.searchParams.get('category') || '',
      subcategory: u.searchParams.get('subcategory') || '',
      q:           $('[data-search]')?.value?.trim() || ''
    };
  }

  // Atualiza querystring sem recarregar
  function setQS(patch) {
    const u = new URL(location.href);
    Object.entries(patch).forEach(([k, v]) => {
      if (v === '' || v == null) u.searchParams.delete(k);
      else u.searchParams.set(k, v);
    });
    history.replaceState({}, '', u.toString());
  }

  // Cria um "batch" de skeletons que dá pra remover de uma vez
  function skeleton(n = 6) {
    const wrap = document.createElement('div');
    wrap.setAttribute('data-skel-batch', '');
    for (let i = 0; i < n; i++) {
      const div = document.createElement('div');
      div.className = 'card skeleton';
      div.innerHTML = `
        <div class="ph-img"></div>
        <div class="pad">
          <div class="ph-line w-70"></div>
          <div class="ph-line w-40"></div>
        </div>`;
      wrap.appendChild(div);
    }
    return wrap;
  }

  async function loadMore(initial = false) {
    if (loading || done) return;
    loading = true;

    const sk = skeleton(initial ? 8 : 4);
    grid.appendChild(sk);

    const st = getState();
    // Usa a PRÓPRIA URL da página, sem hardcode de /toylab
    const url = new URL(window.location.href);
    url.searchParams.set('partial', 'products');
    url.searchParams.set('offset', String(offset));
    url.searchParams.set('limit', String(limit));
    if (st.category)    url.searchParams.set('category', st.category);    else url.searchParams.delete('category');
    if (st.subcategory) url.searchParams.set('subcategory', st.subcategory); else url.searchParams.delete('subcategory');
    if (st.q)           url.searchParams.set('q', st.q);                  else url.searchParams.delete('q');

    try {
      const res = await fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      const html = await res.text();

      // remove TODO o batch de skeletons
      sk.remove();

      const tmp = document.createElement('div');
      tmp.innerHTML = html.trim();
      const nodes = Array.from(tmp.children);

      if (nodes.length === 0) {
        if (initial && offset === 0) {
          grid.innerHTML = '<div class="card"><div class="pad">Nenhum produto disponível.</div></div>';
        }
        done = true;
        return;
      }

      nodes.forEach(n => grid.appendChild(n));
      offset += nodes.length; // incrementa pelo que realmente veio
    } catch (e) {
      console.error(e);
      sk.remove();
      if (initial && offset === 0) {
        grid.innerHTML = '<div class="card"><div class="pad">Erro ao carregar. Tente novamente.</div></div>';
      }
      done = true;
    } finally {
      loading = false;
    }
  }

  // Sentinel do scroll infinito
  const sentinel = document.createElement('div');
  sentinel.setAttribute('data-sentinel', '');
  grid.after(sentinel);
  const io = new IntersectionObserver((entries) => {
    if (entries.some(e => e.isIntersecting)) loadMore();
  }, { rootMargin: '600px' });
  io.observe(sentinel);

  // Filtros (categoria / subcategoria)
  document.addEventListener('click', (e) => {
    const b = e.target.closest('[data-filter]');
    if (!b) return;

    const { type, value } = b.dataset;
    const qsPatch = {};
    if (type === 'category') {
      qsPatch.category = value;
      qsPatch.subcategory = ''; // reset sub
    } else if (type === 'subcategory') {
      qsPatch.subcategory = value;
    }
    setQS(qsPatch);

    // Reinicia listagem
    grid.innerHTML = '';
    offset = 0; done = false;
    loadMore(true);
  });

  // Busca (debounce) + mantém na URL
  const search = $('[data-search]');
  if (search) {
    let t;
    search.addEventListener('input', () => {
      clearTimeout(t);
      t = setTimeout(() => {
        setQS({ q: search.value.trim() });
        grid.innerHTML = '';
        offset = 0; done = false;
        loadMore(true);
      }, 300);
    });
  }

  // Inicial
  loadMore(true);

  /* ---------- Lightbox acessível ---------- */
  const lb = document.querySelector('[data-lightbox]');
  if (lb) {
    const lbImg = lb.querySelector('img');
    const lbClose = lb.querySelector('[data-lb-close]');
    const close = () => { lb.classList.remove('open'); lb.setAttribute('aria-hidden', 'true'); lbClose.blur(); };
    document.addEventListener('click', (e) => {
      const t = e.target.closest('[data-lightbox-open]');
      if (t) {
        lbImg.src = t.getAttribute('data-src') || t.src;
        lb.classList.add('open');
        lb.setAttribute('aria-hidden', 'false');
        lbClose.focus();
      }
      if (e.target === lb || e.target === lbClose) close();
    });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
  }
})();
