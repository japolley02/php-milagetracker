
# Mileage Tracker (PHP, Multi-User) — MariaDB + PDF Reports + Admin

Adds:
- **PDF reports** (month YYYY-MM or year YYYY) via FPDF.
- **Admin (manager) user management**: list users, change roles, reset passwords, delete users.
- UI links for PDF in user home and manager drilldown.

## Requirements
- PHP 8+, PDO MySQL, MariaDB/MySQL server.
- For PDF: install FPDF (either Composer or manual file).
  - Composer: `composer require setasign/fpdf`
  - Manual: download `fpdf.php` and place at `lib/fpdf.php`

## Configure
1) DB & user (example):
```sql
CREATE DATABASE mileage_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'mileage'@'localhost' IDENTIFIED BY 'strongpasswordhere';
GRANT ALL PRIVILEGES ON mileage_tracker.* TO 'mileage'@'localhost';
FLUSH PRIVILEGES;
```
2) Copy `.env.sample` → `.env`, set credentials.
3) Visit `/migrate.php` to create tables and the one-time manager link.

## Files
- `config.php`, `init.php`, `.env.sample`
- `migrate.php`, `provision_manager.php`
- `register.php`, `login.php`, `logout.php`
- `index.php` (user trips, PDF link), `frequent.php`
- `manager.php` (overview + per-user PDF), `admin.php` (user mgmt)
- `export_csv.php`
- `pdf_report.php` (FPDF-driven)

# php-milagetracker
# php-milagetracker
