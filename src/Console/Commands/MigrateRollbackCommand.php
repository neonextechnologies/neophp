<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;
use NeoPhp\Database\Migrations\Migrator;

class MigrateRollbackCommand extends Command
{
    protected string $signature = 'migrate:rollback {--step=1}';
    protected string $description = 'Rollback the last database migration';

    public function handle(): int
    {
        $steps = (int) ($this->option('step') ?? 1);
        
        $this->warning("Rolling back {$steps} batch(es)...");
        $this->newLine();

        try {
            $db = app('db');
            $migrationsPath = __DIR__ . '/../../../database/migrations';
            
            $migrator = new Migrator($db, $migrationsPath);
            
            $migrations = $migrator->rollback($steps);

            if (empty($migrations)) {
                $this->comment('Nothing to rollback.');
                return 0;
            }

            foreach ($migrations as $migration) {
                $this->success("Rolled back: {$migration}");
            }

            $this->newLine();
            $this->success('Rollback completed successfully!');

            return 0;
        } catch (\Exception $e) {
            $this->error('Rollback failed: ' . $e->getMessage());
            return 1;
        }
    }
}
