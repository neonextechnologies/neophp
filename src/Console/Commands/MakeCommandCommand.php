<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;
use NeoPhp\Generator\Generator;

class MakeCommandCommand extends Command
{
    protected string $signature = 'make:command {name}';
    protected string $description = 'Create a new console command';

    public function handle(): int
    {
        $name = $this->argument(0);
        
        if (!$name) {
            $this->error('Command name is required.');
            return 1;
        }

        // Ensure name ends with Command
        if (!str_ends_with($name, 'Command')) {
            $name .= 'Command';
        }

        $generator = new Generator();
        
        // Ask for command signature
        $commandName = $this->ask('Command signature (e.g., app:process)', $generator->kebab(str_replace('Command', '', $name)));
        $description = $this->ask('Command description', 'Execute a custom command');
        
        // Prepare replacements
        $className = $generator->studly($name);
        $namespace = 'App\\Console\\Commands';
        
        $destination = __DIR__ . '/../../../app/Console/Commands/' . $className . '.php';

        try {
            $generator->generate('command', $destination, [
                'namespace' => $namespace,
                'class' => $className,
                'command' => $commandName,
                'description' => $description,
            ]);

            $this->success("Command created: {$className}");
            $this->comment("Location: app/Console/Commands/{$className}.php");
            $this->newLine();
            $this->info("Run the command with: php neo {$commandName}");
            
            return 0;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }
}
