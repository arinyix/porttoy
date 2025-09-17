<?php
// admin/messages/send.php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';
require_once BASE_PATH . '/lib/Mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('admin/messages/list.php'); }

$id      = (int)($_POST['id'] ?? 0);
$to      = trim($_POST['to'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$body    = trim($_POST['body'] ?? '');

if (!csrf_validate($_POST['csrf'] ?? null)) {
  redirect('admin/messages/view.php?id='.$id.'&err='.rawurlencode('CSRF inválido.'));
}

if (!filter_var($to, FILTER_VALIDATE_EMAIL) || $subject === '' || $body === '') {
  redirect('admin/messages/view.php?id='.$id.'&err='.rawurlencode('Preencha todos os campos corretamente.'));
}

// rate-limit simples por sessão
session_start();
$now = time();
if (!empty($_SESSION['last_mail']) && ($now - $_SESSION['last_mail'] < 10)) {
  redirect('admin/messages/view.php?id='.$id.'&err='.rawurlencode('Aguarde alguns segundos antes de enviar outro e-mail.'));
}
$_SESSION['last_mail'] = $now;

// monta e envia
$ok = mailer_send([
  'to_email' => $to,
  'to_name'  => '',
  'subject'  => $subject,
  'text'     => $body, // pode usar 'html' se quiser
]);

if ($ok) {
  redirect('admin/messages/view.php?id='.$id.'&ok='.rawurlencode('E-mail enviado.'));
} else {
  redirect('admin/messages/view.php?id='.$id.'&err='.rawurlencode('Falha ao enviar. Verifique as configurações SMTP.'));
}
