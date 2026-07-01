<?php

namespace MovieChallenge\Database;

use PDO;
use PDOException;

/**
 * MySQL Connection Singleton
 * Provides a single PDO instance for the application.
 */
class MySQLConnection
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                $host = $_ENV['MYSQL_HOST'] ?? '127.0.0.1';
                $port = $_ENV['MYSQL_PORT'] ?? '3306';
                $db = $_ENV['MYSQL_DATABASE'] ?? 'movie_challenge';
                $user = $_ENV['MYSQL_USER'] ?? 'challenge_user';
                $pass = $_ENV['MYSQL_PASSWORD'] ?? 'challenge_pass';

                $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => true,
                ];

                // Add MYSQL_ATTR_INIT_COMMAND if available
                if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
                    $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci";
                }

                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                throw new \RuntimeException("MySQL connection failed: " . $e->getMessage());
            }
        }

        return self::$instance;
    }

    /**
     * Reset the connection (useful for testing).
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}
