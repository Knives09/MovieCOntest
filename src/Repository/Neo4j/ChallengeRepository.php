<?php

namespace MovieChallenge\Repository\Neo4j;

use MovieChallenge\Database\Neo4jConnection;
use Laudis\Neo4j\Contracts\ClientInterface;

/**
 * Neo4j Challenge Repository
 * Implements the 3 challenge queries using Cypher.
 * These queries demonstrate the POWER of a graph database
 * for traversal and pattern-matching operations.
 */
class ChallengeRepository
{
    private ClientInterface $client;

    public function __construct()
    {
        $this->client = Neo4jConnection::getInstance();
    }

    /**
     * CHALLENGE 1: Six Degrees of Kevin Bacon
     * 
     * Find all actors connected within N degrees of separation from a given actor.
     * Uses variable-length path patterns — a NATIVE Neo4j feature.
     * 
     * Why Neo4j excels here:
     * - Index-free adjacency: each node has direct pointers to its neighbors
     * - Variable-length paths are a first-class Cypher feature
     * - O(1) per hop traversal (vs O(N) JOIN in SQL)
     * - No exponential blowup of intermediate results
     * 
     * @param int $actorId Starting actor ID
     * @param int $maxDepth Maximum degrees of separation (1-6)
     * @return array Connected actors with their degree of separation
     */
    public function sixDegrees(int $actorId, int $maxDepth = 3): array
    {
        $result = $this->client->run(
            'MATCH (start:Actor {id: $actorId})
             MATCH path = (start)-[:ACTED_IN*1..' . ($maxDepth * 2) . ']-(other:Actor)
             WHERE other <> start
             WITH other, 
                  min(length(path)) / 2 AS degrees
             RETURN other.id AS actor_id, other.name AS name, degrees
             ORDER BY degrees ASC, other.name ASC
             LIMIT 500',
            ['actorId' => $actorId]
        );

        return $this->toArray($result);
    }

    /**
     * CHALLENGE 2: Shortest Path Between Two Actors
     * 
     * Find the shortest path between two actors through shared movies.
     * Uses Neo4j's BUILT-IN shortestPath() algorithm.
     * 
     * Why Neo4j excels here:
     * - shortestPath() is a native, optimized graph algorithm
     * - Bidirectional BFS under the hood
     * - Returns the complete path with all intermediate nodes
     * - One line of Cypher vs dozens of lines of PHP+SQL
     * 
     * @param int $actorId1 First actor ID
     * @param int $actorId2 Second actor ID
     * @param int $maxDepth Maximum search depth
     * @return array|null Path as array of alternating actors and movies
     */
    public function shortestPath(int $actorId1, int $actorId2, int $maxDepth = 6): ?array
    {
        $result = $this->client->run(
            'MATCH (a1:Actor {id: $actorId1}), (a2:Actor {id: $actorId2})
             MATCH path = shortestPath((a1)-[:ACTED_IN*1..' . ($maxDepth * 2) . ']-(a2))
             UNWIND nodes(path) AS node
             RETURN 
                CASE 
                    WHEN node:Actor THEN "actor" 
                    WHEN node:Movie THEN "movie" 
                    ELSE "unknown" 
                END AS type,
                CASE 
                    WHEN node:Actor THEN node.id 
                    WHEN node:Movie THEN node.id 
                END AS id,
                CASE 
                    WHEN node:Actor THEN node.name 
                    WHEN node:Movie THEN node.title 
                END AS name',
            ['actorId1' => $actorId1, 'actorId2' => $actorId2]
        );

        $path = $this->toArray($result);
        return empty($path) ? null : $path;
    }

    /**
     * CHALLENGE 3: Movie Recommendations (Collaborative Filtering)
     * 
     * Recommend movies based on what similar users liked.
     * Pattern: User → liked Movie ← also liked by Other User → other Movies they liked
     * 
     * Why Neo4j excels here:
     * - The recommendation pattern maps DIRECTLY to a Cypher path pattern
     * - No need for self-joins or subqueries
     * - The query reads almost like natural language
     * - Aggregation is straightforward
     * 
     * @param int $userId User ID to generate recommendations for
     * @param float $minRating Minimum rating to consider as "liked"
     * @param int $limit Number of recommendations
     * @return array Recommended movies with scores
     */
    public function recommendations(int $userId, float $minRating = 3.5, int $limit = 10): array
    {
        $result = $this->client->run(
            'MATCH (u:User {id: $userId})-[r1:REVIEWED]->(m:Movie)
             WHERE r1.rating >= $minRating
             WITH u, collect(m) AS myMovies
             MATCH (m:Movie)<-[r2:REVIEWED]-(other:User)
             WHERE m IN myMovies AND r2.rating >= $minRating AND other <> u
             WITH u, myMovies, other
             MATCH (other)-[r3:REVIEWED]->(rec:Movie)
             WHERE r3.rating >= $minRating AND NOT rec IN myMovies
             WITH rec, 
                  COUNT(DISTINCT other) AS recommender_count,
                  ROUND(AVG(r3.rating), 1) AS avg_recommender_rating
             RETURN rec.id AS id, rec.title AS title, 
                    rec.vote_average AS vote_average,
                    rec.poster_path AS poster_path,
                    recommender_count, avg_recommender_rating
             ORDER BY recommender_count DESC, avg_recommender_rating DESC
             LIMIT $limit',
            ['userId' => $userId, 'minRating' => $minRating, 'limit' => $limit]
        );

        return $this->toArray($result);
    }

    /**
     * Get statistics about the graph database.
     */
    public function getStats(): array
    {
        $stats = [];

        $result = $this->client->run('MATCH (m:Movie) RETURN count(m) AS cnt');
        $stats['movie_count'] = $result->first()->get('cnt');

        $result = $this->client->run('MATCH (a:Actor) RETURN count(a) AS cnt');
        $stats['actor_count'] = $result->first()->get('cnt');

        $result = $this->client->run('MATCH ()-[r:ACTED_IN]->() RETURN count(r) AS cnt');
        $stats['cast_count'] = $result->first()->get('cnt');

        $result = $this->client->run('MATCH ()-[r:REVIEWED]->() RETURN count(r) AS cnt');
        $stats['review_count'] = $result->first()->get('cnt');

        $result = $this->client->run('MATCH (u:User) RETURN count(u) AS cnt');
        $stats['user_count'] = $result->first()->get('cnt');

        $result = $this->client->run('MATCH (g:Genre) RETURN count(g) AS cnt');
        $stats['genre_count'] = $result->first()->get('cnt');

        return $stats;
    }

    /**
     * Convert Neo4j result to associative arrays.
     */
    private function toArray($result): array
    {
        $rows = [];
        foreach ($result as $record) {
            $row = [];
            foreach ($record->keys() as $key) {
                $row[$key] = $record->get($key);
            }
            $rows[] = $row;
        }
        return $rows;
    }
}
