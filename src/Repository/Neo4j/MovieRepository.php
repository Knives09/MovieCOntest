<?php

namespace MovieChallenge\Repository\Neo4j;

use MovieChallenge\Database\Neo4jConnection;
use Laudis\Neo4j\Contracts\ClientInterface;

/**
 * Neo4j Movie Repository
 * Handles all movie-related operations using Cypher queries.
 */
class MovieRepository
{
    private ClientInterface $client;

    public function __construct()
    {
        $this->client = Neo4jConnection::getInstance();
    }

    /**
     * Get all movies with pagination.
     */
    public function findAll(int $limit = 50, int $skip = 0): array
    {
        $result = $this->client->run(
            'MATCH (m:Movie)
             OPTIONAL MATCH (m)-[:HAS_GENRE]->(g:Genre)
             WITH m, COLLECT(g.name) AS genres
             RETURN m.id AS id, m.tmdb_id AS tmdb_id, m.title AS title, 
                    m.release_date AS release_date, m.vote_average AS vote_average,
                    m.poster_path AS poster_path, m.overview AS overview,
                    apoc.text.join(genres, ", ") AS genres
             ORDER BY m.vote_average DESC
             SKIP $skip LIMIT $limit',
            ['skip' => $skip, 'limit' => $limit]
        );

        return $this->toArray($result);
    }

    /**
     * Find a movie by ID.
     */
    public function findById(int $id): ?array
    {
        $result = $this->client->run(
            'MATCH (m:Movie {id: $id})
             OPTIONAL MATCH (m)-[:HAS_GENRE]->(g:Genre)
             WITH m, COLLECT(g.name) AS genres
             RETURN m.id AS id, m.tmdb_id AS tmdb_id, m.title AS title,
                    m.release_date AS release_date, m.vote_average AS vote_average,
                    m.poster_path AS poster_path, m.overview AS overview,
                    apoc.text.join(genres, ", ") AS genres',
            ['id' => $id]
        );

        $rows = $this->toArray($result);
        return $rows[0] ?? null;
    }

    /**
     * Search movies by title.
     */
    public function searchByTitle(string $query): array
    {
        $result = $this->client->run(
            'MATCH (m:Movie)
             WHERE toLower(m.title) CONTAINS toLower($query)
             RETURN m.id AS id, m.title AS title, m.vote_average AS vote_average,
                    m.poster_path AS poster_path
             ORDER BY m.vote_average DESC
             LIMIT 20',
            ['query' => $query]
        );

        return $this->toArray($result);
    }

    /**
     * Get movie count.
     */
    public function count(): int
    {
        $result = $this->client->run('MATCH (m:Movie) RETURN count(m) AS cnt');
        return $result->first()->get('cnt');
    }

    /**
     * Get cast for a movie.
     */
    public function getCast(int $movieId): array
    {
        $result = $this->client->run(
            'MATCH (a:Actor)-[r:ACTED_IN]->(m:Movie {id: $movieId})
             RETURN a.id AS id, a.name AS name, a.profile_path AS profile_path,
                    r.character AS character_name, r.cast_order AS cast_order
             ORDER BY r.cast_order ASC',
            ['movieId' => $movieId]
        );

        return $this->toArray($result);
    }

    /**
     * Get reviews for a movie.
     */
    public function getReviews(int $movieId): array
    {
        $result = $this->client->run(
            'MATCH (u:User)-[r:REVIEWED]->(m:Movie {id: $movieId})
             RETURN r.rating AS rating, r.comment AS comment, 
                    r.created_at AS created_at,
                    u.username AS username, u.team_name AS team_name
             ORDER BY r.created_at DESC',
            ['movieId' => $movieId]
        );

        return $this->toArray($result);
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
