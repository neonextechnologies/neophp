<?php

namespace NeoPhp\Console;

/**
 * Console Input Handler
 */
class Input
{
    protected array $arguments = [];
    protected array $options = [];
    protected array $argv;

    public function __construct(array $argv)
    {
        $this->argv = $argv;
        $this->parse();
    }

    /**
     * Parse command line arguments
     */
    protected function parse(): void
    {
        $args = array_slice($this->argv, 2); // Skip script name and command name

        foreach ($args as $arg) {
            if (str_starts_with($arg, '--')) {
                // Long option: --option=value or --option
                $this->parseLongOption($arg);
            } elseif (str_starts_with($arg, '-')) {
                // Short option: -o value or -o
                $this->parseShortOption($arg);
            } else {
                // Argument
                $this->arguments[] = $arg;
            }
        }
    }

    /**
     * Parse long option
     */
    protected function parseLongOption(string $arg): void
    {
        $arg = substr($arg, 2); // Remove --

        if (str_contains($arg, '=')) {
            [$key, $value] = explode('=', $arg, 2);
            $this->options[$key] = $value;
        } else {
            $this->options[$arg] = true;
        }
    }

    /**
     * Parse short option
     */
    protected function parseShortOption(string $arg): void
    {
        $arg = substr($arg, 1); // Remove -
        $this->options[$arg] = true;
    }

    /**
     * Get argument by index
     */
    public function getArgument(int|string $index): mixed
    {
        if (is_int($index)) {
            return $this->arguments[$index] ?? null;
        }

        // Named argument parsing would need signature definition
        return null;
    }

    /**
     * Get all arguments
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Get option value
     */
    public function getOption(string $name): mixed
    {
        return $this->options[$name] ?? null;
    }

    /**
     * Get all options
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Check if option exists
     */
    public function hasOption(string $name): bool
    {
        return isset($this->options[$name]);
    }

    /**
     * Check if argument exists
     */
    public function hasArgument(int $index): bool
    {
        return isset($this->arguments[$index]);
    }
}
