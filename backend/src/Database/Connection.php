<?php

namespace App\Database;

use PDO;
use PDOException;

class Connection
{
    private static ?PDO $instance = null;
    private static array $config;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$config = require __DIR__ . '/../../config/database.php';
            self::connect();
        }

        return self::$instance;
    }

    private static function connect(): void
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                self::$config['host'],
                self::$config['dbname'],
                self::$config['charset']
            );

            self::$instance = new PDO(
                $dsn,
                self::$config['username'],
                self::$config['password'],
                self::$config['options']
            );

            // Test de la connexion
            self::$instance->query('SELECT 1');

        } catch (PDOException $e) {
            error_log('Erreur de connexion à la base de données: ' . $e->getMessage());
            throw new PDOException('Impossible de se connecter à la base de données', 500);
        }
    }

    public static function beginTransaction(): bool
    {
        return self::getInstance()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::getInstance()->commit();
    }

    public static function rollback(): bool
    {
        return self::getInstance()->rollBack();
    }

    // Empêcher le clonage et la désérialisation

    public function __clone() {}
    public function __wakeup() {}
}