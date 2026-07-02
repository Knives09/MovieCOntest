<?php

/**
 * Database Export Script
 * Exports the current MySQL database to data/dump.json for offline seeding.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use MovieChallenge\Database\MySQLConnection;

// Load environment
$envFile = __DIR__ . '/..';
if (file_exists($envFile . '/.env')) {
    $dotenv = Dotenv::createImmutable($envFile);
    $dotenv->load();
}

echo "\n🎬 MovieChallenge — Exporting Database to JSON...\n";
echo str_repeat('=', 50) . "\n\n";

try {
    $pdo = MySQLConnection::getInstance();
} catch (\Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n\n";
    exit(1);
}

$data = [
    'genres' => [],
    'movies' => [],
    'actors' => [],
    'cast' => [],
    'users' => [],
    'reviews' => []
];

// 1. Export Genres
echo "📁 Exporting Genres...\n";
$stmt = $pdo->query("SELECT * FROM genres");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data['genres'][] = [
        'id' => (int)$row['id'],
        'name' => $row['name'],
        'tmdb_id' => (int)$row['id']
    ];
}

// 2. Export Movies
echo "🎬 Exporting Movies...\n";
$stmt = $pdo->query("SELECT * FROM movies");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data['movies'][$row['id']] = [
        'id' => (int)$row['id'],
        'title' => $row['title'],
        'overview' => $row['overview'],
        'release_date' => $row['release_date'],
        'poster_path' => $row['poster_path'],
        'vote_average' => (float)$row['vote_average'],
        'tmdb_id' => (int)$row['tmdb_id'],
        'genre_ids' => []
    ];
}

// Export Movie Genres links
$stmt = $pdo->query("SELECT * FROM movie_genres");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if (isset($data['movies'][$row['movie_id']])) {
        $data['movies'][$row['movie_id']]['genre_ids'][] = (int)$row['genre_id'];
    }
}

// 3. Export Actors
echo "🎭 Exporting Actors...\n";
$stmt = $pdo->query("SELECT * FROM actors");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data['actors'][$row['id']] = [
        'id' => (int)$row['id'],
        'name' => $row['name'],
        'profile_path' => $row['profile_path'],
        'tmdb_id' => (int)$row['tmdb_id']
    ];
}

// 4. Export Cast relations
echo "🔗 Exporting Cast relations...\n";
$stmt = $pdo->query("SELECT * FROM movie_cast");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data['cast'][] = [
        'movie_id' => (int)$row['movie_id'],
        'actor_id' => (int)$row['actor_id'],
        'character_name' => $row['character_name'],
        'cast_order' => (int)$row['cast_order']
    ];
}

// 5. Export Users
echo "👥 Exporting Users...\n";
$stmt = $pdo->query("SELECT * FROM users");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data['users'][] = [
        'id' => (int)$row['id'],
        'username' => $row['username'],
        'team_name' => $row['team_name']
    ];
}

// 6. Export Reviews
echo "⭐ Exporting Reviews...\n";
$stmt = $pdo->query("SELECT * FROM reviews");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data['reviews'][] = [
        'id' => (int)$row['id'],
        'user_id' => (int)$row['user_id'],
        'movie_id' => (int)$row['movie_id'],
        'rating' => (float)$row['rating'],
        'comment' => $row['comment'],
        'created_at' => $row['created_at']
    ];
}

// Save to file
$dataDir = __DIR__ . '/../data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}
$filePath = $dataDir . '/dump.json';
file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "\n" . str_repeat('=', 50) . "\n";
echo "🎉 Export complete!\n";
echo "Saved " . count($data['movies']) . " movies, " . count($data['actors']) . " actors, and " . count($data['reviews']) . " reviews to: data/dump.json\n\n";
