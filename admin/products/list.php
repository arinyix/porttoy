<?php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';
require_once __DIR__ . '/../partials/header.php';

$rows = fetch_all("
  SELECT p.id, p.title, p.status, p.created_at,
         c.name  AS category_name,
         sc.name AS subcategory_name
  FROM products p
  LEFT JOIN categories c  ON c.id  = p.category_id
  LEFT JOIN categories sc ON sc.id = p.subcategory_id
  ORDER BY p.created_at DESC
");
?>
<h1 class="fade-in">Produtos</h1>

<p>
  <a class="btn" href="<?= e(base_url('admin/products/create.php')) ?>">+ Novo produto</a>
</p>

<table class="table">
  <thead>
    <tr>
      <th>#</th>
      <th>Título</th>
      <th>Cat/Sub</th>
      <th>Status</th>
      <th style="width:280px">Ações</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= (int)$r['id'] ?></td>
        <td><?= e($r['title']) ?></td>
        <td><?= e(($r['category_name'] ?? '—') . ' / ' . ($r['subcategory_name'] ?? '—')) ?></td>
        <td><span class="badge"><?= e($r['status']) ?></span></td>
        <td class="actions">
          <a class="btn secondary" href="<?= e(base_url('admin/products/edit.php?id='.(int)$r['id'])) ?>">Editar</a>
          <a class="btn secondary" href="<?= e(base_url('admin/products/upload.php?id='.(int)$r['id'])) ?>">Imagens</a>

          <!-- Excluir via POST + CSRF -->
          <form action="<?= e(base_url('admin/products/delete.php')) ?>" method="post" style="display:inline" onsubmit="return confirm('Remover o produto &quot;<?= e($r['title']) ?>&quot;?')">
            <?= csrf_field(); ?>
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn" type="submit">Excluir</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
