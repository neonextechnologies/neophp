<?php

namespace NeoPhp\View;

class Blade
{
    protected $viewPath;
    protected $cachePath;
    protected $data = [];

    protected $directives = [];
    protected $compilers = [];

    public function __construct(string $viewPath, string $cachePath)
    {
        $this->viewPath = $viewPath;
        $this->cachePath = $cachePath;
        
        $this->registerDefaultDirectives();
    }

    public function render(string $view, array $data = []): string
    {
        $this->data = array_merge($this->data, $data);
        
        $viewFile = $this->findView($view);
        
        if (!$viewFile) {
            throw new \Exception("View [{$view}] not found.");
        }

        $compiled = $this->compile($viewFile);
        
        return $this->renderCompiled($compiled, $this->data);
    }

    protected function findView(string $view): ?string
    {
        $view = str_replace('.', '/', $view);
        $file = $this->viewPath . '/' . $view . '.blade.php';

        return file_exists($file) ? $file : null;
    }

    protected function compile(string $file): string
    {
        $cacheFile = $this->getCachePath($file);

        if (!$this->isExpired($file, $cacheFile)) {
            return $cacheFile;
        }

        $contents = file_get_contents($file);
        $compiled = $this->compileString($contents);

        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }

        file_put_contents($cacheFile, $compiled);

        return $cacheFile;
    }

    protected function compileString(string $value): string
    {
        // Compile @extends
        $value = $this->compileExtends($value);
        
        // Compile @section and @endsection
        $value = $this->compileSections($value);
        
        // Compile @yield
        $value = $this->compileYields($value);
        
        // Compile @if, @elseif, @else, @endif
        $value = $this->compileConditions($value);
        
        // Compile @foreach, @endforeach
        $value = $this->compileForeach($value);
        
        // Compile @for, @endfor
        $value = $this->compileFor($value);
        
        // Compile @while, @endwhile
        $value = $this->compileWhile($value);
        
        // Compile @include
        $value = $this->compileIncludes($value);
        
        // Compile {{ }} and {!! !!}
        $value = $this->compileEchos($value);
        
        // Compile custom directives
        foreach ($this->directives as $directive => $handler) {
            $value = $this->compileDirective($value, $directive, $handler);
        }

        return $value;
    }

    protected function compileExtends(string $value): string
    {
        return preg_replace(
            '/@extends\s*\(\s*[\'"](.+?)[\'"]\s*\)/',
            '<?php $this->layout(\'$1\'); ?>',
            $value
        );
    }

    protected function compileSections(string $value): string
    {
        $value = preg_replace(
            '/@section\s*\(\s*[\'"](.+?)[\'"]\s*\)/',
            '<?php $this->section(\'$1\'); ?>',
            $value
        );

        return preg_replace(
            '/@endsection/',
            '<?php $this->endSection(); ?>',
            $value
        );
    }

    protected function compileYields(string $value): string
    {
        return preg_replace(
            '/@yield\s*\(\s*[\'"](.+?)[\'"]\s*(?:,\s*[\'"](.+?)[\'"]\s*)?\)/',
            '<?php echo $this->yield(\'$1\', \'$2\'); ?>',
            $value
        );
    }

    protected function compileConditions(string $value): string
    {
        $value = preg_replace('/@if\s*\((.*?)\)/', '<?php if($1): ?>', $value);
        $value = preg_replace('/@elseif\s*\((.*?)\)/', '<?php elseif($1): ?>', $value);
        $value = preg_replace('/@else/', '<?php else: ?>', $value);
        $value = preg_replace('/@endif/', '<?php endif; ?>', $value);

        return $value;
    }

    protected function compileForeach(string $value): string
    {
        $value = preg_replace(
            '/@foreach\s*\((.*?)\)/',
            '<?php foreach($1): ?>',
            $value
        );

        return preg_replace('/@endforeach/', '<?php endforeach; ?>', $value);
    }

    protected function compileFor(string $value): string
    {
        $value = preg_replace('/@for\s*\((.*?)\)/', '<?php for($1): ?>', $value);
        return preg_replace('/@endfor/', '<?php endfor; ?>', $value);
    }

    protected function compileWhile(string $value): string
    {
        $value = preg_replace('/@while\s*\((.*?)\)/', '<?php while($1): ?>', $value);
        return preg_replace('/@endwhile/', '<?php endwhile; ?>', $value);
    }

    protected function compileIncludes(string $value): string
    {
        return preg_replace(
            '/@include\s*\(\s*[\'"](.+?)[\'"]\s*(?:,\s*(\[.*?\]))?\)/',
            '<?php echo $this->include(\'$1\', $2 ?? []); ?>',
            $value
        );
    }

    protected function compileEchos(string $value): string
    {
        // Compile {!! !!} (unescaped)
        $value = preg_replace('/\{\!!\s*(.+?)\s*\!\!\}/', '<?php echo $1; ?>', $value);
        
        // Compile {{ }} (escaped)
        $value = preg_replace('/\{\{\s*(.+?)\s*\}\}/', '<?php echo htmlspecialchars($1 ?? \'\', ENT_QUOTES, \'UTF-8\'); ?>', $value);

        return $value;
    }

    protected function compileDirective(string $value, string $directive, callable $handler): string
    {
        return preg_replace_callback(
            '/@' . $directive . '\s*\((.*?)\)/',
            function ($matches) use ($handler) {
                return $handler($matches[1] ?? '');
            },
            $value
        );
    }

    protected function renderCompiled(string $compiled, array $data): string
    {
        extract($data);
        
        ob_start();
        include $compiled;
        return ob_get_clean();
    }

    protected function getCachePath(string $file): string
    {
        return $this->cachePath . '/' . md5($file) . '.php';
    }

    protected function isExpired(string $file, string $cacheFile): bool
    {
        if (!file_exists($cacheFile)) {
            return true;
        }

        return filemtime($file) > filemtime($cacheFile);
    }

    public function directive(string $name, callable $handler): void
    {
        $this->directives[$name] = $handler;
    }

    protected function registerDefaultDirectives(): void
    {
        // @csrf
        $this->directive('csrf', function () {
            return '<?php echo \'<input type="hidden" name="_token" value="\' . csrf_token() . \'">\'; ?>';
        });

        // @method
        $this->directive('method', function ($method) {
            return '<?php echo \'<input type="hidden" name="_method" value="\' . ' . $method . ' . \'">\'; ?>';
        });

        // @auth
        $this->directive('auth', function () {
            return '<?php if(auth()->check()): ?>';
        });

        $this->directive('endauth', function () {
            return '<?php endif; ?>';
        });

        // @guest
        $this->directive('guest', function () {
            return '<?php if(auth()->guest()): ?>';
        });

        $this->directive('endguest', function () {
            return '<?php endif; ?>';
        });
    }

    public function share(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    protected function section(string $name): void
    {
        ob_start();
    }

    protected function endSection(): void
    {
        // This would need to be handled by the View class
    }

    protected function yield(string $name, string $default = ''): string
    {
        return $default;
    }

    protected function include(string $view, array $data = []): string
    {
        return $this->render($view, array_merge($this->data, $data));
    }
}
