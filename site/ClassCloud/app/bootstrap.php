<?php
declare(strict_types=1);

$configPath = dirname(__DIR__) . '/config/config.php';
if (!is_file($configPath)) {
    http_response_code(500);
    exit('Configuration manquante : copiez config/config.example.php vers config/config.php.');
}
$config = require $configPath;

if (PHP_SAPI !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
    session_name($config['app']['session_name'] ?? 'classcloud_session');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => (bool)($config['app']['secure_cookies'] ?? true),
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    session_start();
}

function db(): PDO {
    static $pdo = null;
    global $config;
    if ($pdo instanceof PDO) return $pdo;
    $d = $config['database'];
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $d['host'], $d['port'], $d['name'], $d['charset']);
    $pdo = new PDO($dsn, $d['user'], $d['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

function h(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void {
    $provided = $_POST['csrf_token'] ?? '';
    $expected = $_SESSION['csrf_token'] ?? '';
    if (!is_string($provided) || !is_string($expected) ||
        $expected === '' || !hash_equals($expected, $provided)) {
        http_response_code(419);
        exit('Formulaire expiré. Rechargez la page puis réessayez.');
    }
}

function current_user(): ?array {
    if (empty($_SESSION['user_id'])) return null;
    $stmt = db()->prepare(
        'SELECT id, school_id, email, role, active FROM users WHERE id = ? LIMIT 1'
    );
    $stmt->execute([(int)$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user || !(int)$user['active']) {
        $_SESSION = [];
        session_destroy();
        return null;
    }
    return $user;
}

function require_login(): array {
    $user = current_user();
    if (!$user) {
        header('Location: index.php');
        exit;
    }
    return $user;
}

function redirect(string $location): never {
    header('Location: ' . $location);
    exit;
}

function flash(string $message, string $type = 'info'): void {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function take_flash(): ?array {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($flash) ? $flash : null;
}
