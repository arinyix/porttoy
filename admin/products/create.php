<?php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';
require_once __DIR__ . '/../partials/header.php';

$cats    = get_top_categories();
$subcats = fetch_all("SELECT id, name FROM categories WHERE parent_id IS NOT NULL ORDER BY name ASC");
$err = [];

if (is_post()) {
  if (!csrf_validate($_POST['csrf'] ?? null)) $err[] = 'CSRF inválido.';
  $title          = trim($_POST['title'] ?? '');
  $description    = trim($_POST['description'] ?? '');
  $category_id    = (int)($_POST['category_id'] ?? 0);
  $subcategory_id = (int)($_POST['subcategory_id'] ?? 0);
  $status         = in_array($_POST['status'] ?? '', ['disponivel','em_desenvolvimento'], true) ? $_POST['status'] : 'disponivel';
  $featured       = !empty($_POST['featured']) ? 1 : 0;

  if ($title==='')       $err[]='Título obrigatório.';
  if (!$category_id)     $err[]='Categoria obrigatória.';

  if (!$err) {
    $id = save_row('products', [
      'title'          => $title,
      'slug'           => slugify($title) . '-' . substr(bin2hex(random_bytes(2)),0,4),
      'description'    => $description,
      'category_id'    => $category_id,
      'subcategory_id' => $subcategory_id ?: null,
      'status'         => $status,
      'featured'       => $featured,
      'created_at'     => now(),
      'updated_at'     => now(),
    ]);
    header('Location: '.base_url('admin/products/upload.php?id='.$id)); exit;
  }
}
?>
<h1 class="fade-in">Novo produto</h1>

<?php if ($err): ?>
  <div class="card"><div class="pad"><ul><?php foreach($err as $e) echo '<li>'.e($e).'</li>'; ?></ul></div></div>
<?php endif; ?>

<form method="post" class="fade-in">
  <?= csrf_field(); ?>

  <label for="title">Título</label>
  <input id="title" class="input" name="title" required>

  <label for="description">Descrição</label>
  <textarea id="description" class="input" name="description" rows="6"></textarea>

  <div class="form-row">
    <div>
      <label for="category_id">Categoria</label>
      <select id="category_id" name="category_id" required>
        <option value="">Selecione</option>
        <?php foreach($cats as $c): ?>
          <option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label for="subcategory_id">Subcategoria</label>
      <select id="subcategory_id" name="subcategory_id">
        <option value="">—</option>
        <?php foreach($subcats as $s): ?>
          <option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="form-row">
    <div>
      <label for="status">Status</label>
      <select id="status" name="status">
        <option value="disponivel">Disponível</option>
        <option value="em_desenvolvimento">Em desenvolvimento</option>
      </select>
    </div>
    <div>
      <label><input type="checkbox" name="featured" value="1"> Destaque</label>
    </div>
  </div>

  <p>
    <button class="btn" type="submit">Salvar e subir imagens</button>
    <a class="btn secondary" href="<?= e(base_url('admin/products/list.php')) ?>">Cancelar</a>
  </p>
</form>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
