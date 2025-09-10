<?php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';
require_once __DIR__ . '/../partials/header.php';

$err = [];
$name = trim($_POST['name'] ?? '');
$role = trim($_POST['role'] ?? '');
$lattes = trim($_POST['lattes_url'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_validate($_POST['csrf'] ?? null)) $err[] = 'CSRF inválido.';
  if ($name === '') $err[] = 'Nome é obrigatório.';
  if ($role === '') $err[] = 'Função é obrigatória.';

  if ($lattes !== '') {
    if (!preg_match('~^https?://~i', $lattes)) $lattes = 'https://' . $lattes;
    if (!filter_var($lattes, FILTER_VALIDATE_URL)) $err[] = 'URL Lattes inválida.';
  } else {
    $lattes = null;
  }

  // Foto (opcional)
  $photo = null;
  if (!$err && !empty($_FILES['photo']) && ($_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
    $paths = handle_images_upload('photo');
    if ($paths) $photo = $paths[0];
  }

  if (!$err) {
    save_row('team', [
      'name'       => $name,
      'role'       => $role,
      'photo_path' => $photo,
      'lattes_url' => $lattes,
      'created_at' => now(),
    ]);
    header('Location: '.base_url('admin/team/list.php?ok=Membro%20criado')); exit;
  }
}
?>
<h1 class="fade-in">Novo membro</h1>

<?php if ($err): ?>
  <div class="card"><div class="pad"><ul><?php foreach ($err as $e) echo '<li>'.e($e).'</li>'; ?></ul></div></div><br>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="fade-in">
  <?= csrf_field(); ?>

  <label for="name">Nome</label>
  <input id="name" name="name" class="input" required value="<?= e($name) ?>">

  <label for="role">Função</label>
  <input id="role" name="role" class="input" required value="<?= e($role) ?>">

  <label for="lattes_url">Lattes (opcional)</label>
  <input id="lattes_url" name="lattes_url" class="input" placeholder="https://lattes.cnpq.br/..." value="<?= e($lattes ?? '') ?>">

  <label for="photo">Foto (jpg, png, webp — até 3MB)</label>
  <input id="photo" name="photo" type="file" class="input" accept=".jpg,.jpeg,.png,.webp">

  <p>
    <button class="btn" type="submit">Salvar</button>
    <a class="btn secondary" href="<?= e(base_url('admin/team/list.php')) ?>">Cancelar</a>
  </p>
</form>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
