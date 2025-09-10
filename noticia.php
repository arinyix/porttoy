<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/functions.php';
require_once __DIR__ . '/templates/header.php';

$id = max(1, (int)($_GET['id'] ?? 0));
$p = fetch_one("SELECT * FROM posts WHERE id=?", [$id]);
if (!$p) { echo '<section class="grid"><div class="card"><div class="pad">Post não encontrado.</div></div></section>'; require_once __DIR__.'/templates/footer.php'; exit; }

$rel = $p['cover_path'] ?: 'img/news-placeholder.png';
$src = is_file(media_fs($rel)) ? media_url($rel) : asset('img/news-placeholder.png');
?>
<section class="hero">
  <div>
    <h1><?= e($p['title']) ?></h1>
    <p class="muted"><?= e(date('d/m/Y', strtotime($p['published_at'] ?? $p['created_at'] ?? now()))) ?></p>
  </div>
</section>

<section class="grid">
  <article class="card" style="grid-column:1/-1;">
    <img src="<?= e($src) ?>" alt="<?= e($p['title']) ?>"
         style="width:100%;max-height:420px;object-fit:cover;display:block;">
    <div class="pad">
      <p><?= nl2br(e($p['content'])) ?></p>
    </div>
  </article>
</section>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
