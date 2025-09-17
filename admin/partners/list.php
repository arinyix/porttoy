<?php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';
require_once __DIR__ . '/../partials/header.php';

$rows = fetch_all("SELECT id, name, logo_path, url, created_at FROM partners ORDER BY name ASC");

$ok  = $_GET['ok']  ?? null;
$err = $_GET['err'] ?? null;
?>
<style>
/* -------- Tabela -> cards no mobile (escopo local) -------- */
.table.rtable{ width:100%; border-collapse:separate; border-spacing:0 8px; }
.table.rtable thead{ display:table-header-group; }
.table.rtable tr{ background:#fff; border:1px solid #e7e7e7; border-radius:16px; }
.table.rtable td, .table.rtable th{ background:transparent; border:0; }
.table.rtable .actions-row{ display:flex; gap:8px; flex-wrap:wrap; }
.dark .table.rtable tr{ background:var(--card); border-color:var(--card-border); }

@media (max-width: 820px){
  .table.rtable{ border-spacing:0 12px; }
  .table.rtable thead{ display:none; }
  .table.rtable tbody tr{ display:block; padding:10px 12px; }
  .table.rtable tbody td{
    display:flex; align-items:center; gap:10px;
    padding:8px 0; border-bottom:1px solid rgba(0,0,0,.06);
  }
  .dark .table.rtable tbody td{ border-color:#1f2831; }
  .table.rtable tbody td:last-child{ border-bottom:0; }
  .table.rtable tbody td::before{
    content: attr(data-label);
    min-width: 110px;
    color: var(--muted);
    font-weight:600;
  }
  .table.rtable .actions-row .btn{ width:100%; justify-content:center; }
}
</style>

<h1 class="fade-in">Parcerias</h1>

<?php if ($ok): ?>
  <div class="card fade-in"><div class="pad" style="border-left:4px solid var(--ok)"><?= e($ok) ?></div></div><br>
<?php endif; ?>
<?php if ($err): ?>
  <div class="card fade-in"><div class="pad" style="border-left:4px solid var(--danger)"><?= e($err) ?></div></div><br>
<?php endif; ?>

<p><a class="btn" href="<?= e(base_url('admin/partners/create.php')) ?>">+ Nova parceria</a></p>

<table class="table rtable fade-in">
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
        <td data-label="#"><?= (int)$r['id'] ?></td>

        <td data-label="Logo">
          <?php if (!empty($r['logo_path'])): ?>
            <img src="<?= e(base_url($r['logo_path'])) ?>" alt="Logo <?= e($r['name']) ?>"
                 style="width:72px;height:36px;object-fit:contain;background:#fff;border:1px solid #e7e7e7;border-radius:8px;padding:4px">
          <?php else: ?>—<?php endif; ?>
        </td>

        <td data-label="Nome"><?= e($r['name']) ?></td>

        <td data-label="URL">
          <?php if (!empty($r['url'])): ?>
            <a href="<?= e($r['url']) ?>" target="_blank" rel="noopener"><?= e($r['url']) ?></a>
          <?php else: ?>—<?php endif; ?>
        </td>

        <td data-label="Ações">
          <div class="actions-row">
            <a class="btn secondary" href="<?= e(base_url('admin/partners/edit.php?id='.(int)$r['id'])) ?>">Editar</a>

            <form action="<?= e(base_url('admin/partners/delete.php')) ?>" method="post" onsubmit="return confirm('Excluir a parceria &quot;<?= e($r['name']) ?>&quot;?')">
              <?= csrf_field(); ?>
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button class="btn" type="submit">Excluir</button>
            </form>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?>
      <tr><td colspan="5">Nenhuma parceria cadastrada.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
