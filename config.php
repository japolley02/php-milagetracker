<?php
declare(strict_types=1);

// Load .env
$envPath = __DIR__ . '/.env';
if (is_readable($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$k, $v] = array_pad(explode('=', $line, 2), 2, '');
        $_ENV[trim($k)] = trim($v);
    }
}

$DB_HOST = $_ENV['DB_HOST'] ?? 'localhost';
$DB_PORT = (int)($_ENV['DB_PORT'] ?? 3306);
$DB_NAME = $_ENV['DB_NAME'] ?? 'mileage_tracker';
$DB_USER = $_ENV['DB_USER'] ?? 'mileage';
$DB_PASS = $_ENV['DB_PASS'] ?? '';
$DB_PERSIST = (bool) (int)($_ENV['DB_PERSIST'] ?? 0);

$dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $DB_HOST, $DB_PORT, $DB_NAME);
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
if ($DB_PERSIST) { $options[PDO::ATTR_PERSISTENT] = true; }

$pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);

function h(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function have_fpdf(): bool {
    // Composer autoload? If vendor/autoload exists, assume FPDF present if class exists after require
    $autoload = __DIR__ . '/vendor/autoload.php';
    if (file_exists($autoload)) { require_once $autoload; if (class_exists('FPDF')) return true; }
    $lib = __DIR__ . '/lib/fpdf.php';
    if (file_exists($lib)) { require_once $lib; return class_exists('FPDF'); }
    return false;
}
