<?php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';
require_once __DIR__ . '/../partials/header.php';

$id = (int)($_GET['id'] ?? 0);
$post = fetch_one("SELECT * FROM posts WHERE id=?", [$id]);
if (!$post) { echo '<div class="card"><div class="pad">Post não encontrado.</div></div>'; require_once __DIR__.'/../partials/footer.php'; exit; }

$err = [];
$title   = trim($_POST['title']   ?? $post['title']);
$excerpt = trim($_POST['excerpt'] ?? $post['excerpt']);
$content = trim($_POST['content'] ?? $post['content']);

// valor para o input datetime-local (YYYY-MM-DDTHH:MM)
$pubLocal = $post['published_at'] ? date('Y-m-d\TH:i', strtotime($post['published_at'])) : '';
$published_at = trim($_POST['published_at'] ?? $pubLocal);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_validate($_POST['csrf'] ?? null)) $err[] = 'CSRF inválido.';
  if ($title === '') $err[] = 'Título é obrigatório.';

  $pubAt = null;
  if ($published_at !== '') {
    $dt = DateTime::createFromFormat('Y-m-d\TH:i', $published_at);
    if ($dt === false) $err[] = 'Data/hora de publicação inválida.';
    else $pubAt = $dt->format('Y-m-d H:i:s');
  }

  if ($excerpt === '' && $content !== '') {
    $tmp = trim(preg_replace('/\s+/', ' ', strip_tags($content)));
    $excerpt = mb_substr($tmp, 0, 160) . (mb_strlen($tmp) > 160 ? '…' : '');
  }

  $cover_path = $post['cover_path'];

  if (isset($_POST['remove_cover']) && $_POST['remove_cover'] === '1') {
    if ($cover_path && str_starts_with($cover_path, 'public/uploads/')) {
      @unlink(BASE_PATH . '/' . $cover_path);
    }
    $cover_path = null;
  }

  if (!$err && !empty($_FILES['cover']) && ($_FILES['cover']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
    $paths = handle_images_upload('cover');
    if ($paths) {
      if ($cover_path && str_starts_with($cover_path, 'public/uploads/')) {
        @unlink(BASE_PATH . '/' . $cover_path);
      }
      $cover_path = $paths[0];
    }
  }

  if (!$err) {
    $slug = $post['slug'] ?: (slugify($title) . '-' . substr(bin2hex(random_bytes(2)), 0, 4));
    save_row('posts', [
      'title'       => $title,
      'slug'        => $slug,
      'excerpt'     => $excerpt,
      'content'     => $content,
      'cover_path'  => $cover_path,
      'published_at'=> $pubAt,
    ], $id);

    header('Location: '.base_url('admin/posts/list.php?ok=Post%20atualizado'));
    exit;
  }
}
?>
<style>
.admin-form .preview{display:flex;gap:12px;align-items:center;flex-wrap:wrap}
.admin-form .form-actions{display:flex;gap:8px;flex-wrap:wrap}
@media (max-width:820px){
  .admin-form .form-row{grid-template-columns:1fr!important}
  .admin-form .form-actions{flex-direction:column}
  .admin-form .form-actions .btn{width:100%;justify-content:center}
}
</style>

<h1 class="fade-in">Editar post #<?= (int)$id ?></h1>

<?php if ($err): ?>
  <div class="card"><div class="pad"><ul><?php foreach ($err as $e) echo '<li>'.e($e).'</li>'; ?></ul></div></div><br>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="admin-form fade-in">
  <?= csrf_field(); ?>

  <label for="title">Título</label>
  <input id="title" name="title" class="input" required value="<?= e($title) ?>">

  <label for="excerpt">Resumo (opcional)</label>
  <textarea id="excerpt" name="excerpt" class="input" rows="3"><?= e($excerpt) ?></textarea>

  <label for="content">Conteúdo</label>
  <textarea id="content" name="content" class="input" rows="10"><?= e($content) ?></textarea>

  <div class="form-row">
    <div>
      <label for="published_at">Publicar em (opcional)</label>
      <input id="published_at" name="published_at" type="datetime-local" class="input" value="<?= e($published_at) ?>">
    </div>
    <div>
      <label for="cover">Capa (opcional)</label>
      <input id="cover" name="cover" type="file" class="input" accept=".jpg,.jpeg,.png,.webp">
    </div>
  </div>

  <label>Capa atual</label>
  <div class="card"><div class="pad">
    <div class="preview">
      <?php if (!empty($post['cover_path'])): ?>
        <img src="<?= e(base_url($post['cover_path'])) ?>" alt="Capa atual" style="width:160px;height:90px;object-fit:cover;border-radius:8px;border:1px solid #e7e7e7;background:#fff">
        <label><input type="checkbox" name="remove_cover" value="1"> Remover capa</label>
      <?php else: ?>
        <em>Sem capa</em>
      <?php endif; ?>
    </div>
  </div></div>

  <p class="form-actions">
    <button class="btn" type="submit">Salvar</button>
    <a class="btn secondary" href="<?= e(base_url('admin/posts/list.php')) ?>">Voltar</a>
  </p>
</form>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
