<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;
use NeoPhp\Database\Migrations\Migrator;

class MigrateCommand extends Command
{
    protected string $signature = 'migrate {--force} {--pretend}';
    protected string $description = 'Run database migrations';

    public function handle(): int
    {
        $this->info('Running migrations...');
        $this->newLine();

        try {
            $db = app('db');
            $migrationsPath = __DIR__ . '/../../../database/migrations';
            
            $migrator = new Migrator($db, $migrationsPath);
            
            $migrations = $migrator->run();

            if (empty($migrations)) {
                $this->comment('Nothing to migrate.');
                return 0;
            }

            foreach ($migrations as $migration) {
                $this->success("Migrated: {$migration}");
            }

            $this->newLine();
            $this->success('Migration completed successfully!');

            return 0;
        } catch (\Exception $e) {
            $this->error('Migration failed: ' . $e->getMessage());
            
            if ($this->option('v') || $this->option('verbose')) {
                $this->newLine();
                $this->line($e->getTraceAsString());
            }
            
            return 1;
        }
    }
}
