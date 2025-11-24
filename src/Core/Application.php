<?php

namespace NeoPhp\Core;

use NeoPhp\Container\Container;
use NeoPhp\Http\Request;
use NeoPhp\Http\Response;
use NeoPhp\Routing\Router;
use NeoPhp\Config\Config;

class Application extends Container
{
    protected $basePath;
    protected $booted = false;
    protected $serviceProviders = [];
    protected $loadedProviders = [];

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '\/');
        
        $this->registerBaseBindings();
        $this->registerBaseServiceProviders();
    }

    protected function registerBaseBindings(): void
    {
        static::setInstance($this);

        $this->instance('app', $this);
        $this->instance(Container::class, $this);
        $this->instance(Application::class, $this);

        $this->singleton(Router::class);
        $this->singleton(Request::class);
        $this->singleton(Config::class, function ($app) {
            return new Config($app->configPath());
        });

        $this->alias(Router::class, 'router');
        $this->alias(Request::class, 'request');
        $this->alias(Config::class, 'config');
    }

    protected function registerBaseServiceProviders(): void
    {
        // Base service providers will be registered here
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        foreach ($this->serviceProviders as $provider) {
            if (method_exists($provider, 'boot')) {
                $this->call([$provider, 'boot']);
            }
        }

        $this->booted = true;
    }

    public function run(): void
    {
        $this->boot();

        $request = $this->make(Request::class);
        $router = $this->make(Router::class);

        $response = $router->dispatch($request);
        $response->send();
    }

    public function register($provider): void
    {
        if (is_string($provider)) {
            $provider = new $provider($this);
        }

        if (method_exists($provider, 'register')) {
            $provider->register();
        }

        $this->serviceProviders[] = $provider;
        $this->loadedProviders[get_class($provider)] = true;
    }

    public function basePath(string $path = ''): string
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }

    public function configPath(string $path = ''): string
    {
        return $this->basePath('config') . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }

    public function storagePath(string $path = ''): string
    {
        return $this->basePath('storage') . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }

    public function publicPath(string $path = ''): string
    {
        return $this->basePath('public') . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }

    public function isBooted(): bool
    {
        return $this->booted;
    }

    public function environment(): string
    {
        return $this->make('config')->get('app.env', 'production');
    }

    public function isProduction(): bool
    {
        return $this->environment() === 'production';
    }

    public function isDebug(): bool
    {
        return $this->make('config')->get('app.debug', false);
    }

    protected function call($callback, array $parameters = [])
    {
        if (is_array($callback)) {
            return call_user_func_array($callback, $parameters);
        }

        return $callback($this, ...$parameters);
    }
}
