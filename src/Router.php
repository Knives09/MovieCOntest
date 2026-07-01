<?php

namespace MovieChallenge;

/**
 * Simple Router
 * Maps URL paths to controller actions.
 */
class Router
{
    private array $routes = [];

    /**
     * Register a GET route.
     */
    public function get(string $path, callable $handler): self
    {
        $this->routes['GET'][$path] = $handler;
        return $this;
    }

    /**
     * Register a POST route.
     */
    public function post(string $path, callable $handler): self
    {
        $this->routes['POST'][$path] = $handler;
        return $this;
    }

    /**
     * Dispatch the current request.
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove trailing slash (except for root)
        if ($uri !== '/' && str_ends_with($uri, '/')) {
            $uri = rtrim($uri, '/');
        }

        // Check for exact match first
        if (isset($this->routes[$method][$uri])) {
            ($this->routes[$method][$uri])();
            return;
        }

        // Check for pattern matches (e.g., /api/challenge/{id})
        foreach ($this->routes[$method] ?? [] as $pattern => $handler) {
            $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
            $regex = '#^' . $regex . '$#';

            if (preg_match($regex, $uri, $matches)) {
                // Extract named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                ($handler)($params);
                return;
            }
        }

        // 404
        http_response_code(404);
        if ($this->isApiRequest($uri)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Not found', 'path' => $uri]);
        } else {
            echo "<h1>404 — Page Not Found</h1>";
        }
    }

    /**
     * Check if this is an API request.
     */
    private function isApiRequest(string $uri): bool
    {
        return str_starts_with($uri, '/api/');
    }

    /**
     * Send a JSON response.
     */
    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Render a PHP template.
     */
    public static function render(string $template, array $data = []): void
    {
        extract($data);
        $templatePath = __DIR__ . '/../templates/' . $template . '.php';

        if (!file_exists($templatePath)) {
            http_response_code(500);
            echo "Template not found: {$template}";
            return;
        }

        ob_start();
        require $templatePath;
        $content = ob_get_clean();

        // Wrap in layout if not a partial
        if (!str_starts_with($template, 'partials/')) {
            $pageTitle = $data['pageTitle'] ?? 'MovieChallenge';
            require __DIR__ . '/../templates/layout.php';
        } else {
            echo $content;
        }
    }
}
