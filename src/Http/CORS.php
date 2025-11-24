<?php

namespace NeoPhp\Http;

class CORS
{
    protected $allowedOrigins;
    protected $allowedMethods;
    protected $allowedHeaders;
    protected $exposedHeaders;
    protected $maxAge;
    protected $credentials;

    public function __construct(array $config = [])
    {
        $this->allowedOrigins = $config['allowed_origins'] ?? ['*'];
        $this->allowedMethods = $config['allowed_methods'] ?? ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];
        $this->allowedHeaders = $config['allowed_headers'] ?? ['*'];
        $this->exposedHeaders = $config['exposed_headers'] ?? [];
        $this->maxAge = $config['max_age'] ?? 86400;
        $this->credentials = $config['credentials'] ?? false;
    }

    public function handle(Request $request, callable $next)
    {
        // Handle preflight
        if ($request->method() === 'OPTIONS') {
            return $this->handlePreflight();
        }

        $response = $next($request);

        return $this->addHeaders($response, $request);
    }

    protected function handlePreflight(): Response
    {
        $response = new Response('', 200);
        
        $response->setHeader('Access-Control-Allow-Methods', implode(', ', $this->allowedMethods));
        $response->setHeader('Access-Control-Allow-Headers', implode(', ', $this->allowedHeaders));
        $response->setHeader('Access-Control-Max-Age', (string) $this->maxAge);

        return $response;
    }

    protected function addHeaders(Response $response, Request $request): Response
    {
        $origin = $request->header('Origin', '*');

        if (in_array('*', $this->allowedOrigins) || in_array($origin, $this->allowedOrigins)) {
            $response->setHeader('Access-Control-Allow-Origin', $origin);
        }

        if ($this->credentials) {
            $response->setHeader('Access-Control-Allow-Credentials', 'true');
        }

        if (!empty($this->exposedHeaders)) {
            $response->setHeader('Access-Control-Expose-Headers', implode(', ', $this->exposedHeaders));
        }

        return $response;
    }
}
