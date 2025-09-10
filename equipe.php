<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/functions.php';
require_once __DIR__ . '/templates/header.php';

$team = fetch_all("SELECT * FROM team ORDER BY created_at DESC");
?>
<section class="grid">
  <header style="grid-column:1/-1"><h1>Quem Somos</h1></header>

  <?php foreach ($team as $m): ?>
    <?php
      $rel = $m['photo_path'] ?: 'img/avatar-placeholder.png';
      $src = is_file(media_fs($rel)) ? media_url($rel) : asset('img/avatar-placeholder.png');
    ?>
    <article class="card">
      <img loading="lazy" src="<?= e($src) ?>" alt="<?= e($m['name']) ?>"
           style="width:100%;height:200px;object-fit:cover;display:block;">
      <div class="pad">
        <h3 style="margin:0 0 4px;"><?= e($m['name']) ?></h3>
        <p style="margin:0;color:var(--muted)"><?= e($m['role']) ?></p>
        <?php if (!empty($m['lattes_url'])): ?>
          <p style="margin-top:8px;"><a href="<?= e($m['lattes_url']) ?>" target="_blank" rel="noopener">Currículo Lattes</a></p>
        <?php endif; ?>
      </div>
    </article>
  <?php endforeach; ?>
</section>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
