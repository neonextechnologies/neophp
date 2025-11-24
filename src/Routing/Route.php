<?php

namespace NeoPhp\Routing;

use NeoPhp\Http\Request;
use Closure;

class Route
{
    protected $method;
    protected $uri;
    protected $action;
    protected $middleware = [];
    protected $parameters = [];
    protected $name;

    public function __construct(string $method, string $uri, $action)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->action = $action;
    }

    public function middleware($middleware): self
    {
        $this->middleware = array_merge($this->middleware, (array) $middleware);
        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function matches(string $uri): bool
    {
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '([^/]+)', $this->uri);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            array_shift($matches);
            $this->parameters = $matches;
            return true;
        }

        return false;
    }

    public function run(Request $request)
    {
        if ($this->action instanceof Closure) {
            return call_user_func_array($this->action, array_merge([$request], $this->parameters));
        }

        if (is_string($this->action)) {
            [$controller, $method] = explode('@', $this->action);

            if (!class_exists($controller)) {
                throw new \Exception("Controller [$controller] not found.");
            }

            $instance = new $controller();

            if (!method_exists($instance, $method)) {
                throw new \Exception("Method [$method] not found in controller [$controller].");
            }

            return call_user_func_array([$instance, $method], array_merge([$request], $this->parameters));
        }

        if (is_array($this->action)) {
            [$controller, $method] = $this->action;

            if (is_string($controller)) {
                $controller = new $controller();
            }

            return call_user_func_array([$controller, $method], array_merge([$request], $this->parameters));
        }

        throw new \Exception("Invalid route action.");
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
