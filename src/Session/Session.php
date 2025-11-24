<?php

namespace NeoPhp\Session;

class Session
{
    protected $started = false;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            $this->started = true;
        }
    }

    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public function put(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function flush(): void
    {
        $_SESSION = [];
    }

    public function regenerate(bool $destroy = false): bool
    {
        return session_regenerate_id($destroy);
    }

    public function getId(): string
    {
        return session_id();
    }

    public function setId(string $id): void
    {
        session_id($id);
    }

    public function all(): array
    {
        return $_SESSION;
    }

    public function flash(string $key, $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public function getFlash(string $key, $default = null)
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }
}
