<?php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';
require_once __DIR__ . '/../partials/header.php';

$rows = fetch_all("
  SELECT c.id, c.name, c.parent_id, p.name AS parent_name
  FROM categories c
  LEFT JOIN categories p ON p.id = c.parent_id
  ORDER BY COALESCE(p.name, c.name), c.name
");
$ok  = $_GET['ok']  ?? null;
$err = $_GET['err'] ?? null;
?>
<h1 class="fade-in">Categorias</h1>

<?php if ($ok): ?>
  <div class="card fade-in"><div class="pad" style="border-left:4px solid var(--ok)">
    <?= e($ok) ?>
  </div></div><br>
<?php endif; ?>
<?php if ($err): ?>
  <div class="card fade-in"><div class="pad" style="border-left:4px solid var(--danger)">
    <?= e($err) ?>
  </div></div><br>
<?php endif; ?>

<p>
  <a class="btn" href="<?= e(base_url('admin/categories/create.php')) ?>">+ Nova categoria</a>
</p>

<table class="table">
  <thead>
    <tr>
      <th>#</th>
      <th>Nome</th>
      <th>Pai</th>
      <th style="width:240px">Ações</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= (int)$r['id'] ?></td>
        <td><?= e($r['name']) ?></td>
        <td><?= e($r['parent_name'] ?? '—') ?></td>
        <td class="actions">
          <a class="btn secondary" href="<?= e(base_url('admin/categories/edit.php?id='.(int)$r['id'])) ?>">Editar</a>

          <form action="<?= e(base_url('admin/categories/delete.php')) ?>" method="post" style="display:inline"
                onsubmit="return confirm('Remover a categoria &quot;<?= e($r['name']) ?>&quot;?')">
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
