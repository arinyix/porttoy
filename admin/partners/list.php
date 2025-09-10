<?php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';
require_once __DIR__ . '/../partials/header.php';

$rows = fetch_all("SELECT id, name, logo_path, url, created_at FROM partners ORDER BY name ASC");

$ok  = $_GET['ok']  ?? null;
$err = $_GET['err'] ?? null;
?>
<h1 class="fade-in">Parcerias</h1>

<?php if ($ok): ?>
  <div class="card fade-in"><div class="pad" style="border-left:4px solid var(--ok)"><?= e($ok) ?></div></div><br>
<?php endif; ?>
<?php if ($err): ?>
  <div class="card fade-in"><div class="pad" style="border-left:4px solid var(--danger)"><?= e($err) ?></div></div><br>
<?php endif; ?>

<p><a class="btn" href="<?= e(base_url('admin/partners/create.php')) ?>">+ Nova parceria</a></p>

<table class="table fade-in">
  <thead>
    <tr>
      <th>#</th>
      <th>Logo</th>
      <th>Nome</th>
      <th>URL</th>
      <th style="width:260px">Ações</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= (int)$r['id'] ?></td>
        <td>
          <?php if (!empty($r['logo_path'])): ?>
            <img src="<?= e(base_url($r['logo_path'])) ?>" alt="Logo <?= e($r['name']) ?>" style="width:72px;height:36px;object-fit:contain;background:#fff;border:1px solid #e7e7e7;border-radius:8px;padding:4px">
          <?php else: ?>
            —
          <?php endif; ?>
        </td>
        <td><?= e($r['name']) ?></td>
        <td>
          <?php if (!empty($r['url'])): ?>
            <a href="<?= e($r['url']) ?>" target="_blank" rel="noopener"><?= e($r['url']) ?></a>
          <?php else: ?>—<?php endif; ?>
        </td>
        <td class="actions">
          <a class="btn secondary" href="<?= e(base_url('admin/partners/edit.php?id='.(int)$r['id'])) ?>">Editar</a>

          <form action="<?= e(base_url('admin/partners/delete.php')) ?>" method="post" style="display:inline" onsubmit="return confirm('Excluir a parceria &quot;<?= e($r['name']) ?>&quot;?')">
            <?= csrf_field(); ?>
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn" type="submit">Excluir</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?>
      <tr><td colspan="5">Nenhuma parceria cadastrada.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
