<?php

/**
 * Data Seeding Script
 * Imports real movie data from TMDb API and seeds both MySQL and Neo4j.
 * 
 * Usage: php scripts/seed.php [number_of_movies]
 * Example: php scripts/seed.php 500
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use MovieChallenge\Seeder\TMDbImporter;
use MovieChallenge\Seeder\MySQLSeeder;
use MovieChallenge\Seeder\Neo4jSeeder;

// Load environment (only if .env exists)
$envFile = __DIR__ . '/..';
if (file_exists($envFile . '/.env')) {
    $dotenv = Dotenv::createImmutable($envFile);
    $dotenv->load();
}

// Parse arguments
$totalMovies = (int) ($argv[1] ?? 1000);

echo "\n🎬 MovieChallenge — Data Seeding\n";
echo str_repeat('=', 50) . "\n\n";
echo "📊 Target: {$totalMovies} movies from TMDb\n\n";

// Progress callback
$progress = function ($current, $total, $message) {
    echo "{$message}\n";
};

// ============================================================
// Step 1: Import from TMDb
// ============================================================
echo "📡 Step 1: Fetching data from TMDb API...\n";
echo "   (This may take a few minutes due to API rate limiting)\n\n";

$importer = new TMDbImporter();

try {
    $data = $importer->importMovies($totalMovies, $progress);
} catch (\Exception $e) {
    echo "\n❌ TMDb import failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n📊 Import Summary:\n";
echo "   🎬 Movies: " . count($data['movies']) . "\n";
echo "   🎭 Actors: " . count($data['actors']) . "\n";
echo "   📁 Genres: " . count($data['genres']) . "\n";
echo "   🔗 Cast relations: " . count($data['cast']) . "\n\n";

// ============================================================
// Step 2: Generate synthetic reviews
// ============================================================
echo "⭐ Step 2: Generating synthetic reviews...\n";

$movieIds = array_keys($data['movies']);
$reviewData = $importer->generateReviews($movieIds, 33, 30);
$data['users'] = $reviewData['users'];
$data['reviews'] = $reviewData['reviews'];

echo "   👥 Users: " . count($data['users']) . " (11 per team × 3 teams)\n";
echo "   ⭐ Reviews: " . count($data['reviews']) . "\n\n";

// ============================================================
// Step 3: Seed MySQL
// ============================================================
echo "🐬 Step 3: Seeding MySQL...\n";

try {
    $mysqlSeeder = new MySQLSeeder();
    $mysqlSeeder->seed($data, $progress);
} catch (\Exception $e) {
    echo "\n❌ MySQL seeding failed: " . $e->getMessage() . "\n";
    echo "   Make sure you ran 'php scripts/setup.php' first!\n\n";
    exit(1);
}

echo "\n";

// ============================================================
// Step 4: Seed Neo4j
// ============================================================
echo "🔵 Step 4: Seeding Neo4j...\n";

try {
    $neo4jSeeder = new Neo4jSeeder();
    $neo4jSeeder->seed($data, $progress);
} catch (\Exception $e) {
    echo "\n❌ Neo4j seeding failed: " . $e->getMessage() . "\n";
    echo "   Make sure Neo4j is running: docker-compose up -d neo4j\n\n";
    exit(1);
}

echo "\n";

// ============================================================
// Done!
// ============================================================
echo str_repeat('=', 50) . "\n";
echo "🎉 Seeding complete!\n\n";
echo "Both MySQL and Neo4j now contain:\n";
echo "   🎬 " . count($data['movies']) . " movies\n";
echo "   🎭 " . count($data['actors']) . " actors\n";
echo "   🔗 " . count($data['cast']) . " ACTED_IN relationships\n";
echo "   ⭐ " . count($data['reviews']) . " reviews\n";
echo "   👥 " . count($data['users']) . " users in 3 teams\n\n";
echo "Start the web server:\n";
echo "   php -S localhost:8080 -t public/\n\n";
echo "Then open: http://localhost:8080\n\n";
