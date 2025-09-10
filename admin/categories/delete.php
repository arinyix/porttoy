<?php
require_once __DIR__ . '/../../config/auth.php'; require_login();
require_once BASE_PATH . '/config/functions.php';
require_once BASE_PATH . '/config/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validate($_POST['csrf'] ?? null)) {
  http_response_code(405);
  exit('Método não permitido.');
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) { header('Location: '.base_url('admin/categories/list.php?err=ID%20inv%C3%A1lido')); exit; }

// Bloqueia exclusão se houver subcategorias ou produtos vinculados
$children = (int)fetch_value("SELECT COUNT(*) FROM categories WHERE parent_id = ?", [$id]);
$prodCats = (int)fetch_value("SELECT COUNT(*) FROM products WHERE category_id = ?", [$id]);
$prodSubs = (int)fetch_value("SELECT COUNT(*) FROM products WHERE subcategory_id = ?", [$id]);

if ($children > 0 || $prodCats > 0 || $prodSubs > 0) {
  $msg = 'Não é possível excluir: há itens vinculados (subcategorias ou produtos).';
  header('Location: '.base_url('admin/categories/list.php?err='.rawurlencode($msg)));
  exit;
}

// Se chegou aqui, pode excluir
delete_row('categories', $id);
header('Location: '.base_url('admin/categories/list.php?ok=Categoria%20exclu%C3%ADda'));
exit;
