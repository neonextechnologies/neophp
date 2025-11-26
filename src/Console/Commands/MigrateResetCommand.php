<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;
use NeoPhp\Database\Migrations\Migrator;

class MigrateResetCommand extends Command
{
    protected string $signature = 'migrate:reset';
    protected string $description = 'Rollback all database migrations';

    public function handle(): int
    {
        if (!$this->confirm('Are you sure you want to reset all migrations?', false)) {
            $this->comment('Reset cancelled.');
            return 0;
        }

        $this->warning('Resetting all migrations...');
        $this->newLine();

        try {
            $db = app('db');
            $migrationsPath = __DIR__ . '/../../../database/migrations';
            
            $migrator = new Migrator($db, $migrationsPath);
            
            $migrations = $migrator->reset();

            if (empty($migrations)) {
                $this->comment('Nothing to reset.');
                return 0;
            }

            foreach ($migrations as $migration) {
                $this->success("Rolled back: {$migration}");
            }

            $this->newLine();
            $this->success('Reset completed successfully!');

            return 0;
        } catch (\Exception $e) {
            $this->error('Reset failed: ' . $e->getMessage());
            return 1;
        }
    }
}
