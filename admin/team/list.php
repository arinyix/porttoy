<?php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';
require_once __DIR__ . '/../partials/header.php';

$rows = fetch_all("SELECT id, name, role, photo_path, lattes_url, created_at FROM team ORDER BY name ASC");
$ok  = $_GET['ok']  ?? null;
$err = $_GET['err'] ?? null;
?>
<h1 class="fade-in">Equipe</h1>

<?php if ($ok): ?>
  <div class="card fade-in"><div class="pad" style="border-left:4px solid var(--ok)"><?= e($ok) ?></div></div><br>
<?php endif; ?>
<?php if ($err): ?>
  <div class="card fade-in"><div class="pad" style="border-left:4px solid var(--danger)"><?= e($err) ?></div></div><br>
<?php endif; ?>

<p><a class="btn" href="<?= e(base_url('admin/team/create.php')) ?>">+ Novo membro</a></p>

<table class="table fade-in">
  <thead>
    <tr>
      <th>#</th>
      <th>Foto</th>
      <th>Nome</th>
      <th>Função</th>
      <th>Lattes</th>
      <th style="width:260px">Ações</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= (int)$r['id'] ?></td>
        <td>
          <?php if (!empty($r['photo_path'])): ?>
            <img src="<?= e(base_url($r['photo_path'])) ?>" alt="Foto de <?= e($r['name']) ?>"
                 style="width:56px;height:56px;object-fit:cover;border-radius:999px;border:1px solid #e7e7e7;background:#fff">
          <?php else: ?>
            —
          <?php endif; ?>
        </td>
        <td><?= e($r['name']) ?></td>
        <td><?= e($r['role']) ?></td>
        <td>
          <?php if (!empty($r['lattes_url'])): ?>
            <a href="<?= e($r['lattes_url']) ?>" target="_blank" rel="noopener">Ver Lattes</a>
          <?php else: ?>—<?php endif; ?>
        </td>
        <td class="actions">
          <a class="btn secondary" href="<?= e(base_url('admin/team/edit.php?id='.(int)$r['id'])) ?>">Editar</a>
          <form action="<?= e(base_url('admin/team/delete.php')) ?>" method="post" style="display:inline"
                onsubmit="return confirm('Excluir o membro &quot;<?= e($r['name']) ?>&quot;?')">
            <?= csrf_field(); ?>
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn" type="submit">Excluir</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?>
      <tr><td colspan="6">Nenhum membro cadastrado.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
