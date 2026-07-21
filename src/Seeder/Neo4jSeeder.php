<?php

namespace MovieChallenge\Seeder;

use MovieChallenge\Database\Neo4jConnection;
use Laudis\Neo4j\Contracts\ClientInterface;

/**
 * Neo4j Seeder
 * Populates the Neo4j graph database with imported TMDb data.
 * Creates nodes and relationships using Cypher MERGE for idempotency.
 */
class Neo4jSeeder
{
    private ClientInterface $client;

    public function __construct()
    {
        $this->client = Neo4jConnection::getInstance();
    }

    /**
     * Seed all data into Neo4j.
     *
     * @param array $data Data from TMDbImporter
     * @param callable|null $onProgress Progress callback
     */
    public function seed(array $data, ?callable $onProgress = null): void
    {
        $this->seedGenres($data['genres'] ?? [], $onProgress);
        $this->seedMovies($data['movies'] ?? [], $onProgress);
        $this->seedActors($data['actors'] ?? [], $onProgress);
        $this->seedCast($data['cast'] ?? [], $onProgress);
        $this->seedMovieGenres($data['movies'] ?? [], $onProgress);
        $this->seedUsers($data['users'] ?? [], $onProgress);
        $this->seedReviews($data['reviews'] ?? [], $onProgress);

        if ($onProgress) $onProgress(0, 0, "✅ Neo4j seeding completato!");
    }

    private function seedGenres(array $genres, ?callable $onProgress): void
    {
        if (empty($genres)) return;
        if ($onProgress) $onProgress(0, 0, "  📁 Creazione nodi Genre in Neo4j...");

        // Batch insert genres
        $this->client->run(
            'UNWIND $genres AS g
             MERGE (genre:Genre {id: g.id})
             SET genre.name = g.name, genre.tmdb_id = g.tmdb_id',
            ['genres' => array_values($genres)]
        );
    }

    private function seedMovies(array $movies, ?callable $onProgress): void
    {
        if (empty($movies)) return;
        if ($onProgress) $onProgress(0, 0, "  🎬 Creazione nodi Movie in Neo4j...");

        // Process in batches of 100
        $batches = array_chunk(array_values($movies), 100);
        $count = 0;

        foreach ($batches as $batch) {
            $movieBatch = array_map(function ($m) {
                return [
                    'id' => $m['id'],
                    'tmdb_id' => $m['tmdb_id'],
                    'title' => $m['title'],
                    'release_date' => $m['release_date'],
                    'overview' => $m['overview'],
                    'vote_average' => $m['vote_average'],
                    'poster_path' => $m['poster_path'],
                ];
            }, $batch);

            $this->client->run(
                'UNWIND $movies AS m
                 MERGE (movie:Movie {id: m.id})
                 SET movie.tmdb_id = m.tmdb_id,
                     movie.title = m.title,
                     movie.release_date = m.release_date,
                     movie.overview = m.overview,
                     movie.vote_average = m.vote_average,
                     movie.poster_path = m.poster_path',
                ['movies' => $movieBatch]
            );

            $count += count($batch);
            if ($onProgress && $count % 200 === 0) {
                $onProgress($count, count($movies), "  🎬 Film Neo4j: {$count}/" . count($movies));
            }
        }
    }

    private function seedActors(array $actors, ?callable $onProgress): void
    {
        if (empty($actors)) return;
        if ($onProgress) $onProgress(0, 0, "  🎭 Creazione nodi Actor in Neo4j...");

        $batches = array_chunk(array_values($actors), 200);
        $count = 0;

        foreach ($batches as $batch) {
            $actorBatch = array_map(function ($a) {
                return [
                    'id' => $a['id'],
                    'tmdb_id' => $a['tmdb_id'],
                    'name' => $a['name'],
                    'profile_path' => $a['profile_path'],
                ];
            }, $batch);

            $this->client->run(
                'UNWIND $actors AS a
                 MERGE (actor:Actor {id: a.id})
                 SET actor.tmdb_id = a.tmdb_id,
                     actor.name = a.name,
                     actor.profile_path = a.profile_path',
                ['actors' => $actorBatch]
            );

            $count += count($batch);
            if ($onProgress && $count % 500 === 0) {
                $onProgress($count, count($actors), "  🎭 Attori Neo4j: {$count}/" . count($actors));
            }
        }
    }

    private function seedCast(array $cast, ?callable $onProgress): void
    {
        if (empty($cast)) return;
        if ($onProgress) $onProgress(0, 0, "  🔗 Creazione relazioni ACTED_IN in Neo4j...");

        $batches = array_chunk($cast, 200);
        $count = 0;

        foreach ($batches as $batch) {
            $castBatch = array_map(function ($c) {
                return [
                    'movie_id' => $c['movie_id'],
                    'actor_id' => $c['actor_id'],
                    'character' => $c['character_name'],
                    'cast_order' => $c['cast_order'],
                ];
            }, $batch);

            $this->client->run(
                'UNWIND $cast AS c
                 MATCH (a:Actor {id: c.actor_id})
                 MATCH (m:Movie {id: c.movie_id})
                 MERGE (a)-[r:ACTED_IN]->(m)
                 SET r.character = c.character, r.cast_order = c.cast_order',
                ['cast' => $castBatch]
            );

            $count += count($batch);
            if ($onProgress && $count % 1000 === 0) {
                $onProgress($count, count($cast), "  🔗 Cast Neo4j: {$count}/" . count($cast));
            }
        }
    }

    private function seedMovieGenres(array $movies, ?callable $onProgress): void
    {
        if ($onProgress) $onProgress(0, 0, "  📎 Creazione relazioni HAS_GENRE in Neo4j...");

        $relations = [];
        foreach ($movies as $movie) {
            foreach ($movie['genre_ids'] ?? [] as $genreId) {
                $relations[] = [
                    'movie_id' => $movie['id'],
                    'genre_id' => $genreId,
                ];
            }
        }

        if (empty($relations)) return;

        $batches = array_chunk($relations, 200);
        foreach ($batches as $batch) {
            $this->client->run(
                'UNWIND $rels AS r
                 MATCH (m:Movie {id: r.movie_id})
                 MATCH (g:Genre {id: r.genre_id})
                 MERGE (m)-[:HAS_GENRE]->(g)',
                ['rels' => $batch]
            );
        }
    }

    private function seedUsers(array $users, ?callable $onProgress): void
    {
        if (empty($users)) return;
        if ($onProgress) $onProgress(0, 0, "  👥 Creazione nodi User in Neo4j...");

        $teamMap = [
            'Team Alpha' => 'Team Flash',
            'Team Beta' => 'Team Batman',
            'Team Gamma' => 'Team Green Lantern'
        ];

        $mappedUsers = [];
        foreach ($users as $user) {
            $mappedUsers[] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'team_name' => $teamMap[$user['team_name']] ?? $user['team_name']
            ];
        }

        $this->client->run(
            'UNWIND $users AS u
             MERGE (user:User {id: u.id})
             SET user.username = u.username, user.team_name = u.team_name',
            ['users' => $mappedUsers]
        );
    }

    private function seedReviews(array $reviews, ?callable $onProgress): void
    {
        if (empty($reviews)) return;
        if ($onProgress) $onProgress(0, 0, "  ⭐ Creazione relazioni REVIEWED in Neo4j...");

        $batches = array_chunk($reviews, 200);
        $count = 0;

        foreach ($batches as $batch) {
            $reviewBatch = array_map(function ($r) {
                return [
                    'user_id' => $r['user_id'],
                    'movie_id' => $r['movie_id'],
                    'rating' => $r['rating'],
                    'comment' => $r['comment'],
                    'created_at' => $r['created_at'],
                ];
            }, $batch);

            $this->client->run(
                'UNWIND $reviews AS r
                 MATCH (u:User {id: r.user_id})
                 MATCH (m:Movie {id: r.movie_id})
                 MERGE (u)-[rev:REVIEWED]->(m)
                 SET rev.rating = r.rating, 
                     rev.comment = r.comment,
                     rev.created_at = r.created_at',
                ['reviews' => $reviewBatch]
            );

            $count += count($batch);
            if ($onProgress && $count % 500 === 0) {
                $onProgress($count, count($reviews), "  ⭐ Review Neo4j: {$count}/" . count($reviews));
            }
        }
    }
}
