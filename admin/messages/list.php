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

// LIMIT/OFFSET sanitarizados (inteiros) direto na query
$sql = "SELECT id, name, email, subject, body, ip, created_at
        FROM messages {$where}
        ORDER BY created_at DESC
        LIMIT {$perPage} OFFSET {$offset}";
$rows = fetch_all($sql, $params);

$ok  = $_GET['ok']  ?? null;
$err = $_GET['err'] ?? null;
?>
<h1 class="fade-in">Mensagens</h1>

<?php if ($ok): ?>
  <div class="card fade-in"><div class="pad" style="border-left:4px solid var(--ok)"><?= e($ok) ?></div></div><br>
<?php endif; ?>
<?php if ($err): ?>
  <div class="card fade-in"><div class="pad" style="border-left:4px solid var(--danger)"><?= e($err) ?></div></div><br>
<?php endif; ?>

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

<table class="table fade-in">
  <thead>
    <tr>
      <th>#</th>
      <th>Nome</th>
      <th>E-mail</th>
      <th>Assunto</th>
      <th>Data</th>
      <th>IP</th>
      <th style="width:260px">Ações</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= (int)$r['id'] ?></td>
        <td><?= e($r['name']) ?></td>
        <td><a href="mailto:<?= e($r['email']) ?>"><?= e($r['email']) ?></a></td>
        <td><?= e($r['subject']) ?></td>
        <td><?= e(date('d/m/Y H:i', strtotime($r['created_at']))) ?></td>
        <td><?= e($r['ip']) ?></td>
        <td class="actions">
          <a class="btn secondary" href="<?= e(base_url('admin/messages/view.php?id='.(int)$r['id'])) ?>">Ver</a>
          <a class="btn secondary" href="mailto:<?= e($r['email']) ?>?subject=<?= rawurlencode('Re: '.$r['subject']) ?>">Responder</a>
          <form action="<?= e(base_url('admin/messages/delete.php')) ?>" method="post" style="display:inline" onsubmit="return confirm('Excluir esta mensagem?')">
            <?= csrf_field(); ?>
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <input type="hidden" name="q" value="<?= e($q) ?>">
            <input type="hidden" name="page" value="<?= (int)$page ?>">
            <button class="btn" type="submit">Excluir</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?>
      <tr><td colspan="7">Nenhuma mensagem.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?php if ($pages > 1): ?>
  <nav class="fade-in" aria-label="Paginação" style="display:flex;gap:8px;justify-content:center;margin:16px 0">
    <?php
      $mk = function($p) use ($q){ $u = base_url('admin/messages/list.php?page='.$p.($q!==''?'&q='.rawurlencode($q):'')); return $u; };
    ?>
    <a class="btn secondary" href="<?= e($mk(max(1,$page-1))) ?>">&laquo; Anterior</a>
    <span class="btn" style="pointer-events:none"><?= (int)$page ?> / <?= (int)$pages ?></span>
    <a class="btn secondary" href="<?= e($mk(min($pages,$page+1))) ?>">Próxima &raquo;</a>
  </nav>
<?php endif; ?>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
