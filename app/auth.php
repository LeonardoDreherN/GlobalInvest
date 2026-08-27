<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('gib_admin');
    session_set_cookie_params(['httponly' => true, 'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'), 'samesite' => 'Lax']);
    session_start();
}
function current_admin(): ?array { return $_SESSION['admin'] ?? null; }
function require_admin(): array { if (!current_admin()) { header('Location: /admin/login.php'); exit; } return current_admin(); }
function csrf(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(24)); return $_SESSION['csrf']; }
function verify_csrf(): void { if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(419); exit('Sessão expirada. Atualize a página e tente novamente.'); } }
