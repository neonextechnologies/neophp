<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;
use NeoPhp\Generator\Generator;

class MakeProviderCommand extends Command
{
    protected string $signature = 'make:provider {name}';
    protected string $description = 'Create a new service provider class';

    public function handle(): int
    {
        $name = $this->argument(0);
        
        if (!$name) {
            $this->error('Provider name is required.');
            return 1;
        }

        // Ensure name ends with ServiceProvider
        if (!str_ends_with($name, 'ServiceProvider')) {
            $name .= 'ServiceProvider';
        }

        $generator = new Generator();
        
        // Prepare replacements
        $className = $generator->studly($name);
        $serviceName = $generator->snake(str_replace('ServiceProvider', '', $className));
        $namespace = 'App\\Providers';
        
        $destination = __DIR__ . '/../../../app/Providers/' . $className . '.php';

        try {
            $generator->generate('provider', $destination, [
                'namespace' => $namespace,
                'class' => $className,
                'service' => $serviceName,
                'serviceClass' => 'App\\Services\\' . $generator->studly($serviceName),
            ]);

            $this->success("Service provider created: {$className}");
            $this->comment("Location: app/Providers/{$className}.php");
            $this->newLine();
            $this->info("Don't forget to register the provider in config/app.php");
            
            return 0;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }
}
