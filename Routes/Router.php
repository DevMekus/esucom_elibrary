<?php

namespace App\Routes;

class Router
{
    private static array $routes = [];
    private static string $currentGroupPrefix = '';
    private static array $currentGroupMiddleware = [];

    public static function add(string $method, string $route, callable $callback, array $middleware = []): void
    {
        $fullRoute = self::normalizeRoute(self::$currentGroupPrefix . '/' . $route);
        self::$routes[] = [
            'method'     => strtoupper($method),
            'route'      => $fullRoute,
            'callback'   => $callback,
            'middleware' => array_merge(self::$currentGroupMiddleware, $middleware)
        ];
    }

    public static function group(string $prefix, callable $callback, array $middleware = []): void
    {
        $prevPrefix = self::$currentGroupPrefix;
        $prevMiddleware = self::$currentGroupMiddleware;

        self::$currentGroupPrefix .= self::normalizeRoute($prefix);
        self::$currentGroupMiddleware = array_merge(self::$currentGroupMiddleware, $middleware);

        $callback(); // Define routes inside group

        // Reset to previous state
        self::$currentGroupPrefix = $prevPrefix;
        self::$currentGroupMiddleware = $prevMiddleware;
    }

    public static function dispatch(string $requestedMethod, string $requestedUri): void
    {
        $requestedMethod = strtoupper($requestedMethod);
        $requestedUri = self::normalizeRoute($requestedUri);
        $pathMatched = false;



        foreach (self::$routes as $route) {
            $pattern = self::convertRouteToRegex($route['route']);
            if (preg_match($pattern, $requestedUri, $matches)) {
                $pathMatched = true;

                if ($requestedMethod === $route['method']) {
                    array_shift($matches); // remove full match

                    // Run middleware
                    foreach ($route['middleware'] as $middlewareClass) {
                        $middleware = new $middlewareClass();
                        if (method_exists($middleware, 'handle')) {
                            $continue = $middleware->handle();
                            if ($continue === false) return; // Stop request
                        }
                    }

                    call_user_func_array($route['callback'], $matches);
                    return;
                }
            }
        }

        http_response_code($pathMatched ? 405 : 404);
        echo json_encode([
            'status' => 'error',
            'message' => $pathMatched ? 'Method Not Allowed' : 'Endpoint not found'
        ]);
    }

    private static function convertRouteToRegex(string $route): string
    {
        return "~^" . preg_replace('/\{([^\/]+)\}/', '([^/]+)', $route) . "$~";
    }

    private static function normalizeRoute(string $route): string
    {
        // Remove leading/trailing slashes and collapse multiple slashes
        $route = preg_replace('#/+#', '/', '/' . trim($route, '/'));
        return $route === '/' ? '/' : rtrim($route, '/');
    }
}
