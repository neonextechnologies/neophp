<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;
use NeoPhp\Generator\Generator;

class MakeControllerCommand extends Command
{
    protected string $signature = 'make:controller {name} {--resource}';
    protected string $description = 'Create a new controller class';

    public function handle(): int
    {
        $name = $this->argument(0);
        
        if (!$name) {
            $this->error('Controller name is required.');
            return 1;
        }

        // Ensure name ends with Controller
        if (!str_ends_with($name, 'Controller')) {
            $name .= 'Controller';
        }

        $generator = new Generator();
        
        // Prepare replacements
        $className = $generator->studly($name);
        $namespace = 'App\\Controllers';
        
        $destination = __DIR__ . '/../../../app/Controllers/' . $className . '.php';

        try {
            $generator->generate('controller', $destination, [
                'namespace' => $namespace,
                'class' => $className,
            ]);

            $this->success("Controller created: {$className}");
            $this->comment("Location: app/Controllers/{$className}.php");
            
            return 0;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }
}
