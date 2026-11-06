<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function verify_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $sent = $_POST['csrf'] ?? '';
        if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
            http_response_code(419);
            exit('Invalid form token. Go back and try again.');
        }
    }
}
function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}
function require_login(): void {
    if (!current_user()) { header('Location: index.php'); exit; }
}
function require_admin(): void {
    require_login();
    if (current_user()['role'] !== 'admin') {
        http_response_code(403);
        exit('Access denied.');
    }
}
function flash(string $message, string $type='success'): void {
    $_SESSION['flash'] = ['message'=>$message, 'type'=>$type];
}
function show_flash(): void {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash']; unset($_SESSION['flash']);
        $color = $f['type'] === 'error' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800';
        echo '<div class="mb-4 rounded-lg p-3 '. $color .'">'.e($f['message']).'</div>';
    }
}
