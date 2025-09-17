<?php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';
require_once __DIR__ . '/../partials/header.php';

$id = (int)($_GET['id'] ?? 0);
$mil = fetch_one("SELECT * FROM milestones WHERE id=?", [$id]);
if (!$mil) { echo '<div class="card"><div class="pad">Marco não encontrado.</div></div>'; require_once __DIR__.'/../partials/footer.php'; exit; }

$err = [];
$title = trim($_POST['title'] ?? $mil['title']);
$description = trim($_POST['description'] ?? $mil['description']);
$event_date = trim($_POST['event_date'] ?? $mil['event_date']); // Y-m-d

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_validate($_POST['csrf'] ?? null)) $err[] = 'CSRF inválido.';
  if ($title === '') $err[] = 'Título é obrigatório.';
  $dt = DateTime::createFromFormat('Y-m-d', $event_date);
  if (!$dt || $dt->format('Y-m-d') !== $event_date) $err[] = 'Data inválida.';

  if (!$err) {
    save_row('milestones', [
      'title'       => $title,
      'description' => $description,
      'event_date'  => $event_date,
    ], $id);
    header('Location: '.base_url('admin/milestones/list.php?ok=Marco%20atualizado'));
    exit;
  }
}
?>
<style>
.admin-form .form-actions{display:flex;gap:8px;flex-wrap:wrap}
@media (max-width:820px){
  .admin-form .form-actions{flex-direction:column}
  .admin-form .form-actions .btn{width:100%;justify-content:center}
}
</style>

<h1 class="fade-in">Editar marco #<?= (int)$id ?></h1>

<?php if ($err): ?>
  <div class="card"><div class="pad"><ul><?php foreach ($err as $e) echo '<li>'.e($e).'</li>'; ?></ul></div></div><br>
<?php endif; ?>

<form method="post" class="admin-form fade-in">
  <?= csrf_field(); ?>

  <label for="title">Título</label>
  <input id="title" name="title" class="input" required value="<?= e($title) ?>">

  <label for="event_date">Data do evento</label>
  <input id="event_date" name="event_date" class="input" type="date" required value="<?= e(substr($event_date,0,10)) ?>">

  <label for="description">Descrição</label>
  <textarea id="description" name="description" class="input" rows="6"><?= e($description) ?></textarea>

  <p class="form-actions">
    <button class="btn" type="submit">Salvar</button>
    <a class="btn secondary" href="<?= e(base_url('admin/milestones/list.php')) ?>">Voltar</a>
  </p>
</form>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
