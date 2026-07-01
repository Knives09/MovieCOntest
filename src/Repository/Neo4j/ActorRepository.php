<?php

namespace MovieChallenge\Repository\Neo4j;

use MovieChallenge\Database\Neo4jConnection;
use Laudis\Neo4j\Contracts\ClientInterface;

/**
 * Neo4j Actor Repository
 * Handles all actor-related operations using Cypher queries.
 */
class ActorRepository
{
    private ClientInterface $client;

    public function __construct()
    {
        $this->client = Neo4jConnection::getInstance();
    }

    /**
     * Get all actors with pagination, ordered by number of movies.
     */
    public function findAll(int $limit = 50, int $skip = 0): array
    {
        $result = $this->client->run(
            'MATCH (a:Actor)
             OPTIONAL MATCH (a)-[:ACTED_IN]->(m:Movie)
             WITH a, count(m) AS movie_count
             RETURN a.id AS id, a.name AS name, a.profile_path AS profile_path,
                    movie_count
             ORDER BY movie_count DESC
             SKIP $skip LIMIT $limit',
            ['skip' => $skip, 'limit' => $limit]
        );

        return $this->toArray($result);
    }

    /**
     * Find an actor by ID.
     */
    public function findById(int $id): ?array
    {
        $result = $this->client->run(
            'MATCH (a:Actor {id: $id})
             RETURN a.id AS id, a.name AS name, a.tmdb_id AS tmdb_id,
                    a.profile_path AS profile_path',
            ['id' => $id]
        );

        $rows = $this->toArray($result);
        return $rows[0] ?? null;
    }

    /**
     * Find an actor by name.
     */
    public function findByName(string $name): ?array
    {
        $result = $this->client->run(
            'MATCH (a:Actor {name: $name})
             RETURN a.id AS id, a.name AS name, a.tmdb_id AS tmdb_id,
                    a.profile_path AS profile_path
             LIMIT 1',
            ['name' => $name]
        );

        $rows = $this->toArray($result);
        return $rows[0] ?? null;
    }

    /**
     * Search actors by name.
     */
    public function searchByName(string $query): array
    {
        $result = $this->client->run(
            'MATCH (a:Actor)
             WHERE toLower(a.name) CONTAINS toLower($query)
             OPTIONAL MATCH (a)-[:ACTED_IN]->(m:Movie)
             WITH a, count(m) AS movie_count
             RETURN a.id AS id, a.name AS name, a.profile_path AS profile_path,
                    movie_count
             ORDER BY movie_count DESC
             LIMIT 20',
            ['query' => $query]
        );

        return $this->toArray($result);
    }

    /**
     * Get the filmography for an actor.
     */
    public function getFilmography(int $actorId): array
    {
        $result = $this->client->run(
            'MATCH (a:Actor {id: $actorId})-[r:ACTED_IN]->(m:Movie)
             RETURN m.id AS id, m.title AS title, m.release_date AS release_date,
                    m.vote_average AS vote_average, m.poster_path AS poster_path,
                    r.character AS character_name
             ORDER BY m.release_date DESC',
            ['actorId' => $actorId]
        );

        return $this->toArray($result);
    }

    /**
     * Get actor count.
     */
    public function count(): int
    {
        $result = $this->client->run('MATCH (a:Actor) RETURN count(a) AS cnt');
        return $result->first()->get('cnt');
    }

    /**
     * Find co-actors (actors who appeared in the same movies).
     */
    public function findCoActors(int $actorId): array
    {
        $result = $this->client->run(
            'MATCH (a:Actor {id: $actorId})-[:ACTED_IN]->(m:Movie)<-[:ACTED_IN]-(co:Actor)
             WITH co, count(DISTINCT m) AS shared_movies
             RETURN co.id AS id, co.name AS name, co.profile_path AS profile_path,
                    shared_movies
             ORDER BY shared_movies DESC
             LIMIT 20',
            ['actorId' => $actorId]
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
