<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;
use NeoPhp\Generator\Generator;

class MakeModelCommand extends Command
{
    protected string $signature = 'make:model {name} {--migration} {--m}';
    protected string $description = 'Create a new model class';

    public function handle(): int
    {
        $name = $this->argument(0);
        
        if (!$name) {
            $this->error('Model name is required.');
            return 1;
        }

        $generator = new Generator();
        
        // Prepare replacements
        $className = $generator->studly($name);
        $tableName = $generator->pluralize($generator->snake($className));
        $namespace = 'App\\Models';
        
        $destination = __DIR__ . '/../../../app/Models/' . $className . '.php';

        try {
            $generator->generate('model', $destination, [
                'namespace' => $namespace,
                'class' => $className,
                'table' => $tableName,
            ]);

            $this->success("Model created: {$className}");
            $this->comment("Location: app/Models/{$className}.php");
            
            // Create migration if requested
            if ($this->option('migration') || $this->option('m')) {
                $this->line('');
                $this->call('make:migration', ["create_{$tableName}_table"]);
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }
}
