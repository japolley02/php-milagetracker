<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  name VARCHAR(255) NULL,
  pass_hash VARCHAR(255) NOT NULL,
  role ENUM('user','manager') NOT NULL DEFAULT 'user',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS trips (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  date DATE NOT NULL,
  ticket VARCHAR(255) NULL,
  client VARCHAR(255) NULL,
  location VARCHAR(255) NULL,
  notes TEXT NULL,
  mileage DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_trips_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_date (user_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS frequent_trips (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL UNIQUE,
  client VARCHAR(255) NULL,
  location VARCHAR(255) NULL,
  mileage DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo "<h2>Migration complete.</h2>";

session_start();
$hasManager = (int)($pdo->query("SELECT COUNT(*) AS c FROM users WHERE role='manager'")->fetch()['c'] ?? 0);
if (!$hasManager) {
    $token = bin2hex(random_bytes(16));
    $_SESSION['provision_token'] = $token;
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = rtrim(dirname($_SERVER['PHP_SELF'] ?? ''), '/');
    $url = sprintf('%s://%s%s/provision_manager.php?token=%s', $scheme, $host, $base, $token);
    echo "<p>No managers found. Create one via: <a href=\"$url\">$url</a> (one-time link)</p>";
} else {
    echo "<p>At least one manager exists. You can <a href='/login.php'>login</a>.</p>";
}
