<?php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validate($_POST['csrf'] ?? null)) {
  http_response_code(405);
  exit('Método não permitido.');
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
  header('Location: '.base_url('admin/partners/list.php?err=ID%20inv%C3%A1lido')); exit;
}

// Tenta remover o arquivo da logo (se for do nosso /uploads)
$logo = fetch_value("SELECT logo_path FROM partners WHERE id=?", [$id]);
if ($logo && is_string($logo) && str_starts_with($logo, 'public/uploads/')) {
  @unlink(BASE_PATH . '/' . $logo);
}

delete_row('partners', $id);
header('Location: '.base_url('admin/partners/list.php?ok=Parceria%20exclu%C3%ADda'));
exit;
