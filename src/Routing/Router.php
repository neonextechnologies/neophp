<?php

namespace NeoPhp\Routing;

use NeoPhp\Http\Request;
use NeoPhp\Http\Response;
use Closure;
use Exception;

class Router
{
    protected $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'PATCH' => [],
        'DELETE' => [],
    ];

    protected $prefix = '';
    protected $middleware = [];
    protected $currentRoute = null;

    public function get(string $uri, $action): Route
    {
        return $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, $action): Route
    {
        return $this->addRoute('POST', $uri, $action);
    }

    public function put(string $uri, $action): Route
    {
        return $this->addRoute('PUT', $uri, $action);
    }

    public function patch(string $uri, $action): Route
    {
        return $this->addRoute('PATCH', $uri, $action);
    }

    public function delete(string $uri, $action): Route
    {
        return $this->addRoute('DELETE', $uri, $action);
    }

    public function any(string $uri, $action): void
    {
        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $method) {
            $this->addRoute($method, $uri, $action);
        }
    }

    protected function addRoute(string $method, string $uri, $action): Route
    {
        $uri = $this->prefix . '/' . trim($uri, '/');
        $uri = rtrim($uri, '/') ?: '/';

        $route = new Route($method, $uri, $action);
        $route->middleware($this->middleware);

        $this->routes[$method][$uri] = $route;

        return $route;
    }

    public function group(array $attributes, Closure $callback): void
    {
        $previousPrefix = $this->prefix;
        $previousMiddleware = $this->middleware;

        if (isset($attributes['prefix'])) {
            $this->prefix .= '/' . trim($attributes['prefix'], '/');
        }

        if (isset($attributes['middleware'])) {
            $this->middleware = array_merge(
                $this->middleware,
                (array) $attributes['middleware']
            );
        }

        $callback($this);

        $this->prefix = $previousPrefix;
        $this->middleware = $previousMiddleware;
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $uri = $request->path();

        $route = $this->findRoute($method, $uri);

        if (!$route) {
            return new Response('404 Not Found', 404);
        }

        $this->currentRoute = $route;

        try {
            $response = $route->run($request);

            if (!$response instanceof Response) {
                if (is_array($response) || is_object($response)) {
                    $response = (new Response())->json($response);
                } else {
                    $response = new Response((string) $response);
                }
            }

            return $response;
        } catch (Exception $e) {
            return new Response('500 Internal Server Error: ' . $e->getMessage(), 500);
        }
    }

    protected function findRoute(string $method, string $uri): ?Route
    {
        if (isset($this->routes[$method][$uri])) {
            return $this->routes[$method][$uri];
        }

        foreach ($this->routes[$method] as $route) {
            if ($route->matches($uri)) {
                return $route;
            }
        }

        return null;
    }

    public function getCurrentRoute(): ?Route
    {
        return $this->currentRoute;
    }
}
