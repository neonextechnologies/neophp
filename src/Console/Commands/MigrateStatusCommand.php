<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;
use NeoPhp\Database\Migrations\Migrator;

class MigrateStatusCommand extends Command
{
    protected string $signature = 'migrate:status';
    protected string $description = 'Show the status of each migration';

    public function handle(): int
    {
        try {
            $db = app('db');
            $migrationsPath = __DIR__ . '/../../../database/migrations';
            
            $migrator = new Migrator($db, $migrationsPath);
            
            $status = $migrator->status();

            if (empty($status)) {
                $this->comment('No migrations found.');
                return 0;
            }

            $this->info('Migration Status:');
            $this->newLine();

            $headers = ['Ran?', 'Migration', 'Batch'];
            $rows = [];

            foreach ($status as $item) {
                $rows[] = [
                    $item['executed'] ? 'Yes' : 'No',
                    $item['migration'],
                    $item['batch'] ?? '-',
                ];
            }

            $this->table($headers, $rows);

            return 0;
        } catch (\Exception $e) {
            $this->error('Status check failed: ' . $e->getMessage());
            return 1;
        }
    }
}
