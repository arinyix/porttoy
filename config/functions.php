<?php
// config/functions.php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/* ===== Helpers básicos ===== */

/** Escape seguro para HTML */
if (!function_exists('e')) {
    function e(?string $v): string {
        return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

/** Timestamp padrão MySQL */
if (!function_exists('now')) {
    function now(): string { return date('Y-m-d H:i:s'); }
}

/** Verifica se a requisição é POST */
if (!function_exists('is_post')) {
    function is_post(): bool { return (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'); }
}

/** Slug simples para URLs (com fallback) */
if (!function_exists('slugify')) {
    function slugify(string $text): string {
        $text = trim($text);
        if (function_exists('iconv')) {
            $t = @iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$text);
            if ($t !== false) $text = $t;
        }
        $text = preg_replace('~[^\\pL\\d]+~u','-',$text);
        $text = trim((string)$text, '-');
        $text = strtolower((string)$text);
        $text = preg_replace('~[^-a-z0-9]+~','', $text);
        return $text !== '' ? $text : bin2hex(random_bytes(3));
    }
}

/* ===== Acesso ao banco (PDO) ===== */

/** Prepara e executa uma query com parâmetros */
function q(string $sql, array $params = []): PDOStatement {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/** Retorna todas as linhas (ASSOC) */
function fetch_all(string $sql, array $params = []): array {
    return q($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
}

/** Retorna uma linha (ASSOC) ou null */
function fetch_one(string $sql, array $params = []): ?array {
    $row = q($sql, $params)->fetch(PDO::FETCH_ASSOC);
    return $row === false ? null : $row;
}

/** Retorna a 1ª coluna da 1ª linha (ex.: COUNT(*)) ou null */
function fetch_value(string $sql, array $params = []) {
    $st = db()->prepare($sql);
    $st->execute($params);
    $v = $st->fetchColumn();
    return $v === false ? null : $v;
}

/**
 * Insert/Update seguro por tabela (usa prepared).
 * $id null => INSERT; $id numérico => UPDATE WHERE id = :id
 */
function save_row(string $table, array $data, ?int $id = null): int {
    // defesa mínima: garante nomes de colunas válidos (a-zA-Z0-9_)
    foreach (array_keys($data) as $k) {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $k)) {
            throw new InvalidArgumentException("Invalid column name: $k");
        }
    }

    if ($id) {
        $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
        $data['id'] = $id;
        q("UPDATE {$table} SET {$set} WHERE id = :id", $data);
        return $id;
    } else {
        $cols = implode(', ', array_keys($data));
        $vals = implode(', ', array_map(fn($k) => ":$k", array_keys($data)));
        q("INSERT INTO {$table} ({$cols}) VALUES ({$vals})", $data);
        return (int)db()->lastInsertId();
    }
}

/** Delete por id (usa prepared) */
function delete_row(string $table, int $id): void {
    q("DELETE FROM {$table} WHERE id = ?", [$id]);
}

/* ===== Upload seguro de imagens (múltiplos) ===== */
function handle_images_upload(string $inputName, ?string $destDir = null): array {
    $destDir = $destDir ?: BASE_PATH . '/public/uploads';
    if (!is_dir($destDir)) @mkdir($destDir, 0775, true);

    $allowedExt  = ['jpg','jpeg','png','webp'];
    $allowedMime = ['image/jpeg','image/png','image/webp'];
    $maxSize     = 3 * 1024 * 1024; // 3MB
    $paths = [];

    if (empty($_FILES[$inputName])) return $paths;

    $names = $_FILES[$inputName]['name'];
    $tmps  = $_FILES[$inputName]['tmp_name'];
    $sizes = $_FILES[$inputName]['size'];
    $errs  = $_FILES[$inputName]['error'];

    // fallback para 1 arquivo
    if (!is_array($names)) { $names = [$names]; $tmps = [$tmps]; $sizes = [$sizes]; $errs = [$errs]; }

    $finfo = new finfo(FILEINFO_MIME_TYPE);

    foreach ($names as $i => $name) {
        if (($errs[$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;

        $tmp  = (string)($tmps[$i] ?? '');
        $size = (int)($sizes[$i] ?? 0);
        $ext  = strtolower(pathinfo((string)$name, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt, true)) continue;
        if ($size <= 0 || $size > $maxSize) continue;
        if (!is_uploaded_file($tmp)) continue;

        $mime = $finfo->file($tmp) ?: '';
        if (!in_array($mime, $allowedMime, true)) continue;

        $imgInfo = @getimagesize($tmp);
        if ($imgInfo === false) continue;

        $newName = bin2hex(random_bytes(16)) . '.' . $ext;
        $dest    = $destDir . '/' . $newName;

        if (move_uploaded_file($tmp, $dest)) {
            // caminho web relativo à raiz do projeto
            $paths[] = 'public/uploads/' . $newName;
        }
    }
    return $paths;
}

/* ===== Consultas de domínio ===== */

function get_categories(): array {
    return fetch_all("SELECT id, name, parent_id FROM categories ORDER BY parent_id IS NULL DESC, name ASC");
}

function get_top_categories(): array {
    return fetch_all("SELECT id, name FROM categories WHERE parent_id IS NULL ORDER BY name ASC");
}

function get_subcategories(int $parentId): array {
    return fetch_all("SELECT id, name FROM categories WHERE parent_id = ? ORDER BY name ASC", [$parentId]);
}

/**
 * ATUALIZAÇÃO: agora aceita filtro por $status ('disponivel' | 'em_desenvolvimento')
 */
function get_products(
    int $limit = 12,
    int $offset = 0,
    ?int $cat = null,
    ?int $sub = null,
    ?string $qstr = null,
    ?string $status = null   // <- novo parâmetro opcional
): array {
    $sql = "SELECT p.*, c.name AS category_name, sc.name AS subcategory_name
            FROM products p
            LEFT JOIN categories c  ON c.id  = p.category_id
            LEFT JOIN categories sc ON sc.id = p.subcategory_id
            WHERE 1=1";
    $params = [];
    if ($cat)    { $sql .= " AND p.category_id = ?";    $params[] = $cat; }
    if ($sub)    { $sql .= " AND p.subcategory_id = ?"; $params[] = $sub; }
    if ($qstr)   { $sql .= " AND p.title LIKE ?";       $params[] = "%{$qstr}%"; }
    if ($status) { $sql .= " AND p.status = ?";         $params[] = $status; }
    $sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
    $params[] = (int)$limit;
    $params[] = (int)$offset;
    return fetch_all($sql, $params);
}

/** Atalho útil para a home (produtos disponíveis) */
function get_available_products(int $limit = 12, int $offset = 0, ?int $cat = null, ?int $sub = null, ?string $qstr = null): array {
    return get_products($limit, $offset, $cat, $sub, $qstr, 'disponivel');
}

function get_product(int $id): ?array {
    return fetch_one("SELECT p.*, c.name AS category_name, sc.name AS subcategory_name
                      FROM products p
                      LEFT JOIN categories c  ON c.id  = p.category_id
                      LEFT JOIN categories sc ON sc.id = p.subcategory_id
                      WHERE p.id = ?", [$id]);
}

function get_product_images(int $productId): array {
    return fetch_all("SELECT id, product_id, path, alt, created_at
                      FROM product_images
                      WHERE product_id = ?
                      ORDER BY id ASC", [$productId]);
}

/** Conta registros de uma tabela (use só com tabelas conhecidas) */
function count_table(string $table): int {
    return (int) fetch_value("SELECT COUNT(*) FROM {$table}");
}

