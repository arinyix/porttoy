<?php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';
require_once __DIR__ . '/../partials/header.php';

$err = [];
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$event_date = trim($_POST['event_date'] ?? ''); // yyyy-mm-dd

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_validate($_POST['csrf'] ?? null)) $err[] = 'CSRF inválido.';
  if ($title === '') $err[] = 'Título é obrigatório.';
  // valida data
  $dt = DateTime::createFromFormat('Y-m-d', $event_date);
  if (!$dt || $dt->format('Y-m-d') !== $event_date) $err[] = 'Data inválida.';

  if (!$err) {
    save_row('milestones', [
      'title'       => $title,
      'description' => $description,
      'event_date'  => $event_date,
      'created_at'  => now(),
    ]);
    header('Location: '.base_url('admin/milestones/list.php?ok=Marco%20criado'));
    exit;
  }
}
?>
<h1 class="fade-in">Novo marco</h1>

<?php if ($err): ?>
  <div class="card"><div class="pad"><ul><?php foreach ($err as $e) echo '<li>'.e($e).'</li>'; ?></ul></div></div><br>
<?php endif; ?>

<form method="post" class="fade-in">
  <?= csrf_field(); ?>

  <label for="title">Título</label>
  <input id="title" name="title" class="input" required value="<?= e($title) ?>">

  <label for="event_date">Data do evento</label>
  <input id="event_date" name="event_date" class="input" type="date" required value="<?= e($event_date) ?>">

  <label for="description">Descrição</label>
  <textarea id="description" name="description" class="input" rows="6" placeholder="Resumo do marco (opcional)"><?= e($description) ?></textarea>

  <p>
    <button class="btn" type="submit">Salvar</button>
    <a class="btn secondary" href="<?= e(base_url('admin/milestones/list.php')) ?>">Cancelar</a>
  </p>
</form>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
