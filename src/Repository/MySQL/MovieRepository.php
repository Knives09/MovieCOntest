<?php

namespace MovieChallenge\Repository\MySQL;

use MovieChallenge\Database\MySQLConnection;
use PDO;

/**
 * MySQL Movie Repository
 * Handles all movie-related database operations for MySQL.
 */
class MovieRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = MySQLConnection::getInstance();
    }

    /**
     * Get all movies with pagination.
     */
    public function findAll(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT m.*, GROUP_CONCAT(g.name SEPARATOR ', ') as genres
             FROM movies m
             LEFT JOIN movie_genres mg ON m.id = mg.movie_id
             LEFT JOIN genres g ON mg.genre_id = g.id
             GROUP BY m.id
             ORDER BY m.vote_average DESC
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Find a movie by ID.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT m.*, GROUP_CONCAT(g.name SEPARATOR ', ') as genres
             FROM movies m
             LEFT JOIN movie_genres mg ON m.id = mg.movie_id
             LEFT JOIN genres g ON mg.genre_id = g.id
             WHERE m.id = :id
             GROUP BY m.id"
        );
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Search movies by title.
     */
    public function searchByTitle(string $query): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM movies WHERE title LIKE :query ORDER BY vote_average DESC LIMIT 20"
        );
        $stmt->execute(['query' => "%{$query}%"]);
        return $stmt->fetchAll();
    }

    /**
     * Get movie count.
     */
    public function count(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM movies")->fetchColumn();
    }

    /**
     * Get cast for a movie.
     */
    public function getCast(int $movieId): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.*, mc.character_name, mc.cast_order
             FROM actors a
             JOIN movie_cast mc ON a.id = mc.actor_id
             WHERE mc.movie_id = :movie_id
             ORDER BY mc.cast_order ASC"
        );
        $stmt->execute(['movie_id' => $movieId]);
        return $stmt->fetchAll();
    }

    /**
     * Get reviews for a movie.
     */
    public function getReviews(int $movieId): array
    {
        $stmt = $this->db->prepare(
            "SELECT r.*, u.username, u.team_name
             FROM reviews r
             JOIN users u ON r.user_id = u.id
             WHERE r.movie_id = :movie_id
             ORDER BY r.created_at DESC"
        );
        $stmt->execute(['movie_id' => $movieId]);
        return $stmt->fetchAll();
    }

    /**
     * Get top-rated movies.
     */
    public function getTopRated(int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM movies ORDER BY vote_average DESC LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
