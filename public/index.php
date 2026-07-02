<?php

/**
 * MovieChallenge — Front Controller
 * All requests are routed through this file.
 * 
 * Start with: php -S localhost:8080 -t public/
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Basic Authentication
$authUser = 'mashfrog';
$authPass = 'Movies123!';

if (!isset($_SERVER['PHP_AUTH_USER']) || 
    !isset($_SERVER['PHP_AUTH_PW']) || 
    $_SERVER['PHP_AUTH_USER'] !== $authUser || 
    $_SERVER['PHP_AUTH_PW'] !== $authPass
) {
    header('WWW-Authenticate: Basic realm="MovieChallenge Realm"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Richiesta autenticazione per accedere al progetto.';
    exit;
}

use Dotenv\Dotenv;
use MovieChallenge\Router;
use MovieChallenge\Service\ChallengeService;
use MovieChallenge\Service\TeamService;
use MovieChallenge\Repository\MySQL\MovieRepository as MySQLMovieRepo;
use MovieChallenge\Repository\MySQL\ActorRepository as MySQLActorRepo;
use MovieChallenge\Repository\Neo4j\MovieRepository as Neo4jMovieRepo;
use MovieChallenge\Repository\Neo4j\ActorRepository as Neo4jActorRepo;

// Load environment (only if .env exists - Docker sets env vars directly)
$envFile = __DIR__ . '/..';
if (file_exists($envFile . '/.env')) {
    $dotenv = Dotenv::createImmutable($envFile);
    $dotenv->load();
}

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Europe/Rome');

$router = new Router();

// ============================================================
// Pages (HTML)
// ============================================================

// Dashboard
$router->get('/', function () {
    Router::render('dashboard', [
        'pageTitle' => 'MovieChallenge — MySQL vs Neo4j',
    ]);
});

// Challenge page
$router->get('/challenge/{id}', function (array $params) {
    $challengeService = new ChallengeService();
    $challenges = $challengeService->getChallenges();
    $id = (int) $params['id'];

    if (!isset($challenges[$id])) {
        http_response_code(404);
        echo "Challenge not found";
        return;
    }

    Router::render('challenge', [
        'pageTitle' => $challenges[$id]['title'] . ' — MovieChallenge',
        'challenge' => $challenges[$id],
        'sqlQuery' => $challengeService->getSqlQuery($id),
        'cypherQuery' => $challengeService->getCypherQuery($id),
        'sqlTemplate' => $challengeService->getSqlTemplate($id),
    ]);
});
// Schema page
$router->get('/schema', function () {
    Router::render('schema', [
        'pageTitle' => 'Schema Database — MovieChallenge',
    ]);
});

// Graph Schema page (Teacher Mode)
$router->get('/graph-schema', function () {
    Router::render('graph-schema', [
        'pageTitle' => 'Schema Grafo Neo4j — MovieChallenge',
    ]);
});

// Leaderboard page
$router->get('/leaderboard', function () {
    Router::render('leaderboard', [
        'pageTitle' => 'Classifica — MovieChallenge',
    ]);
});

// ============================================================
// API Endpoints (JSON)
// ============================================================

// Get database stats
$router->get('/api/stats', function () {
    try {
        $challengeService = new ChallengeService();
        $stats = $challengeService->getStats();
        Router::json($stats);
    } catch (\Throwable $e) {
        Router::json(['error' => $e->getMessage()], 500);
    }
});

// Get challenges list
$router->get('/api/challenges', function () {
    $challengeService = new ChallengeService();
    Router::json($challengeService->getChallenges());
});

// Search actors
$router->get('/api/actors/search', function () {
    $query = $_GET['q'] ?? '';
    if (strlen($query) < 2) {
        Router::json([]);
        return;
    }
    try {
        $repo = new MySQLActorRepo();
        Router::json($repo->searchByName($query));
    } catch (\Throwable $e) {
        Router::json(['error' => $e->getMessage()], 500);
    }
});

// Search movies
$router->get('/api/movies/search', function () {
    $query = $_GET['q'] ?? '';
    if (strlen($query) < 2) {
        Router::json([]);
        return;
    }
    try {
        $repo = new MySQLMovieRepo();
        Router::json($repo->searchByTitle($query));
    } catch (\Throwable $e) {
        Router::json(['error' => $e->getMessage()], 500);
    }
});

// Run Challenge 1: Six Degrees
$router->post('/api/challenge/1/run', function () {
    $input = json_decode(file_get_contents('php://input'), true);
    $actorId = (int) ($input['actor_id'] ?? 0);
    $maxDepth = min(6, max(1, (int) ($input['max_depth'] ?? 3)));
    $teamName = $input['team_name'] ?? '';
    $userSql = $input['sql_query'] ?? '';

    if ($actorId === 0) {
        Router::json(['error' => 'actor_id is required'], 400);
        return;
    }
    if (empty(trim($userSql))) {
        Router::json(['error' => 'La query SQL è richiesta per la challenge.'], 400);
        return;
    }

    try {
        $challengeService = new ChallengeService();
        $result = $challengeService->runSixDegrees($actorId, $maxDepth, $userSql, $teamName);

        // Record result if team specified and validation is successful
        if ($teamName && isset($result['validation']['valid']) && $result['validation']['valid'] === true) {
            $teamService = new TeamService();
            $teamService->recordResult(
                $teamName, 1,
                $result['mysql']['time_ms'],
                $result['neo4j']['time_ms'],
                $result['neo4j']['result_count']
            );
        }

        Router::json($result);
    } catch (\Throwable $e) {
        Router::json(['error' => $e->getMessage()], 500);
    }
});

// Run Challenge 1: Scalability Test
$router->post('/api/challenge/1/scalability', function () {
    $input = json_decode(file_get_contents('php://input'), true);
    $actorId = (int) ($input['actor_id'] ?? 0);

    if ($actorId === 0) {
        Router::json(['error' => 'actor_id is required'], 400);
        return;
    }

    try {
        $challengeService = new ChallengeService();
        $result = $challengeService->runSixDegreesScalability($actorId);
        Router::json($result);
    } catch (\Throwable $e) {
        Router::json(['error' => $e->getMessage()], 500);
    }
});

// Run Challenge 2: Shortest Path
$router->post('/api/challenge/2/run', function () {
    $input = json_decode(file_get_contents('php://input'), true);
    $actorId1 = (int) ($input['actor_id_1'] ?? 0);
    $actorId2 = (int) ($input['actor_id_2'] ?? 0);
    $teamName = $input['team_name'] ?? '';
    $userSql = $input['sql_query'] ?? '';

    if ($actorId1 === 0 || $actorId2 === 0) {
        Router::json(['error' => 'actor_id_1 and actor_id_2 are required'], 400);
        return;
    }
    if (empty(trim($userSql))) {
        Router::json(['error' => 'La query SQL è richiesta per la challenge.'], 400);
        return;
    }

    try {
        $challengeService = new ChallengeService();
        $result = $challengeService->runShortestPath($actorId1, $actorId2, $userSql, $teamName);

        // Record result if team specified and validation is successful
        if ($teamName && isset($result['validation']['valid']) && $result['validation']['valid'] === true) {
            $teamService = new TeamService();
            $teamService->recordResult(
                $teamName, 2,
                $result['mysql']['time_ms'],
                $result['neo4j']['time_ms'],
                $result['neo4j']['result_count']
            );
        }

        Router::json($result);
    } catch (\Throwable $e) {
        Router::json(['error' => $e->getMessage()], 500);
    }
});

// Run Challenge 3: Recommendations
$router->post('/api/challenge/3/run', function () {
    $input = json_decode(file_get_contents('php://input'), true);
    $userId = (int) ($input['user_id'] ?? 1);
    $minRating = (float) ($input['min_rating'] ?? 3.5);
    $teamName = $input['team_name'] ?? '';
    $userSql = $input['sql_query'] ?? '';

    if (empty(trim($userSql))) {
        Router::json(['error' => 'La query SQL è richiesta per la challenge.'], 400);
        return;
    }

    try {
        $challengeService = new ChallengeService();
        $result = $challengeService->runRecommendations($userId, $minRating, $userSql, $teamName);

        // Record result if team specified and validation is successful
        if ($teamName && isset($result['validation']['valid']) && $result['validation']['valid'] === true) {
            $teamService = new TeamService();
            $teamService->recordResult(
                $teamName, 3,
                $result['mysql']['time_ms'],
                $result['neo4j']['time_ms'],
                $result['neo4j']['result_count']
            );
        }

        Router::json($result);
    } catch (\Throwable $e) {
        Router::json(['error' => $e->getMessage()], 500);
    }
});

// Get leaderboard
$router->get('/api/leaderboard', function () {
    try {
        $teamService = new TeamService();
        Router::json([
            'leaderboard' => $teamService->getLeaderboard(),
            'teams' => $teamService->getDefaultTeams(),
        ]);
    } catch (\Throwable $e) {
        Router::json(['error' => $e->getMessage()], 500);
    }
});

// Reset leaderboard
$router->post('/api/leaderboard/reset', function () {
    try {
        $db = \MovieChallenge\Database\MySQLConnection::getInstance();
        $db->exec("TRUNCATE TABLE challenge_results");
        Router::json(['success' => true, 'message' => 'Classifica azzerata con successo!']);
    } catch (\Throwable $e) {
        Router::json(['error' => $e->getMessage()], 500);
    }
});

// Get users (for recommendations challenge)
$router->get('/api/users', function () {
    try {
        $db = \MovieChallenge\Database\MySQLConnection::getInstance();
        $stmt = $db->query("SELECT id, username, team_name FROM users ORDER BY id LIMIT 150");
        Router::json($stmt->fetchAll());
    } catch (\Throwable $e) {
        Router::json(['error' => $e->getMessage()], 500);
    }
});

// Dispatch
$router->dispatch();
