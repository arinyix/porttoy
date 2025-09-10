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
  header('Location: '.base_url('admin/milestones/list.php?err=ID%20inv%C3%A1lido')); exit;
}

delete_row('milestones', $id);
header('Location: '.base_url('admin/milestones/list.php?ok=Marco%20exclu%C3%ADdo'));
exit;
