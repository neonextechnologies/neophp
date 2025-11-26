<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;
use NeoPhp\Plugin\PluginManager;

class PluginListCommand extends Command
{
    protected string $signature = 'plugin:list';
    protected string $description = 'List all installed plugins';

    public function handle(): int
    {
        try {
            $pluginManager = app(PluginManager::class);
            $plugins = $pluginManager->getAllPlugins();

            if (empty($plugins)) {
                $this->comment('No plugins installed.');
                return 0;
            }

            $this->info('Installed Plugins:');
            $this->newLine();

            $headers = ['Name', 'Version', 'Status', 'Description'];
            $rows = [];

            foreach ($plugins as $name => $plugin) {
                $rows[] = [
                    $name,
                    $plugin->getVersion(),
                    $plugin->isActive() ? 'Active' : 'Inactive',
                    $plugin->getDescription() ?? '-',
                ];
            }

            $this->table($headers, $rows);

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to list plugins: ' . $e->getMessage());
            return 1;
        }
    }
}
