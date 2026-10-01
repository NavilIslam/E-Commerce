<?php
/**
 * Database Singleton Connection Manager
 */

class Database {
    private static ?Database $instance = null;
    private ?PDO $pdo = null;

    private function __construct() {
        $configPath = ROOT_PATH . '/config/database.php';
        if (!file_exists($configPath)) {
            throw new Exception("Database configuration file not found.");
        }
        $config = require $configPath;

        $connected = false;

        // Try MySQL first if not explicitly configured for sqlite
        if (($config['driver'] ?? 'mysql') !== 'sqlite') {
            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                $config['host'],
                $config['port'],
                $config['dbname'],
                $config['charset']
            );

            try {
                $this->pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
                $connected = true;
            } catch (PDOException $e) {
                error_log("MySQL connection failed: " . $e->getMessage() . " - falling back to SQLite.");
            }
        }

        // Fallback to SQLite if MySQL failed or driver is sqlite
        if (!$connected) {
            $sqlitePath = ROOT_PATH . '/database/ecommerce.sqlite';
            if (!file_exists($sqlitePath)) {
                throw new Exception("Database connection failed. Please verify your database configuration.");
            }

            $this->pdo = new PDO("sqlite:" . $sqlitePath, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);

            // Register MySQL compatibility functions for SQLite
            $this->pdo->sqliteCreateFunction('CURDATE', fn() => date('Y-m-d'));
            $this->pdo->sqliteCreateFunction('NOW', fn() => date('Y-m-d H:i:s'));
            $this->pdo->sqliteCreateFunction('DATE', fn($d) => date('Y-m-d', strtotime($d ?? 'now')));
            $this->pdo->sqliteCreateFunction('MONTH', fn($d) => (int)date('m', strtotime($d ?? 'now')));
            $this->pdo->sqliteCreateFunction('YEAR', fn($d) => (int)date('Y', strtotime($d ?? 'now')));
            $this->pdo->sqliteCreateFunction('DATE_SUB', fn($date, $interval) => date('Y-m-d H:i:s', strtotime('-7 days', strtotime($date ?? 'now'))));
            $this->pdo->sqliteCreateFunction('IFNULL', fn($a, $b) => $a !== null ? $a : $b);
        }
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->pdo;
    }

    public function query(string $sql, array $params = []): PDOStatement {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchAll(string $sql, array $params = []): array {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetchOne(string $sql, array $params = []): ?array {
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }

    public function fetchColumn(string $sql, array $params = [], int $columnIndex = 0) {
        return $this->query($sql, $params)->fetchColumn($columnIndex);
    }

    public function insert(string $table, array $data): int {
        $keys = array_keys($data);
        $fields = implode(', ', array_map(fn($k) => "`$k`", $keys));
        $placeholders = implode(', ', array_map(fn($k) => ":$k", $keys));

        $sql = "INSERT INTO `$table` ($fields) VALUES ($placeholders)";
        $this->query($sql, $data);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): int {
        $setClauses = [];
        $params = [];

        foreach ($data as $key => $value) {
            $setClauses[] = "`$key` = :set_$key";
            $params["set_$key"] = $value;
        }

        $params = array_merge($params, $whereParams);
        $sql = "UPDATE `$table` SET " . implode(', ', $setClauses) . " WHERE $where";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function delete(string $table, string $where, array $params = []): int {
        $sql = "DELETE FROM `$table` WHERE $where";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function lastInsertId(): int {
        return (int) $this->pdo->lastInsertId();
    }

    public function beginTransaction(): bool {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool {
        return $this->pdo->commit();
    }

    public function rollBack(): bool {
        return $this->pdo->rollBack();
    }
}
