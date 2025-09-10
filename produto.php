<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/functions.php';
$id = max(1, (int)($_GET['id'] ?? 0));
$p = get_product($id);
if (!$p) { http_response_code(404); exit('Produto não encontrado.'); }
$imgs = get_product_images($id);
require_once __DIR__ . '/templates/header.php';
?>
<section class="grid" style="grid-template-columns: 1fr 1fr;">
  <div>
    <?php if ($imgs): ?>
      <?php foreach ($imgs as $im): ?>
        <img src="<?= e('/toylab/' . $im['path']) ?>" alt="<?= e($im['alt'] ?: $p['title']) ?>" loading="lazy" style="margin-bottom:10px; border-radius:12px;" data-lightbox-open data-src="<?= e('/toylab/' . $im['path']) ?>">
      <?php endforeach; ?>
    <?php else: ?>
      <img src="<?= e(asset('img/placeholder.png')) ?>" alt="<?= e($p['title']) ?>">
    <?php endif; ?>
  </div>
  <div>
    <h1><?= e($p['title']) ?></h1>
    <p><?= nl2br(e($p['description'])) ?></p>
    <p><strong>Categoria:</strong> <?= e($p['category_name']) ?> <?php if($p['subcategory_name']): ?>→ <?= e($p['subcategory_name']) ?><?php endif; ?></p>
    <p><strong>Status:</strong> <?= e($p['status']) ?></p>
    <p><strong>Cadastrado:</strong> <?= date('d/m/Y', strtotime($p['created_at'])) ?></p>
    <?php if (!empty($p['slug'])): ?>
      <p><a class="btn" href="<?= e(base_url()) ?>">Voltar</a></p>
    <?php endif; ?>
  </div>
</section>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
