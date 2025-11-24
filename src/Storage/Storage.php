<?php

namespace NeoPhp\Storage;

class Storage
{
    protected $basePath;

    public function __construct(string $basePath = 'storage/app')
    {
        $this->basePath = rtrim($basePath, '/');
        
        if (!is_dir($this->basePath)) {
            mkdir($this->basePath, 0755, true);
        }
    }

    public function put(string $path, $contents): bool
    {
        $fullPath = $this->getFullPath($path);
        $directory = dirname($fullPath);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return file_put_contents($fullPath, $contents) !== false;
    }

    public function get(string $path)
    {
        $fullPath = $this->getFullPath($path);

        if (!file_exists($fullPath)) {
            return null;
        }

        return file_get_contents($fullPath);
    }

    public function exists(string $path): bool
    {
        return file_exists($this->getFullPath($path));
    }

    public function delete(string $path): bool
    {
        $fullPath = $this->getFullPath($path);

        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }

    public function move(string $from, string $to): bool
    {
        $fromPath = $this->getFullPath($from);
        $toPath = $this->getFullPath($to);

        $directory = dirname($toPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return rename($fromPath, $toPath);
    }

    public function copy(string $from, string $to): bool
    {
        $fromPath = $this->getFullPath($from);
        $toPath = $this->getFullPath($to);

        $directory = dirname($toPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return copy($fromPath, $toPath);
    }

    public function size(string $path): int
    {
        $fullPath = $this->getFullPath($path);

        if (file_exists($fullPath)) {
            return filesize($fullPath);
        }

        return 0;
    }

    public function putFile(string $path, $file): string
    {
        if (is_array($file) && isset($file['tmp_name'])) {
            // From $_FILES
            $filename = basename($file['name']);
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $newName = uniqid() . '.' . $extension;
            $destination = $path . '/' . $newName;

            if (move_uploaded_file($file['tmp_name'], $this->getFullPath($destination))) {
                return $destination;
            }
        }

        return '';
    }

    public function url(string $path): string
    {
        return '/storage/' . ltrim($path, '/');
    }

    protected function getFullPath(string $path): string
    {
        return $this->basePath . '/' . ltrim($path, '/');
    }
}
