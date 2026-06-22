<?php
// =====================================================
// Lab & Equipment Manager - Configuration
// Designed for Laragon/XAMPP local development
// =====================================================

define('APP_NAME', 'Lab & Equipment Manager');
define('APP_VERSION', '1.0.0');

// Laragon default MySQL user is usually root with an empty password.
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'lab_equipment_manager');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// If true, the app tries to import database/lab_equipment_manager.sql
// automatically when the database does not exist yet.
define('AUTO_INSTALL_DATABASE', true);

define('BASE_PATH', dirname(__DIR__));

function app_base_path(): string
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $dir = rtrim(dirname($scriptName), '/');
    return ($dir === '.' || $dir === '/') ? '' : $dir;
}

function current_origin(): string
{
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $scheme = $https ? 'https' : 'http';
    return $scheme . '://' . $host;
}

function base_url(): string
{
    return current_origin() . app_base_path();
}

function is_public_document_root(): bool
{
    $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    $publicPath = realpath(BASE_PATH . '/public');
    return $documentRoot && $publicPath && $documentRoot === $publicPath;
}

function public_url(): string
{
    $base = base_url();

    if (is_public_document_root()) {
        return $base;
    }

    if (substr(app_base_path(), -7) === '/public') {
        return $base;
    }

    return $base . '/public';
}

function url(string $route = '', array $params = []): string
{
    $query = array_merge(['route' => $route], $params);
    return base_url() . '/index.php?' . http_build_query($query);
}

function asset(string $path): string
{
    return public_url() . '/assets/' . ltrim($path, '/') . '?v=6';
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
