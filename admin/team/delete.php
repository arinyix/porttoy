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
  header('Location: '.base_url('admin/team/list.php?err=ID%20inv%C3%A1lido')); exit;
}

// Remover foto do filesystem (se for do /uploads)
$photo = fetch_value("SELECT photo_path FROM team WHERE id=?", [$id]);
if ($photo && is_string($photo) && str_starts_with($photo, 'public/uploads/')) {
  @unlink(BASE_PATH . '/' . $photo);
}

delete_row('team', $id);
header('Location: '.base_url('admin/team/list.php?ok=Membro%20exclu%C3%ADdo'));
exit;
