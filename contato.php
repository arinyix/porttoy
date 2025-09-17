<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/functions.php';
require_once __DIR__ . '/config/csrf.php';
require_once __DIR__ . '/lib/Mailer.php';

$mailCfg = require __DIR__ . '/config/mail.php';

$success = null;
$errors  = [];

if (is_post()) {
  if (!csrf_validate($_POST['csrf'] ?? null)) { $errors[] = 'Token CSRF inválido.'; }
  if (!empty($_POST['website']))            { $errors[] = 'Detecção de spam.'; }

  // Rate-limit: 5 envios / 10min
  $now = time();
  $_SESSION['contact_times'] = array_values(array_filter($_SESSION['contact_times'] ?? [], fn($t)=> $now - $t < 600));
  if (count($_SESSION['contact_times']) >= 5) $errors[] = 'Muitas mensagens. Tente novamente em alguns minutos.';

  $name    = trim($_POST['name']    ?? '');
  $email   = trim($_POST['email']   ?? '');
  $subject = trim($_POST['subject'] ?? '');
  $body    = trim($_POST['message'] ?? '');

  if ($name === '')                               $errors[] = 'Nome é obrigatório.';
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'E-mail inválido.';
  if ($subject === '')                            $errors[] = 'Assunto é obrigatório.';
  if ($body === '')                               $errors[] = 'Mensagem é obrigatória.';

  if (!$errors) {
    // grava no banco
    save_row('messages', [
      'name'       => $name,
      'email'      => $email,
      'subject'    => $subject,
      'body'       => $body,
      'ip'         => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
      'created_at' => now()
    ]);
    $_SESSION['contact_times'][] = $now;

    // e-mail para o admin
    $errMail1 = $errMail2 = null;
    $adminOk = mailer_send([
      'to_email' => $mailCfg['to_email'],
      'to_name'  => $mailCfg['to_name'],
      'subject'  => 'Nova mensagem • ToyLab',
      'html'     => '
        <h2>Nova mensagem pelo site</h2>
        <p><b>Nome:</b> '.e($name).'</p>
        <p><b>E-mail:</b> '.e($email).'</p>
        <p><b>Assunto:</b> '.e($subject).'</p>
        <hr>
        <pre style="font:14px/1.5 monospace;white-space:pre-wrap">'.e($body).'</pre>
      ',
      'text'     => "Nova mensagem\n\nNome: $name\nE-mail: $email\nAssunto: $subject\n\n$body",
      'reply_to' => $email,
      'reply_to_name' => $name,
    ], $errMail1);

    // auto-reply (opcional)
    $autoOk = mailer_send([
      'to_email' => $email,
      'to_name'  => $name,
      'subject'  => 'Recebemos sua mensagem • ToyLab',
      'text'     => "Olá $name,\n\nRecebemos sua mensagem e retornaremos em breve.\n\n— ToyLab • UFOPA",
    ], $errMail2);

    if ($adminOk) {
      $success = 'Mensagem enviada com sucesso! Em breve entraremos em contato.';
      if (!$autoOk) { error_log('[Contato] Falha no auto-reply: ' . ($errMail2 ?: ($GLOBALS['MAIL_LAST_ERROR'] ?? ''))); }
    } else {
      error_log('[Contato] Falha no envio para admin: ' . ($errMail1 ?: ($GLOBALS['MAIL_LAST_ERROR'] ?? '')));
      $errors[] = 'Sua mensagem foi recebida, porém houve uma falha no envio de notificação por e-mail. Tente novamente mais tarde.';
    }
  }
}

require_once __DIR__ . '/templates/header.php';
?>
<section style="max-width:800px;margin:24px auto;padding:0 16px;">
  <h1>Fale Conosco</h1>

  <?php if ($success): ?>
    <p class="btn" style="display:inline-block;"><?= e($success) ?></p>
  <?php endif; ?>

  <?php if ($errors): ?>
    <div class="card" style="border-left:4px solid var(--danger);">
      <div class="pad">
        <strong>Erros:</strong>
        <ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
      </div>
    </div>
  <?php endif; ?>

  <form method="post" novalidate>
    <?= csrf_field(); ?>
    <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">

    <label for="name">Nome</label>
    <input class="input" id="name" name="name" required value="<?= e($_POST['name'] ?? '') ?>">

    <div class="form-row">
      <div>
        <label for="email">E-mail</label>
        <input class="input" id="email" type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div>
        <label for="subject">Assunto</label>
        <select id="subject" name="subject">
          <option value="Dúvida"   <?= (($_POST['subject'] ?? '')==='Dúvida')  ?'selected':'' ?>>Dúvida</option>
          <option value="Parceria" <?= (($_POST['subject'] ?? '')==='Parceria')?'selected':'' ?>>Parceria</option>
          <option value="Projeto"  <?= (($_POST['subject'] ?? '')==='Projeto') ?'selected':'' ?>>Projeto</option>
        </select>
      </div>
    </div>

    <label for="message">Mensagem</label>
    <textarea class="input" id="message" name="message" rows="6" required><?= e($_POST['message'] ?? '') ?></textarea>

    <p><button class="btn" type="submit">Enviar</button></p>
  </form>
</section>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
