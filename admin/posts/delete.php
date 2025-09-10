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
  header('Location: '.base_url('admin/posts/list.php?err=ID%20inv%C3%A1lido')); exit;
}

// Apaga a capa do filesystem se for nossa
$cover = fetch_value("SELECT cover_path FROM posts WHERE id=?", [$id]);
if ($cover && is_string($cover) && str_starts_with($cover, 'public/uploads/')) {
  @unlink(BASE_PATH . '/' . $cover);
}

delete_row('posts', $id);
header('Location: '.base_url('admin/posts/list.php?ok=Post%20exclu%C3%ADdo'));
exit;
