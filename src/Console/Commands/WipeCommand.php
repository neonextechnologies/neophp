<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;

class WipeCommand extends Command
{
    protected string $signature = 'db:wipe';
    protected string $description = 'Drop all tables from the database';

    public function handle(): int
    {
        if (!$this->confirm('Are you sure you want to drop all tables?', false)) {
            $this->comment('Wipe cancelled.');
            return 0;
        }

        $this->warning('Dropping all tables...');

        try {
            $db = app('db');

            // Disable foreign key checks
            $db->query('SET FOREIGN_KEY_CHECKS = 0');

            // Get all tables
            $tables = $db->query('SHOW TABLES');
            
            if (empty($tables)) {
                $this->comment('No tables to drop.');
                return 0;
            }

            foreach ($tables as $table) {
                $tableName = array_values($table)[0];
                $db->query("DROP TABLE IF EXISTS `{$tableName}`");
                $this->success("Dropped: {$tableName}");
            }

            // Re-enable foreign key checks
            $db->query('SET FOREIGN_KEY_CHECKS = 1');

            $this->newLine();
            $this->success('All tables dropped successfully!');

            return 0;
        } catch (\Exception $e) {
            $this->error('Wipe failed: ' . $e->getMessage());
            return 1;
        }
    }
}
