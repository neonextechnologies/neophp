<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;
use NeoPhp\Generator\Generator;

class MakePluginCommand extends Command
{
    protected string $signature = 'make:plugin {name}';
    protected string $description = 'Create a new plugin';

    public function handle(): int
    {
        $name = $this->argument(0);
        
        if (!$name) {
            $this->error('Plugin name is required.');
            return 1;
        }

        $generator = new Generator();
        
        // Ask for additional info
        $description = $this->ask('Plugin description', 'A custom plugin for NeoPhp');
        $author = $this->ask('Plugin author', 'Your Name');
        
        // Prepare replacements
        $className = $generator->studly($name) . 'Plugin';
        $pluginName = $generator->kebab($name);
        $namespace = 'Plugins\\' . $generator->studly($name);
        
        $destination = __DIR__ . '/../../../plugins/' . $pluginName . '/' . $className . '.php';

        try {
            $generator->generate('plugin', $destination, [
                'namespace' => $namespace,
                'class' => $className,
                'plugin' => $pluginName,
                'description' => $description,
                'author' => $author,
            ]);

            $this->success("Plugin created: {$pluginName}");
            $this->comment("Location: plugins/{$pluginName}/{$className}.php");
            $this->newLine();
            $this->info("Install the plugin with: php neo plugin:install {$pluginName}");
            
            return 0;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }
}
