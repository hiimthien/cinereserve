<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Movie;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TmdbMovieSyncService
{
    protected string $apiKey;
    protected string $readToken;
    protected string $baseUrl;
    protected string $imageBaseUrl;

    public function __construct()
    {
        $this->apiKey = (string) config('services.tmdb.api_key');
        $this->readToken = (string) config('services.tmdb.read_token');
        $this->baseUrl = (string) config('services.tmdb.base_url', 'https://api.themoviedb.org/3');
        $this->imageBaseUrl = (string) config('services.tmdb.image_base_url', 'https://image.tmdb.org/t/p');
    }

    /**
     * Đồng bộ toàn bộ phim đang chiếu (Now Playing) và sắp chiếu (Upcoming) tại Việt Nam
     */
    public function syncAllMovies(int $nowPlayingPages = 2, int $upcomingPages = 1): array
    {
        $syncedNowPlaying = $this->syncNowPlayingMovies($nowPlayingPages);
        $syncedUpcoming = $this->syncUpcomingMovies($upcomingPages);

        return [
            'now_playing_count' => count($syncedNowPlaying),
            'upcoming_count' => count($syncedUpcoming),
            'total_movies' => count($syncedNowPlaying) + count($syncedUpcoming),
        ];
    }

    /**
     * Đồng bộ phim đang chiếu tại Việt Nam
     */
    public function syncNowPlayingMovies(int $pages = 2): array
    {
        $movies = [];
        for ($page = 1; $page <= $pages; $page++) {
            $response = $this->makeRequest('/movie/now_playing', [
                'region' => 'VN',
                'language' => 'vi-VN',
                'page' => $page,
            ]);

            if ($response->successful()) {
                $results = $response->json('results', []);
                foreach ($results as $item) {
                    $movie = $this->processAndSaveMovie($item['id'], 'now_showing');
                    if ($movie) {
                        $movies[] = $movie;
                    }
                }
            }
        }

        return $movies;
    }

    /**
     * Đồng bộ phim sắp chiếu
     */
    public function syncUpcomingMovies(int $pages = 1): array
    {
        $movies = [];
        for ($page = 1; $page <= $pages; $page++) {
            $response = $this->makeRequest('/movie/upcoming', [
                'region' => 'VN',
                'language' => 'vi-VN',
                'page' => $page,
            ]);

            if ($response->successful()) {
                $results = $response->json('results', []);
                foreach ($results as $item) {
                    $movie = $this->processAndSaveMovie($item['id'], 'coming_soon');
                    if ($movie) {
                        $movies[] = $movie;
                    }
                }
            }
        }

        return $movies;
    }

    /**
     * Lấy chi tiết 1 bộ phim, credits, videos và lưu vào DB
     */
    public function processAndSaveMovie(int $tmdbId, string $status = 'now_showing'): ?Movie
    {
        try {
            // Lấy chi tiết tiếng Việt
            $viResponse = $this->makeRequest("/movie/{$tmdbId}", [
                'append_to_response' => 'videos,credits',
                'language' => 'vi-VN',
            ]);

            if (!$viResponse->successful()) {
                return null;
            }

            $viData = $viResponse->json();

            // Nếu thiếu overview hoặc trailer thì gọi thêm bản tiếng Anh
            $enData = [];
            if (empty($viData['overview']) || empty($viData['videos']['results'])) {
                $enResponse = $this->makeRequest("/movie/{$tmdbId}", [
                    'append_to_response' => 'videos,credits',
                    'language' => 'en-US',
                ]);
                if ($enResponse->successful()) {
                    $enData = $enResponse->json();
                }
            }

            $title = !empty($viData['title']) ? $viData['title'] : ($enData['title'] ?? 'Phim Chiếu Rạp');
            $originalTitle = $viData['original_title'] ?? $title;
            $overview = !empty($viData['overview']) ? $viData['overview'] : ($enData['overview'] ?? 'Đang cập nhật tóm tắt nội dung phim.');
            
            // Xử lý Poster & Backdrop chất lượng cao
            $posterPath = $viData['poster_path'] ?? ($enData['poster_path'] ?? null);
            $backdropPath = $viData['backdrop_path'] ?? ($enData['backdrop_path'] ?? null);

            $posterUrl = $posterPath 
                ? "{$this->imageBaseUrl}/w780{$posterPath}" 
                : 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=600&auto=format&fit=crop&q=80';

            $backdropUrl = $backdropPath 
                ? "{$this->imageBaseUrl}/original{$backdropPath}" 
                : 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?w=1600&auto=format&fit=crop&q=80';

            // Xử lý Trailer YouTube
            $videos = array_merge($viData['videos']['results'] ?? [], $enData['videos']['results'] ?? []);
            $trailerKey = null;
            foreach ($videos as $vid) {
                if ($vid['site'] === 'YouTube' && in_array($vid['type'], ['Trailer', 'Teaser'])) {
                    $trailerKey = $vid['key'];
                    break;
                }
            }
            $trailerUrl = $trailerKey ? "https://www.youtube.com/embed/{$trailerKey}" : null;

            // Xử lý Đạo diễn & Diễn viên
            $credits = !empty($viData['credits']) ? $viData['credits'] : ($enData['credits'] ?? []);
            $director = null;
            if (!empty($credits['crew'])) {
                foreach ($credits['crew'] as $crew) {
                    if ($crew['job'] === 'Director') {
                        $director = $crew['name'];
                        break;
                    }
                }
            }

            $cast = [];
            if (!empty($credits['cast'])) {
                $topCast = array_slice($credits['cast'], 0, 5);
                $cast = array_map(fn($c) => $c['name'], $topCast);
            }

            // Xử lý Thể loại (Genres)
            $genres = [];
            if (!empty($viData['genres'])) {
                $genres = array_map(fn($g) => $g['name'], $viData['genres']);
            } elseif (!empty($enData['genres'])) {
                $genres = array_map(fn($g) => $g['name'], $enData['genres']);
            }

            $duration = (int) ($viData['runtime'] ?? ($enData['runtime'] ?? 115));
            if ($duration <= 0) $duration = 115;

            $releaseDate = !empty($viData['release_date']) ? $viData['release_date'] : date('Y-m-d');
            $rating = round((float) ($viData['vote_average'] ?? 8.0), 1);
            if ($rating <= 0) $rating = 8.5;

            $slug = Str::slug($originalTitle . '-' . $tmdbId);

            return Movie::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'original_title' => $originalTitle,
                    'duration' => $duration,
                    'release_date' => $releaseDate,
                    'poster_url' => $posterUrl,
                    'backdrop_url' => $backdropUrl,
                    'trailer_url' => $trailerUrl,
                    'rating' => $rating,
                    'genre' => $genres,
                    'description' => $overview,
                    'director' => $director ?? 'Đang cập nhật',
                    'cast' => $cast,
                    'status' => $status,
                ]
            );
        } catch (\Exception $e) {
            Log::error("Lỗi sync phim TMDb ID {$tmdbId}: " . $e->getMessage());
            return null;
        }
    }

    protected function makeRequest(string $endpoint, array $params = [])
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');

        $request = Http::withoutVerifying()->timeout(15);
        if (!empty($this->readToken)) {
            $request = $request->withToken($this->readToken);
        } else {
            $params['api_key'] = $this->apiKey;
        }

        return $request->get($url, $params);
    }

}
