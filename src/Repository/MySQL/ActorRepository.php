<?php

namespace MovieChallenge\Repository\MySQL;

use MovieChallenge\Database\MySQLConnection;
use PDO;

/**
 * MySQL Actor Repository
 * Handles all actor-related database operations for MySQL.
 */
class ActorRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = MySQLConnection::getInstance();
    }

    /**
     * Get all actors with pagination.
     */
    public function findAll(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.*, COUNT(mc.movie_id) as movie_count
             FROM actors a
             LEFT JOIN movie_cast mc ON a.id = mc.actor_id
             GROUP BY a.id
             ORDER BY movie_count DESC
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Find an actor by ID.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM actors WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Find an actor by name (exact match).
     */
    public function findByName(string $name): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM actors WHERE name = :name LIMIT 1");
        $stmt->execute(['name' => $name]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Search actors by name.
     */
    public function searchByName(string $query): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.*, COUNT(mc.movie_id) as movie_count
             FROM actors a
             LEFT JOIN movie_cast mc ON a.id = mc.actor_id
             WHERE a.name LIKE :query
             GROUP BY a.id
             ORDER BY movie_count DESC
             LIMIT 20"
        );
        $stmt->execute(['query' => "%{$query}%"]);
        return $stmt->fetchAll();
    }

    /**
     * Get the filmography for an actor.
     */
    public function getFilmography(int $actorId): array
    {
        $stmt = $this->db->prepare(
            "SELECT m.*, mc.character_name
             FROM movies m
             JOIN movie_cast mc ON m.id = mc.movie_id
             WHERE mc.actor_id = :actor_id
             ORDER BY m.release_date DESC"
        );
        $stmt->execute(['actor_id' => $actorId]);
        return $stmt->fetchAll();
    }

    /**
     * Get actor count.
     */
    public function count(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM actors")->fetchColumn();
    }

    /**
     * Find co-actors (actors who appeared in the same movies).
     */
    public function findCoActors(int $actorId): array
    {
        $stmt = $this->db->prepare(
            "SELECT DISTINCT a.*, COUNT(DISTINCT mc1.movie_id) as shared_movies
             FROM actors a
             JOIN movie_cast mc2 ON a.id = mc2.actor_id
             JOIN movie_cast mc1 ON mc1.movie_id = mc2.movie_id AND mc1.actor_id = :actor_id
             WHERE a.id != :actor_id2
             GROUP BY a.id
             ORDER BY shared_movies DESC
             LIMIT 20"
        );
        $stmt->execute(['actor_id' => $actorId, 'actor_id2' => $actorId]);
        return $stmt->fetchAll();
    }
}
