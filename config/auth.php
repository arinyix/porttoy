<?php
// config/auth.php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/csrf.php';

function attempt_login(string $email, string $password): bool {
    $stmt = db()->prepare("SELECT id, name, email, password_hash FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int)$user['id'], 'name' => $user['name'], 'email' => $user['email']];
        return true;
    }
    return false;
}

function require_login(): void {
    if (empty($_SESSION['user'])) {
        redirect('admin/login.php');
    }
}

function logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        setcookie(session_name(), '', time()-42000, '/');
    }
    session_destroy();
}
