<?php
// templates/components/filter-bar.php
$tops = get_top_categories();
?>
<div class="filterbar">
  <div class="wrap">
    <strong>Filtrar: </strong>
    <?php foreach ($tops as $t): ?>
      <button class="chip" data-filter data-type="category" data-value="<?= e($t['id']) ?>"><?= e($t['name']) ?></button>
      <?php foreach (get_subcategories((int)$t['id']) as $sc): ?>
        <button class="chip" data-filter data-type="subcategory" data-value="<?= e($sc['id']) ?>"><?= e($sc['name']) ?></button>
      <?php endforeach; ?>
    <?php endforeach; ?>
    <input type="search" class="input search" placeholder="Buscar por título..." aria-label="Buscar produtos" data-search>
  </div>
</div>
