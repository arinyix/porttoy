<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/functions.php';

/* --------- filtros da URL --------- */
$category    = isset($_GET['category'])    ? (int)$_GET['category']    : null;
$subcategory = isset($_GET['subcategory']) ? (int)$_GET['subcategory'] : null;
$q           = isset($_GET['q'])           ? trim((string)$_GET['q'])   : null;

/* --------- ENDPOINT PARCIAL (antes do header!) --------- */
if (isset($_GET['partial']) && $_GET['partial'] === 'products') {
    $limit  = max(1, min(24, (int)($_GET['limit']  ?? 12)));
    $offset = max(0,        (int)($_GET['offset'] ?? 0));
    $rows = get_products($limit, $offset, $category, $subcategory, $q, 'disponivel');
    foreach ($rows as $p) { include __DIR__ . '/templates/components/product-card.php'; }
    exit; // <-- importantíssimo
}

/* --------- página completa a partir daqui --------- */
require_once __DIR__ . '/templates/header.php';

$perPage  = 12;
$products = get_products($perPage, 0, $category, $subcategory, $q, 'disponivel');
?>
<section class="hero">
  <div>
    <h1>ToyLab • UFOPA</h1>
    <p>Laboratório Aberto de Prototipagem e Inovação do Tapajós — projetos de
      <strong>Impressão 3D</strong>, <strong>Corte a Laser</strong>, <strong>Jogos</strong> e <strong>Protótipos</strong>.
    </p>
    <div class="cta">
      <a class="btn" href="<?= e(base_url('contato.php')) ?>">Fale Conosco</a>
    </div>
  </div>
  <img src="<?= e(asset('img/placeholder.png')) ?>" alt="Logo/Arte ToyLab" style="width:100%; border-radius: 16px;">
</section>

<?php include __DIR__ . '/templates/components/filter-bar.php'; ?>

<section
  id="grid-products"
  class="grid"
  data-products-grid
  data-limit="<?= (int)$perPage ?>"
  data-offset="<?= (int)count($products) ?>"
  data-category="<?= $category !== null ? (int)$category : '' ?>"
  data-subcategory="<?= $subcategory !== null ? (int)$subcategory : '' ?>"
  data-q="<?= e($q ?? '') ?>"
>
  <?php if ($products): ?>
    <?php foreach ($products as $p) { include __DIR__ . '/templates/components/product-card.php'; } ?>
  <?php else: ?>
    <div class="card"><div class="pad">Nenhum produto disponível.</div></div>
  <?php endif; ?>
</section>

<section class="grid">
  <header style="grid-column:1/-1"><h2>Em Desenvolvimento</h2></header>
  <?php
  $devs = fetch_all(
    "SELECT p.*, c.name AS category_name, sc.name AS subcategory_name
     FROM products p
     LEFT JOIN categories c  ON c.id  = p.category_id
     LEFT JOIN categories sc ON sc.id = p.subcategory_id
     WHERE p.status = 'em_desenvolvimento'
     ORDER BY p.created_at DESC
     LIMIT 6"
  );
  foreach ($devs as $p) include __DIR__ . '/templates/components/product-card.php';
  ?>
</section>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
