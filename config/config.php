<?php
// config/config.php
declare(strict_types=1);

ini_set('display_errors','1');
error_reporting(E_ALL);
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../toylab_error.log');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// ---- Security Headers (PHP) ----
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
// CSP: usar apenas recursos locais. Permite inline CSS apenas para fallback de segurança mínima.
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'none'");

define('DB_HOST', 'localhost');
define('DB_NAME', 'toylab');
define('DB_USER', 'root'); // ajuste se necessário
define('DB_PASS', '');     // ajuste se necessário
define('BASE_PATH', realpath(__DIR__ . '/..'));
define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/toylab'), '/\\') === '' ? '/toylab' : '/' . trim(explode('/toylab', $_SERVER['SCRIPT_NAME'] ?? '/toylab')[0], '/'));

function db(): PDO {
    static $pdo;
    if (!$pdo) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    return $pdo;
}

function e(?string $str): string {
    return htmlspecialchars((string)$str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Detecta a URL base automaticamente sem hardcode de "/toylab"
function base_url_root(): string {
    $doc = rtrim(str_replace('\\','/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $root = str_replace('\\','/', BASE_PATH);
    $url  = str_replace($doc, '', $root); // ex.: "/toylab"
    return $url ?: '';
}

function asset(string $path): string {
    $full = BASE_PATH . '/public/' . ltrim($path, '/');
    $v = file_exists($full) ? filemtime($full) : time();
    return base_url_root() . '/public/' . ltrim($path, '/') . '?v=' . $v;
}

function base_url(string $path = ''): string {
    return base_url_root() . ($path ? '/' . ltrim($path, '/') : '');
}


function is_post(): bool { return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'; }

function redirect(string $path): never {
    header('Location: ' . base_url($path));
    exit;
}

function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text ?: bin2hex(random_bytes(4)));
}

function now(): string { return date('Y-m-d H:i:s'); }

function current_url(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/toylab/');
}

// Helpers de mídia: aceitam 'uploads/…', 'public/uploads/…' e até caminhos com prefixo '/toylab/' ou '/porttoy/'
if (!function_exists('media_fs')) {
    function media_fs(string $path): string {
        $rel = ltrim($path, '/');
        // Se vier algo como 'toylab/public/uploads/...' ou 'porttoy/public/uploads/...'
        if (preg_match('~(public/.*)$~', $rel, $m)) {
            $rel = $m[1];
        } elseif (preg_match('~(uploads/.*)$~', $rel, $m)) {
            $rel = 'public/' . $m[1];
        } elseif (!str_starts_with($rel, 'public/')) {
            $rel = 'public/' . $rel;
        }
        return BASE_PATH . '/' . $rel;
    }
}

if (!function_exists('media_url')) {
    function media_url(string $path): string {
        $rel = ltrim($path, '/');
        if (preg_match('~(public/.*)$~', $rel, $m)) {
            $rel = $m[1];
        } elseif (preg_match('~(uploads/.*)$~', $rel, $m)) {
            $rel = 'public/' . $m[1];
        } elseif (!str_starts_with($rel, 'public/')) {
            $rel = 'public/' . $rel;
        }
        return base_url($rel);
    }
}
