<?php

namespace MovieChallenge\Seeder;

/**
 * TMDb API Importer
 * Downloads real movie data from The Movie Database (TMDb) API.
 * Fetches popular movies with their cast, genres, and creates synthetic reviews.
 */
class TMDbImporter
{
    private string $apiKey;
    private string $accessToken;
    private string $baseUrl;
    private int $requestCount = 0;
    private float $lastRequestTime = 0;

    public function __construct()
    {
        $this->apiKey = $_ENV['TMDB_API_KEY'] ?? '';
        $this->accessToken = $_ENV['TMDB_ACCESS_TOKEN'] ?? '';
        $this->baseUrl = $_ENV['TMDB_BASE_URL'] ?? 'https://api.themoviedb.org/3';
    }

    /**
     * Import movies from TMDb.
     *
     * @param int $totalMovies Number of movies to import
     * @param callable|null $onProgress Progress callback (current, total, message)
     * @return array ['movies' => [...], 'actors' => [...], 'genres' => [...], 'cast' => [...]]
     */
    public function importMovies(int $totalMovies = 1000, ?callable $onProgress = null): array
    {
        $movies = [];
        $actors = [];
        $genres = [];
        $cast = [];
        $actorIds = []; // Track unique actors by tmdb_id
        $genreIds = []; // Track unique genres by tmdb_id
        $movieAutoId = 0;
        $actorAutoId = 0;
        $genreAutoId = 0;

        // First, get the genre list
        $genreList = $this->fetchGenres();
        foreach ($genreList as $g) {
            $genreAutoId++;
            $genres[$g['id']] = [
                'id' => $genreAutoId,
                'tmdb_id' => $g['id'],
                'name' => $g['name'],
            ];
            $genreIds[$g['id']] = $genreAutoId;
        }

        $movieTmdbIds = []; // Track unique movie tmdb_ids
        $moviesPerPage = 20; // TMDb returns 20 per page
        $totalPages = (int) ceil($totalMovies / $moviesPerPage);
        $imported = 0;

        for ($page = 1; $page <= $totalPages && $imported < $totalMovies; $page++) {
            $response = $this->apiRequest("/movie/popular", ['page' => $page]);

            if (!$response || !isset($response['results'])) {
                if ($onProgress) $onProgress($imported, $totalMovies, "⚠️  API error on page {$page}");
                continue;
            }

            foreach ($response['results'] as $movieData) {
                if ($imported >= $totalMovies) break;

                $tmdbId = $movieData['id'];
                if (isset($movieTmdbIds[$tmdbId])) {
                    continue; // Skip duplicate movies returned by API pages
                }
                $movieTmdbIds[$tmdbId] = true;

                $movieAutoId++;
                $imported++;

                $movie = [
                    'id' => $movieAutoId,
                    'tmdb_id' => $tmdbId,
                    'title' => $movieData['title'] ?? 'Unknown',
                    'release_date' => $movieData['release_date'] ?? null,
                    'overview' => $movieData['overview'] ?? '',
                    'vote_average' => $movieData['vote_average'] ?? 0,
                    'poster_path' => $movieData['poster_path'] ?? null,
                    'genre_ids' => [],
                ];

                // Map genre IDs
                foreach ($movieData['genre_ids'] ?? [] as $tmdbGenreId) {
                    if (isset($genreIds[$tmdbGenreId])) {
                        $movie['genre_ids'][] = $genreIds[$tmdbGenreId];
                    }
                }

                $movies[$movieAutoId] = $movie;

                // Fetch cast for this movie
                $credits = $this->apiRequest("/movie/{$movieData['id']}/credits");
                if ($credits && isset($credits['cast'])) {
                    $castOrder = 0;
                    foreach (array_slice($credits['cast'], 0, 15) as $person) { // Top 15 cast
                        if (!isset($person['id']) || !isset($person['name'])) continue;

                        $tmdbActorId = $person['id'];

                        // Only add actor once
                        if (!isset($actorIds[$tmdbActorId])) {
                            $actorAutoId++;
                            $actorIds[$tmdbActorId] = $actorAutoId;
                            $actors[$actorAutoId] = [
                                'id' => $actorAutoId,
                                'tmdb_id' => $tmdbActorId,
                                'name' => $person['name'],
                                'profile_path' => $person['profile_path'] ?? null,
                            ];
                        }

                        $cast[] = [
                            'movie_id' => $movieAutoId,
                            'actor_id' => $actorIds[$tmdbActorId],
                            'character_name' => $person['character'] ?? '',
                            'cast_order' => $castOrder++,
                        ];
                    }
                }

                if ($onProgress && $imported % 10 === 0) {
                    $onProgress($imported, $totalMovies, "📥 Importati {$imported}/{$totalMovies} film...");
                }
            }
        }

        if ($onProgress) {
            $onProgress($imported, $totalMovies, "✅ Import completato: {$imported} film, " . count($actors) . " attori, " . count($cast) . " relazioni cast");
        }

        return [
            'movies' => $movies,
            'actors' => $actors,
            'genres' => $genres,
            'cast' => $cast,
        ];
    }

    /**
     * Generate synthetic users and reviews.
     *
     * @param array $movieIds Array of movie IDs to review
     * @param int $numUsers Number of synthetic users to create
     * @param int $reviewsPerUser Average reviews per user
     * @return array ['users' => [...], 'reviews' => [...]]
     */
    public function generateReviews(array $movieIds, int $numUsers = 33, int $reviewsPerUser = 30): array
    {
        $users = [];
        $reviews = [];
        $teams = ['Team Alpha', 'Team Beta', 'Team Gamma'];

        // Create synthetic users (11 per team)
        for ($i = 1; $i <= $numUsers; $i++) {
            $teamIndex = ($i - 1) % 3;
            $users[] = [
                'id' => $i,
                'username' => "user_{$i}",
                'team_name' => $teams[$teamIndex],
            ];
        }

        // Generate reviews
        $reviewId = 0;
        foreach ($users as $user) {
            // Each user reviews a random subset of movies
            $numReviews = rand(max(1, $reviewsPerUser - 10), $reviewsPerUser + 10);
            $reviewedMovies = array_rand(array_flip($movieIds), min($numReviews, count($movieIds)));
            if (!is_array($reviewedMovies)) $reviewedMovies = [$reviewedMovies];

            foreach ($reviewedMovies as $movieId) {
                $reviewId++;
                // Generate realistic-ish ratings (skewed towards higher ratings)
                $rating = $this->generateRating();
                $reviews[] = [
                    'id' => $reviewId,
                    'user_id' => $user['id'],
                    'movie_id' => $movieId,
                    'rating' => $rating,
                    'comment' => $this->generateComment($rating),
                    'created_at' => date('Y-m-d H:i:s', strtotime("-" . rand(1, 365) . " days")),
                ];
            }
        }

        return [
            'users' => $users,
            'reviews' => $reviews,
        ];
    }

    /**
     * Fetch genre list from TMDb.
     */
    private function fetchGenres(): array
    {
        $response = $this->apiRequest('/genre/movie/list');
        return $response['genres'] ?? [];
    }

    /**
     * Make an API request to TMDb with rate limiting.
     */
    private function apiRequest(string $endpoint, array $params = []): ?array
    {
        // Rate limiting: max 40 requests per 10 seconds
        $this->requestCount++;
        if ($this->requestCount % 35 === 0) {
            $elapsed = microtime(true) - $this->lastRequestTime;
            if ($elapsed < 10) {
                usleep((int) ((10 - $elapsed) * 1_000_000));
            }
            $this->lastRequestTime = microtime(true);
        }

        $url = $this->baseUrl . $endpoint;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    "Authorization: Bearer {$this->accessToken}",
                    "Accept: application/json",
                ],
                'timeout' => 15,
                'ignore_errors' => true,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            return null;
        }

        return json_decode($response, true);
    }

    /**
     * Generate a realistic rating (skewed bell curve, most ratings 3-4.5).
     */
    private function generateRating(): float
    {
        // Use a weighted random to create a realistic distribution
        $weights = [
            "1.0" => 2, "1.5" => 3, "2.0" => 5, "2.5" => 8,
            "3.0" => 15, "3.5" => 25, "4.0" => 22, "4.5" => 12,
            "5.0" => 8,
        ];

        $total = array_sum($weights);
        $rand = rand(1, $total);
        $cumulative = 0;

        foreach ($weights as $rating => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return (float) $rating;
            }
        }

        return 3.5;
    }

    /**
     * Generate a simple review comment based on rating.
     */
    private function generateComment(float $rating): string
    {
        $comments = [
            1 => ['Terribile.', 'Non lo consiglio.', 'Deludente.', 'Pessimo film.'],
            2 => ['Mediocre.', 'Poteva essere meglio.', 'Non il massimo.', 'Noioso.'],
            3 => ['Carino.', 'Nella media.', 'Discreto.', 'Si lascia guardare.'],
            4 => ['Molto bello!', 'Consigliato!', 'Ottimo film.', 'Ben fatto.'],
            5 => ['Capolavoro!', 'Straordinario!', 'Da vedere assolutamente!', 'Perfetto!'],
        ];

        $key = max(1, min(5, (int) round($rating)));
        return $comments[$key][array_rand($comments[$key])];
    }
}
