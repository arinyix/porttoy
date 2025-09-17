<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/csrf.php';

$err = null;
if (is_post()) {
  if (!csrf_validate($_POST['csrf'] ?? null)) {
    $err = 'CSRF inválido.';
  } else {
    $ok = attempt_login(trim($_POST['email'] ?? ''), $_POST['password'] ?? '');
    if ($ok) redirect('admin/index.php');
    $err = 'Credenciais inválidas.';
  }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="<?= e(asset('css/styles.css')) ?>">
  <title>Login • Admin</title>
</head>
<body class="dark">
  <main class="admin-login fade-in">
    <h1>Admin • ToyLab</h1>

    <?php if ($err): ?>
      <p class="error"><?= e($err) ?></p>
    <?php endif; ?>

    <form method="post" class="admin-login__form">
      <?= csrf_field(); ?>

      <label for="email">E-mail</label>
      <input class="input" id="email" type="email" name="email" required autocomplete="username" autofocus>

      <label for="password">Senha</label>
      <input class="input" id="password" type="password" name="password" required autocomplete="current-password">

      <p><button class="btn" type="submit">Entrar</button></p>
    </form>
  </main>
</body>
</html>
