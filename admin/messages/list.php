<?php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';
require_once __DIR__ . '/../partials/header.php';

$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$where = '';
$params = [];
if ($q !== '') {
  $where  = "WHERE name LIKE ? OR email LIKE ? OR subject LIKE ? OR body LIKE ?";
  $like = "%{$q}%";
  $params = [$like, $like, $like, $like];
}

$total = (int)fetch_value("SELECT COUNT(*) FROM messages {$where}", $params);
$pages = max(1, (int)ceil($total / $perPage));
if ($page > $pages) { $page = $pages; }
$offset = ($page - 1) * $perPage;

$sql = "SELECT id, name, email, subject, body, ip, created_at
        FROM messages {$where}
        ORDER BY created_at DESC
        LIMIT {$perPage} OFFSET {$offset}";
$rows = fetch_all($sql, $params);

$ok  = $_GET['ok']  ?? null;
$err = $_GET['err'] ?? null;
?>
<style>
.table.rtable{ width:100%; border-collapse:separate; border-spacing:0 8px; }
.table.rtable tr{ background:#fff; border:1px solid #e7e7e7; border-radius:16px; }
.table.rtable td,.table.rtable th{ background:transparent; border:0; }
.table.rtable .actions-row{ display:flex; gap:8px; flex-wrap:wrap; }
.dark .table.rtable tr{ background:var(--card); border-color:var(--card-border); }
@media (max-width:820px){
  .table.rtable thead{ display:none; }
  .table.rtable tbody tr{ display:block; padding:10px 12px; }
  .table.rtable tbody td{
    display:flex; gap:10px; align-items:center;
    padding:8px 0; border-bottom:1px solid rgba(0,0,0,.06);
  }
  .dark .table.rtable tbody td{ border-color:#1f2831; }
  .table.rtable tbody td:last-child{ border-bottom:0; }
  .table.rtable tbody td::before{
    content:attr(data-label);
    min-width:110px; color:var(--muted); font-weight:600;
  }
  .table.rtable .actions-row .btn{ width:100%; justify-content:center; }
}
</style>

<h1 class="fade-in">Mensagens</h1>

<?php if ($ok): ?><div class="card fade-in"><div class="pad" style="border-left:4px solid var(--ok)"><?= e($ok) ?></div></div><br><?php endif; ?>
<?php if ($err): ?><div class="card fade-in"><div class="pad" style="border-left:4px solid var(--danger)"><?= e($err) ?></div></div><br><?php endif; ?>

<form method="get" class="form-row" style="align-items:end">
  <div>
    <label for="q">Buscar</label>
    <input id="q" class="input" name="q" value="<?= e($q) ?>" placeholder="nome, e-mail, assunto ou mensagem">
  </div>
  <div>
    <label>&nbsp;</label>
    <button class="btn" type="submit">Filtrar</button>
  </div>
</form>

<p class="fade-in" style="color:var(--muted)"><?= $total ?> resultado(s)</p>

<table class="table rtable fade-in">
  <thead>
    <tr>
      <th>#</th><th>Nome</th><th>E-mail</th><th>Assunto</th><th>Data</th><th>IP</th><th style="width:260px">Ações</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td data-label="#"><?= (int)$r['id'] ?></td>
      <td data-label="Nome"><?= e($r['name']) ?></td>
      <td data-label="E-mail"><a href="mailto:<?= e($r['email']) ?>"><?= e($r['email']) ?></a></td>
      <td data-label="Assunto"><?= e($r['subject']) ?></td>
      <td data-label="Data"><?= e(date('d/m/Y H:i', strtotime($r['created_at']))) ?></td>
      <td data-label="IP"><?= e($r['ip']) ?></td>
      <td data-label="Ações">
        <div class="actions-row">
          <a class="btn secondary" href="<?= e(base_url('admin/messages/view.php?id='.(int)$r['id'])) ?>">Ver</a>
          <a class="btn secondary" href="mailto:<?= e($r['email']) ?>?subject=<?= rawurlencode('Re: '.$r['subject']) ?>">Responder</a>
          <form action="<?= e(base_url('admin/messages/delete.php')) ?>" method="post" onsubmit="return confirm('Excluir esta mensagem?')">
            <?= csrf_field(); ?>
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <input type="hidden" name="q" value="<?= e($q) ?>">
            <input type="hidden" name="page" value="<?= (int)$page ?>">
            <button class="btn" type="submit">Excluir</button>
          </form>
        </div>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="7">Nenhuma mensagem.</td></tr><?php endif; ?>
  </tbody>
</table>

<?php if ($pages > 1): ?>
  <nav class="fade-in" aria-label="Paginação" style="display:flex;gap:8px;justify-content:center;margin:16px 0">
    <?php $mk = function($p) use ($q){ return base_url('admin/messages/list.php?page='.$p.($q!==''?'&q='.rawurlencode($q):'')); }; ?>
    <a class="btn secondary" href="<?= e($mk(max(1,$page-1))) ?>">&laquo; Anterior</a>
    <span class="btn" style="pointer-events:none"><?= (int)$page ?> / <?= (int)$pages ?></span>
    <a class="btn secondary" href="<?= e($mk(min($pages,$page+1))) ?>">Próxima &raquo;</a>
  </nav>
<?php endif; ?>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
