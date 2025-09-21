<?php
// templates/footer.php
?>
</main>
<footer class="footer">
  <div class="wrap">
    <div>
      <strong>Parcerias:</strong>
      <?php foreach (fetch_all("SELECT id,name,logo_path,url FROM partners ORDER BY id DESC LIMIT 6") as $p): ?>
        <?php $href = !empty($p['url']) ? $p['url'] : '#'; ?>
        <a href="<?= e($href) ?>" target="_blank" rel="noopener" aria-label="Parceiro: <?= e($p['name']) ?>">
          <img
            src="<?= e(media_url($p['logo_path'])) ?>"
            alt="<?= e($p['name']) ?>"
            style="height:28px; vertical-align:middle; margin-right:8px;"
            loading="lazy">
        </a>
      <?php endforeach; ?>
    </div>
    <div>
      <a class="btn" href="<?= e(base_url('contato.php')) ?>">Fale Conosco</a>
    </div>
  </div>
</footer>
<div class="lightbox" role="dialog" aria-modal="true" aria-hidden="true" data-lightbox>
  <button type="button" aria-label="Fechar" data-lb-close>✕</button>
  <img src="" alt="Visualização ampliada">
</div>
<script src="<?= e(asset('js/darkmode.js')) ?>"></script>
<script src="<?= e(asset('js/main.js')) ?>"></script>
</body>
</html>
