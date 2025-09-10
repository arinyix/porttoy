<?php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';
require_once __DIR__ . '/../partials/header.php';

$parents = fetch_all("SELECT id, name FROM categories WHERE parent_id IS NULL ORDER BY name");
$err = []; $name = trim($_POST['name'] ?? ''); $parent_id = (int)($_POST['parent_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_validate($_POST['csrf'] ?? null)) $err[] = 'CSRF inválido.';
  if ($name === '') $err[] = 'Nome é obrigatório.';

  if (!$err) {
    save_row('categories', [
      'name' => $name,
      'parent_id' => $parent_id ?: null
    ]);
    header('Location: ' . base_url('admin/categories/list.php?ok=Categoria%20criada')); exit;
  }
}
?>
<h1 class="fade-in">Nova categoria</h1>

<?php if ($err): ?>
  <div class="card"><div class="pad"><ul><?php foreach($err as $e) echo '<li>'.e($e).'</li>'; ?></ul></div></div><br>
<?php endif; ?>

<form method="post" class="fade-in">
  <?= csrf_field(); ?>

  <label for="name">Nome</label>
  <input id="name" name="name" class="input" required value="<?= e($name) ?>">

  <label for="parent_id">Categoria pai (opcional)</label>
  <select id="parent_id" name="parent_id" class="input">
    <option value="">— Nenhuma (nível superior)</option>
    <?php foreach($parents as $p): ?>
      <option value="<?= (int)$p['id'] ?>" <?= $parent_id===(int)$p['id']?'selected':'' ?>>
        <?= e($p['name']) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <p>
    <button class="btn" type="submit">Salvar</button>
    <a class="btn secondary" href="<?= e(base_url('admin/categories/list.php')) ?>">Cancelar</a>
  </p>
</form>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
