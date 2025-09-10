<?php
// Garante sessão/logado e carrega helpers
require_once __DIR__ . '/../config/auth.php';
require_login();
require_once BASE_PATH . '/config/functions.php';

// Header reutilizável do admin (inclui <head> com CSS e topbar)
require_once __DIR__ . '/partials/header.php';

// Se não tiver count_table(), dá para contar assim:
// function count_table($t){ return (int)db()->query("SELECT COUNT(*) FROM $t")->fetchColumn(); }
?>
<h1 class="fade-in">Dashboard</h1>

<section class="stat-grid fade-in">
  <div class="stat-card">
    <h3>Produtos</h3>
    <div class="n"><?= (int)count_table('products') ?></div>
    <p><a class="btn secondary" href="<?= e(base_url('admin/products/list.php')) ?>">Gerenciar</a></p>
  </div>

  <div class="stat-card">
    <h3>Categorias</h3>
    <div class="n"><?= (int)count_table('categories') ?></div>
    <p><a class="btn secondary" href="<?= e(base_url('admin/categories/list.php')) ?>">Gerenciar</a></p>
  </div>

  <div class="stat-card">
    <h3>Equipe</h3>
    <div class="n"><?= (int)count_table('team') ?></div>
    <p><a class="btn secondary" href="<?= e(base_url('admin/team/list.php')) ?>">Gerenciar</a></p>
  </div>

  <div class="stat-card">
    <h3>Notícias</h3>
    <div class="n"><?= (int)count_table('posts') ?></div>
    <p><a class="btn secondary" href="<?= e(base_url('admin/posts/list.php')) ?>">Gerenciar</a></p>
  </div>

  <div class="stat-card">
    <h3>Timeline</h3>
    <div class="n"><?= (int)count_table('milestones') ?></div>
    <p><a class="btn secondary" href="<?= e(base_url('admin/milestones/list.php')) ?>">Gerenciar</a></p>
  </div>

  <div class="stat-card">
    <h3>Parcerias</h3>
    <div class="n"><?= (int)count_table('partners') ?></div>
    <p><a class="btn secondary" href="<?= e(base_url('admin/partners/list.php')) ?>">Gerenciar</a></p>
  </div>

  <div class="stat-card">
    <h3>Mensagens</h3>
    <div class="n"><?= (int)count_table('messages') ?></div>
    <p><a class="btn secondary" href="<?= e(base_url('admin/messages/list.php')) ?>">Ver</a></p>
  </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
