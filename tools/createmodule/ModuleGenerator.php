<?php
namespace Tools\CreateModule;

class ModuleGenerator {
    private string $moduleName;
    private string $moduleType;
    private string $basePath;
    private array $validTypes = ['basic', 'api', 'react'];

    public function __construct(string $moduleName, string $moduleType = 'basic') {
        $this->moduleName = ucfirst($moduleName);
        $this->moduleType = strtolower($moduleType);
        $this->basePath = dirname(__DIR__, 2) . "/modules/{$this->moduleName}";

        if (!in_array($this->moduleType, $this->validTypes)) {
            throw new \InvalidArgumentException("Invalid module type. Valid types are: " . implode(', ', $this->validTypes));
        }
    }

    public function generate(): bool {
        try {
            // Create module directory structure
            $this->createDirectoryStructure();
            
            // Generate MVC files
            $this->generateControllerFile();
            $this->generateModelFile();
            $this->generateViewFile();
            
            // Generate routes
            $this->generateRoutesFile();
            
            // Generate additional files based on module type
            $this->generateAdditionalFiles();
            
            // Update InitMods.php
            $this->updateInitMods();
            
            // Update composer.json
            $this->updateComposerJson();
            
            return true;
        } catch (\Exception $e) {
            echo "Error generating module: " . $e->getMessage() . "\n";
            return false;
        }
    }

    private function createDirectoryStructure(): void {
        $directories = [
            $this->basePath,
            "{$this->basePath}/routes",
            "{$this->basePath}/views",
            "{$this->basePath}/views/layout",
            "{$this->basePath}/etc"
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    private function generateControllerFile(): void {
        $template = match($this->moduleType) {
            'api' => ModuleTemplates::getApiControllerTemplate($this->moduleName),
            'react' => ModuleTemplates::getReactControllerTemplate($this->moduleName),
            default => $this->getControllerTemplate()
        };
        file_put_contents("{$this->basePath}/Controller.php", $template);
    }

    private function generateModelFile(): void {
        $modelTemplate = $this->getModelTemplate();
        file_put_contents("{$this->basePath}/Model.php", $modelTemplate);
    }

    private function generateViewFile(): void {
        $template = match($this->moduleType) {
            'react' => ModuleTemplates::getReactViewTemplate($this->moduleName),
            default => $this->getViewTemplate()
        };
        file_put_contents("{$this->basePath}/View.php", $template);

        // Generate view templates for basic modules
        if ($this->moduleType === 'basic') {
            $this->generateViewTemplates();
        }
    }

    private function generateViewTemplates(): void {
        // Create index.php view template
        $indexTemplate = <<<HTML
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-4"><?php echo \$title; ?></h1>
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <p class="mb-4">Start building your module content here!</p>
    </div>
</div>
HTML;
        file_put_contents("{$this->basePath}/views/index.php", $indexTemplate);

        // Create header.php
        $headerTemplate = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo \$title ?? '{$this->moduleName}'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">
HTML;
        file_put_contents("{$this->basePath}/views/layout/header.php", $headerTemplate);

        // Create footer.php
        $footerTemplate = <<<HTML
    <footer class="bg-white shadow mt-8 py-4">
        <div class="container mx-auto px-4">
            <p class="text-center text-gray-600">&copy; <?php echo date('Y'); ?> {$this->moduleName}. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
HTML;
        file_put_contents("{$this->basePath}/views/layout/footer.php", $footerTemplate);
    }

    private function generateRoutesFile(): void {
        $template = match($this->moduleType) {
            'react' => ModuleTemplates::getReactRoutesTemplate($this->moduleName),
            default => $this->getRoutesTemplate()
        };
        file_put_contents("{$this->basePath}/routes/Routes.php", $template);
    }

    private function generateAdditionalFiles(): void {
        if ($this->moduleType === 'react') {
            // Create etc directory if it doesn't exist
            $etcPath = "{$this->basePath}/etc";
            if (!is_dir($etcPath)) {
                mkdir($etcPath, 0755, true);
            }

            // Create index.html
            $indexContent = str_replace(
                '{MODULE_NAME}', 
                strtolower($this->moduleName), 
                ModuleTemplates::getReactIndexTemplate()
            );
            file_put_contents("{$etcPath}/index.html", $indexContent);

            // Create app.js
            file_put_contents("{$etcPath}/app.js", ModuleTemplates::getReactAppTemplate());
        }
    }

    private function updateInitMods(): void {
        $initModsPath = dirname(__DIR__, 2) . '/etc/InitMods.php';
        $content = file_get_contents($initModsPath);

        // Add use statement
        $useStatement = "use {$this->moduleName}\\Routes\\Routes as {$this->moduleName}Routes;\n//add other module routes";
        $content = str_replace("//add other module routes", $useStatement, $content);

        // Add route registration
        $routeRegistration = "            new {$this->moduleName}Routes(),\n            //new OtherRoutes()";
        $content = str_replace("            //new OtherRoutes()", $routeRegistration, $content);

        file_put_contents($initModsPath, $content);
    }

    private function updateComposerJson(): void {
        $composerPath = dirname(__DIR__, 2) . '/composer.json';
        $composer = json_decode(file_get_contents($composerPath), true);

        // Add namespace to autoload
        $composer['autoload']['psr-4']["{$this->moduleName}\\\\"] = "modules/{$this->moduleName}/";
        $composer['autoload']['psr-4']["{$this->moduleName}\\\\Routes\\\\"] = "modules/{$this->moduleName}/routes/";

        file_put_contents($composerPath, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function getControllerTemplate(): string {
        return <<<PHP
<?php
namespace {$this->moduleName};

use Common\\Bmvc\\BaseController;

class Controller extends BaseController {
    public function display(\$reqRoute, \$reqMet) {
        // Add your controller logic here
        \$view = new View();
        \$data = ['title' => 'Welcome to {$this->moduleName}'];
        \$view->render('index', \$data);
    }
}
PHP;
    }

    private function getModelTemplate(): string {
        return <<<PHP
<?php
namespace {$this->moduleName};

use Common\\Bmvc\\BaseModel;

class Model extends BaseModel {
    protected \$table = '{$this->moduleName}s';
    
    public function __construct() {
        parent::__construct();
    }
    
    // Add your model logic here
}
PHP;
    }

    private function getViewTemplate(): string {
        return <<<PHP
<?php
namespace {$this->moduleName};

use Common\\Bmvc\\BaseView;

class View extends BaseView {
    public function render(\$template, array \$data = []): void {
        // Extract data to make it available in the template
        extract(\$data);
        
        // Include header template
        include __DIR__ . '/views/layout/header.php';
        
        // Include the main template
        include __DIR__ . "/views/{\$template}.php";
        
        // Include footer template
        include __DIR__ . '/views/layout/footer.php';
    }
}
PHP;
    }

    private function getRoutesTemplate(): string {
        return <<<PHP
<?php
namespace {$this->moduleName}\\Routes;

use upMVC\\Router;
use {$this->moduleName}\\Controller;

class Routes {
    public function Routes(Router \$router): void {
        // Main route
        \$router->addRoute('/{$this->moduleName}', Controller::class, 'display');
        
        // Add more routes here as needed
        // \$router->addRoute('/{$this->moduleName}/create', Controller::class, 'create');
        // \$router->addRoute('/{$this->moduleName}/edit/:id', Controller::class, 'edit');
        // \$router->addRoute('/{$this->moduleName}/delete/:id', Controller::class, 'delete');
    }
}
PHP;
    }
}
