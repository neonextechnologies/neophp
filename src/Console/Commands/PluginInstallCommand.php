<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;
use NeoPhp\Plugin\PluginManager;

class PluginInstallCommand extends Command
{
    protected string $signature = 'plugin:install {name}';
    protected string $description = 'Install a plugin';

    public function handle(): int
    {
        $name = $this->argument(0);

        if (!$name) {
            $this->error('Plugin name is required.');
            return 1;
        }

        $this->info("Installing plugin: {$name}...");

        try {
            $pluginManager = app(PluginManager::class);
            $pluginManager->install($name);

            $this->success("Plugin '{$name}' installed successfully!");

            return 0;
        } catch (\Exception $e) {
            $this->error('Installation failed: ' . $e->getMessage());
            return 1;
        }
    }
}
