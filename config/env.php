<?php
// config/env.php

// BASE_PATH (se ainda não existir)
if (!defined('BASE_PATH')) {
  define('BASE_PATH', realpath(__DIR__ . '/..'));
}

// 1) Tenta via Composer + vlucas/phpdotenv
$autoload1 = BASE_PATH . '/vendor/autoload.php';
$autoload2 = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload1)) {
  require_once $autoload1;
} elseif (file_exists($autoload2)) {
  require_once $autoload2;
}
if (class_exists(\Dotenv\Dotenv::class)) {
  \Dotenv\Dotenv::createImmutable(BASE_PATH)->safeLoad();
} else {
  // 2) Fallback simples (sem Composer)
  $envFile = BASE_PATH . '/.env';
  if (is_file($envFile) && is_readable($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
      $line = ltrim($line);
      if ($line === '' || $line[0] === '#') continue;
      [$k, $v] = array_pad(explode('=', $line, 2), 2, null);
      if ($k === null || $v === null) continue;
      $v = trim($v);
      if (strlen($v) >= 2 && ($v[0] === '"' || $v[0] === "'")) {
        $v = trim($v, "\"'");
      }
      if (getenv($k) === false) {
        putenv("$k=$v"); $_ENV[$k] = $v; $_SERVER[$k] = $v;
      }
    }
  }
}

// Helper env()
if (!function_exists('env')) {
  function env(string $key, $default = null) {
    $v = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($v === false || $v === null || $v === '') return $default;
    $l = strtolower((string)$v);
    if ($l === 'true') return true;
    if ($l === 'false') return false;
    if ($l === 'null') return null;
    if (is_numeric($v)) return $v + 0;
    return $v;
  }
}
