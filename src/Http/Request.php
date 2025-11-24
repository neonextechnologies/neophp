<?php

namespace NeoPhp\Http;

class Request
{
    protected $method;
    protected $uri;
    protected $query = [];
    protected $data = [];
    protected $files = [];
    protected $server = [];
    protected $headers = [];
    protected $cookies = [];

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri = $this->parseUri();
        $this->query = $_GET;
        $this->data = $_POST;
        $this->files = $_FILES;
        $this->server = $_SERVER;
        $this->cookies = $_COOKIE;
        $this->headers = $this->parseHeaders();
    }

    protected function parseUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $queryPos = strpos($uri, '?');
        
        if ($queryPos !== false) {
            $uri = substr($uri, 0, $queryPos);
        }

        return rtrim($uri, '/') ?: '/';
    }

    protected function parseHeaders(): array
    {
        $headers = [];

        foreach ($this->server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerKey = str_replace('_', '-', substr($key, 5));
                $headers[$headerKey] = $value;
            }
        }

        if (isset($this->server['CONTENT_TYPE'])) {
            $headers['CONTENT-TYPE'] = $this->server['CONTENT_TYPE'];
        }

        if (isset($this->server['CONTENT_LENGTH'])) {
            $headers['CONTENT-LENGTH'] = $this->server['CONTENT_LENGTH'];
        }

        return $headers;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function path(): string
    {
        return $this->uri;
    }

    public function get(string $key, $default = null)
    {
        return $this->query[$key] ?? $default;
    }

    public function post(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function input(string $key, $default = null)
    {
        return $this->post($key) ?? $this->get($key) ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->data);
    }

    public function has(string $key): bool
    {
        return isset($this->query[$key]) || isset($this->data[$key]);
    }

    public function header(string $key, $default = null)
    {
        $key = strtoupper(str_replace('-', '_', $key));
        return $this->headers[$key] ?? $default;
    }

    public function cookie(string $key, $default = null)
    {
        return $this->cookies[$key] ?? $default;
    }

    public function file(string $key)
    {
        return $this->files[$key] ?? null;
    }

    public function isMethod(string $method): bool
    {
        return strtoupper($method) === $this->method;
    }

    public function isGet(): bool
    {
        return $this->isMethod('GET');
    }

    public function isPost(): bool
    {
        return $this->isMethod('POST');
    }

    public function isAjax(): bool
    {
        return $this->header('X-REQUESTED-WITH') === 'XMLHttpRequest';
    }

    public function ip(): string
    {
        return $this->server['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }
}
