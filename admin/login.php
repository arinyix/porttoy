<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/csrf.php';

$err = null;
if (is_post()) {
  if (!csrf_validate($_POST['csrf'] ?? null)) { $err = 'CSRF inválido.'; }
  else {
    $ok = attempt_login(trim($_POST['email'] ?? ''), $_POST['password'] ?? '');
    if ($ok) redirect('admin/index.php');
    $err = 'Credenciais inválidas.';
  }
}
?>
<!doctype html><html lang="pt-br"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="<?= e(asset('css/styles.css')) ?>">
<title>Login • Admin</title></head><body class="dark">
<section style="max-width:380px;margin:10vh auto;padding:16px;">
  <h1>Admin • ToyLab</h1>
  <?php if ($err): ?><p style="color:#f55;"><?= e($err) ?></p><?php endif; ?>
  <form method="post">
    <?= csrf_field(); ?>
    <label>E-mail</label>
    <input class="input" type="email" name="email" required>
    <label>Senha</label>
    <input class="input" type="password" name="password" required>
    <p><button class="btn" type="submit">Entrar</button></p>
  </form>
</section>
</body></html>
