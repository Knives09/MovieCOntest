<?php

namespace MovieChallenge\Repository\MySQL;

use MovieChallenge\Database\MySQLConnection;
use PDO;

/**
 * MySQL Challenge Repository
 * Implements the 3 challenge queries using standard SQL.
 * These queries demonstrate the limitations of relational databases
 * for graph-like traversal operations.
 */
class ChallengeRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = MySQLConnection::getInstance();
    }

    /**
     * CHALLENGE 1: Six Degrees of Kevin Bacon
     * 
     * Find all actors connected within N degrees of separation from a given actor.
     * Uses recursive CTE (Common Table Expression) - MySQL 8.0+
     * 
     * This query becomes EXPONENTIALLY slower as depth increases because:
     * - Each hop requires a full table JOIN
     * - No index-free adjacency: must scan indexes repeatedly
     * - The intermediate result set grows explosively
     * 
     * @param int $actorId Starting actor ID
     * @param int $maxDepth Maximum degrees of separation (1-6)
     * @return array Connected actors with their degree of separation
     */
    public function sixDegrees(int $actorId, int $maxDepth = 3): array
    {
        // For higher depths, we use an iterative approach to avoid CTE limitations
        // and to track visited actors properly
        $allResults = [];
        $visited = [$actorId];
        $currentLevel = [$actorId];

        for ($depth = 1; $depth <= $maxDepth; $depth++) {
            if (empty($currentLevel)) {
                break;
            }

            $placeholders = implode(',', array_fill(0, count($currentLevel), '?'));
            $visitedPlaceholders = implode(',', array_fill(0, count($visited), '?'));

            $sql = "
                SELECT DISTINCT mc2.actor_id, a.name, ? as depth
                FROM movie_cast mc1
                JOIN movie_cast mc2 ON mc1.movie_id = mc2.movie_id AND mc1.actor_id != mc2.actor_id
                JOIN actors a ON mc2.actor_id = a.id
                WHERE mc1.actor_id IN ({$placeholders})
                AND mc2.actor_id NOT IN ({$visitedPlaceholders})
                LIMIT 500
            ";

            $stmt = $this->db->prepare($sql);
            
            $paramIndex = 1;
            $stmt->bindValue($paramIndex++, $depth, PDO::PARAM_INT);
            foreach ($currentLevel as $id) {
                $stmt->bindValue($paramIndex++, $id, PDO::PARAM_INT);
            }
            foreach ($visited as $id) {
                $stmt->bindValue($paramIndex++, $id, PDO::PARAM_INT);
            }

            $stmt->execute();
            $results = $stmt->fetchAll();

            $nextLevel = [];
            foreach ($results as $row) {
                $allResults[] = $row;
                $visited[] = $row['actor_id'];
                $nextLevel[] = $row['actor_id'];
            }
            $currentLevel = $nextLevel;
        }

        return $allResults;
    }

    /**
     * CHALLENGE 2: Shortest Path Between Two Actors
     * 
     * Find the shortest path between two actors through shared movies.
     * Uses BFS (Breadth-First Search) implemented with iterative queries.
     * 
     * This is extremely complex and slow in SQL because:
     * - SQL is not designed for path traversal
     * - Each BFS level requires a new query
     * - Must track entire path (not just distance)
     * - No native graph algorithms available
     * 
     * @param int $actorId1 First actor ID
     * @param int $actorId2 Second actor ID
     * @param int $maxDepth Maximum search depth
     * @return array|null Path as array of [actor_name, movie_title, actor_name, ...]
     */
    public function shortestPath(int $actorId1, int $actorId2, int $maxDepth = 6): ?array
    {
        // BFS implementation using iterative SQL queries
        // Each entry: ['actor_id' => int, 'path' => array]
        $queue = [['actor_id' => $actorId1, 'path' => [$actorId1]]];
        $visited = [$actorId1 => true];

        for ($depth = 0; $depth < $maxDepth; $depth++) {
            if (empty($queue)) {
                break;
            }

            $nextQueue = [];

            foreach ($queue as $current) {
                // Find all actors connected through shared movies
                $stmt = $this->db->prepare(
                    "SELECT DISTINCT mc2.actor_id, a.name as actor_name, 
                            m.title as movie_title, m.id as movie_id
                     FROM movie_cast mc1
                     JOIN movie_cast mc2 ON mc1.movie_id = mc2.movie_id 
                          AND mc1.actor_id != mc2.actor_id
                     JOIN actors a ON mc2.actor_id = a.id
                     JOIN movies m ON mc1.movie_id = m.id
                     WHERE mc1.actor_id = :actor_id
                     LIMIT 200"
                );
                $stmt->execute(['actor_id' => $current['actor_id']]);
                $neighbors = $stmt->fetchAll();

                foreach ($neighbors as $neighbor) {
                    if (isset($visited[$neighbor['actor_id']])) {
                        continue;
                    }

                    $newPath = $current['path'];
                    $newPath[] = [
                        'movie_id' => $neighbor['movie_id'],
                        'movie_title' => $neighbor['movie_title'],
                    ];
                    $newPath[] = $neighbor['actor_id'];

                    // Found the target!
                    if ($neighbor['actor_id'] === $actorId2) {
                        return $this->resolvePath($newPath);
                    }

                    $visited[$neighbor['actor_id']] = true;
                    $nextQueue[] = [
                        'actor_id' => $neighbor['actor_id'],
                        'path' => $newPath,
                    ];
                }
            }

            $queue = $nextQueue;
        }

        return null; // No path found
    }

    /**
     * Resolve a path array into human-readable format.
     */
    private function resolvePath(array $path): array
    {
        $result = [];
        foreach ($path as $item) {
            if (is_int($item)) {
                // Actor ID - resolve name
                $stmt = $this->db->prepare("SELECT name FROM actors WHERE id = :id");
                $stmt->execute(['id' => $item]);
                $actor = $stmt->fetch();
                $result[] = [
                    'type' => 'actor',
                    'id' => $item,
                    'name' => $actor['name'] ?? 'Unknown',
                ];
            } elseif (is_array($item)) {
                // Movie info
                $result[] = [
                    'type' => 'movie',
                    'id' => $item['movie_id'],
                    'title' => $item['movie_title'],
                ];
            }
        }
        return $result;
    }

    /**
     * CHALLENGE 3: Movie Recommendations (Collaborative Filtering)
     * 
     * Recommend movies based on what similar users liked.
     * Pattern: User → liked Movie ← also liked by Other User → other Movies they liked
     * 
     * This requires multiple JOINs and subqueries in SQL:
     * - 3 self-joins on reviews table
     * - Subquery for exclusion (movies already seen)
     * - Complex aggregation
     * 
     * @param int $userId User ID to generate recommendations for
     * @param float $minRating Minimum rating to consider as "liked"
     * @param int $limit Number of recommendations
     * @return array Recommended movies with scores
     */
    public function recommendations(int $userId, float $minRating = 3.5, int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT m.id, m.title, m.vote_average, m.poster_path,
                    COUNT(DISTINCT r3.user_id) as recommender_count,
                    ROUND(AVG(r3.rating), 1) as avg_recommender_rating,
                    GROUP_CONCAT(DISTINCT shared_m.title SEPARATOR ', ') as based_on
             FROM reviews r1
             JOIN reviews r2 ON r1.movie_id = r2.movie_id 
                  AND r1.user_id != r2.user_id
                  AND r2.rating >= :min_rating1
             JOIN reviews r3 ON r2.user_id = r3.user_id 
                  AND r3.movie_id != r1.movie_id
                  AND r3.rating >= :min_rating2
             JOIN movies m ON m.id = r3.movie_id
             JOIN movies shared_m ON shared_m.id = r1.movie_id
             WHERE r1.user_id = :user_id
               AND r1.rating >= :min_rating3
               AND r3.movie_id NOT IN (
                   SELECT movie_id FROM reviews WHERE user_id = :user_id2
               )
             GROUP BY m.id, m.title, m.vote_average, m.poster_path
             ORDER BY recommender_count DESC, avg_recommender_rating DESC
             LIMIT :limit"
        );

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id2', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':min_rating1', $minRating);
        $stmt->bindValue(':min_rating2', $minRating);
        $stmt->bindValue(':min_rating3', $minRating);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get statistics about the dataset.
     */
    public function getStats(): array
    {
        $stats = [];
        $stats['movie_count'] = (int) $this->db->query("SELECT COUNT(*) FROM movies")->fetchColumn();
        $stats['actor_count'] = (int) $this->db->query("SELECT COUNT(*) FROM actors")->fetchColumn();
        $stats['cast_count'] = (int) $this->db->query("SELECT COUNT(*) FROM movie_cast")->fetchColumn();
        $stats['review_count'] = (int) $this->db->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
        $stats['user_count'] = (int) $this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stats['genre_count'] = (int) $this->db->query("SELECT COUNT(*) FROM genres")->fetchColumn();

        return $stats;
    }
}
