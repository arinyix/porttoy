<?php
// templates/components/timeline-item.php
// Exige $m (milestone)
?>
<div class="item fade-in">
  <h4><?= e($m['title']) ?></h4>
  <time datetime="<?= e($m['event_date']) ?>"><?= date('d/m/Y', strtotime($m['event_date'])) ?></time>
  <p><?= e($m['description']) ?></p>
</div>
