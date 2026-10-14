<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
function is_logged_in(): bool {
    return isset($_SESSION['user']);
}
function is_admin(): bool {
    return is_logged_in() && ($_SESSION['user']['role'] ?? '') === 'admin';
}
function require_login(): void {
    if (!is_logged_in()) {
        header('Location: /eco_recycle_b2c/public/login.php');
        exit;
    }
}
function require_admin(): void {
    if (!is_admin()) {
        http_response_code(403);
        exit('Bạn không có quyền truy cập trang này.');
    }
}
function money(float|int|string $amount): string {
    return number_format((float)$amount, 0, ',', '.') . ' ₫';
}
