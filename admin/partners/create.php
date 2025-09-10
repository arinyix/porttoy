<?php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';
require_once __DIR__ . '/../partials/header.php';

$err = [];
$name = trim($_POST['name'] ?? '');
$url  = trim($_POST['url']  ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_validate($_POST['csrf'] ?? null)) $err[] = 'CSRF inválido.';
  if ($name === '') $err[] = 'Nome é obrigatório.';

  // Normaliza URL (aceita em branco)
  if ($url !== '') {
    if (!preg_match('~^https?://~i', $url)) $url = 'https://' . $url;
    if (!filter_var($url, FILTER_VALIDATE_URL)) $err[] = 'URL inválida.';
  }

  $logoPath = null;
  if (!$err && !empty($_FILES['logo']) && ($_FILES['logo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
    $paths = handle_images_upload('logo');
    if ($paths) $logoPath = $paths[0];
  }

  if (!$err) {
    save_row('partners', [
      'name'       => $name,
      'logo_path'  => $logoPath,
      'url'        => $url,
      'created_at' => now(),
    ]);
    header('Location: '.base_url('admin/partners/list.php?ok=Parceria%20criada')); exit;
  }
}
?>
<h1 class="fade-in">Nova parceria</h1>

<?php if ($err): ?>
  <div class="card"><div class="pad"><ul><?php foreach ($err as $e) echo '<li>'.e($e).'</li>'; ?></ul></div></div><br>
<?php endif; ?>

<form method="post" class="fade-in" enctype="multipart/form-data">
  <?= csrf_field(); ?>

  <label for="name">Nome</label>
  <input id="name" name="name" class="input" required value="<?= e($name) ?>">

  <label for="url">URL (opcional)</label>
  <input id="url" name="url" class="input" placeholder="https://exemplo.com" value="<?= e($url) ?>">

  <label for="logo">Logo (jpg, png, webp — até 3MB)</label>
  <input id="logo" name="logo" class="input" type="file" accept=".jpg,.jpeg,.png,.webp">

  <p>
    <button class="btn" type="submit">Salvar</button
