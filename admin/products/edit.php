<?php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';
require_once __DIR__ . '/../partials/header.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { echo '<div class="card"><div class="pad">ID inválido.</div></div>'; require_once __DIR__.'/../partials/footer.php'; exit; }

$p = fetch_one("SELECT * FROM products WHERE id = ?", [$id]);
if (!$p) { echo '<div class="card"><div class="pad">Produto não encontrado.</div></div>'; require_once __DIR__.'/../partials/footer.php'; exit; }

$cats    = get_top_categories();
$subcats = fetch_all("SELECT id, name FROM categories WHERE parent_id IS NOT NULL ORDER BY name ASC");

$err = [];

if (is_post()) {
  if (!csrf_validate($_POST['csrf'] ?? null)) $err[]='CSRF inválido.';

  $title          = trim($_POST['title'] ?? '');
  $description    = trim($_POST['description'] ?? '');
  $category_id    = (int)($_POST['category_id'] ?? 0);
  $subcategory_id = (int)($_POST['subcategory_id'] ?? 0);
  $status         = in_array($_POST['status'] ?? '', ['disponivel','em_desenvolvimento'], true) ? $_POST['status'] : 'disponivel';
  $featured       = !empty($_POST['featured']) ? 1 : 0;

  if ($title==='')    $err[]='Título obrigatório.';
  if (!$category_id)  $err[]='Categoria obrigatória.';

  if (!$err) {
    $slug = $p['slug'] ?: slugify($title);

    save_row('products', [
      'title'          => $title,
      'slug'           => $slug,
      'description'    => $description,
      'category_id'    => $category_id,
      'subcategory_id' => $subcategory_id ?: null,
      'status'         => $status,
      'featured'       => $featured,
      'updated_at'     => now(),
    ], $id);

    header('Location: ' . base_url('admin/products/list.php'));
    exit;
  }
}
?>
<style>
.admin-form .form-actions{display:flex;gap:8px;flex-wrap:wrap}
@media (max-width:820px){
  .admin-form .form-row{grid-template-columns:1fr!important}
  .admin-form .form-actions{flex-direction:column}
  .admin-form .form-actions .btn{width:100%;justify-content:center}
}
</style>

<h1 class="fade-in">Editar Produto #<?= (int)$id ?></h1>

<?php if ($err): ?>
  <div class="card"><div class="pad">
    <strong>Erros:</strong>
    <ul><?php foreach($err as $e) echo '<li>'.e($e).'</li>'; ?></ul>
  </div></div>
<?php endif; ?>

<form method="post" class="admin-form fade-in">
  <?= csrf_field(); ?>

  <label for="title">Título</label>
  <input class="input" id="title" name="title" required value="<?= e($_POST['title'] ?? $p['title']) ?>">

  <label for="description">Descrição</label>
  <textarea class="input" id="description" name="description" rows="6"><?= e($_POST['description'] ?? $p['description']) ?></textarea>

  <div class="form-row">
    <div>
      <label for="category_id">Categoria</label>
      <select id="category_id" name="category_id" required>
        <?php foreach ($cats as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= ((int)($_POST['category_id'] ?? $p['category_id']) === (int)$c['id']) ? 'selected' : '' ?>>
            <?= e($c['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label for="subcategory_id">Subcategoria</label>
      <select id="subcategory_id" name="subcategory_id">
        <option value="">—</option>
        <?php foreach ($subcats as $s): ?>
          <option value="<?= (int)$s['id'] ?>" <?= ((int)($_POST['subcategory_id'] ?? ($p['subcategory_id'] ?? 0)) === (int)$s['id']) ? 'selected' : '' ?>>
            <?= e($s['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="form-row">
    <div>
      <label for="status">Status</label>
      <select id="status" name="status">
        <option value="disponivel" <?= ($_POST['status'] ?? $p['status']) === 'disponivel' ? 'selected' : '' ?>>Disponível</option>
        <option value="em_desenvolvimento" <?= ($_POST['status'] ?? $p['status']) === 'em_desenvolvimento' ? 'selected' : '' ?>>Em desenvolvimento</option>
      </select>
    </div>
    <div>
      <label>&nbsp;</label>
      <label><input type="checkbox" name="featured" value="1" <?= !empty($_POST) ? (!empty($_POST['featured'])?'checked':'') : ($p['featured']?'checked':'') ?>> Destaque</label>
    </div>
  </div>

  <p class="form-actions">
    <button class="btn" type="submit">Salvar</button>
    <a class="btn secondary" href="<?= e(base_url('admin/products/list.php')) ?>">Voltar</a>
  </p>
</form>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
