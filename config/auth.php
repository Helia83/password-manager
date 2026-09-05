<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

// Application timezone: Iran (UTC+03:30).
date_default_timezone_set('Asia/Tehran');

ini_set('session.gc_maxlifetime', (string) SESSION_LIFETIME);
session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path' => '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        if (str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
            json_response(['ok' => false, 'message' => 'نشست کاربری معتبر نیست.'], 401);
        }
        header('Location: login.php');
        exit;
    }
}

function require_admin(): void {
    require_login();
    if (strcasecmp((string)($_SESSION['role'] ?? ''), 'Admin') !== 0) {
        if (str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
            json_response(['ok' => false, 'message' => 'دسترسی فقط برای Admin مجاز است.'], 403);
        }
        http_response_code(403);
        exit('دسترسی غیرمجاز');
    }
}

function current_user_id(): int { return (int)($_SESSION['user_id'] ?? 0); }
function current_username(): string { return (string)($_SESSION['username'] ?? ''); }
function current_role(): string {
    $role = (string)($_SESSION['role'] ?? 'User');
    return strcasecmp($role, 'Admin') === 0 ? 'Admin' : 'User';
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function verify_csrf(): void {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf'] ?? '');
    if (!$token || !hash_equals((string)($_SESSION['csrf'] ?? ''), $token)) {
        json_response(['ok' => false, 'message' => 'درخواست نامعتبر است.'], 419);
    }
}

function json_response(array $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
