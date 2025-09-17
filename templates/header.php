<?php
// templates/header.php
require_once __DIR__ . '/../config/config.php';
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ToyLab • UFOPA</title>
  <meta name="description" content="Portfólio do Laboratório TOYLab/UFOPA: projetos, protótipos, corte a laser, impressão 3D, notícias e equipe.">

  <!-- CSS principal -->
  <link rel="stylesheet" href="<?= e(asset('css/styles.css')) ?>">
  <link rel="icon" href="<?= e(asset('img/favicon.png')) ?>">
</head>
<body>


<header class="header">
  <nav class="nav" data-nav>
    <a class="brand" href="<?= e(base_url()) ?>">ToyLab</a>

    <!-- Botão hamburguer (mobile) -->
    <button class="nav-toggle" type="button"
            aria-label="Abrir menu" aria-expanded="false"
            aria-controls="primary-menu" data-nav-toggle>
      <span class="bar" aria-hidden="true"></span>
      <span class="sr-only">Menu</span>
    </button>

    <!-- Links do menu -->
    <div class="nav-links" id="primary-menu">
      <a href="<?= e(base_url('equipe.php')) ?>">Equipe</a>
      <a href="<?= e(base_url('historia.php')) ?>">História</a>
      <a href="<?= e(base_url('noticias.php')) ?>">Notícias</a>
      <a href="<?= e(base_url('contato.php')) ?>">Fale Conosco</a>
    </div>

    <!-- Botão de modo escuro -->
    <button class="btn" type="button" data-toggle-darkmode aria-label="Alternar modo escuro">🌗 Modo</button>
  </nav>

  <!-- Backdrop para fechar ao tocar fora -->
  <div class="nav-backdrop" data-nav-backdrop></div>
</header>

<main>
