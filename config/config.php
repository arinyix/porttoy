<?php
// config/config.php
declare(strict_types=1);

/**
 * Arquivo idempotente: pode ser incluído mais de uma vez sem warnings/erros.
 * - Protege define() com !defined(...)
 * - Carrega .env via config/env.php
 * - Aplica diretivas de sessão APENAS antes do session_start()
 */

/* ===== Raiz do projeto ===== */
if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(__DIR__ . '/..'));
}

/* ===== .env ===== */
require_once __DIR__ . '/env.php';

/* ===== Exibição de erros ===== */
$debug = filter_var(env('APP_DEBUG', 'true'), FILTER_VALIDATE_BOOLEAN);
ini_set('display_errors', $debug ? '1' : '0');
error_reporting($debug ? E_ALL : (E_ALL & ~E_WARNING));
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/toylab_error.log');

/* ===== Sessão: só configurar se AINDA não estiver ativa ===== */
if (session_status() !== PHP_SESSION_ACTIVE) {
    // Diretivas só podem ser alteradas antes de iniciar a sessão
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Lax');

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

/* ===== Security headers (só se ainda não enviados) ===== */
if (!headers_sent()) {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'none'");
}

/* ===== Banco (com fallback) ===== */
if (!defined('DB_HOST')) define('DB_HOST', env('DB_HOST', 'localhost'));
if (!defined('DB_NAME')) define('DB_NAME', env('DB_NAME', 'toylab'));
if (!defined('DB_USER')) define('DB_USER', env('DB_USER', 'root'));
if (!defined('DB_PASS')) define('DB_PASS', env('DB_PASS', ''));

/* ===== BASE_URL detectada automaticamente ===== */
if (!defined('BASE_URL')) {
    $doc  = rtrim(str_replace('\\','/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $root = str_replace('\\','/', BASE_PATH);
    $url  = str_replace($doc, '', $root); // ex.: "/porttoy" ou "/toylab"
    define('BASE_URL', $url ?: '');
}

/* =========================
   Funções utilitárias
   ========================= */

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

/** URL base do projeto */
function base_url_root(): string {
    return BASE_URL;
}

/** Monta URL de assets com versionamento por mtime */
function asset(string $path): string {
    $full = BASE_PATH . '/public/' . ltrim($path, '/');
    $v = file_exists($full) ? filemtime($full) : time();
    return base_url_root() . '/public/' . ltrim($path, '/') . '?v=' . $v;
}

/** Monta URL relativa ao projeto */
function base_url(string $path = ''): string {
    return base_url_root() . ($path ? '/' . ltrim($path, '/') : '');
}

function is_post(): bool {
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

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
    return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/');
}

/* ===== Helpers de mídia ===== */
if (!function_exists('media_fs')) {
    function media_fs(string $path): string {
        $rel = ltrim($path, '/');
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
