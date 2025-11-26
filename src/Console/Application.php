<?php

namespace NeoPhp\Console;

use NeoPhp\DI\Container;

/**
 * Console Application
 * Main CLI application handler
 */
class Application
{
    protected Container $container;
    protected array $commands = [];
    protected Input $input;
    protected Output $output;
    protected string $name = 'NeoPhp CLI';
    protected string $version = '2.0.0';

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Run the console application
     */
    public function run(array $argv): int
    {
        $this->input = new Input($argv);
        $this->output = new Output();

        // Get command name
        $commandName = $argv[1] ?? null;

        if (!$commandName || $commandName === 'list') {
            $this->listCommands();
            return 0;
        }

        if ($commandName === '--version' || $commandName === '-V') {
            $this->showVersion();
            return 0;
        }

        if ($commandName === '--help' || $commandName === '-h') {
            $this->listCommands();
            return 0;
        }

        // Find and execute command
        if (!isset($this->commands[$commandName])) {
            $this->output->error("Command '{$commandName}' not found.");
            $this->output->line("Run 'php neo list' to see available commands.");
            return 1;
        }

        try {
            $commandClass = $this->commands[$commandName];
            $command = new $commandClass($this->input, $this->output);
            
            return $command->handle();
        } catch (\Exception $e) {
            $this->output->error($e->getMessage());
            
            if ($this->input->hasOption('v') || $this->input->hasOption('verbose')) {
                $this->output->newLine();
                $this->output->line($e->getTraceAsString());
            }
            
            return 1;
        }
    }

    /**
     * Register a command
     */
    public function register(string $name, string $commandClass): void
    {
        $this->commands[$name] = $commandClass;
    }

    /**
     * Register multiple commands
     */
    public function registerCommands(array $commands): void
    {
        foreach ($commands as $name => $commandClass) {
            $this->register($name, $commandClass);
        }
    }

    /**
     * Auto-discover commands from directory
     */
    public function discover(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $files = glob($directory . '/*Command.php');

        foreach ($files as $file) {
            $className = $this->getClassNameFromFile($file);
            
            if ($className && class_exists($className) && is_subclass_of($className, Command::class)) {
                $command = new $className($this->input, $this->output);
                $signature = $command->getSignature();
                
                if ($signature) {
                    // Extract command name from signature
                    $name = explode(' ', $signature)[0];
                    $this->register($name, $className);
                }
            }
        }
    }

    /**
     * Call another command
     */
    public function call(string $commandName, array $arguments = []): int
    {
        if (!isset($this->commands[$commandName])) {
            return 1;
        }

        $commandClass = $this->commands[$commandName];
        $command = new $commandClass($this->input, $this->output);
        
        return $command->handle();
    }

    /**
     * List all registered commands
     */
    protected function listCommands(): void
    {
        $this->output->line($this->name . ' ' . $this->version);
        $this->output->newLine();
        $this->output->line(Output::COLOR_YELLOW . 'Usage:' . Output::COLOR_RESET);
        $this->output->line('  php neo <command> [options] [arguments]');
        $this->output->newLine();
        $this->output->line(Output::COLOR_YELLOW . 'Available commands:' . Output::COLOR_RESET);

        $groups = $this->groupCommands();

        foreach ($groups as $group => $commands) {
            if ($group !== '') {
                $this->output->line(' ' . Output::COLOR_GREEN . $group . Output::COLOR_RESET);
            }

            foreach ($commands as $name => $commandClass) {
                $command = new $commandClass($this->input, $this->output);
                $description = $command->getDescription();
                
                $this->output->line(sprintf(
                    '  %s%-30s%s %s',
                    Output::COLOR_CYAN,
                    $name,
                    Output::COLOR_RESET,
                    $description
                ));
            }

            $this->output->newLine();
        }
    }

    /**
     * Group commands by namespace
     */
    protected function groupCommands(): array
    {
        $groups = [];

        foreach ($this->commands as $name => $commandClass) {
            $parts = explode(':', $name);
            $group = count($parts) > 1 ? $parts[0] : '';
            
            if (!isset($groups[$group])) {
                $groups[$group] = [];
            }

            $groups[$group][$name] = $commandClass;
        }

        ksort($groups);
        return $groups;
    }

    /**
     * Show version
     */
    protected function showVersion(): void
    {
        $this->output->line($this->name . ' ' . Output::COLOR_GREEN . $this->version . Output::COLOR_RESET);
    }

    /**
     * Get class name from file
     */
    protected function getClassNameFromFile(string $file): ?string
    {
        $content = file_get_contents($file);

        if (preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatch) &&
            preg_match('/class\s+(\w+)/', $content, $classMatch)) {
            return $namespaceMatch[1] . '\\' . $classMatch[1];
        }

        return null;
    }

    /**
     * Set application name
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set application version
     */
    public function setVersion(string $version): self
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Get all registered commands
     */
    public function getCommands(): array
    {
        return $this->commands;
    }
}
