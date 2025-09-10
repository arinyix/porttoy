<?php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';
require_once __DIR__ . '/../partials/header.php';

$id = max(1, (int)($_GET['id'] ?? 0));
$p  = fetch_one("SELECT * FROM products WHERE id=?", [$id]);
if (!$p) {
  echo '<div class="card"><div class="pad">Produto não encontrado.</div></div>';
  require_once __DIR__ . '/../partials/footer.php';
  exit;
}

/* ---------- ações POST (upload / delete) ---------- */
$msg = $err = null;

if (is_post()) {
  // excluir uma imagem específica?
  if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['image_id'])) {
    if (!csrf_validate($_POST['csrf'] ?? null)) {
      $err = 'CSRF inválido.';
    } else {
      $imgId = (int)$_POST['image_id'];
      $img = fetch_one("SELECT * FROM product_images WHERE id=? AND product_id=?", [$imgId, $id]);
      if ($img) {
        $fs = BASE_PATH . '/' . ltrim((string)$img['path'], '/');
        if (is_file($fs) && str_starts_with($img['path'], 'public/uploads/')) {
          @unlink($fs);
        }
        delete_row('product_images', $imgId);
        $msg = 'Imagem removida.';
      }
    }
  }
  // subir novas imagens?
  elseif (isset($_FILES['images'])) {
    if (!csrf_validate($_POST['csrf'] ?? null)) {
      $err = 'CSRF inválido.';
    } else {
      $paths = handle_images_upload('images'); // valida MIME/tamanho/gera nome seguro
      if ($paths) {
        foreach ($paths as $path) {
          // normaliza sem barra inicial
          $path = ltrim($path, '/');
          save_row('product_images', [
            'product_id' => $id,
            'path'       => $path,  // ex.: public/uploads/xxxxx.webp
            'alt'        => $p['title'],
            'created_at' => now()
          ]);
        }
        $msg = 'Imagens enviadas com sucesso.';
      } else {
        $err = 'Nenhum arquivo válido enviado.';
      }
    }
  }
}

$imgs = get_product_images($id);
?>
<h1 class="fade-in">Imagens • <?= e($p['title']) ?></h1>

<?php if($msg): ?><div class="card fade-in"><div class="pad" style="border-left:4px solid var(--ok)"><?= e($msg) ?></div></div><?php endif; ?>
<?php if($err): ?><div class="card fade-in"><div class="pad" style="border-left:4px solid var(--danger)"><?= e($err) ?></div></div><?php endif; ?>

<form method="post" enctype="multipart/form-data" class="fade-in">
  <?= csrf_field(); ?>
  <label>Escolha imagens (jpg, png, webp) até 3MB cada</label>
  <input class="input" type="file" name="images[]" multiple accept=".jpg,.jpeg,.png,.webp">
  <p>
    <button class="btn" type="submit">Enviar</button>
    <a class="btn secondary" href="<?= e(base_url('admin/products/list.php')) ?>">Concluir</a>
  </p>
</form>

<div class="grid fade-in">
  <?php foreach ($imgs as $im): ?>
    <?php
      $rel  = ltrim((string)$im['path'], '/');
      $fs   = BASE_PATH . '/' . $rel;
      $src  = is_file($fs) ? base_url($rel) : asset('img/placeholder.png');
      $alt  = $im['alt'] ?: $p['title'];
    ?>
    <div class="card">
      <img src="<?= e($src) ?>" alt="<?= e($alt) ?>" style="width:100%;height:180px;object-fit:cover;display:block;">
      <div class="pad" style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
        <small><?= is_file($fs) ? 'OK' : 'arquivo ausente' ?></small>
        <form method="post" onsubmit="return confirm('Remover esta imagem?')">
          <?= csrf_field(); ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="image_id" value="<?= (int)$im['id'] ?>">
          <button class="btn" type="submit">Excluir</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
