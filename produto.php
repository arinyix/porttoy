<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/functions.php';

$id  = max(1, (int)($_GET['id'] ?? 0));
$p   = get_product($id);
if (!$p) { http_response_code(404); exit('Produto não encontrado.'); }

$imgs   = get_product_images($id);
$cover  = $imgs[0] ?? null;
$thumbs = $cover ? array_slice($imgs, 1) : [];
require_once __DIR__ . '/templates/header.php';
?>

<section class="detail">
  <div class="gallery">
    <?php if ($cover): ?>
      <?php $coverUrl = media_url($cover['path']); ?>
      <img
        class="cover"
        src="<?= e($coverUrl) ?>"
        alt="<?= e($cover['alt'] ?: $p['title']) ?>"
        loading="lazy"
        data-lightbox-open
        data-src="<?= e($coverUrl) ?>"
      >
      <?php if ($thumbs): ?>
        <div class="thumbs">
          <?php foreach ($thumbs as $im): $u = media_url($im['path']); ?>
            <img
              src="<?= e($u) ?>"
              alt="<?= e($im['alt'] ?: $p['title']) ?>"
              loading="lazy"
              data-lightbox-open
              data-src="<?= e($u) ?>"
            >
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php else: ?>
      <img class="cover" src="<?= e(asset('img/placeholder.png')) ?>" alt="<?= e($p['title']) ?>">
    <?php endif; ?>
  </div>

  <aside class="info">
    <h1><?= e($p['title']) ?></h1>
    <p><?= nl2br(e($p['description'])) ?></p>
    <p><strong>Categoria:</strong> <?= e($p['category_name']) ?><?php if ($p['subcategory_name']): ?> → <?= e($p['subcategory_name']) ?><?php endif; ?></p>
    <p><strong>Status:</strong> <?= e($p['status']) ?></p>
    <p><strong>Cadastrado:</strong> <?= date('d/m/Y', strtotime($p['created_at'])) ?></p>
    <p><a class="btn" href="<?= e(base_url()) ?>">Voltar</a></p>
  </aside>
</section>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
