<?php

/**
 * Database Setup Script
 * Creates the MySQL schema and Neo4j constraints.
 * 
 * Usage: php scripts/setup.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use MovieChallenge\Database\MySQLConnection;
use MovieChallenge\Database\Neo4jConnection;

// Load environment (only if .env exists)
$envFile = __DIR__ . '/..';
if (file_exists($envFile . '/.env')) {
    $dotenv = Dotenv::createImmutable($envFile);
    $dotenv->load();
}

echo "\n🎬 MovieChallenge — Database Setup\n";
echo str_repeat('=', 50) . "\n\n";

// ============================================================
// MySQL Schema
// ============================================================
echo "📦 Setting up MySQL schema...\n";

try {
    $db = MySQLConnection::getInstance();

    // Drop tables in reverse dependency order
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    $tables = ['challenge_results', 'reviews', 'movie_genres', 'movie_cast', 'users', 'actors', 'genres', 'movies'];
    foreach ($tables as $table) {
        $db->exec("DROP TABLE IF EXISTS {$table}");
    }
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");

    // Create tables
    $db->exec("
        CREATE TABLE movies (
            id INT PRIMARY KEY AUTO_INCREMENT,
            tmdb_id INT UNIQUE,
            title VARCHAR(500) NOT NULL,
            release_date DATE NULL,
            overview TEXT,
            vote_average DECIMAL(3,1) DEFAULT 0,
            poster_path VARCHAR(255),
            INDEX idx_title (title(100)),
            INDEX idx_vote (vote_average)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  ✅ Table 'movies' created\n";

    $db->exec("
        CREATE TABLE actors (
            id INT PRIMARY KEY AUTO_INCREMENT,
            tmdb_id INT UNIQUE,
            name VARCHAR(255) NOT NULL,
            profile_path VARCHAR(255),
            INDEX idx_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  ✅ Table 'actors' created\n";

    $db->exec("
        CREATE TABLE movie_cast (
            id INT PRIMARY KEY AUTO_INCREMENT,
            movie_id INT NOT NULL,
            actor_id INT NOT NULL,
            character_name VARCHAR(500),
            cast_order INT DEFAULT 0,
            FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
            FOREIGN KEY (actor_id) REFERENCES actors(id) ON DELETE CASCADE,
            INDEX idx_movie (movie_id),
            INDEX idx_actor (actor_id),
            UNIQUE KEY uk_movie_actor (movie_id, actor_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  ✅ Table 'movie_cast' created\n";

    $db->exec("
        CREATE TABLE users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(100) UNIQUE NOT NULL,
            team_name VARCHAR(100),
            INDEX idx_team (team_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  ✅ Table 'users' created\n";

    $db->exec("
        CREATE TABLE reviews (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            movie_id INT NOT NULL,
            rating DECIMAL(2,1) NOT NULL,
            comment TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
            INDEX idx_user (user_id),
            INDEX idx_movie (movie_id),
            INDEX idx_rating (rating),
            UNIQUE KEY uk_user_movie (user_id, movie_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  ✅ Table 'reviews' created\n";

    $db->exec("
        CREATE TABLE genres (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) UNIQUE NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  ✅ Table 'genres' created\n";

    $db->exec("
        CREATE TABLE movie_genres (
            movie_id INT NOT NULL,
            genre_id INT NOT NULL,
            PRIMARY KEY (movie_id, genre_id),
            FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
            FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  ✅ Table 'movie_genres' created\n";

    $db->exec("
        CREATE TABLE challenge_results (
            id INT PRIMARY KEY AUTO_INCREMENT,
            team_name VARCHAR(100) NOT NULL,
            challenge_id INT NOT NULL,
            mysql_time_ms DECIMAL(10,3) NOT NULL,
            neo4j_time_ms DECIMAL(10,3) NOT NULL,
            result_count INT DEFAULT 0,
            score INT DEFAULT 0,
            completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_team (team_name),
            INDEX idx_challenge (challenge_id),
            UNIQUE KEY uk_team_challenge (team_name, challenge_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  ✅ Table 'challenge_results' created\n";

    echo "\n✅ MySQL schema setup complete!\n\n";

} catch (\Exception $e) {
    echo "\n❌ MySQL setup failed: " . $e->getMessage() . "\n";
    echo "   Make sure MySQL is running: docker-compose up -d mysql\n\n";
    exit(1);
}

// ============================================================
// Neo4j Constraints
// ============================================================
echo "📦 Setting up Neo4j constraints...\n";

try {
    $client = Neo4jConnection::getInstance();

    // Drop all data first
    $client->run('MATCH (n) DETACH DELETE n');
    echo "  🗑️  Cleared existing Neo4j data\n";

    // Create constraints and indexes
    $constraints = [
        "CREATE CONSTRAINT movie_id IF NOT EXISTS FOR (m:Movie) REQUIRE m.id IS UNIQUE",
        "CREATE CONSTRAINT movie_tmdb IF NOT EXISTS FOR (m:Movie) REQUIRE m.tmdb_id IS UNIQUE",
        "CREATE CONSTRAINT actor_id IF NOT EXISTS FOR (a:Actor) REQUIRE a.id IS UNIQUE",
        "CREATE CONSTRAINT actor_tmdb IF NOT EXISTS FOR (a:Actor) REQUIRE a.tmdb_id IS UNIQUE",
        "CREATE CONSTRAINT user_id IF NOT EXISTS FOR (u:User) REQUIRE u.id IS UNIQUE",
        "CREATE CONSTRAINT user_username IF NOT EXISTS FOR (u:User) REQUIRE u.username IS UNIQUE",
        "CREATE CONSTRAINT genre_id IF NOT EXISTS FOR (g:Genre) REQUIRE g.id IS UNIQUE",
    ];

    foreach ($constraints as $constraint) {
        try {
            $client->run($constraint);
            // Extract constraint name from the query
            preg_match('/CONSTRAINT (\w+)/', $constraint, $matches);
            echo "  ✅ Constraint '{$matches[1]}' created\n";
        } catch (\Exception $e) {
            // Constraint might already exist
            echo "  ⚠️  Constraint might already exist: " . $e->getMessage() . "\n";
        }
    }

    // Create indexes for performance
    $indexes = [
        "CREATE INDEX actor_name IF NOT EXISTS FOR (a:Actor) ON (a.name)",
        "CREATE INDEX movie_title IF NOT EXISTS FOR (m:Movie) ON (m.title)",
        "CREATE INDEX movie_vote IF NOT EXISTS FOR (m:Movie) ON (m.vote_average)",
    ];

    foreach ($indexes as $index) {
        try {
            $client->run($index);
            preg_match('/INDEX (\w+)/', $index, $matches);
            echo "  ✅ Index '{$matches[1]}' created\n";
        } catch (\Exception $e) {
            echo "  ⚠️  Index might already exist\n";
        }
    }

    echo "\n✅ Neo4j constraints setup complete!\n\n";

} catch (\Exception $e) {
    echo "\n❌ Neo4j setup failed: " . $e->getMessage() . "\n";
    echo "   Make sure Neo4j is running: docker-compose up -d neo4j\n\n";
    exit(1);
}

echo "🎉 All databases are ready! Run 'php scripts/seed.php' to import data.\n\n";
