<?php

if (!function_exists('app')) {
    function app($abstract = null, array $parameters = [])
    {
        if (is_null($abstract)) {
            return \NeoPhp\Core\Application::getInstance();
        }

        return \NeoPhp\Core\Application::getInstance()->make($abstract, $parameters);
    }
}

if (!function_exists('config')) {
    function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('config');
        }

        return app('config')->get($key, $default);
    }
}

if (!function_exists('env')) {
    function env($key, $default = null)
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false) {
            return $default;
        }

        return $value;
    }
}

if (!function_exists('base_path')) {
    function base_path($path = '')
    {
        return app()->basePath($path);
    }
}

if (!function_exists('config_path')) {
    function config_path($path = '')
    {
        return app()->configPath($path);
    }
}

if (!function_exists('storage_path')) {
    function storage_path($path = '')
    {
        return app()->storagePath($path);
    }
}

if (!function_exists('public_path')) {
    function public_path($path = '')
    {
        return app()->publicPath($path);
    }
}

if (!function_exists('response')) {
    function response($content = '', $status = 200, array $headers = [])
    {
        return new \NeoPhp\Http\Response($content, $status, $headers);
    }
}

if (!function_exists('json')) {
    function json($data, $status = 200)
    {
        return response()->json($data, $status);
    }
}

if (!function_exists('redirect')) {
    function redirect($url, $status = 302)
    {
        return response()->redirect($url, $status);
    }
}

if (!function_exists('dd')) {
    function dd(...$vars)
    {
        foreach ($vars as $var) {
            var_dump($var);
        }
        die(1);
    }
}

if (!function_exists('dump')) {
    function dump(...$vars)
    {
        foreach ($vars as $var) {
            var_dump($var);
        }
    }
}

if (!function_exists('view')) {
    function view(string $view, array $data = [])
    {
        return app('view')->render($view, $data);
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return config('app.url') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        return config('app.url') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('auth')) {
    function auth()
    {
        return app(\NeoPhp\Auth\Auth::class);
    }
}

if (!function_exists('validator')) {
    function validator(array $data, array $rules, array $messages = [])
    {
        return \NeoPhp\Validation\Validator::make($data, $rules, $messages);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('old')) {
    function old(string $key, $default = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION['_old'][$key] ?? $default;
    }
}

if (!function_exists('session')) {
    function session(string $key = null, $default = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($key === null) {
            return $_SESSION;
        }

        return $_SESSION[$key] ?? $default;
    }
}

if (!function_exists('cache')) {
    function cache($key = null, $value = null, $seconds = 3600)
    {
        $cache = app(\NeoPhp\Cache\Cache::class);

        if (is_null($key)) {
            return $cache;
        }

        if (is_null($value)) {
            return $cache->get($key);
        }

        return $cache->put($key, $value, $seconds);
    }
}

if (!function_exists('session')) {
    function session($key = null, $default = null)
    {
        $session = app(\NeoPhp\Session\Session::class);

        if (is_null($key)) {
            return $session;
        }

        return $session->get($key, $default);
    }
}

if (!function_exists('logger')) {
    function logger($message = null, array $context = [])
    {
        $logger = app(\NeoPhp\Logging\Logger::class);

        if (is_null($message)) {
            return $logger;
        }

        return $logger->info($message, $context);
    }
}

if (!function_exists('storage')) {
    function storage()
    {
        return app(\NeoPhp\Storage\Storage::class);
    }
}

if (!function_exists('event')) {
    function event(string $event, $payload = null)
    {
        return \NeoPhp\Events\EventDispatcher::dispatch($event, $payload);
    }
}

if (!function_exists('queue')) {
    function queue()
    {
        return app(\NeoPhp\Queue\Queue::class);
    }
}

if (!function_exists('mail')) {
    function mail()
    {
        return app(\NeoPhp\Mail\Mailer::class);
    }
}

if (!function_exists('benchmark')) {
    function benchmark(string $name, callable $callback = null)
    {
        if (is_null($callback)) {
            return \NeoPhp\Performance\Benchmark::class;
        }

        return \NeoPhp\Performance\Benchmark::measure($name, $callback);
    }
}

if (!function_exists('paginate')) {
    function paginate(array $items, int $total, int $perPage = 15, int $currentPage = 1)
    {
        return new \NeoPhp\Pagination\Paginator($items, $total, $perPage, $currentPage);
    }
}

if (!function_exists('schedule')) {
    function schedule()
    {
        return \NeoPhp\Schedule\Schedule::class;
    }
}
