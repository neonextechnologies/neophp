<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;

class SeedCommand extends Command
{
    protected string $signature = 'db:seed {--class=}';
    protected string $description = 'Seed the database with records';

    public function handle(): int
    {
        $this->info('Seeding database...');

        try {
            $class = $this->option('class') ?? 'DatabaseSeeder';
            
            $seederPath = __DIR__ . '/../../../database/seeders/' . $class . '.php';

            if (!file_exists($seederPath)) {
                $this->error("Seeder not found: {$class}");
                return 1;
            }

            require_once $seederPath;

            $seeder = new $class();
            $seeder->run();

            $this->success('Database seeded successfully!');

            return 0;
        } catch (\Exception $e) {
            $this->error('Seeding failed: ' . $e->getMessage());
            return 1;
        }
    }
}
