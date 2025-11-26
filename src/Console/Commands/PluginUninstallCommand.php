<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;
use NeoPhp\Plugin\PluginManager;

class PluginUninstallCommand extends Command
{
    protected string $signature = 'plugin:uninstall {name}';
    protected string $description = 'Uninstall a plugin';

    public function handle(): int
    {
        $name = $this->argument(0);

        if (!$name) {
            $this->error('Plugin name is required.');
            return 1;
        }

        if (!$this->confirm("Are you sure you want to uninstall plugin '{$name}'?", false)) {
            $this->comment('Uninstall cancelled.');
            return 0;
        }

        $this->warning("Uninstalling plugin: {$name}...");

        try {
            $pluginManager = app(PluginManager::class);
            $pluginManager->uninstall($name);

            $this->success("Plugin '{$name}' uninstalled successfully!");

            return 0;
        } catch (\Exception $e) {
            $this->error('Uninstallation failed: ' . $e->getMessage());
            return 1;
        }
    }
}
