<?php
require_once __DIR__ . '/../../config/auth.php';      // garante login
require_once BASE_PATH . '/config/functions.php';
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin • ToyLab</title>
  <link rel="stylesheet" href="<?= e(asset('css/styles.css')) ?>">
  <style>
    /* Complementos leves para o admin (mobile-first) */
    .admin-bar{position:sticky;top:0;z-index:60;background:rgba(255,255,255,.9);
      border-bottom:1px solid #e7e7e7;padding:10px 12px}
    .dark .admin-bar{background:rgba(16,20,24,.75);border-color:var(--card-border)}
    .admin-bar .wrap{max-width:1100px;margin:0 auto;display:flex;gap:10px;align-items:center;flex-wrap:wrap}
    .admin-bar a{padding:6px 10px;border-radius:8px}
    .admin-main{max-width:1100px;margin:16px auto;padding:0 12px}
    .stat-grid{display:grid;gap:14px;grid-template-columns:repeat(auto-fit,minmax(180px,1fr))}
    .stat-card{border:1px solid #e7e7e7;border-radius:16px;padding:14px;background:#fff}
    .dark .stat-card{background:var(--card);border-color:var(--card-border)}
    .stat-card h3{margin:0 0 6px 0;font-size:1rem}
    .stat-card .n{font-size:2rem;font-weight:800}
    .table{width:100%;border-collapse:separate;border-spacing:0 6px}
    .table th{font-weight:700;color:var(--muted)}
    .table td,.table th{padding:10px;background:#fff;border:1px solid #e7e7e7}
    .dark .table td,.dark .table th{background:var(--card);border-color:var(--card-border)}
    .table .actions a{margin-right:8px}
    .badge{display:inline-block;padding:2px 8px;border-radius:999px;background:rgba(127,166,82,.15);color:var(--brand);font-size:.75rem}
    .btn.secondary{background:#e9eef5;color:#0b0d0f}
    .dark .btn.secondary{background:#222c35;color:#dfe6ef}
    @media (max-width:640px){ table.table{display:block;overflow-x:auto;white-space:nowrap} }
  </style>
</head>
<body>
<header class="admin-bar">
  <div class="wrap">
    <a href="<?= e(base_url('admin/index.php')) ?>" class="brand" style="font-weight:700;color:var(--brand)">Admin • ToyLab</a>
    <a href="<?= e(base_url('admin/products/list.php')) ?>">Produtos</a>
    <a href="<?= e(base_url('admin/categories/list.php')) ?>">Categorias</a>
    <a href="<?= e(base_url('admin/team/list.php')) ?>">Equipe</a>
    <a href="<?= e(base_url('admin/posts/list.php')) ?>">Notícias</a>
    <a href="<?= e(base_url('admin/milestones/list.php')) ?>">Timeline</a>
    <a href="<?= e(base_url('admin/partners/list.php')) ?>">Parcerias</a>
    <a href="<?= e(base_url('admin/messages/list.php')) ?>">Mensagens</a>
    <span style="margin-left:auto"></span>
    <a class="btn secondary" href="<?= e(base_url()) ?>">Ver site</a>
    <a class="btn" href="<?= e(base_url('admin/logout.php')) ?>">Sair</a>
  </div>
</header>
<main class="admin-main">
