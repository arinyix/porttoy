<?php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';
require_once __DIR__ . '/../partials/header.php';

$id = (int)($_GET['id'] ?? 0);
$cat = fetch_one("SELECT * FROM categories WHERE id=?", [$id]);
if (!$cat) { echo '<div class="card"><div class="pad">Categoria não encontrada.</div></div>'; require_once __DIR__.'/../partials/footer.php'; exit; }

$parents = fetch_all("SELECT id, name FROM categories WHERE parent_id IS NULL AND id <> ? ORDER BY name", [$id]);

$err = [];
$name = trim($_POST['name'] ?? $cat['name']);
$parent_id = (int)($_POST['parent_id'] ?? ($cat['parent_id'] ?? 0));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_validate($_POST['csrf'] ?? null)) $err[] = 'CSRF inválido.';
  if ($name === '') $err[] = 'Nome é obrigatório.';
  if ($parent_id === $id) $err[] = 'Uma categoria não pode ser pai de si mesma.';

  if (!$err) {
    save_row('categories', [
      'name' => $name,
      'parent_id' => $parent_id ?: null
    ], $id);
    header('Location: ' . base_url('admin/categories/list.php?ok=Categoria%20atualizada')); exit;
  }
}
?>
<style>
/* responsivo local do form */
.admin-form .form-actions{display:flex;gap:8px;flex-wrap:wrap}
@media (max-width:820px){
  .admin-form .form-actions{flex-direction:column}
  .admin-form .form-actions .btn{width:100%;justify-content:center}
}
</style>

<h1 class="fade-in">Editar categoria #<?= (int)$id ?></h1>

<?php if ($err): ?>
  <div class="card"><div class="pad"><ul><?php foreach($err as $e) echo '<li>'.e($e).'</li>'; ?></ul></div></div><br>
<?php endif; ?>

<form method="post" class="admin-form fade-in">
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

  <p class="form-actions">
    <button class="btn" type="submit">Salvar</button>
    <a class="btn secondary" href="<?= e(base_url('admin/categories/list.php')) ?>">Voltar</a>
  </p>
</form>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
