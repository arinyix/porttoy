<?php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';
require_once __DIR__ . '/../partials/header.php';

$err = [];
$title = trim($_POST['title'] ?? '');
$excerpt = trim($_POST['excerpt'] ?? '');
$content = trim($_POST['content'] ?? '');
$published_at = trim($_POST['published_at'] ?? ''); // yyyy-mm-ddTHH:MM

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_validate($_POST['csrf'] ?? null)) $err[] = 'CSRF inválido.';
  if ($title === '') $err[] = 'Título é obrigatório.';

  // Normaliza published_at
  $pubAt = null;
  if ($published_at !== '') {
    // converte do input datetime-local (Y-m-d\TH:i) para Y-m-d H:i:s
    $dt = DateTime::createFromFormat('Y-m-d\TH:i', $published_at);
    if ($dt === false) $err[] = 'Data/hora de publicação inválida.';
    else $pubAt = $dt->format('Y-m-d H:i:s');
  }

  // Excerpt automático se vazio
  if ($excerpt === '' && $content !== '') {
    $tmp = trim(preg_replace('/\s+/', ' ', strip_tags($content)));
    $excerpt = mb_substr($tmp, 0, 160) . (mb_strlen($tmp) > 160 ? '…' : '');
  }

  // Upload da capa (opcional)
  $coverPath = null;
  if (!$err && !empty($_FILES['cover']) && ($_FILES['cover']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
    $paths = handle_images_upload('cover');
    if ($paths) $coverPath = $paths[0];
  }

  if (!$err) {
    $slug = slugify($title) . '-' . substr(bin2hex(random_bytes(2)), 0, 4); // minimiza colisão
    save_row('posts', [
      'title'       => $title,
      'slug'        => $slug,
      'excerpt'     => $excerpt,
      'content'     => $content,
      'cover_path'  => $coverPath,
      'published_at'=> $pubAt,
      'created_at'  => now(),
    ]);
    header('Location: '.base_url('admin/posts/list.php?ok=Post%20criado'));
    exit;
  }
}
?>
<h1 class="fade-in">Novo post</h1>

<?php if ($err): ?>
  <div class="card"><div class="pad"><ul><?php foreach ($err as $e) echo '<li>'.e($e).'</li>'; ?></ul></div></div><br>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="fade-in">
  <?= csrf_field(); ?>

  <label for="title">Título</label>
  <input id="title" name="title" class="input" required value="<?= e($title) ?>">

  <label for="excerpt">Resumo (opcional)</label>
  <textarea id="excerpt" name="excerpt" class="input" rows="3" placeholder="até ~160 caracteres"><?= e($excerpt) ?></textarea>

  <label for="content">Conteúdo</label>
  <textarea id="content" name="content" class="input" rows="10" placeholder="HTML básico permitido"><?= e($content) ?></textarea>

  <div class="form-row">
    <div>
      <label for="published_at">Publicar em (opcional)</label>
      <input id="published_at" name="published_at" type="datetime-local" class="input" value="<?= e($published_at) ?>">
      <small style="color:var(--muted)">Se vazio, o post fica como rascunho.</small>
    </div>
    <div>
      <label for="cover">Capa (jpg, png, webp — até 3MB)</label>
      <input id="cover" name="cover" type="file" class="input" accept=".jpg,.jpeg,.png,.webp">
    </div>
  </div>

  <p>
    <button class="btn" type="submit">Salvar</button>
    <a class="btn secondary" href="<?= e(base_url('admin/posts/list.php')) ?>">Cancelar</a>
  </p>
</form>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
