<?php

namespace NeoPhp\View;

use Exception;

class View
{
    protected $viewPath;
    protected $data = [];
    protected $layout = null;
    protected $sections = [];
    protected $currentSection = null;

    public function __construct(string $viewPath)
    {
        $this->viewPath = $viewPath;
    }

    public function render(string $view, array $data = []): string
    {
        $this->data = array_merge($this->data, $data);
        
        $viewFile = $this->findView($view);
        
        if (!$viewFile) {
            throw new Exception("View [{$view}] not found.");
        }

        $content = $this->renderView($viewFile, $this->data);

        if ($this->layout) {
            $layoutFile = $this->findView($this->layout);
            
            if (!$layoutFile) {
                throw new Exception("Layout [{$this->layout}] not found.");
            }

            $this->data['content'] = $content;
            $content = $this->renderView($layoutFile, $this->data);
        }

        return $content;
    }

    protected function renderView(string $file, array $data): string
    {
        extract($data);
        
        ob_start();
        include $file;
        return ob_get_clean();
    }

    protected function findView(string $view): ?string
    {
        $view = str_replace('.', '/', $view);
        $file = $this->viewPath . '/' . $view . '.php';

        return file_exists($file) ? $file : null;
    }

    public function share(string $key, $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    public function layout(string $layout): void
    {
        $this->layout = $layout;
    }

    public function section(string $name): void
    {
        $this->currentSection = $name;
        ob_start();
    }

    public function endSection(): void
    {
        if ($this->currentSection) {
            $this->sections[$this->currentSection] = ob_get_clean();
            $this->currentSection = null;
        }
    }

    public function yield(string $section, string $default = ''): string
    {
        return $this->sections[$section] ?? $default;
    }

    public function include(string $view, array $data = []): void
    {
        echo $this->render($view, array_merge($this->data, $data));
    }

    public function escape($value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }

    public function e($value): string
    {
        return $this->escape($value);
    }
}
