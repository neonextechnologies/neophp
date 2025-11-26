<?php

namespace NeoPhp\Generator;

/**
 * Base Generator Class
 * Handles file generation from stubs/templates
 */
abstract class Generator
{
    protected string $stubsPath;
    protected array $replacements = [];

    public function __construct()
    {
        $this->stubsPath = __DIR__ . '/stubs';
    }

    /**
     * Generate file from stub
     */
    public function generate(string $stubName, string $destination, array $replacements = []): bool
    {
        $this->replacements = $replacements;
        
        $stub = $this->getStub($stubName);
        $content = $this->replaceStubVariables($stub);
        
        return $this->writeFile($destination, $content);
    }

    /**
     * Get stub content
     */
    protected function getStub(string $name): string
    {
        $stubPath = $this->stubsPath . '/' . $name . '.stub';
        
        if (!file_exists($stubPath)) {
            throw new \RuntimeException("Stub file not found: {$stubPath}");
        }
        
        return file_get_contents($stubPath);
    }

    /**
     * Replace variables in stub
     */
    protected function replaceStubVariables(string $stub): string
    {
        foreach ($this->replacements as $key => $value) {
            $stub = str_replace('{{' . $key . '}}', $value, $stub);
        }
        
        return $stub;
    }

    /**
     * Write file to destination
     */
    protected function writeFile(string $destination, string $content): bool
    {
        $directory = dirname($destination);
        
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        if (file_exists($destination)) {
            throw new \RuntimeException("File already exists: {$destination}");
        }
        
        return file_put_contents($destination, $content) !== false;
    }

    /**
     * Get namespace from path
     */
    protected function getNamespaceFromPath(string $path): string
    {
        // Convert path to namespace
        // app/Controllers/UserController.php -> App\Controllers
        $path = str_replace(['/', '\\'], '\\', $path);
        $path = trim($path, '\\');
        
        // Remove file name
        $parts = explode('\\', $path);
        array_pop($parts);
        
        // Capitalize first letter of each part
        $parts = array_map('ucfirst', $parts);
        
        return implode('\\', $parts);
    }

    /**
     * Get class name from path
     */
    protected function getClassNameFromPath(string $path): string
    {
        $basename = basename($path, '.php');
        return $basename;
    }

    /**
     * Convert string to studly case
     */
    protected function studly(string $value): string
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));
        return str_replace(' ', '', $value);
    }

    /**
     * Convert string to camel case
     */
    protected function camel(string $value): string
    {
        return lcfirst($this->studly($value));
    }

    /**
     * Convert string to snake case
     */
    protected function snake(string $value): string
    {
        $value = preg_replace('/\s+/u', '', ucwords($value));
        return strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1_', $value));
    }

    /**
     * Convert string to kebab case
     */
    protected function kebab(string $value): string
    {
        return str_replace('_', '-', $this->snake($value));
    }

    /**
     * Pluralize word
     */
    protected function pluralize(string $word): string
    {
        if (str_ends_with($word, 'y')) {
            return substr($word, 0, -1) . 'ies';
        }
        
        if (str_ends_with($word, 's') || 
            str_ends_with($word, 'x') || 
            str_ends_with($word, 'z') ||
            str_ends_with($word, 'ch') ||
            str_ends_with($word, 'sh')) {
            return $word . 'es';
        }
        
        return $word . 's';
    }

    /**
     * Get current timestamp for migrations
     */
    protected function getMigrationTimestamp(): string
    {
        return date('Y_m_d_His');
    }
}
