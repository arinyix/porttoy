<?php
/** @var array $p */

$imgs   = get_product_images((int)$p['id']);
$srcRel = $imgs[0]['path'] ?? 'public/img/placeholder.webp';   // ex.: public/uploads/abc.webp

// defesa: se o arquivo não existir no FS, usa placeholder
$fsPath = BASE_PATH . '/' . ltrim($srcRel, '/');
if (!is_file($fsPath)) {
  $srcRel = 'public/img/placeholder.webp';
}

$src = base_url($srcRel);  // <<< SEM HARDCODE
?>
<article class="card" data-category="<?= (int)$p['category_id'] ?>" data-subcategory="<?= (int)($p['subcategory_id'] ?? 0) ?>">
  <a href="<?= e(base_url('produto.php?id='.(int)$p['id'])) ?>" class="block" data-lightbox-open>
    <img loading="lazy" src="<?= e($src) ?>" alt="<?= e($p['title']) ?>">
  </a>
  <div class="pad">
    <h3 style="margin:0 0 6px">
      <a href="<?= e(base_url('produto.php?id='.(int)$p['id'])) ?>"><?= e($p['title']) ?></a>
    </h3>
    <div>
      <?php if (!empty($p['category_name'])): ?><span class="tag"><?= e($p['category_name']) ?></span><?php endif; ?>
      <?php if (!empty($p['subcategory_name'])): ?><span class="tag"><?= e($p['subcategory_name']) ?></span><?php endif; ?>
      <?php if ($p['status']==='em_desenvolvimento'): ?><div class="status-dev">EM DESENVOLVIMENTO</div><?php endif; ?>
    </div>
  </div>
</article>
