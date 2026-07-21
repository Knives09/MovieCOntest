<?php

namespace MovieChallenge\Service;

use MovieChallenge\Repository\MySQL\ChallengeRepository as MySQLChallengeRepo;
use MovieChallenge\Repository\Neo4j\ChallengeRepository as Neo4jChallengeRepo;

/**
 * Challenge Service
 * Orchestrates the 3 challenges, running queries on both databases
 * and collecting benchmark results.
 */
class ChallengeService
{
    private MySQLChallengeRepo $mysqlRepo;
    private Neo4jChallengeRepo $neo4jRepo;
    private BenchmarkService $benchmark;

    public function __construct()
    {
        $this->mysqlRepo = new MySQLChallengeRepo();
        $this->neo4jRepo = new Neo4jChallengeRepo();
        $this->benchmark = new BenchmarkService();
    }

    /**
     * Get the list of available challenges.
     */
    public function getChallenges(): array
    {
        return [
            1 => [
                'id' => 1,
                'title' => 'Six Degrees of Kevin Bacon',
                'description' => 'Trova tutti gli attori collegati entro N gradi di separazione da un attore dato.',
                'icon' => '🎯',
                'difficulty' => 'Media',
                'mysql_weakness' => 'JOIN ricorsivi esponenziali: ogni hop moltiplica il costo',
                'neo4j_strength' => 'Index-free adjacency: O(1) per hop grazie ai puntatori diretti',
            ],
            2 => [
                'id' => 2,
                'title' => 'Shortest Path',
                'description' => 'Trova il percorso più breve tra due attori attraverso film in comune.',
                'icon' => '🛤️',
                'difficulty' => 'Alta',
                'mysql_weakness' => 'BFS manuale con query iterative e tracking del percorso',
                'neo4j_strength' => 'shortestPath() nativo con BFS bidirezionale ottimizzato',
            ],
            3 => [
                'id' => 3,
                'title' => 'Raccomandazioni Film',
                'description' => 'Suggerisci film basandoti su gusti simili di altri utenti (collaborative filtering).',
                'icon' => '⭐',
                'difficulty' => 'Alta',
                'mysql_weakness' => 'Multi-JOIN + subquery + aggregazione = query illeggibile',
                'neo4j_strength' => 'Pattern matching: la query è quasi linguaggio naturale',
            ],
        ];
    }

    /**
     * Run Challenge 1: Six Degrees with user-provided SQL.
     */
    public function runSixDegrees(int $actorId, int $maxDepth, string $userSql, string $teamName): array
    {
        // Pre-warm Neo4j connection to exclude handshake from benchmark
        try {
            \MovieChallenge\Database\Neo4jConnection::getInstance()->run("RETURN 1");
        } catch (\Exception $e) {}

        $neo4jFn = fn() => $this->neo4jRepo->sixDegrees($actorId, $maxDepth);
        $params = [
            'actor_id' => $actorId,
            'max_depth' => $maxDepth,
        ];

        $comparison = $this->benchmark->compareUserQuery($userSql, $params, $neo4jFn);
        
        // Validation
        $comparison['validation'] = $this->validateSixDegrees($comparison['mysql']['result'], $comparison['neo4j']['result']);
        
        return $comparison;
    }

    /**
     * Run Challenge 1 Scalability Test: Six Degrees with increasing depth.
     */
    public function runSixDegreesScalability(int $actorId): array
    {
        // Using the default reference SQL for scalability tests
        $refSql = "
            WITH RECURSIVE actor_connections AS (
                SELECT mc2.actor_id, 1 as depth
                FROM movie_cast mc1
                JOIN movie_cast mc2 ON mc1.movie_id = mc2.movie_id AND mc1.actor_id != mc2.actor_id
                WHERE mc1.actor_id = :actor_id
                UNION
                SELECT mc2.actor_id, ac.depth + 1
                FROM actor_connections ac
                JOIN movie_cast mc1 ON mc1.actor_id = ac.actor_id
                JOIN movie_cast mc2 ON mc1.movie_id = mc2.movie_id AND mc1.actor_id != mc2.actor_id
                WHERE ac.depth < :max_depth
            )
            SELECT a.name, MIN(ac.depth) as degrees
            FROM actor_connections ac
            JOIN actors a ON a.id = ac.actor_id
            GROUP BY a.name
            ORDER BY degrees;
        ";

        return $this->benchmark->scalabilityTest(
            fn(int $depth) => function() use ($actorId, $depth, $refSql) {
                $db = \MovieChallenge\Database\MySQLConnection::getInstance();
                $stmt = $db->prepare($refSql);
                $stmt->bindValue(':actor_id', $actorId, \PDO::PARAM_INT);
                $stmt->bindValue(':max_depth', $depth, \PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll();
            },
            fn(int $depth) => fn() => $this->neo4jRepo->sixDegrees($actorId, $depth),
            [1, 2, 3, 4] // Reduced depth for safety in live benchmark
        );
    }

    /**
     * Run Challenge 2: Shortest Path with user-provided SQL.
     */
    public function runShortestPath(int $actorId1, int $actorId2, string $userSql, string $teamName): array
    {
        // Pre-warm Neo4j connection to exclude handshake from benchmark
        try {
            \MovieChallenge\Database\Neo4jConnection::getInstance()->run("RETURN 1");
        } catch (\Exception $e) {}

        $neo4jFn = fn() => $this->neo4jRepo->shortestPath($actorId1, $actorId2);
        $params = [
            'actor_id_1' => $actorId1,
            'actor_id_2' => $actorId2,
        ];

        $comparison = $this->benchmark->compareUserQuery($userSql, $params, $neo4jFn);
        
        // Validation
        $comparison['validation'] = $this->validateShortestPath($comparison['mysql']['result'], $comparison['neo4j']['result']);
        
        return $comparison;
    }

    /**
     * Run Challenge 3: Recommendations with user-provided SQL.
     */
    public function runRecommendations(int $userId, float $minRating, string $userSql, string $teamName): array
    {
        // Pre-warm Neo4j connection to exclude handshake from benchmark
        try {
            \MovieChallenge\Database\Neo4jConnection::getInstance()->run("RETURN 1");
        } catch (\Exception $e) {}

        $neo4jFn = fn() => $this->neo4jRepo->recommendations($userId, $minRating);
        $params = [
            'user_id' => $userId,
            'min_rating' => $minRating,
        ];

        $comparison = $this->benchmark->compareUserQuery($userSql, $params, $neo4jFn);
        
        // Validation
        $comparison['validation'] = $this->validateRecommendations($comparison['mysql']['result'], $comparison['neo4j']['result']);
        
        return $comparison;
    }

    /**
     * Validate Challenge 1 results.
     */
    private function validateSixDegrees(mixed $mysqlResult, mixed $neo4jResult): array
    {
        if (!is_array($mysqlResult) || empty($mysqlResult)) {
            return ['valid' => false, 'message' => 'Nessun dato restituito dalla query MySQL.'];
        }

        // Normalize results to [name => depth] maps
        $mysqlMap = [];
        foreach ($mysqlResult as $row) {
            $rowValues = array_values($row);
            if (count($rowValues) >= 2) {
                // Assume first string-ish is name, first int-ish is depth
                $name = null;
                $depth = null;
                foreach ($rowValues as $val) {
                    if (is_numeric($val) && $depth === null) {
                        $depth = (int)$val;
                    } elseif (is_string($val) && $name === null) {
                        $name = trim($val);
                    }
                }
                if ($name !== null && $depth !== null) {
                    $mysqlMap[strtolower($name)] = $depth;
                }
            }
        }

        $neo4jMap = [];
        foreach ($neo4jResult as $row) {
            if (isset($row['name']) && isset($row['degrees'])) {
                $neo4jMap[strtolower(trim($row['name']))] = (int)$row['degrees'];
            }
        }

        if (empty($mysqlMap)) {
            return ['valid' => false, 'message' => 'Le colonne restituite non contengono un nome (stringa) e una profondità (numero). Assicurati di selezionare ad esempio: `actors.name` e `depth`.'];
        }

        // Compare intersections
        $correctCount = 0;
        $mismatches = 0;
        
        foreach ($neo4jMap as $actor => $depth) {
            if (isset($mysqlMap[$actor])) {
                if ($mysqlMap[$actor] === $depth) {
                    $correctCount++;
                } else {
                    $mismatches++;
                }
            }
        }

        $neo4jTotal = count($neo4jMap);
        $matchRate = $neo4jTotal > 0 ? ($correctCount / $neo4jTotal) : 0;

        if ($matchRate >= 0.85 && $mismatches === 0) {
            return [
                'valid' => true,
                'message' => "Ottimo! Risultati validati con successo. Corrispondenza del " . round($matchRate * 100) . "% con il database a grafo."
            ];
        }

        if ($mismatches > 0) {
            return [
                'valid' => false,
                'message' => "I gradi di separazione calcolati per alcuni attori non sono corretti rispetto al database a grafo."
            ];
        }

        return [
            'valid' => false,
            'message' => "I risultati non corrispondono al database a grafo di riferimento (trovati solo {$correctCount} attori corretti su {$neo4jTotal})."
        ];
    }

    /**
     * Validate Challenge 2 results.
     */
    private function validateShortestPath(mixed $mysqlResult, mixed $neo4jResult): array
    {
        if (empty($neo4jResult)) {
            // No path exists
            if (empty($mysqlResult)) {
                return ['valid' => true, 'message' => 'Validato! Nessun percorso esistente tra i due attori sia in SQL che in Neo4j.'];
            }
            return ['valid' => false, 'message' => 'Neo4j indica che non esiste un percorso, ma la tua query SQL ha restituito dei dati.'];
        }

        if (empty($mysqlResult)) {
            return ['valid' => false, 'message' => 'La query SQL non ha trovato alcun percorso, ma un percorso esiste nel database a grafo.'];
        }

        // Count elements or check length.
        // Shortest path has a fixed length.
        // Let's count how many actors/movies are in the path.
        // Neo4j returns alternating Actor - Movie - Actor...
        // Let's extract values from MySQL result.
        $mysqlElements = [];
        foreach ($mysqlResult as $row) {
            foreach ($row as $val) {
                if ($val) {
                    $strVal = strtolower(trim((string)$val));
                    if (str_contains($strVal, '->')) {
                        // Split path string by "->" separator
                        $parts = explode('->', $strVal);
                        foreach ($parts as $part) {
                            $mysqlElements[] = strtolower(trim($part));
                        }
                    } else {
                        $mysqlElements[] = $strVal;
                    }
                }
            }
        }

        $neo4jElements = [];
        foreach ($neo4jResult as $row) {
            if (isset($row['name'])) {
                $neo4jElements[] = strtolower(trim($row['name']));
            }
        }

        // Check if the target actor and start actor are in the path, and that path length matches.
        $mysqlUniqueCount = count(array_unique($mysqlElements));
        $neo4jUniqueCount = count(array_unique($neo4jElements));

        // Shortest path is defined by length of path
        $neo4jLength = count($neo4jElements);
        
        // Find if target actor is reached. We can check if the start and end actors from Neo4j exist in MySQL result.
        $startActor = $neo4jElements[0] ?? '';
        $endActor = end($neo4jElements) ?: '';

        $hasStart = false;
        $hasEnd = false;
        foreach ($mysqlElements as $el) {
            if ($el === $startActor) $hasStart = true;
            if ($el === $endActor) $hasEnd = true;
        }

        if (!$hasStart || !$hasEnd) {
            return ['valid' => false, 'message' => 'Il percorso trovato non collega correttamente l\'attore di partenza con quello di arrivo.'];
        }

        // Allow some flexibility in format, but path must have the same number of nodes/hops
        // In Neo4j, path has N nodes. If MySQL returned path has roughly same amount of unique nodes, it is correct.
        if (abs($mysqlUniqueCount - $neo4jUniqueCount) <= 1) {
            return [
                'valid' => true,
                'message' => "Ottimo! Percorso validato con successo. Lunghezza del percorso coerente con il database a grafo."
            ];
        }

        return [
            'valid' => false,
            'message' => "Il percorso trovato non è quello minimo (lunghezza trovata differente da quella di riferimento di Neo4j)."
        ];
    }

    /**
     * Validate Challenge 3 results.
     */
    private function validateRecommendations(mixed $mysqlResult, mixed $neo4jResult): array
    {
        if (empty($neo4jResult)) {
            return ['valid' => true, 'message' => 'Nessuna raccomandazione disponibile sia in SQL che in Neo4j.'];
        }

        if (empty($mysqlResult)) {
            return ['valid' => false, 'message' => 'La query SQL non ha restituito raccomandazioni, ma ce ne sono nel database a grafo.'];
        }

        // Extract movie titles from user SQL and reference Neo4j
        $mysqlMovies = [];
        foreach ($mysqlResult as $row) {
            $rowVals = array_values($row);
            // Assume first string value is the movie title
            foreach ($rowVals as $val) {
                if (is_string($val) && strlen($val) > 2 && !is_numeric($val)) {
                    $mysqlMovies[] = strtolower(trim($val));
                    break;
                }
            }
        }

        $neo4jMovies = [];
        foreach ($neo4jResult as $row) {
            if (isset($row['title'])) {
                $neo4jMovies[] = strtolower(trim($row['title']));
            }
        }

        // Compare top recommendations
        $matches = array_intersect(array_slice($mysqlMovies, 0, 10), array_slice($neo4jMovies, 0, 10));
        $matchRate = count($neo4jMovies) > 0 ? (count($matches) / min(10, count($neo4jMovies))) : 0;

        if ($matchRate >= 0.6) {
            return [
                'valid' => true,
                'message' => "Ottimo! Raccomandazioni validate con successo (corrispondenza dei suggerimenti principali: " . round($matchRate * 100) . "%)."
            ];
        }

        return [
            'valid' => false,
            'message' => "I film raccomandati non corrispondono a quelli corretti calcolati dal motore a grafo (corrispondenza solo del " . round($matchRate * 100) . "%)."
        ];
    }

    /**
     * Get database statistics from both MySQL and Neo4j.
     */
    public function getStats(): array
    {
        return $this->benchmark->compare(
            fn() => $this->mysqlRepo->getStats(),
            fn() => $this->neo4jRepo->getStats()
        );
    }

    /**
     * Get Cypher query text for a challenge (for display).
     */
    public function getCypherQuery(int $challengeId): string
    {
        return match ($challengeId) {
            1 => <<<'CYPHER'
MATCH (start:Actor {id: $actorId})
MATCH path = (start)-[:ACTED_IN*1..N]-(other:Actor)
WHERE other <> start
WITH other, min(length(path)) / 2 AS degrees
RETURN other.name AS name, degrees
ORDER BY degrees
CYPHER,
            2 => <<<'CYPHER'
MATCH (a1:Actor {id: $actorId1}), (a2:Actor {id: $actorId2})
MATCH path = shortestPath((a1)-[:ACTED_IN*]-(a2))
UNWIND nodes(path) AS node
RETURN 
    CASE WHEN node:Actor THEN node.name 
         WHEN node:Movie THEN node.title 
    END AS name
CYPHER,
            3 => <<<'CYPHER'
MATCH (u:User {id: $userId})-[r1:REVIEWED]->(m:Movie)
WHERE r1.rating >= $minRating
WITH u, collect(m) AS myMovies
MATCH (m:Movie)<-[r2:REVIEWED]-(other:User)
WHERE m IN myMovies AND r2.rating >= $minRating AND other <> u
WITH u, myMovies, other
MATCH (other)-[r3:REVIEWED]->(rec:Movie)
WHERE r3.rating >= $minRating AND NOT rec IN myMovies
RETURN rec.title, COUNT(DISTINCT other) AS score
ORDER BY score DESC LIMIT 10
CYPHER,
            default => 'N/A',
        };
    }

    /**
     * Get SQL query text for a challenge (for display).
     */
    public function getSqlQuery(int $challengeId): string
    {
        return match ($challengeId) {
            1 => <<<'SQL'
-- Approccio iterativo: per ogni livello di profondità
-- serve un nuovo ciclo di JOIN
SELECT DISTINCT mc2.actor_id, a.name, ? as depth
FROM movie_cast mc1
JOIN movie_cast mc2 
  ON mc1.movie_id = mc2.movie_id 
  AND mc1.actor_id != mc2.actor_id
JOIN actors a ON mc2.actor_id = a.id
WHERE mc1.actor_id IN (... livello precedente ...)
AND mc2.actor_id NOT IN (... già visitati ...)
-- Ripetuto per OGNI livello di profondità!
SQL,
            2 => <<<'SQL'
-- BFS manuale con query iterative PHP
-- Per ogni nodo nella coda:
SELECT DISTINCT mc2.actor_id, a.name, 
       m.title, m.id
FROM movie_cast mc1
JOIN movie_cast mc2 
  ON mc1.movie_id = mc2.movie_id
  AND mc1.actor_id != mc2.actor_id
JOIN actors a ON mc2.actor_id = a.id
JOIN movies m ON mc1.movie_id = m.id
WHERE mc1.actor_id = :current_actor
-- Ripetuto per OGNI nodo, OGNI livello!
-- + tracking manuale del percorso in PHP
SQL,
            3 => <<<'SQL'
SELECT m.title, 
       COUNT(DISTINCT r3.user_id) as score,
       AVG(r3.rating) as avg_rating
FROM reviews r1
JOIN reviews r2 ON r1.movie_id = r2.movie_id 
     AND r1.user_id != r2.user_id AND r2.rating >= 3.5
JOIN reviews r3 ON r2.user_id = r3.user_id 
     AND r3.movie_id != r1.movie_id AND r3.rating >= 3.5
JOIN movies m ON m.id = r3.movie_id
WHERE r1.user_id = :user_id AND r1.rating >= 3.5
  AND r3.movie_id NOT IN (
      SELECT movie_id FROM reviews 
      WHERE user_id = :user_id
  )
GROUP BY m.title
ORDER BY score DESC LIMIT 10
SQL,
            default => 'N/A',
        };
    }

    /**
     * Get SQL boilerplate template for a challenge (for students).
     */
    public function getSqlTemplate(int $challengeId): string
    {
        return match ($challengeId) {
            1 => <<<'SQL'
WITH RECURSIVE actor_connections AS (
    -- Caso base: seleziona gli attori direttamente connessi all'attore di partenza (:actor_id)
    SELECT mc2.actor_id, 1 as depth
    FROM movie_cast mc1
    JOIN movie_cast mc2 ON mc1.movie_id = mc2.movie_id AND mc1.actor_id != mc2.actor_id
    WHERE mc1.actor_id = :actor_id
    
    UNION
    
    -- Caso ricorsivo: connetti gli attori trovati al passo precedente con i loro co-protagonisti
    SELECT mc2.actor_id, ac.depth + 1
    FROM actor_connections ac
    -- [COMPLETA QUI: inserisci i JOIN necessari tra ac, movie_cast mc1, movie_cast mc2 per trovare i co-protagonisti]
    -- [Suggerimento: unisci ac con movie_cast mc1 (actor_id), poi mc1 con movie_cast mc2 (movie_id), escludendo l'attore di partenza]
    
    WHERE ac.depth < :max_depth
)
SELECT a.name, MIN(ac.depth) as degrees
FROM actor_connections ac
JOIN actors a ON a.id = ac.actor_id
GROUP BY a.name
ORDER BY degrees;
SQL,
            2 => <<<'SQL'
WITH RECURSIVE bfs_path AS (
    -- Caso base: parti dall'attore 1 (:actor_id_1)
    SELECT 
        mc1.actor_id AS current_actor_id,
        CAST(a.name AS CHAR(1000)) AS path_names,
        1 AS depth,
        CAST(CONCAT(',', mc1.actor_id, ',') AS CHAR(1000)) AS visited
    FROM actors a
    JOIN movie_cast mc1 ON a.id = mc1.actor_id
    WHERE mc1.actor_id = :actor_id_1

    UNION ALL

    -- Caso ricorsivo: naviga verso attori connessi tramite i film
    SELECT 
        mc2.actor_id AS current_actor_id,
        -- [COMPLETA QUI: concatena il percorso corrente con il titolo del film e il nome del co-protagonista]
        -- [Esempio: CONCAT(bp.path_names, ' -> ', m.title, ' -> ', a2.name)]
        
        bp.depth + 1 AS depth,
        CONCAT(bp.visited, mc2.actor_id, ',') AS visited
    FROM bfs_path bp
    -- [COMPLETA QUI: inserisci i JOIN con movie_cast mc1, movies m, movie_cast mc2 e actors a2]
    
    WHERE POSITION(CONCAT(',', mc2.actor_id, ',') IN bp.visited) = 0
      AND bp.depth < 4
)
SELECT path_names
FROM bfs_path
WHERE current_actor_id = :actor_id_2
ORDER BY depth ASC
LIMIT 1;
SQL,
            3 => <<<'SQL'
SELECT m.title, 
       COUNT(DISTINCT r3.user_id) as score,
       AVG(r3.rating) as avg_rating
FROM reviews r1
-- R2: recensioni degli altri utenti sullo stesso film (r1.movie_id)
JOIN reviews r2 ON r1.movie_id = r2.movie_id AND r1.user_id != r2.user_id AND r2.rating >= :min_rating
-- R3: recensioni degli altri utenti (r2.user_id) su ALTRI film (diversi da r1.movie_id)
-- [COMPLETA QUI: inserisci i JOIN mancanti per r3 (recensioni) e m (movies)]
-- [Ricorda che r3 deve collegare l'utente r2.user_id a film diversi r3.movie_id != r1.movie_id]

WHERE r1.user_id = :user_id 
  AND r1.rating >= :min_rating
  AND r3.movie_id NOT IN (
      SELECT movie_id FROM reviews WHERE user_id = :user_id
  )
GROUP BY m.title
ORDER BY score DESC 
LIMIT 10;
SQL,
            default => '-- Nessun template disponibile per questa sfida.',
        };
    }

    /**
     * Get SQL solution query for a challenge.
     */
    public function getSqlSolution(int $challengeId): string
    {
        return match ($challengeId) {
            1 => <<<'SQL'
WITH RECURSIVE actor_connections AS (
    -- Caso base: seleziona gli attori direttamente connessi all'attore di partenza (:actor_id)
    SELECT mc2.actor_id, 1 as depth
    FROM movie_cast mc1
    JOIN movie_cast mc2 ON mc1.movie_id = mc2.movie_id AND mc1.actor_id != mc2.actor_id
    WHERE mc1.actor_id = :actor_id
    
    UNION
    
    -- Caso ricorsivo: connetti gli attori trovati al passo precedente con i loro co-protagonisti
    SELECT mc2.actor_id, ac.depth + 1
    FROM actor_connections ac
    JOIN movie_cast mc1 ON mc1.actor_id = ac.actor_id
    JOIN movie_cast mc2 ON mc1.movie_id = mc2.movie_id AND mc1.actor_id != mc2.actor_id
    WHERE ac.depth < :max_depth
)
SELECT a.name, MIN(ac.depth) as degrees
FROM actor_connections ac
JOIN actors a ON a.id = ac.actor_id
GROUP BY a.name
ORDER BY degrees;
SQL,
            2 => <<<'SQL'
WITH RECURSIVE bfs_path AS (
    -- Caso base: parti dall'attore 1 (:actor_id_1)
    SELECT 
        mc1.actor_id AS current_actor_id,
        CAST(a.name AS CHAR(1000)) AS path_names,
        1 AS depth,
        CAST(CONCAT(',', mc1.actor_id, ',') AS CHAR(1000)) AS visited
    FROM actors a
    JOIN movie_cast mc1 ON a.id = mc1.actor_id
    WHERE mc1.actor_id = :actor_id_1

    UNION ALL

    -- Caso ricorsivo: naviga verso attori connessi tramite i film
    SELECT 
        mc2.actor_id AS current_actor_id,
        CONCAT(bp.path_names, ' -> ', m.title, ' -> ', a2.name) AS path_names,
        bp.depth + 1 AS depth,
        CONCAT(bp.visited, mc2.actor_id, ',') AS visited
    FROM bfs_path bp
    JOIN movie_cast mc1 ON mc1.actor_id = bp.current_actor_id
    JOIN movies m ON m.id = mc1.movie_id
    JOIN movie_cast mc2 ON mc2.movie_id = mc1.movie_id AND mc2.actor_id != mc1.actor_id
    JOIN actors a2 ON a2.id = mc2.actor_id
    WHERE POSITION(CONCAT(',', mc2.actor_id, ',') IN bp.visited) = 0
      AND bp.depth < 4
)
SELECT path_names
FROM bfs_path
WHERE current_actor_id = :actor_id_2
ORDER BY depth ASC
LIMIT 1;
SQL,
            3 => <<<'SQL'
SELECT m.title, 
       COUNT(DISTINCT r3.user_id) as score,
       AVG(r3.rating) as avg_rating
FROM reviews r1
-- R2: recensioni degli altri utenti sullo stesso film (r1.movie_id)
JOIN reviews r2 ON r1.movie_id = r2.movie_id AND r1.user_id != r2.user_id AND r2.rating >= :min_rating
-- R3: recensioni degli altri utenti (r2.user_id) su ALTRI film (diversi da r1.movie_id)
JOIN reviews r3 ON r2.user_id = r3.user_id AND r3.movie_id != r1.movie_id AND r3.rating >= :min_rating
JOIN movies m ON m.id = r3.movie_id
WHERE r1.user_id = :user_id 
  AND r1.rating >= :min_rating
  AND r3.movie_id NOT IN (
      SELECT movie_id FROM reviews WHERE user_id = :user_id
  )
GROUP BY m.title
ORDER BY score DESC 
LIMIT 10;
SQL,
            default => '-- Nessuna soluzione disponibile per questa sfida.',
        };
    }
}

