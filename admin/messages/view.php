<?php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once __DIR__ . '/../partials/header.php';

$id = (int)($_GET['id'] ?? 0);
$msg = fetch_one("SELECT * FROM messages WHERE id=?", [$id]);
if (!$msg) { echo '<div class="card"><div class="pad">Mensagem não encontrada.</div></div>'; require_once __DIR__.'/../partials/footer.php'; exit; }

$reply = 'mailto:'.$msg['email'].'?subject='.rawurlencode('Re: '.$msg['subject']);
?>
<h1 class="fade-in">Mensagem #<?= (int)$id ?></h1>

<div class="card fade-in">
  <div class="pad">
    <p><strong>De:</strong> <?= e($msg['name']) ?> &lt;<a href="<?= e($reply) ?>"><?= e($msg['email']) ?></a>&gt;</p>
    <p><strong>Assunto:</strong> <?= e($msg['subject']) ?></p>
    <p><strong>Recebida em:</strong> <?= e(date('d/m/Y H:i', strtotime($msg['created_at']))) ?> • <strong>IP:</strong> <?= e($msg['ip']) ?></p>
    <hr>
    <p style="white-space:pre-line"><?= e($msg['body']) ?></p>
    <hr>
    <p>
      <a class="btn" href="<?= e($reply) ?>">Responder por e-mail</a>
      <a class="btn secondary" href="<?= e(base_url('admin/messages/list.php')) ?>">Voltar</a>
    </p>
  </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
