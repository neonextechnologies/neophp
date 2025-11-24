<?php

namespace NeoPhp\Security;

class CSRF
{
    protected static $tokenKey = '_csrf_token';

    public static function generateToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION[static::$tokenKey] = $token;

        return $token;
    }

    public static function getToken(): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION[static::$tokenKey] ?? null;
    }

    public static function validate(string $token): bool
    {
        $sessionToken = static::getToken();

        if (!$sessionToken) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    public static function validateRequest(Request $request): bool
    {
        $token = $request->input('_token') ?? $request->header('X-CSRF-TOKEN');

        if (!$token) {
            return false;
        }

        return static::validate($token);
    }
}

class XSS
{
    public static function clean($data)
    {
        if (is_array($data)) {
            return array_map([static::class, 'clean'], $data);
        }

        if (!is_string($data)) {
            return $data;
        }

        return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function cleanDeep(array $data): array
    {
        return array_map([static::class, 'clean'], $data);
    }
}

class RateLimiter
{
    protected $cache;
    protected $maxAttempts;
    protected $decayMinutes;

    public function __construct($cache, int $maxAttempts = 60, int $decayMinutes = 1)
    {
        $this->cache = $cache;
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
    }

    public function tooManyAttempts(string $key): bool
    {
        return $this->attempts($key) >= $this->maxAttempts;
    }

    public function hit(string $key, int $decayMinutes = null): int
    {
        $decayMinutes = $decayMinutes ?? $this->decayMinutes;
        
        $this->cache->put(
            $key . ':timer',
            time() + ($decayMinutes * 60),
            $decayMinutes * 60
        );

        $attempts = (int) $this->cache->get($key, 0);
        $this->cache->put($key, $attempts + 1, $decayMinutes * 60);

        return $attempts + 1;
    }

    public function attempts(string $key): int
    {
        return (int) $this->cache->get($key, 0);
    }

    public function resetAttempts(string $key): void
    {
        $this->cache->forget($key);
        $this->cache->forget($key . ':timer');
    }

    public function availableIn(string $key): int
    {
        $timer = (int) $this->cache->get($key . ':timer', 0);
        return max(0, $timer - time());
    }
}
