<?php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';
require_once __DIR__ . '/../partials/header.php';

$rows = fetch_all("
  SELECT p.id, p.title, p.status, p.created_at,
         c.name  AS category_name,
         sc.name AS subcategory_name
  FROM products p
  LEFT JOIN categories c  ON c.id  = p.category_id
  LEFT JOIN categories sc ON sc.id = p.subcategory_id
  ORDER BY p.created_at DESC
");
?>
<style>
/* -------- Tabela -> cards no mobile (escopo local) -------- */
.table.rtable{ width:100%; border-collapse:separate; border-spacing:0 8px; }
.table.rtable thead{ display:table-header-group; }
.table.rtable tr{ background:#fff; border:1px solid #e7e7e7; border-radius:16px; }
.table.rtable td, .table.rtable th{ background:transparent; border:0; }
.table.rtable .actions-row{ display:flex; gap:8px; flex-wrap:wrap; }
.dark .table.rtable tr{ background:var(--card); border-color:var(--card-border); }

@media (max-width: 820px){
  .table.rtable{ border-spacing:0 12px; }
  .table.rtable thead{ display:none; }
  .table.rtable tbody tr{ display:block; padding:10px 12px; }
  .table.rtable tbody td{
    display:flex; align-items:center; gap:10px;
    padding:8px 0; border-bottom:1px solid rgba(0,0,0,.06);
  }
  .dark .table.rtable tbody td{ border-color:#1f2831; }
  .table.rtable tbody td:last-child{ border-bottom:0; }
  .table.rtable tbody td::before{
    content: attr(data-label);
    min-width: 110px;
    color: var(--muted);
    font-weight:600;
  }
  .table.rtable .actions-row .btn{ width:100%; justify-content:center; }
}
</style>

<h1 class="fade-in">Produtos</h1>

<p>
  <a class="btn" href="<?= e(base_url('admin/products/create.php')) ?>">+ Novo produto</a>
</p>

<table class="table rtable fade-in">
  <thead>
    <tr>
      <th>#</th>
      <th>Título</th>
      <th>Cat/Sub</th>
      <th>Status</th>
      <th style="width:280px">Ações</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td data-label="#"><?= (int)$r['id'] ?></td>
        <td data-label="Título"><?= e($r['title']) ?></td>
        <td data-label="Cat/Sub">
          <?= e(($r['category_name'] ?? '—') . ' / ' . ($r['subcategory_name'] ?? '—')) ?>
        </td>
        <td data-label="Status"><span class="badge"><?= e($r['status']) ?></span></td>
        <td data-label="Ações">
          <div class="actions-row">
            <a class="btn secondary" href="<?= e(base_url('admin/products/edit.php?id='.(int)$r['id'])) ?>">Editar</a>
            <a class="btn secondary" href="<?= e(base_url('admin/products/upload.php?id='.(int)$r['id'])) ?>">Imagens</a>

            <form action="<?= e(base_url('admin/products/delete.php')) ?>" method="post" onsubmit="return confirm('Remover o produto &quot;<?= e($r['title']) ?>&quot;?')">
              <?= csrf_field(); ?>
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button class="btn" type="submit">Excluir</button>
            </form>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?>
      <tr><td colspan="5">Nenhum produto cadastrado.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
