<?php

namespace NeoPhp\Console\Commands;

use NeoPhp\Console\Command;

class CacheClearCommand extends Command
{
    protected string $signature = 'cache:clear';
    protected string $description = 'Clear the application cache';

    public function handle(): int
    {
        $this->info('Clearing application cache...');

        try {
            $cache = app('cache');
            $cache->flush();

            $this->success('Application cache cleared!');

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to clear cache: ' . $e->getMessage());
            return 1;
        }
    }
}
