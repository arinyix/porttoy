<?php
require_once __DIR__ . '/../../config/auth.php';
require_once BASE_PATH . '/config/functions.php';
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin • ToyLab</title>
  <link rel="stylesheet" href="<?= e(asset('css/styles.css')) ?>">
  <script src="<?= e(asset('js/admin.js')) ?>" defer></script>
</head>
<body>
<header class="admin-bar">
  <nav class="admin-nav" aria-label="Admin">
    <a class="brand" href="<?= e(base_url('admin/index.php')) ?>">Admin • ToyLab</a>

    <!-- Botão hamburguer (só no mobile faz efeito) -->
    <button type="button"
            class="admin-toggle"
            data-admin-toggle
            aria-controls="adminLinks"
            aria-expanded="false"
            aria-label="Abrir menu">
      <span class="bar"></span>
    </button>

    <!-- Links do menu -->
    <div id="adminLinks" data-admin-links hidden>
      <a href="<?= e(base_url('admin/products/list.php')) ?>">Produtos</a>
      <a href="<?= e(base_url('admin/categories/list.php')) ?>">Categorias</a>
      <a href="<?= e(base_url('admin/team/list.php')) ?>">Equipe</a>
      <a href="<?= e(base_url('admin/posts/list.php')) ?>">Notícias</a>
      <a href="<?= e(base_url('admin/milestones/list.php')) ?>">Timeline</a>
      <a href="<?= e(base_url('admin/partners/list.php')) ?>">Parcerias</a>
      <a href="<?= e(base_url('admin/messages/list.php')) ?>">Mensagens</a>

      <span class="spacer"></span>
      <a class="btn secondary" href="<?= e(base_url()) ?>" target="_blank" rel="noopener">Ver site</a>
      <button class="btn" type="button" data-toggle-darkmode>🌗 Modo</button>
      <a class="btn" href="<?= e(base_url('admin/logout.php')) ?>">Sair</a>
    </div>
  </nav>
  <div class="nav-backdrop" id="adminBackdrop" hidden></div>
</header>

<main class="admin-main">
