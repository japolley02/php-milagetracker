<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/config.php';

function current_user(): ?array { return $_SESSION['user'] ?? null; }
function is_manager(): bool { $u = current_user(); return $u && ($u['role'] ?? '') === 'manager'; }

function require_login(): void {
    if (!current_user()) { header('Location: /login.php'); exit; }
}
function require_manager(): void {
    if (!is_manager()) { http_response_code(403); echo "<h1>Forbidden</h1><p>Manager access required.</p>"; exit; }
}
