<?php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';
require_once __DIR__ . '/../partials/header.php';

$id = (int)($_GET['id'] ?? 0);
$partner = fetch_one("SELECT * FROM partners WHERE id=?", [$id]);
if (!$partner) { echo '<div class="card"><div class="pad">Parceria não encontrada.</div></div>'; require_once __DIR__.'/../partials/footer.php'; exit; }

$err = [];
$name = trim($_POST['name'] ?? $partner['name']);
$url  = trim($_POST['url']  ?? $partner['url']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_validate($_POST['csrf'] ?? null)) $err[] = 'CSRF inválido.';
  if ($name === '') $err[] = 'Nome é obrigatório.';

  if ($url !== '') {
    if (!preg_match('~^https?://~i', $url)) $url = 'https://' . $url;
    if (!filter_var($url, FILTER_VALIDATE_URL)) $err[] = 'URL inválida.';
  } else {
    $url = null;
  }

  $logo_path = $partner['logo_path'];

  // Remover logo atual
  if (isset($_POST['remove_logo']) && $_POST['remove_logo'] === '1') {
    if ($logo_path && str_starts_with($logo_path, 'public/uploads/')) {
      @unlink(BASE_PATH . '/' . $logo_path); // ignora falhas silenciosamente
    }
    $logo_path = null;
  }

  // Substituir por logo nova (se enviada)
  if (!$err && !empty($_FILES['logo']) && ($_FILES['logo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
    $paths = handle_images_upload('logo');
    if ($paths) {
      if ($logo_path && str_starts_with($logo_path, 'public/uploads/')) {
        @unlink(BASE_PATH . '/' . $logo_path);
      }
      $logo_path = $paths[0];
    }
  }

  if (!$err) {
    save_row('partners', [
      'name'      => $name,
      'url'       => $url,
      'logo_path' => $logo_path,
    ], $id);
    header('Location: '.base_url('admin/partners/list.php?ok=Parceria%20atualizada')); exit;
  }
}
?>
<h1 class="fade-in">Editar parceria #<?= (int)$id ?></h1>

<?php if ($err): ?>
  <div class="card"><div class="pad"><ul><?php foreach ($err as $e) echo '<li>'.e($e).'</li>'; ?></ul></div></div><br>
<?php endif; ?>

<form method="post" class="fade-in" enctype="multipart/form-data">
  <?= csrf_field(); ?>

  <label for="name">Nome</label>
  <input id="name" name="name" class="input" required value="<?= e($name) ?>">

  <label for="url">URL (opcional)</label>
  <input id="url" name="url" class="input" value="<?= e($url) ?>">

  <label>Logo atual</label>
  <div class="card"><div class="pad">
    <?php if (!empty($partner['logo_path'])): ?>
      <img src="<?= e(base_url($partner['logo_path'])) ?>" alt="Logo atual" style="width:120px;height:60px;object-fit:contain;background:#fff;border:1px solid #e7e7e7;border-radius:8px;padding:6px">
      <div><label><input type="checkbox" name="remove_logo" value="1"> Remover logo</label></div>
    <?php else: ?>
      <em>Sem logo</em>
    <?php endif; ?>
  </div></div>

  <label for="logo">Enviar nova logo (opcional)</label>
  <input id="logo" name="logo" class="input" type="file" accept=".jpg,.jpeg,.png,.webp">

  <p>
    <button class="btn" type="submit">Salvar</button>
    <a class="btn secondary" href="<?= e(base_url('admin/partners/list.php')) ?>">Voltar</a>
  </p>
</form>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
