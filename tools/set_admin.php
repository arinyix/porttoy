<?php
// tools/set_admin.php — Criador/atualizador de ADMIN (use e APAGUE em seguida)
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../config/config.php';
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';

// Permite rodar só a partir do localhost
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($ip, ['127.0.0.1', '::1'], true)) {
  http_response_code(403);
  exit('Acesso negado. Rode apenas via localhost.');
}

$ok = $err = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_validate($_POST['csrf'] ?? null)) {
    $err = 'CSRF inválido.';
  } else {
    $email = trim((string)($_POST['email'] ?? ''));
    $name  = trim((string)($_POST['name']  ?? 'Administrador'));
    $pass  = (string)($_POST['password']   ?? '');
    $pass2 = (string)($_POST['password2']  ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $err = 'E-mail inválido.';
    } elseif (strlen($pass) < 8) {
      $err = 'A senha precisa de pelo menos 8 caracteres.';
    } elseif ($pass !== $pass2) {
      $err = 'A confirmação de senha não confere.';
    } else {
      try {
        // Gera hash bcrypt seguro
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        // Cria ou atualiza o admin
        $sql = "INSERT INTO users (name, email, password_hash, created_at)
                VALUES (:name, :email, :hash, NOW())
                ON DUPLICATE KEY UPDATE name = VALUES(name), password_hash = VALUES(password_hash)";
        $st = db()->prepare($sql);
        $st->execute([':name'=>$name, ':email'=>$email, ':hash'=>$hash]);

        $ok = 'Admin criado/atualizado com sucesso! Você já pode logar no painel.';
      } catch (Throwable $e) {
        $err = 'Erro ao salvar no banco: ' . $e->getMessage();
      }
    }
  }
}
?>
<!doctype html>
<html lang="pt-br">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Configurar Admin • ToyLab</title>
<link rel="stylesheet" href="<?= e(asset('css/styles.css')) ?>">
<style>
  body{max-width:720px;margin:24px auto;padding:0 16px}
  .box{border:1px solid #e7e7e7;border-radius:12px;padding:16px}
  .dark .box{border-color:var(--card-border);background:var(--card)}
  .note{font-size:.95rem;color:var(--muted)}
</style>
<body>
  <h1>Configurar Admin</h1>

  <?php if ($ok): ?>
    <p class="btn"><?= e($ok) ?></p>
    <p><a class="btn" href="<?= e(base_url('admin/login.php')) ?>">Ir para o Login do Admin</a></p>
    <p class="note">Por segurança, apague este arquivo: <code>/tools/set_admin.php</code> após usar.</p>
  <?php endif; ?>

  <?php if ($err): ?>
    <div class="box" style="border-left:4px solid var(--danger)"><strong>Erro:</strong> <?= e($err) ?></div>
    <br>
  <?php endif; ?>

  <div class="box">
    <form method="post" novalidate>
      <?= csrf_field(); ?>
      <label for="name">Nome</label>
      <input class="input" id="name" name="name" value="<?= e($_POST['name'] ?? 'Administrador') ?>">

      <label for="email">E-mail</label>
      <input class="input" id="email" type="email" required name="email" value="<?= e($_POST['email'] ?? 'admin@toylab.ufopa.br') ?>">

      <div class="form-row">
        <div>
          <label for="password">Senha</label>
          <input class="input" id="password" type="password" required name="password" placeholder="mín. 8 caracteres">
        </div>
        <div>
          <label for="password2">Confirmar senha</label>
          <input class="input" id="password2" type="password" required name="password2">
        </div>
      </div>

      <p><button class="btn" type="submit">Salvar admin</button></p>
    </form>
  </div>
</body>
</html>
