<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;

class ServeCommand extends Command
{
    protected string $signature = 'serve {--host=localhost} {--port=8000}';
    protected string $description = 'Start the NeoPhp development server';

    public function handle(): int
    {
        $host = $this->option('host') ?? 'localhost';
        $port = $this->option('port') ?? '8000';

        $this->info("NeoPhp development server started on http://{$host}:{$port}");
        $this->comment('Press Ctrl+C to stop the server');
        $this->newLine();

        $publicPath = __DIR__ . '/../../../public';

        if (!is_dir($publicPath)) {
            $this->error('Public directory not found: ' . $publicPath);
            return 1;
        }

        $command = "php -S {$host}:{$port} -t {$publicPath}";

        passthru($command);

        return 0;
    }
}
