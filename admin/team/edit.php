<?php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';
require_once __DIR__ . '/../partials/header.php';

$id = (int)($_GET['id'] ?? 0);
$member = fetch_one("SELECT * FROM team WHERE id=?", [$id]);
if (!$member) { echo '<div class="card"><div class="pad">Membro não encontrado.</div></div>'; require_once __DIR__.'/../partials/footer.php'; exit; }

$err = [];
$name   = trim($_POST['name']  ?? $member['name']);
$role   = trim($_POST['role']  ?? $member['role']);
$lattes = trim($_POST['lattes_url'] ?? ($member['lattes_url'] ?? ''));

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

  $photo_path = $member['photo_path'];

  // Remover foto atual?
  if (isset($_POST['remove_photo']) && $_POST['remove_photo'] === '1') {
    if ($photo_path && str_starts_with($photo_path, 'public/uploads/')) {
      @unlink(BASE_PATH . '/' . $photo_path);
    }
    $photo_path = null;
  }

  // Substituir por nova foto?
  if (!$err && !empty($_FILES['photo']) && ($_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
    $paths = handle_images_upload('photo');
    if ($paths) {
      if ($photo_path && str_starts_with($photo_path, 'public/uploads/')) {
        @unlink(BASE_PATH . '/' . $photo_path);
      }
      $photo_path = $paths[0];
    }
  }

  if (!$err) {
    save_row('team', [
      'name'       => $name,
      'role'       => $role,
      'lattes_url' => $lattes,
      'photo_path' => $photo_path,
    ], $id);
    header('Location: '.base_url('admin/team/list.php?ok=Membro%20atualizado')); exit;
  }
}
?>
<style>
/* responsividade local do form (não afeta outras telas) */
.admin-form .preview{display:flex;gap:12px;align-items:center;flex-wrap:wrap}
.admin-form .form-actions{display:flex;gap:8px;flex-wrap:wrap}
@media (max-width:820px){
  .admin-form .form-actions{flex-direction:column}
  .admin-form .form-actions .btn{width:100%;justify-content:center}
}
</style>

<h1 class="fade-in">Editar membro #<?= (int)$id ?></h1>

<?php if ($err): ?>
  <div class="card"><div class="pad"><ul><?php foreach ($err as $e) echo '<li>'.e($e).'</li>'; ?></ul></div></div><br>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="admin-form fade-in">
  <?= csrf_field(); ?>

  <label for="name">Nome</label>
  <input id="name" name="name" class="input" required value="<?= e($name) ?>">

  <label for="role">Função</label>
  <input id="role" name="role" class="input" required value="<?= e($role) ?>">

  <label for="lattes_url">Lattes (opcional)</label>
  <input id="lattes_url" name="lattes_url" class="input" value="<?= e($lattes ?? '') ?>">

  <label>Foto atual</label>
  <div class="card"><div class="pad">
    <div class="preview">
      <?php if (!empty($member['photo_path'])): ?>
        <img src="<?= e(base_url($member['photo_path'])) ?>" alt="Foto atual"
             style="width:96px;height:96px;object-fit:cover;border-radius:12px;border:1px solid #e7e7e7;background:#fff">
        <label><input type="checkbox" name="remove_photo" value="1"> Remover foto</label>
      <?php else: ?>
        <em>Sem foto</em>
      <?php endif; ?>
    </div>
  </div></div>

  <label for="photo">Enviar nova foto (opcional)</label>
  <input id="photo" name="photo" type="file" class="input" accept=".jpg,.jpeg,.png,.webp">

  <p class="form-actions">
    <button class="btn" type="submit">Salvar</button>
    <a class="btn secondary" href="<?= e(base_url('admin/team/list.php')) ?>">Voltar</a>
  </p>
</form>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
