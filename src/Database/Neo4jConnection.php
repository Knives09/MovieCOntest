<?php

namespace MovieChallenge\Database;

use Laudis\Neo4j\ClientBuilder;
use Laudis\Neo4j\Contracts\ClientInterface;

/**
 * Neo4j Connection Singleton
 * Provides a single Neo4j client instance using laudis/neo4j-php-client.
 */
class Neo4jConnection
{
    private static ?ClientInterface $instance = null;

    public static function getInstance(): ClientInterface
    {
        if (self::$instance === null) {
            $host = $_ENV['NEO4J_HOST'] ?? '127.0.0.1';
            $port = $_ENV['NEO4J_PORT'] ?? '7687';
            $user = $_ENV['NEO4J_USER'] ?? 'neo4j';
            $pass = $_ENV['NEO4J_PASSWORD'] ?? 'challenge123';

            $uri = "bolt://{$user}:{$pass}@{$host}:{$port}";

            self::$instance = ClientBuilder::create()
                ->withDriver('bolt', $uri)
                ->withDefaultDriver('bolt')
                ->build();
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
