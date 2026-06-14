<?php
// =====================================================
// Database configuration for Laragon / XAMPP local setup
// Default Laragon MySQL account: root with empty password
// =====================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'lab_equipment_manager');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

function installDatabaseIfNeeded(): void
{
    $sqlFile = __DIR__ . '/../database/lab_equipment_manager.sql';
    if (!file_exists($sqlFile)) {
        throw new RuntimeException('SQL file not found: ' . $sqlFile);
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS);
    $mysqli->set_charset(DB_CHARSET);
    $sql = file_get_contents($sqlFile);

    if (!$mysqli->multi_query($sql)) {
        throw new RuntimeException('Cannot run SQL installer.');
    }

    do {
        if ($result = $mysqli->store_result()) {
            $result->free();
        }
    } while ($mysqli->more_results() && $mysqli->next_result());

    $mysqli->close();
}

function getPDO(bool $autoInstall = true): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        if ($autoInstall) {
            $check = $pdo->query("SHOW TABLES LIKE 'users'")->fetch();
            if (!$check) {
                installDatabaseIfNeeded();
                $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            }
        }
    } catch (PDOException $e) {
        if ($autoInstall) {
            installDatabaseIfNeeded();
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } else {
            throw $e;
        }
    }

    return $pdo;
}
