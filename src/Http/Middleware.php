<?php

namespace NeoPhp\Http;

abstract class Middleware
{
    abstract public function handle(Request $request, callable $next): Response;
}

class MiddlewareStack
{
    protected $middleware = [];

    public function add($middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    public function handle(Request $request, callable $final): Response
    {
        $next = $final;

        foreach (array_reverse($this->middleware) as $middleware) {
            $next = function ($request) use ($middleware, $next) {
                if (is_string($middleware)) {
                    $middleware = new $middleware();
                }

                return $middleware->handle($request, $next);
            };
        }

        return $next($request);
    }
}
