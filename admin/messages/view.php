<?php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';
require_once BASE_PATH . '/lib/Mailer.php';
require_once __DIR__ . '/../partials/header.php';

$id = (int)($_GET['id'] ?? 0);
$m  = fetch_one("SELECT * FROM messages WHERE id = ?", [$id]);
if (!$m) { echo '<div class="card"><div class="pad">Mensagem não encontrada.</div></div>'; require_once __DIR__.'/../partials/footer.php'; exit; }

$ok = $err = null;

if (is_post()) {
  if (!csrf_validate($_POST['csrf'] ?? null)) {
    $err = 'CSRF inválido.';
  } else {
    $subject = trim($_POST['subject'] ?? ('Re: '.$m['subject']));
    $body    = trim($_POST['body'] ?? '');

    if ($subject === '' || $body === '') {
      $err = 'Assunto e mensagem são obrigatórios.';
    } else {
      $errMail = null;
      $sent = mailer_send([
        'to_email'      => $m['email'],
        'to_name'       => $m['name'],
        'subject'       => $subject,
        'text'          => $body,
        'reply_to'      => $m['email'],
        'reply_to_name' => $m['name'],
      ], $errMail);

      if ($sent) {
        $ok = 'Resposta enviada.';
      } else {
        $err = 'Falha ao enviar resposta.';
        $cfg = require BASE_PATH . '/config/mail.php';
        if (!empty($cfg['show_errors'])) {
          $err .= ' Detalhe: ' . e($errMail ?: ($GLOBALS['MAIL_LAST_ERROR'] ?? ''));
        }
      }
    }
  }
}
?>
<h1 class="fade-in">Mensagem #<?= (int)$id ?></h1>

<div class="card fade-in"><div class="pad">
  <p><b>De:</b> <?= e($m['name']) ?> &lt;<a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a>&gt;</p>
  <p><b>Assunto:</b> <?= e($m['subject']) ?></p>
  <p><b>Data:</b> <?= e(date('d/m/Y H:i', strtotime($m['created_at']))) ?></p>
  <hr>
  <pre style="white-space:pre-wrap;font:14px/1.5 monospace;"><?= e($m['body']) ?></pre>
</div></div>

<h2 class="fade-in">Responder</h2>
<?php if ($ok): ?>
  <div class="card fade-in"><div class="pad" style="border-left:4px solid var(--ok)"><?= e($ok) ?></div></div><br>
<?php endif; ?>
<?php if ($err): ?>
  <div class="card fade-in"><div class="pad" style="border-left:4px solid var(--danger)"><?= e($err) ?></div></div><br>
<?php endif; ?>

<form method="post" class="fade-in">
  <?= csrf_field(); ?>
  <label for="subject">Assunto</label>
  <input id="subject" name="subject" class="input" required value="<?= e('Re: '.$m['subject']) ?>">

  <label for="body">Mensagem</label>
  <textarea id="body" name="body" class="input" rows="8" required><?= e($_POST['body'] ?? '') ?></textarea>

  <p>
    <button class="btn" type="submit">Enviar resposta</button>
    <a class="btn secondary" href="<?= e(base_url('admin/messages/list.php')) ?>">Voltar</a>
  </p>
</form>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
