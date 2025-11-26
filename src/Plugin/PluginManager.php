<?php

namespace NeoPhp\Plugin;

use NeoPhp\Contracts\PluginInterface;
use NeoPhp\DI\Container;

/**
 * Plugin Manager
 * Manages plugin lifecycle - discovery, installation, activation
 * Inspired by Neonex Core & WordPress plugin systems
 */
class PluginManager
{
    protected Container $app;
    protected array $plugins = [];
    protected array $active = [];
    protected array $installed = [];
    protected string $pluginPath;
    protected string $dataFile;

    public function __construct(Container $app, string $pluginPath = null, string $dataFile = null)
    {
        $this->app = $app;
        $this->pluginPath = $pluginPath ?? $app->basePath('plugins');
        $this->dataFile = $dataFile ?? $app->basePath('storage/plugins.json');
        
        $this->loadPluginData();
    }

    /**
     * Discover plugins from directory
     */
    public function discover(): array
    {
        $discovered = [];

        if (!is_dir($this->pluginPath)) {
            return $discovered;
        }

        $directories = glob($this->pluginPath . '/*', GLOB_ONLYDIR);

        foreach ($directories as $dir) {
            $pluginFile = $dir . '/Plugin.php';
            
            if (file_exists($pluginFile)) {
                $className = $this->getPluginClassName($dir);
                
                if ($className && class_exists($className)) {
                    $reflection = new \ReflectionClass($className);
                    
                    if (!$reflection->isAbstract() && $reflection->implementsInterface(PluginInterface::class)) {
                        $plugin = new $className();
                        $discovered[$plugin->getName()] = $className;
                    }
                }
            }
        }

        return $discovered;
    }

    /**
     * Register a plugin
     */
    public function register(string $pluginClass): void
    {
        if (isset($this->plugins[$pluginClass])) {
            return;
        }

        $plugin = new $pluginClass();

        if (!$plugin instanceof PluginInterface) {
            throw new \InvalidArgumentException("Plugin must implement PluginInterface");
        }

        $this->plugins[$plugin->getName()] = $plugin;
    }

    /**
     * Install a plugin
     */
    public function install(string $pluginName): bool
    {
        if (!isset($this->plugins[$pluginName])) {
            throw new \RuntimeException("Plugin {$pluginName} not found");
        }

        if ($this->isInstalled($pluginName)) {
            return true;
        }

        $plugin = $this->plugins[$pluginName];

        // Check dependencies
        foreach ($plugin->getDependencies() as $dependency) {
            if (!$this->isInstalled($dependency)) {
                throw new \RuntimeException("Plugin {$pluginName} requires {$dependency}");
            }
        }

        try {
            $plugin->install();
            $this->installed[$pluginName] = [
                'version' => $plugin->getVersion(),
                'installed_at' => date('Y-m-d H:i:s')
            ];
            $this->savePluginData();
            return true;
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to install plugin {$pluginName}: " . $e->getMessage());
        }
    }

    /**
     * Uninstall a plugin
     */
    public function uninstall(string $pluginName): bool
    {
        if (!$this->isInstalled($pluginName)) {
            return true;
        }

        // Deactivate first
        if ($this->isActive($pluginName)) {
            $this->deactivate($pluginName);
        }

        $plugin = $this->plugins[$pluginName];

        try {
            $plugin->uninstall();
            unset($this->installed[$pluginName]);
            $this->savePluginData();
            return true;
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to uninstall plugin {$pluginName}: " . $e->getMessage());
        }
    }

    /**
     * Activate a plugin
     */
    public function activate(string $pluginName): bool
    {
        if (!$this->isInstalled($pluginName)) {
            throw new \RuntimeException("Plugin {$pluginName} must be installed first");
        }

        if ($this->isActive($pluginName)) {
            return true;
        }

        $plugin = $this->plugins[$pluginName];

        try {
            $plugin->boot();
            $plugin->activate();
            
            $this->active[$pluginName] = true;
            $this->savePluginData();
            
            return true;
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to activate plugin {$pluginName}: " . $e->getMessage());
        }
    }

    /**
     * Deactivate a plugin
     */
    public function deactivate(string $pluginName): bool
    {
        if (!$this->isActive($pluginName)) {
            return true;
        }

        $plugin = $this->plugins[$pluginName];
        $plugin->deactivate();
        
        unset($this->active[$pluginName]);
        $this->savePluginData();
        
        return true;
    }

    /**
     * Boot all active plugins
     */
    public function bootPlugins(): void
    {
        foreach ($this->active as $pluginName => $status) {
            if (isset($this->plugins[$pluginName]) && $status) {
                $this->plugins[$pluginName]->boot();
            }
        }
    }

    /**
     * Check if plugin is installed
     */
    public function isInstalled(string $pluginName): bool
    {
        return isset($this->installed[$pluginName]);
    }

    /**
     * Check if plugin is active
     */
    public function isActive(string $pluginName): bool
    {
        return isset($this->active[$pluginName]) && $this->active[$pluginName];
    }

    /**
     * Get plugin
     */
    public function getPlugin(string $pluginName): ?PluginInterface
    {
        return $this->plugins[$pluginName] ?? null;
    }

    /**
     * Get all plugins
     */
    public function getPlugins(): array
    {
        return $this->plugins;
    }

    /**
     * Get all active plugins
     */
    public function getActivePlugins(): array
    {
        return array_filter($this->plugins, fn($name) => $this->isActive($name), ARRAY_FILTER_USE_KEY);
    }

    /**
     * Get all installed plugins
     */
    public function getInstalledPlugins(): array
    {
        return $this->installed;
    }

    /**
     * Load plugin data from storage
     */
    protected function loadPluginData(): void
    {
        if (file_exists($this->dataFile)) {
            $data = json_decode(file_get_contents($this->dataFile), true);
            $this->installed = $data['installed'] ?? [];
            $this->active = $data['active'] ?? [];
        }
    }

    /**
     * Save plugin data to storage
     */
    protected function savePluginData(): void
    {
        $dir = dirname($this->dataFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $data = [
            'installed' => $this->installed,
            'active' => $this->active
        ];

        file_put_contents($this->dataFile, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Get plugin class name from directory
     */
    protected function getPluginClassName(string $dir): ?string
    {
        $pluginFile = $dir . '/Plugin.php';
        
        if (!file_exists($pluginFile)) {
            return null;
        }

        $content = file_get_contents($pluginFile);

        if (preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatch) &&
            preg_match('/class\s+(\w+)/', $content, $classMatch)) {
            return $namespaceMatch[1] . '\\' . $classMatch[1];
        }

        return null;
    }
}
