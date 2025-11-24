<?php

namespace NeoPhp\Core;

use NeoPhp\Core\Attributes\Module as ModuleAttribute;
use NeoPhp\Core\Attributes\Injectable;
use NeoPhp\Core\Attributes\Controller as ControllerAttribute;
use NeoPhp\Core\Attributes\Get;
use NeoPhp\Core\Attributes\Post;
use ReflectionClass;
use ReflectionMethod;

class ModuleLoader
{
    protected $app;
    protected $loadedModules = [];
    protected $moduleRegistry = [];

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function loadModule(string $moduleClass): void
    {
        if (isset($this->loadedModules[$moduleClass])) {
            return;
        }

        $reflection = new ReflectionClass($moduleClass);
        $attributes = $reflection->getAttributes(ModuleAttribute::class);

        if (empty($attributes)) {
            throw new \Exception("Class {$moduleClass} is not a valid module. Missing #[Module] attribute.");
        }

        $moduleAttribute = $attributes[0]->newInstance();
        
        // Load imported modules first
        foreach ($moduleAttribute->getImports() as $importedModule) {
            $this->loadModule($importedModule);
        }

        // Register providers
        foreach ($moduleAttribute->getProviders() as $provider) {
            $this->registerProvider($provider);
        }

        // Register controllers
        foreach ($moduleAttribute->getControllers() as $controller) {
            $this->registerController($controller);
        }

        $this->loadedModules[$moduleClass] = true;
        $this->moduleRegistry[$moduleClass] = [
            'attribute' => $moduleAttribute,
            'reflection' => $reflection,
        ];
    }

    protected function registerProvider(string $providerClass): void
    {
        $reflection = new ReflectionClass($providerClass);
        $attributes = $reflection->getAttributes(Injectable::class);

        if (!empty($attributes)) {
            $injectable = $attributes[0]->newInstance();
            
            if ($injectable->scope === 'singleton') {
                $this->app->singleton($providerClass);
            } else {
                $this->app->bind($providerClass);
            }
        } else {
            $this->app->singleton($providerClass);
        }

        // Auto-resolve and store instance
        $instance = $this->app->make($providerClass);
        
        // Register by class name and interface
        $interfaces = class_implements($providerClass);
        foreach ($interfaces as $interface) {
            $this->app->instance($interface, $instance);
        }
    }

    protected function registerController(string $controllerClass): void
    {
        $reflection = new ReflectionClass($controllerClass);
        $controllerAttributes = $reflection->getAttributes(ControllerAttribute::class);
        
        $prefix = '';
        if (!empty($controllerAttributes)) {
            $controllerAttr = $controllerAttributes[0]->newInstance();
            $prefix = $controllerAttr->prefix;
        }

        // Register controller in container
        $this->app->bind($controllerClass);

        // Scan methods for route attributes
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        $router = $this->app->make('router');

        foreach ($methods as $method) {
            // Check for GET attribute
            $getAttributes = $method->getAttributes(Get::class);
            if (!empty($getAttributes)) {
                $getAttr = $getAttributes[0]->newInstance();
                $path = $prefix . $getAttr->path;
                $router->get($path, [$controllerClass, $method->getName()]);
                continue;
            }

            // Check for POST attribute
            $postAttributes = $method->getAttributes(Post::class);
            if (!empty($postAttributes)) {
                $postAttr = $postAttributes[0]->newInstance();
                $path = $prefix . $postAttr->path;
                $router->post($path, [$controllerClass, $method->getName()]);
                continue;
            }
        }
    }

    public function loadModulesFromDirectory(string $directory, string $namespace): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );

        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $relativePath = str_replace($directory, '', $file->getPathname());
                $relativePath = str_replace(['/', '\\'], '\\', $relativePath);
                $relativePath = ltrim($relativePath, '\\');
                $className = $namespace . '\\' . str_replace('.php', '', $relativePath);

                if (class_exists($className)) {
                    try {
                        $reflection = new ReflectionClass($className);
                        $attributes = $reflection->getAttributes(ModuleAttribute::class);
                        
                        if (!empty($attributes)) {
                            $this->loadModule($className);
                        }
                    } catch (\Exception $e) {
                        // Skip classes that can't be reflected
                        continue;
                    }
                }
            }
        }
    }

    public function getLoadedModules(): array
    {
        return array_keys($this->loadedModules);
    }

    public function getModuleMetadata(string $moduleClass): ?array
    {
        return $this->moduleRegistry[$moduleClass] ?? null;
    }
}
