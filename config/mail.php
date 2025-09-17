<?php
// config/mail.php
// Lê variáveis do .env com defaults seguros para DEV/local

return [
  // remetente padrão
  'from_email' => env('MAIL_FROM_EMAIL', ''),
  'from_name'  => env('MAIL_FROM_NAME',  'ToyLab • UFOPA'),

  // destinatário padrão (quem recebe as notificações do site)
  'to_email'   => env('MAIL_TO_EMAIL', ''),
  'to_name'    => env('MAIL_TO_NAME',  'ToyLab Admin'),

  // SMTP
  'host'       => env('SMTP_HOST', 'smtp.gmail.com'),
  'port'       => env('SMTP_PORT', 587),
  'encryption' => env('SMTP_ENCRYPTION', 'tls'), // "tls" ou "ssl"
  'username'   => env('SMTP_USERNAME', ''),
  'password'   => env('SMTP_PASSWORD', ''),      // APP PASSWORD 16 dígitos

  // Controle de erros/debug
  'show_errors' => env('MAIL_SHOW_ERRORS', false),
  'smtp_debug'  => env('MAIL_SMTP_DEBUG', 0),    // 0 = off (não imprime na tela)
  'log_debug'   => env('MAIL_LOG_DEBUG',  true), // envia debug para error_log
];
