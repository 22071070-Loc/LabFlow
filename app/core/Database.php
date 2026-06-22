<?php
class Database
{
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

        try {
            self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            self::ensureLatestSchema(self::$pdo);
            if (self::$pdo === null) {
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            }
            return self::$pdo;
        } catch (PDOException $e) {
            // Unknown database: try automatic installation
            if (AUTO_INSTALL_DATABASE && strpos($e->getMessage(), 'Unknown database') !== false) {
                self::installDatabase();
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
                return self::$pdo;
            }

            die('Database connection failed: ' . $e->getMessage());
        }
    }

    private static function ensureLatestSchema(PDO $pdo): void
    {
        if (!AUTO_INSTALL_DATABASE) {
            return;
        }

        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'student_code'");
            $hasStudentCode = (bool)$stmt->fetch();
            if (!$hasStudentCode) {
                self::installDatabase();
                self::$pdo = null;
            }
        } catch (Throwable $e) {
            self::installDatabase();
            self::$pdo = null;
        }
    }

    private static function installDatabase(): void
    {
        $sqlFile = BASE_PATH . '/database/lab_equipment_manager.sql';
        if (!file_exists($sqlFile)) {
            die('Database does not exist and SQL file was not found: database/lab_equipment_manager.sql');
        }

        $pdo = new PDO('mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
        ]);
        $sql = file_get_contents($sqlFile);
        $pdo->exec($sql);
    }
}
