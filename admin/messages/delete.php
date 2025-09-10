<?php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validate($_POST['csrf'] ?? null)) {
  http_response_code(405);
  exit('Método não permitido.');
}

$id   = (int)($_POST['id'] ?? 0);
$q    = trim($_POST['q'] ?? '');
$page = max(1, (int)($_POST['page'] ?? 1));

if ($id > 0) {
  delete_row('messages', $id);
  $msg = 'Mensagem excluída';
} else {
  $msg = 'ID inválido';
}

$redir = base_url('admin/messages/list.php?ok='.rawurlencode($msg));
$redir .= ($q !== '') ? '&q='.rawurlencode($q) : '';
$redir .= '&page='.$page;

header('Location: '.$redir);
exit;
