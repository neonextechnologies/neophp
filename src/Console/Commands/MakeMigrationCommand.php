<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;
use NeoPhp\Generator\Generator;

class MakeMigrationCommand extends Command
{
    protected string $signature = 'make:migration {name}';
    protected string $description = 'Create a new migration file';

    public function handle(): int
    {
        $name = $this->argument(0);
        
        if (!$name) {
            $this->error('Migration name is required.');
            return 1;
        }

        $generator = new Generator();
        
        // Parse migration name to extract table name
        $tableName = $this->extractTableName($name);
        
        // Create migration file name with timestamp
        $timestamp = $generator->getMigrationTimestamp();
        $fileName = $timestamp . '_' . $name . '.php';
        
        $destination = __DIR__ . '/../../../database/migrations/' . $fileName;

        try {
            $generator->generate('migration', $destination, [
                'table' => $tableName,
            ]);

            $this->success("Migration created: {$fileName}");
            $this->comment("Location: database/migrations/{$fileName}");
            
            return 0;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }

    /**
     * Extract table name from migration name
     */
    protected function extractTableName(string $name): string
    {
        // create_users_table -> users
        // add_email_to_users_table -> users
        
        if (preg_match('/create_(.+)_table/', $name, $matches)) {
            return $matches[1];
        }
        
        if (preg_match('/_to_(.+)_table/', $name, $matches)) {
            return $matches[1];
        }
        
        if (preg_match('/_from_(.+)_table/', $name, $matches)) {
            return $matches[1];
        }
        
        // Default: use the name as-is
        return strtolower(str_replace('_', '', $name));
    }
}
