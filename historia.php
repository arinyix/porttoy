<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/functions.php';
require_once __DIR__ . '/templates/header.php';
$milestones = fetch_all("SELECT * FROM milestones ORDER BY event_date ASC");
?>
<section class="timeline">
  <h1>História</h1>
  <?php foreach ($milestones as $m): include __DIR__ . '/templates/components/timeline-item.php'; endforeach; ?>
</section>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
