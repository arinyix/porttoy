<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/functions.php';
require_once __DIR__ . '/templates/header.php';

$posts = fetch_all("SELECT id, title, slug, excerpt, cover_path, published_at
                    FROM posts ORDER BY published_at DESC");
?>
<section class="grid">
  <header style="grid-column:1/-1"><h1>Notícias</h1></header>

  <?php foreach ($posts as $p): ?>
    <?php
      $rel = $p['cover_path'] ?: 'img/news-placeholder.png'; // relativo a /public
      $src = is_file(media_fs($rel)) ? media_url($rel) : asset('img/news-placeholder.png');
    ?>
    <article class="card">
      <a href="<?= e(base_url('noticia.php?id='.(int)$p['id'])) ?>">
        <img loading="lazy" src="<?= e($src) ?>" alt="<?= e($p['title']) ?>"
             style="width:100%;height:200px;object-fit:cover;display:block;">
      </a>
      <div class="pad">
        <h3 style="margin:0 0 8px">
          <a href="<?= e(base_url('noticia.php?id='.(int)$p['id'])) ?>"><?= e($p['title']) ?></a>
        </h3>
        <div class="muted" style="margin-bottom:8px;">
          <?= e(date('d/m/Y', strtotime($p['published_at'] ?? $p['created_at'] ?? now()))) ?>
        </div>
        <p><?= e($p['excerpt']) ?></p>
      </div>
    </article>
  <?php endforeach; ?>
</section>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
