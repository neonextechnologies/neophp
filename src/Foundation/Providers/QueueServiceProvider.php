<?php

namespace NeoPhp\Foundation\Providers;

use NeoPhp\Foundation\ServiceProvider;
use NeoPhp\Contracts\QueueInterface;
use NeoPhp\Queue\Queue;

/**
 * Queue Service Provider
 */
class QueueServiceProvider extends ServiceProvider
{
    protected array $provides = [
        QueueInterface::class,
        'queue',
        Queue::class
    ];

    protected bool $defer = true;

    public function register(): void
    {
        $this->singleton(QueueInterface::class, function ($app) {
            $config = require $app->basePath('config/queue.php');
            return new Queue($config);
        });

        $this->alias(QueueInterface::class, 'queue');
        $this->alias(QueueInterface::class, Queue::class);
    }
}
