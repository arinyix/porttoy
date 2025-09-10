<?php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validate($_POST['csrf'] ?? null)) {
  http_response_code(405);
  exit('Método não permitido.');
}

$id = max(1, (int)($_POST['id'] ?? 0));
// ON DELETE CASCADE para imagens já está no schema.
delete_row('products', $id);

header('Location: ' . base_url('admin/products/list.php'));
exit;
