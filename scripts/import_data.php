<?php

/**
 * Offline Data Seeding Script
 * Reads real movie and user reviews data from data/dump.json and seeds both MySQL and Neo4j.
 * 
 * Usage: php scripts/import_data.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use MovieChallenge\Seeder\MySQLSeeder;
use MovieChallenge\Seeder\Neo4jSeeder;

// Load environment (only if .env exists)
$envFile = __DIR__ . '/..';
if (file_exists($envFile . '/.env')) {
    $dotenv = Dotenv::createImmutable($envFile);
    $dotenv->load();
}

echo "\n🎬 MovieChallenge — Offline Data Importing\n";
echo str_repeat('=', 50) . "\n\n";

$dumpFile = __DIR__ . '/../data/dump.json';
if (!file_exists($dumpFile)) {
    echo "❌ Error: Dump file data/dump.json not found!\n";
    echo "   Please run 'php scripts/export_db_to_json.php' in your local environment first to export data.\n\n";
    exit(1);
}

echo "📂 Reading dump file: data/dump.json...\n";
$jsonContent = file_get_contents($dumpFile);
$data = json_decode($jsonContent, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ Error parsing JSON dump: " . json_last_error_msg() . "\n\n";
    exit(1);
}

echo "📊 Loaded Dataset Summary:\n";
echo "   🎬 Movies: " . count($data['movies'] ?? []) . "\n";
echo "   🎭 Actors: " . count($data['actors'] ?? []) . "\n";
echo "   📁 Genres: " . count($data['genres'] ?? []) . "\n";
echo "   🔗 Cast relations: " . count($data['cast'] ?? []) . "\n";
echo "   👥 Users: " . count($data['users'] ?? []) . "\n";
echo "   ⭐ Reviews: " . count($data['reviews'] ?? []) . "\n\n";

// Progress callback
$progress = function ($current, $total, $message) {
    echo "{$message}\n";
};

// ============================================================
// Step 1: Seed MySQL
// ============================================================
echo "🐬 Step 1: Seeding MySQL...\n";

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
// Step 2: Seed Neo4j
// ============================================================
echo "🔵 Step 2: Seeding Neo4j...\n";

try {
    $neo4jSeeder = new Neo4jSeeder();
    $neo4jSeeder->seed($data, $progress);
} catch (\Exception $e) {
    echo "\n❌ Neo4j seeding failed: " . $e->getMessage() . "\n";
    echo "   Make sure Neo4j is running: docker-compose up -d neo4j\n\n";
    exit(1);
}

echo "\n" . str_repeat('=', 50) . "\n";
echo "🎉 Import complete! Databases successfully populated from JSON dump.\n\n";
