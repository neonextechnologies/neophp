<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;
use NeoPhp\Generator\Generator;

class MakeMiddlewareCommand extends Command
{
    protected string $signature = 'make:middleware {name}';
    protected string $description = 'Create a new middleware class';

    public function handle(): int
    {
        $name = $this->argument(0);
        
        if (!$name) {
            $this->error('Middleware name is required.');
            return 1;
        }

        $generator = new Generator();
        
        // Prepare replacements
        $className = $generator->studly($name);
        $namespace = 'App\\Middleware';
        
        $destination = __DIR__ . '/../../../app/Middleware/' . $className . '.php';

        try {
            $generator->generate('middleware', $destination, [
                'namespace' => $namespace,
                'class' => $className,
            ]);

            $this->success("Middleware created: {$className}");
            $this->comment("Location: app/Middleware/{$className}.php");
            
            return 0;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }
}
