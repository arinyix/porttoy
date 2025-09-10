<?php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';
require_once __DIR__ . '/../partials/header.php';

$rows = fetch_all("SELECT id, title, description, event_date, created_at FROM milestones ORDER BY event_date DESC, id DESC");
$ok  = $_GET['ok']  ?? null;
$err = $_GET['err'] ?? null;
?>
<h1 class="fade-in">Timeline • Marcos</h1>

<?php if ($ok): ?>
  <div class="card fade-in"><div class="pad" style="border-left:4px solid var(--ok)"><?= e($ok) ?></div></div><br>
<?php endif; ?>
<?php if ($err): ?>
  <div class="card fade-in"><div class="pad" style="border-left:4px solid var(--danger)"><?= e($err) ?></div></div><br>
<?php endif; ?>

<p><a class="btn" href="<?= e(base_url('admin/milestones/create.php')) ?>">+ Novo marco</a></p>

<table class="table fade-in">
  <thead>
    <tr>
      <th>#</th>
      <th>Título</th>
      <th>Data</th>
      <th style="width:260px">Ações</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= (int)$r['id'] ?></td>
        <td><?= e($r['title']) ?></td>
        <td><?= e(date('d/m/Y', strtotime($r['event_date']))) ?></td>
        <td class="actions">
          <a class="btn secondary" href="<?= e(base_url('admin/milestones/edit.php?id='.(int)$r['id'])) ?>">Editar</a>
          <form action="<?= e(base_url('admin/milestones/delete.php')) ?>" method="post" style="display:inline"
                onsubmit="return confirm('Excluir o marco &quot;<?= e($r['title']) ?>&quot;?')">
            <?= csrf_field(); ?>
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn" type="submit">Excluir</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?>
      <tr><td colspan="4">Nenhum marco cadastrado.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
