<?php
// templates/footer.php
?>
</main>
<footer class="footer">
  <div class="wrap">
    <div>
      <strong>Parcerias:</strong>
      <?php foreach (fetch_all("SELECT * FROM partners ORDER BY id DESC LIMIT 6") as $p): ?>
        <a href="<?= e($p['url']) ?>" target="_blank" rel="noopener" aria-label="Parceiro: <?= e($p['name']) ?>">
          <img src="<?= e('/toylab/' . $p['logo_path']) ?>" alt="<?= e($p['name']) ?>" style="height:28px; vertical-align:middle; margin-right:8px;">
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
