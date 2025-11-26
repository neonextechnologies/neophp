<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;
use NeoPhp\Database\Migrations\Migrator;

class MigrateRefreshCommand extends Command
{
    protected string $signature = 'migrate:refresh';
    protected string $description = 'Reset and re-run all migrations';

    public function handle(): int
    {
        if (!$this->confirm('Are you sure you want to refresh all migrations?', false)) {
            $this->comment('Refresh cancelled.');
            return 0;
        }

        $this->warning('Refreshing migrations...');
        $this->newLine();

        try {
            $db = app('db');
            $migrationsPath = __DIR__ . '/../../../database/migrations';
            
            $migrator = new Migrator($db, $migrationsPath);
            
            // Reset
            $this->info('Rolling back migrations...');
            $rolled = $migrator->reset();
            
            foreach ($rolled as $migration) {
                $this->comment("Rolled back: {$migration}");
            }

            $this->newLine();

            // Re-run
            $this->info('Running migrations...');
            $executed = $migrator->run();
            
            foreach ($executed as $migration) {
                $this->success("Migrated: {$migration}");
            }

            $this->newLine();
            $this->success('Refresh completed successfully!');

            return 0;
        } catch (\Exception $e) {
            $this->error('Refresh failed: ' . $e->getMessage());
            return 1;
        }
    }
}
