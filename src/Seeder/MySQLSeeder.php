<?php

namespace MovieChallenge\Seeder;

use MovieChallenge\Database\MySQLConnection;
use PDO;

/**
 * MySQL Seeder
 * Populates the MySQL database with imported TMDb data.
 * Uses batch inserts for performance.
 */
class MySQLSeeder
{
    private PDO $db;

    public function __construct()
    {
        $this->db = MySQLConnection::getInstance();
    }

    /**
     * Seed all data into MySQL.
     *
     * @param array $data Data from TMDbImporter
     * @param callable|null $onProgress Progress callback
     */
    public function seed(array $data, ?callable $onProgress = null): void
    {
        $this->db->beginTransaction();

        try {
            $this->seedGenres($data['genres'] ?? [], $onProgress);
            $this->seedMovies($data['movies'] ?? [], $onProgress);
            $this->seedActors($data['actors'] ?? [], $onProgress);
            $this->seedCast($data['cast'] ?? [], $onProgress);
            $this->seedMovieGenres($data['movies'] ?? [], $onProgress);
            $this->seedUsers($data['users'] ?? [], $onProgress);
            $this->seedReviews($data['reviews'] ?? [], $onProgress);

            $this->db->commit();

            if ($onProgress) $onProgress(0, 0, "✅ MySQL seeding completato!");
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function seedGenres(array $genres, ?callable $onProgress): void
    {
        if (empty($genres)) return;
        if ($onProgress) $onProgress(0, 0, "  📁 Inserimento generi in MySQL...");

        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO genres (id, name) VALUES (:id, :name)"
        );

        foreach ($genres as $genre) {
            $stmt->execute([
                'id' => $genre['id'],
                'name' => $genre['name'],
            ]);
        }
    }

    private function seedMovies(array $movies, ?callable $onProgress): void
    {
        if (empty($movies)) return;
        if ($onProgress) $onProgress(0, 0, "  🎬 Inserimento film in MySQL...");

        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO movies (id, tmdb_id, title, release_date, overview, vote_average, poster_path) 
             VALUES (:id, :tmdb_id, :title, :release_date, :overview, :vote_average, :poster_path)"
        );

        $count = 0;
        foreach ($movies as $movie) {
            $stmt->execute([
                'id' => $movie['id'],
                'tmdb_id' => $movie['tmdb_id'],
                'title' => $movie['title'],
                'release_date' => $movie['release_date'] ?: null,
                'overview' => $movie['overview'],
                'vote_average' => $movie['vote_average'],
                'poster_path' => $movie['poster_path'],
            ]);
            $count++;
            if ($onProgress && $count % 100 === 0) {
                $onProgress($count, count($movies), "  🎬 Film inseriti: {$count}/" . count($movies));
            }
        }
    }

    private function seedActors(array $actors, ?callable $onProgress): void
    {
        if (empty($actors)) return;
        if ($onProgress) $onProgress(0, 0, "  🎭 Inserimento attori in MySQL...");

        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO actors (id, tmdb_id, name, profile_path) 
             VALUES (:id, :tmdb_id, :name, :profile_path)"
        );

        $count = 0;
        foreach ($actors as $actor) {
            $stmt->execute([
                'id' => $actor['id'],
                'tmdb_id' => $actor['tmdb_id'],
                'name' => $actor['name'],
                'profile_path' => $actor['profile_path'],
            ]);
            $count++;
            if ($onProgress && $count % 500 === 0) {
                $onProgress($count, count($actors), "  🎭 Attori inseriti: {$count}/" . count($actors));
            }
        }
    }

    private function seedCast(array $cast, ?callable $onProgress): void
    {
        if (empty($cast)) return;
        if ($onProgress) $onProgress(0, 0, "  🔗 Inserimento relazioni cast in MySQL...");

        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO movie_cast (movie_id, actor_id, character_name, cast_order) 
             VALUES (:movie_id, :actor_id, :character_name, :cast_order)"
        );

        $count = 0;
        foreach ($cast as $c) {
            $stmt->execute([
                'movie_id' => $c['movie_id'],
                'actor_id' => $c['actor_id'],
                'character_name' => $c['character_name'],
                'cast_order' => $c['cast_order'],
            ]);
            $count++;
            if ($onProgress && $count % 1000 === 0) {
                $onProgress($count, count($cast), "  🔗 Cast inseriti: {$count}/" . count($cast));
            }
        }
    }

    private function seedMovieGenres(array $movies, ?callable $onProgress): void
    {
        if ($onProgress) $onProgress(0, 0, "  📎 Inserimento generi-film in MySQL...");

        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO movie_genres (movie_id, genre_id) VALUES (:movie_id, :genre_id)"
        );

        foreach ($movies as $movie) {
            foreach ($movie['genre_ids'] ?? [] as $genreId) {
                $stmt->execute([
                    'movie_id' => $movie['id'],
                    'genre_id' => $genreId,
                ]);
            }
        }
    }

    private function seedUsers(array $users, ?callable $onProgress): void
    {
        if (empty($users)) return;
        if ($onProgress) $onProgress(0, 0, "  👥 Inserimento utenti in MySQL...");

        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO users (id, username, team_name) VALUES (:id, :username, :team_name)"
        );

        foreach ($users as $user) {
            $stmt->execute([
                'id' => $user['id'],
                'username' => $user['username'],
                'team_name' => $user['team_name'],
            ]);
        }
    }

    private function seedReviews(array $reviews, ?callable $onProgress): void
    {
        if (empty($reviews)) return;
        if ($onProgress) $onProgress(0, 0, "  ⭐ Inserimento review in MySQL...");

        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO reviews (id, user_id, movie_id, rating, comment, created_at) 
             VALUES (:id, :user_id, :movie_id, :rating, :comment, :created_at)"
        );

        $count = 0;
        foreach ($reviews as $review) {
            $stmt->execute([
                'id' => $review['id'],
                'user_id' => $review['user_id'],
                'movie_id' => $review['movie_id'],
                'rating' => $review['rating'],
                'comment' => $review['comment'],
                'created_at' => $review['created_at'],
            ]);
            $count++;
            if ($onProgress && $count % 500 === 0) {
                $onProgress($count, count($reviews), "  ⭐ Review inserite: {$count}/" . count($reviews));
            }
        }
    }
}
