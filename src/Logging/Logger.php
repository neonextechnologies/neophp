<?php

namespace NeoPhp\Logging;

class Logger
{
    protected $logPath;
    protected $channel;

    public function __construct(string $logPath = 'storage/logs', string $channel = 'app')
    {
        $this->logPath = $logPath;
        $this->channel = $channel;

        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    public function emergency($message, array $context = []): void
    {
        $this->log('EMERGENCY', $message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->log('ALERT', $message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->log('CRITICAL', $message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->log('NOTICE', $message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    public function debug($message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context);
    }

    protected function log(string $level, $message, array $context = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextString = !empty($context) ? ' ' . json_encode($context) : '';
        
        $logMessage = "[{$timestamp}] {$this->channel}.{$level}: {$message}{$contextString}\n";
        
        $filename = $this->logPath . '/' . date('Y-m-d') . '.log';
        
        file_put_contents($filename, $logMessage, FILE_APPEND);
    }

    public function channel(string $channel): self
    {
        $logger = clone $this;
        $logger->channel = $channel;
        return $logger;
    }
}
