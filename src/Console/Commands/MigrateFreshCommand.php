<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;
use NeoPhp\Database\Migrations\Migrator;

class MigrateFreshCommand extends Command
{
    protected string $signature = 'migrate:fresh';
    protected string $description = 'Drop all tables and re-run all migrations';

    public function handle(): int
    {
        if (!$this->confirm('Are you sure you want to drop all tables and re-run migrations?', false)) {
            $this->comment('Fresh migration cancelled.');
            return 0;
        }

        $this->warning('Dropping all tables and running migrations...');
        $this->newLine();

        try {
            $db = app('db');
            $migrationsPath = __DIR__ . '/../../../database/migrations';
            
            $migrator = new Migrator($db, $migrationsPath);
            
            $this->info('Dropping all tables...');
            $migrations = $migrator->fresh();

            foreach ($migrations as $migration) {
                $this->success("Migrated: {$migration}");
            }

            $this->newLine();
            $this->success('Fresh migration completed successfully!');

            return 0;
        } catch (\Exception $e) {
            $this->error('Fresh migration failed: ' . $e->getMessage());
            return 1;
        }
    }
}
