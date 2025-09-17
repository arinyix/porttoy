<?php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';
require_once BASE_PATH . '/lib/Mailer.php';
require_once __DIR__ . '/../partials/header.php';

$ok = $err = null;

$to    = trim($_GET['to']    ?? '');
$subj  = trim($_GET['subject'] ?? '');

if (is_post()) {
  if (!csrf_validate($_POST['csrf'] ?? null)) {
    $err = 'CSRF inválido.';
  } else {
    $to    = trim($_POST['to'] ?? '');
    $name  = trim($_POST['name'] ?? '');
    $subj  = trim($_POST['subject'] ?? '');
    $body  = trim($_POST['body'] ?? '');

    if (!filter_var($to, FILTER_VALIDATE_EMAIL))       $err = 'E-mail de destino inválido.';
    elseif ($subj === '')                               $err = 'Assunto obrigatório.';
    elseif ($body === '')                               $err = 'Mensagem obrigatória.';
    else {
        $errMail = null;
      $sent = mailer_send([
        'to_email' => $to,
        'to_name'  => $name,
        'subject'  => $subj,
        'text'     => $body,
        // reply-to ficará o próprio ToyLab (padrão)
      ], $errMail);
      
      if ($sent) $ok = 'E-mail enviado com sucesso.';
      else       $err = 'Falha ao enviar e-mail.';
    }
  }
}
?>
<h1 class="fade-in">Escrever e-mail</h1>

<?php if ($ok): ?>
  <div class="card fade-in"><div class="pad" style="border-left:4px solid var(--ok)"><?= e($ok) ?></div></div><br>
<?php endif; ?>
<?php if ($err): ?>
  <div class="card fade-in"><div class="pad" style="border-left:4px solid var(--danger)"><?= e($err) ?></div></div><br>
<?php endif; ?>

<form method="post" class="fade-in">
  <?= csrf_field(); ?>

  <label for="to">Para (e-mail)</label>
  <input id="to" name="to" class="input" type="email" required value="<?= e($to) ?>">

  <label for="name">Nome do destinatário (opcional)</label>
  <input id="name" name="name" class="input" value="<?= e($_POST['name'] ?? '') ?>">

  <label for="subject">Assunto</label>
  <input id="subject" name="subject" class="input" required value="<?= e($subj) ?>">

  <label for="body">Mensagem</label>
  <textarea id="body" name="body" class="input" rows="10" required><?= e($_POST['body'] ?? '') ?></textarea>

  <p>
    <button class="btn" type="submit">Enviar</button>
    <a class="btn secondary" href="<?= e(base_url('admin/messages/list.php')) ?>">Voltar</a>
  </p>
</form>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
