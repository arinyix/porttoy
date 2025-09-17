<?php
// lib/Mailer.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Autoload do Composer (preferencial)
if (defined('BASE_PATH') && file_exists(BASE_PATH . '/vendor/autoload.php')) {
  require_once BASE_PATH . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../vendor/autoload.php')) {
  require_once __DIR__ . '/../vendor/autoload.php';
}
// (Se você tiver PHPMailer “manual” em lib/PHPMailer/src, adicione os requires aqui.)

/**
 * Envia um e-mail usando PHPMailer.
 *
 * @param array $data {
 *   to_email, to_name?, subject, text?, html?, reply_to?, reply_to_name?
 * }
 * @param string|null $errOut  Recebe motivo técnico de falha (se houver)
 * @return bool true se enviou, false se falhou
 */
function mailer_send(array $data, ?string &$errOut = null): bool {
  $cfg = require BASE_PATH . '/config/mail.php';

  $mail = new PHPMailer(true);

  // Buffer para capturar o debug do SMTP (nunca vai para a tela)
  $debugBuffer = '';

  // Função que acumula o debug (em vez de imprimir)
  $debugCapture = function ($str, $level) use (&$debugBuffer) {
    $debugBuffer .= gmdate('Y-m-d H:i:s ') . trim($str) . PHP_EOL;
  };

  try {
    // Transporte SMTP (Gmail)
    $mail->isSMTP();
    $mail->Host       = $cfg['host'];
    $mail->Port       = (int)$cfg['port'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $cfg['username'];
    $mail->Password   = $cfg['password'];
    $mail->CharSet    = 'UTF-8';

    // Criptografia
    if (($cfg['encryption'] ?? '') === 'ssl') {
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
      if ($mail->Port === 0) $mail->Port = 465;
    } else {
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      if ($mail->Port === 0) $mail->Port = 587;
    }

    // ===== DEBUG: nunca exibe na tela =====
    // Nível vem do config, mas sempre captura em $debugBuffer (não ecoa).
    $debugLevel = (int)($cfg['smtp_debug'] ?? 0);
    $mail->SMTPDebug  = $debugLevel > 0 ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;
    $mail->Debugoutput = $debugCapture;

    // Remetente e destinatário
    $mail->setFrom($cfg['from_email'], $cfg['from_name']);
    $mail->addAddress($data['to_email'], $data['to_name'] ?? '');

    // Reply-To (opcional)
    if (!empty($data['reply_to'])) {
      $mail->addReplyTo($data['reply_to'], $data['reply_to_name'] ?? '');
    }

    // Assunto e corpo
    $mail->Subject = (string)$data['subject'];

    if (!empty($data['html'])) {
      $mail->isHTML(true);
      $mail->Body    = $data['html'];
      $mail->AltBody = $data['text'] ?? strip_tags($data['html']);
    } else {
      $mail->isHTML(false);
      $mail->Body = (string)($data['text'] ?? '');
    }

    // Envia
    $ok = $mail->send();

    // Se quiser logar debug mesmo em sucesso quando smtp_debug > 0:
    if ($ok && !empty($cfg['log_debug']) && $debugLevel > 0 && $debugBuffer) {
      error_log("[Mailer][SMTP DEBUG - sucesso]\n" . $debugBuffer);
    }

    return true;

  } catch (Exception $e) {
    $msg = $mail->ErrorInfo ?: $e->getMessage();
    $errOut = $msg;
    $GLOBALS['MAIL_LAST_ERROR'] = $msg;

    // Loga debug e motivo técnico quando falhar
    if (!empty($cfg['log_debug']) && $debugBuffer) {
      error_log("[Mailer][SMTP DEBUG - erro] $msg\n" . $debugBuffer);
    } elseif (!empty($cfg['log_debug'])) {
      error_log("[Mailer][SMTP erro] $msg");
    }

    return false;
  }
}
    