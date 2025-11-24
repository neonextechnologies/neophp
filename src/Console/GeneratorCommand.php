<?php

namespace NeoPhp\Console;

class GeneratorCommand
{
    protected $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    public function generateModule(string $name): void
    {
        $name = $this->sanitizeName($name);
        $modulePath = $this->basePath . "/app/Modules/{$name}";

        if (is_dir($modulePath)) {
            throw new \Exception("Module '{$name}' already exists!");
        }

        // Create module structure
        mkdir($modulePath, 0755, true);
        mkdir($modulePath . '/Controllers', 0755, true);
        mkdir($modulePath . '/Services', 0755, true);
        mkdir($modulePath . '/Repositories', 0755, true);

        // Generate module class
        $moduleContent = $this->getModuleTemplate($name);
        file_put_contents($modulePath . "/{$name}Module.php", $moduleContent);

        // Generate controller
        $controllerContent = $this->getControllerTemplate($name);
        file_put_contents($modulePath . "/Controllers/{$name}Controller.php", $controllerContent);

        // Generate service
        $serviceContent = $this->getServiceTemplate($name);
        file_put_contents($modulePath . "/Services/{$name}Service.php", $serviceContent);

        echo "✓ Module '{$name}' generated successfully!\n";
        echo "  - {$modulePath}/{$name}Module.php\n";
        echo "  - {$modulePath}/Controllers/{$name}Controller.php\n";
        echo "  - {$modulePath}/Services/{$name}Service.php\n";
    }

    public function generateController(string $name): void
    {
        $name = $this->sanitizeName($name);
        $controllerPath = $this->basePath . "/app/Controllers";

        if (!is_dir($controllerPath)) {
            mkdir($controllerPath, 0755, true);
        }

        $filePath = $controllerPath . "/{$name}.php";

        if (file_exists($filePath)) {
            throw new \Exception("Controller '{$name}' already exists!");
        }

        $content = $this->getStandaloneControllerTemplate($name);
        file_put_contents($filePath, $content);

        echo "✓ Controller '{$name}' generated successfully!\n";
        echo "  - {$filePath}\n";
    }

    public function generateService(string $name): void
    {
        $name = $this->sanitizeName($name);
        $servicePath = $this->basePath . "/app/Services";

        if (!is_dir($servicePath)) {
            mkdir($servicePath, 0755, true);
        }

        $filePath = $servicePath . "/{$name}.php";

        if (file_exists($filePath)) {
            throw new \Exception("Service '{$name}' already exists!");
        }

        $content = $this->getStandaloneServiceTemplate($name);
        file_put_contents($filePath, $content);

        echo "✓ Service '{$name}' generated successfully!\n";
        echo "  - {$filePath}\n";
    }

    public function generateRepository(string $name): void
    {
        $name = $this->sanitizeName($name);
        $repoPath = $this->basePath . "/app/Repositories";

        if (!is_dir($repoPath)) {
            mkdir($repoPath, 0755, true);
        }

        $filePath = $repoPath . "/{$name}.php";

        if (file_exists($filePath)) {
            throw new \Exception("Repository '{$name}' already exists!");
        }

        $content = $this->getRepositoryTemplate($name);
        file_put_contents($filePath, $content);

        echo "✓ Repository '{$name}' generated successfully!\n";
        echo "  - {$filePath}\n";
    }

    protected function sanitizeName(string $name): string
    {
        return str_replace(['/', '\\', '.'], '', $name);
    }

    protected function getModuleTemplate(string $name): string
    {
        return "<?php

namespace App\\Modules\\{$name};

use NeoPhp\\Core\\Attributes\\Module;
use App\\Modules\\{$name}\\Controllers\\{$name}Controller;
use App\\Modules\\{$name}\\Services\\{$name}Service;

#[Module(
    controllers: [{$name}Controller::class],
    providers: [{$name}Service::class]
)]
class {$name}Module
{
    //
}
";
    }

    protected function getControllerTemplate(string $name): string
    {
        $lowerName = strtolower($name);
        return "<?php

namespace App\\Modules\\{$name}\\Controllers;

use NeoPhp\\Core\\Attributes\\Controller;
use NeoPhp\\Core\\Attributes\\Get;
use NeoPhp\\Core\\Attributes\\Post;
use NeoPhp\\Http\\Request;
use NeoPhp\\Http\\Response;
use App\\Modules\\{$name}\\Services\\{$name}Service;

#[Controller(prefix: '/{$lowerName}')]
class {$name}Controller
{
    public function __construct(
        protected {$name}Service \$service
    ) {
    }

    #[Get('/')]
    public function index(Request \$request): Response
    {
        \$data = \$this->service->findAll();
        
        return response()->json([
            'data' => \$data
        ]);
    }

    #[Get('/{id}')]
    public function show(Request \$request, string \$id): Response
    {
        \$item = \$this->service->findById((int) \$id);
        
        if (!\$item) {
            return response()->json(['error' => 'Not found'], 404);
        }
        
        return response()->json([
            'data' => \$item
        ]);
    }

    #[Post('/')]
    public function create(Request \$request): Response
    {
        \$data = \$request->all();
        \$id = \$this->service->create(\$data);
        
        return response()->json([
            'id' => \$id,
            'message' => 'Created successfully'
        ], 201);
    }
}
";
    }

    protected function getServiceTemplate(string $name): string
    {
        return "<?php

namespace App\\Modules\\{$name}\\Services;

use NeoPhp\\Core\\Attributes\\Injectable;

#[Injectable]
class {$name}Service
{
    public function findAll(): array
    {
        // TODO: Implement findAll logic
        return [];
    }

    public function findById(int \$id): ?array
    {
        // TODO: Implement findById logic
        return null;
    }

    public function create(array \$data): int
    {
        // TODO: Implement create logic
        return 0;
    }

    public function update(int \$id, array \$data): bool
    {
        // TODO: Implement update logic
        return false;
    }

    public function delete(int \$id): bool
    {
        // TODO: Implement delete logic
        return false;
    }
}
";
    }

    protected function getStandaloneControllerTemplate(string $name): string
    {
        return "<?php

namespace App\\Controllers;

use NeoPhp\\Core\\Attributes\\Controller;
use NeoPhp\\Core\\Attributes\\Get;
use NeoPhp\\Core\\Attributes\\Post;
use NeoPhp\\Http\\Request;
use NeoPhp\\Http\\Response;

#[Controller(prefix: '/" . strtolower($name) . "')]
class {$name}
{
    #[Get('/')]
    public function index(Request \$request): Response
    {
        return response()->json([
            'message' => 'Hello from {$name}'
        ]);
    }
}
";
    }

    protected function getStandaloneServiceTemplate(string $name): string
    {
        return "<?php

namespace App\\Services;

use NeoPhp\\Core\\Attributes\\Injectable;

#[Injectable]
class {$name}
{
    public function handle()
    {
        // TODO: Implement service logic
    }
}
";
    }

    protected function getRepositoryTemplate(string $name): string
    {
        $tableName = strtolower(preg_replace('/Repository$/', '', $name)) . 's';
        
        return "<?php

namespace App\\Repositories;

use NeoPhp\\Database\\Repository;
use NeoPhp\\Core\\Attributes\\Injectable;

#[Injectable]
class {$name} extends Repository
{
    protected \$table = '{$tableName}';
    protected \$primaryKey = 'id';

    // Add custom query methods here
}
";
    }
}
