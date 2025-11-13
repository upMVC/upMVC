<?php
namespace App\Tools\ModuleGenerator;

use PDO;
use PDOException;
use Exception;

/**
 * Enhanced Module Generator for upMVC
 * 
 * Generates complete module structures with MVC components,
 * routes, database tables, and proper integration.
 */
class ModuleGenerator
{
    private array $config;
    private string $modulePath;
    private string $namespace;
    private array $validTypes = ['basic', 'crud', 'api', 'auth', 'dashboard'];

    public function __construct(array $config)
    {
        $this->config = $this->validateConfig($config);
        $this->namespace = $this->config['namespace'];
        $this->modulePath = __DIR__ . '/../../modules/' . $this->namespace;
    }

    public function generate(): bool
    {
        try {
            echo "Generating module '{$this->namespace}'...\n";
            
            // Create directory structure
            $this->createDirectoryStructure();
            
            // Generate core files
            $this->generateController();
            $this->generateModel();
            $this->generateView();
            $this->generateRoutes();
            
            // Generate additional files based on type
            $this->generateAdditionalFiles();
            
            // Update framework files
            $this->updateFrameworkFiles();
            
            // Create database table if needed
            if ($this->config['type'] === 'crud' && ($this->config['create_table'] ?? false)) {
                $this->createDatabaseTable();
            }
            
            echo "Module generation completed successfully!\n";
            return true;
            
        } catch (Exception $e) {
            echo "Error generating module: " . $e->getMessage() . "\n";
            return false;
        }
    }

    private function validateConfig(array $config): array
    {
        $required = ['name', 'type'];
        foreach ($required as $key) {
            if (empty($config[$key])) {
                throw new Exception("Configuration key '{$key}' is required.");
            }
        }

        if (!in_array($config['type'], $this->validTypes)) {
            throw new Exception("Invalid module type: {$config['type']}");
        }

        // Set defaults
        $config['namespace'] = $config['namespace'] ?? ucfirst($config['name']);
        $config['table_name'] = $config['table_name'] ?? strtolower($config['name']) . 's';
        $config['route_name'] = $config['route_name'] ?? strtolower($config['name']) . 's';
        $config['fields'] = $config['fields'] ?? [];

        return $config;
    }

    private function createDirectoryStructure(): void
    {
        $directories = [
            $this->modulePath,
            $this->modulePath . '/routes',
            $this->modulePath . '/views',
            $this->modulePath . '/views/layouts',
            $this->modulePath . '/etc',
            $this->modulePath . '/assets'
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
                echo "Created directory: {$dir}\n";
            }
        }
    }

    private function generateController(): void
    {
        $template = match($this->config['type']) {
            'crud' => $this->getCrudControllerTemplate(),
            'api' => $this->getApiControllerTemplate(),
            'auth' => $this->getAuthControllerTemplate(),
            'dashboard' => $this->getDashboardControllerTemplate(),
            default => $this->getBasicControllerTemplate()
        };

        $this->writeFile('/Controller.php', $template);
    }

    private function generateModel(): void
    {
        $template = match($this->config['type']) {
            'crud' => $this->getCrudModelTemplate(),
            'auth' => $this->getAuthModelTemplate(),
            default => $this->getBasicModelTemplate()
        };

        $this->writeFile('/Model.php', $template);
    }

    private function generateView(): void
    {
        $template = match($this->config['type']) {
            'crud' => $this->getCrudViewTemplate(),
            'dashboard' => $this->getDashboardViewTemplate(),
            default => $this->getBasicViewTemplate()
        };

        $this->writeFile('/View.php', $template);
        
        // Generate view templates
        $this->generateViewTemplates();
    }

    private function generateRoutes(): void
    {
        $template = $this->getRoutesTemplate();
        $this->writeFile('/routes/Routes.php', $template);
    }

    private function generateAdditionalFiles(): void
    {
        // Generate CSS file
        $this->writeFile('/assets/style.css', $this->getCssTemplate());
        
        // Generate JavaScript file if needed
        if (in_array($this->config['type'], ['crud', 'dashboard'])) {
            $this->writeFile('/assets/script.js', $this->getJsTemplate());
        }
        
        // Generate API documentation for API modules
        if ($this->config['type'] === 'api' || $this->config['include_api'] ?? false) {
            $this->writeFile('/etc/api-docs.md', $this->getApiDocsTemplate());
        }
    }

    private function generateViewTemplates(): void
    {
        // Generate layout files
        $this->writeFile('/views/layouts/header.php', $this->getHeaderTemplate());
        $this->writeFile('/views/layouts/footer.php', $this->getFooterTemplate());
        
        // Generate view files based on type
        switch ($this->config['type']) {
            case 'crud':
                $this->writeFile('/views/index.php', $this->getCrudIndexViewTemplate());
                $this->writeFile('/views/create.php', $this->getCrudCreateViewTemplate());
                $this->writeFile('/views/edit.php', $this->getCrudEditViewTemplate());
                break;
            case 'dashboard':
                $this->writeFile('/views/dashboard.php', $this->getDashboardViewTemplate());
                break;
            case 'auth':
                $this->writeFile('/views/login.php', $this->getLoginViewTemplate());
                $this->writeFile('/views/register.php', $this->getRegisterViewTemplate());
                break;
            default:
                $this->writeFile('/views/index.php', $this->getBasicIndexViewTemplate());
        }
    }

    private function updateFrameworkFiles(): void
    {
        // Update composer.json
        $this->updateComposerJson();
        
        // Update InitMods.php
        $this->updateInitMods();
        
        echo "Updated framework files\n";
    }

    private function updateComposerJson(): void
    {
        $composerFile = __DIR__ . '/../../composer.json';
        if (!file_exists($composerFile)) {
            throw new Exception("composer.json not found");
        }

        $composer = json_decode(file_get_contents($composerFile), true);
        
        // Add namespace
        $composer['autoload']['psr-4'][$this->namespace . '\\'] = "modules/{$this->namespace}/";
        $composer['autoload']['psr-4'][$this->namespace . '\\Routes\\'] = "modules/{$this->namespace}/routes/";
        
        file_put_contents($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function updateInitMods(): void
    {
        $initModsFile = __DIR__ . '/../../etc/InitMods.php';
        if (!file_exists($initModsFile)) {
            throw new Exception("InitMods.php not found");
        }

        $content = file_get_contents($initModsFile);
        
        // Add use statement
        $useStatement = "use {$this->namespace}\\Routes\\Routes as {$this->namespace}Routes;";
        if (strpos($content, $useStatement) === false) {
            $content = str_replace(
                "//add other module routes",
                "{$useStatement}\n//add other module routes",
                $content
            );
        }
        
        // Add to getModules array
        $moduleInstance = "new {$this->namespace}Routes(),";
        if (strpos($content, $moduleInstance) === false) {
            $content = str_replace(
                "//new ProductsRoutes(),",
                "{$moduleInstance}\n            //new ProductsRoutes(),",
                $content
            );
        }
        
        file_put_contents($initModsFile, $content);
    }

    private function createDatabaseTable(): void
    {
        if (empty($this->config['fields'])) {
            echo "No fields defined, skipping table creation\n";
            return;
        }

        try {
            // Get database configuration
            require_once __DIR__ . '/../../etc/ConfigDatabase.php';
            
            $host = \upMVC\ConfigDatabase::get('db.host', '127.0.0.1');
            $dbname = \upMVC\ConfigDatabase::get('db.name', 'test');
            $username = \upMVC\ConfigDatabase::get('db.user', 'root');
            $password = \upMVC\ConfigDatabase::get('db.pass', '');

            $pdo = new PDO("mysql:host={$host};dbname={$dbname}", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql = $this->generateTableSQL();
            $pdo->exec($sql);
            
            echo "Database table '{$this->config['table_name']}' created successfully\n";
            
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage() . "\n";
            echo "Please create the table manually using this SQL:\n";
            echo $this->generateTableSQL() . "\n";
        }
    }

    private function generateTableSQL(): string
    {
        $fields = ["id INT AUTO_INCREMENT PRIMARY KEY"];
        
        foreach ($this->config['fields'] as $field) {
            $fields[] = "`{$field['name']}` {$field['sql_type']} NOT NULL";
        }
        
        $fields[] = "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        $fields[] = "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
        
        return "CREATE TABLE IF NOT EXISTS `{$this->config['table_name']}` (\n  " . 
               implode(",\n  ", $fields) . 
               "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    }

    private function writeFile(string $relativePath, string $content): void
    {
        $filePath = $this->modulePath . $relativePath;
        $dir = dirname($filePath);
        
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($filePath, $content);
        echo "Generated: {$relativePath}\n";
    }

    // Template methods follow...
    private function getBasicControllerTemplate(): string
    {
        return <<<PHP
<?php
namespace {$this->namespace};

use App\Common\\Bmvc\\BaseController;

/**
 * {$this->namespace} Controller
 * 
 * Basic controller for {$this->namespace} module
 */
class Controller extends BaseController
{
    private \$model;
    private \$view;

    public function __construct()
    {
        \$this->model = new Model();
        \$this->view = new View();
    }

    /**
     * Main display method
     */
    public function display(\$reqRoute, \$reqMet): void
    {
        \$data = [
            'title' => '{$this->namespace} Module',
            'message' => 'Welcome to the {$this->namespace} module!'
        ];
        
        \$this->view->render('index', \$data);
    }

    /**
     * About page
     */
    public function about(\$reqRoute, \$reqMet): void
    {
        \$data = [
            'title' => 'About {$this->namespace}',
            'content' => 'This is the about page for {$this->namespace} module.'
        ];
        
        \$this->view->render('about', \$data);
    }
}
PHP;
    }

    private function getCrudControllerTemplate(): string
    {
        $fieldsSanitize = '';
        $fieldsArray = '';
        
        foreach ($this->config['fields'] as $field) {
            $filter = match($field['html_type']) {
                'email' => 'FILTER_SANITIZE_EMAIL',
                'number', 'range' => 'FILTER_SANITIZE_NUMBER_INT',
                default => 'FILTER_SANITIZE_SPECIAL_CHARS'
            };
            
            $fieldsSanitize .= "        \${$field['name']} = filter_input(INPUT_POST, '{$field['name']}', {$filter});\n";
            $fieldsArray .= "            '{$field['name']}' => \${$field['name']},\n";
        }

        return <<<PHP
<?php
namespace {$this->namespace};

use App\Common\\Bmvc\\BaseController;

/**
 * {$this->namespace} CRUD Controller
 * 
 * Handles all CRUD operations for {$this->namespace} module
 */
class Controller extends BaseController
{
    private \$model;
    private \$view;
    private \$table = '{$this->config['table_name']}';
    private \$moduleRoute = BASE_URL . '/{$this->config['route_name']}';

    public function __construct()
    {
        \$this->model = new Model();
        \$this->view = new View();
    }

    /**
     * Main display method - shows list of records
     */
    public function display(\$reqRoute, \$reqMet): void
    {
        if (isset(\$_SESSION["username"])) {
            \$this->selectAction(\$reqMet);
        } else {
            header('Location: ' . BASE_URL . '/auth');
            exit;
        }
    }

    /**
     * Route actions based on request method and parameters
     */
    private function selectAction(\$reqMet): void
    {
        if (\$reqMet === 'POST') {
            \$action = \$_POST['action'] ?? '';
            switch (\$action) {
                case 'create':
                    \$this->create();
                    break;
                case 'update':
                    \$this->update();
                    break;
                default:
                    \$this->index();
            }
        } else {
            \$action = \$_GET['action'] ?? '';
            switch (\$action) {
                case 'create':
                    \$this->showCreateForm();
                    break;
                case 'edit':
                    \$this->showEditForm();
                    break;
                case 'delete':
                    \$this->delete();
                    break;
                default:
                    \$this->index();
            }
        }
    }

    /**
     * Display list of records with pagination
     */
    private function index(): void
    {
        \$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT) ?: 1;
        \$pageSize = 10;
        
        \$records = \$this->model->readWithPagination(\$this->table, \$page, \$pageSize);
        \$totalRecords = count(\$this->model->readAll(\$this->table));
        \$totalPages = ceil(\$totalRecords / \$pageSize);
        
        \$data = [
            'title' => '{$this->namespace} Management',
            'records' => \$records,
            'currentPage' => \$page,
            'totalPages' => \$totalPages,
            'moduleRoute' => \$this->moduleRoute
        ];
        
        \$this->view->render('index', \$data);
    }

    /**
     * Show create form
     */
    private function showCreateForm(): void
    {
        \$data = [
            'title' => 'Create New {$this->namespace}',
            'moduleRoute' => \$this->moduleRoute
        ];
        
        \$this->view->render('create', \$data);
    }

    /**
     * Create new record
     */
    private function create(): void
    {
{$fieldsSanitize}
        \$data = [
{$fieldsArray}        ];

        \$id = \$this->model->create(\$data, \$this->table);
        
        if (\$id) {
            \$_SESSION['flash_message'] = '{$this->namespace} created successfully!';
            \$_SESSION['flash_type'] = 'success';
        } else {
            \$_SESSION['flash_message'] = 'Error creating {$this->namespace}.';
            \$_SESSION['flash_type'] = 'error';
        }
        
        header('Location: ' . \$this->moduleRoute);
        exit;
    }

    /**
     * Show edit form
     */
    private function showEditForm(): void
    {
        \$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        \$record = \$this->model->read(\$id, \$this->table);
        
        if (!\$record) {
            \$_SESSION['flash_message'] = '{$this->namespace} not found.';
            \$_SESSION['flash_type'] = 'error';
            header('Location: ' . \$this->moduleRoute);
            exit;
        }
        
        \$data = [
            'title' => 'Edit {$this->namespace}',
            'record' => \$record,
            'moduleRoute' => \$this->moduleRoute
        ];
        
        \$this->view->render('edit', \$data);
    }

    /**
     * Update existing record
     */
    private function update(): void
    {
        \$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
{$fieldsSanitize}
        \$data = [
{$fieldsArray}        ];

        \$success = \$this->model->update(\$id, \$data, \$this->table);
        
        if (\$success) {
            \$_SESSION['flash_message'] = '{$this->namespace} updated successfully!';
            \$_SESSION['flash_type'] = 'success';
        } else {
            \$_SESSION['flash_message'] = 'Error updating {$this->namespace}.';
            \$_SESSION['flash_type'] = 'error';
        }
        
        header('Location: ' . \$this->moduleRoute);
        exit;
    }

    /**
     * Delete record
     */
    private function delete(): void
    {
        \$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        \$success = \$this->model->delete(\$id, \$this->table);
        
        if (\$success) {
            \$_SESSION['flash_message'] = '{$this->namespace} deleted successfully!';
            \$_SESSION['flash_type'] = 'success';
        } else {
            \$_SESSION['flash_message'] = 'Error deleting {$this->namespace}.';
            \$_SESSION['flash_type'] = 'error';
        }
        
        header('Location: ' . \$this->moduleRoute);
        exit;
    }

    /**
     * API endpoint for AJAX requests
     */
    public function api(\$reqRoute, \$reqMet): void
    {
        header('Content-Type: application/json');
        
        try {
            switch (\$reqMet) {
                case 'GET':
                    \$this->apiGet();
                    break;
                case 'POST':
                    \$this->apiPost();
                    break;
                case 'PUT':
                    \$this->apiPut();
                    break;
                case 'DELETE':
                    \$this->apiDelete();
                    break;
                default:
                    http_response_code(405);
                    echo json_encode(['error' => 'Method not allowed']);
            }
        } catch (Exception \$e) {
            http_response_code(500);
            echo json_encode(['error' => \$e->getMessage()]);
        }
    }

    private function apiGet(): void
    {
        \$id = \$_GET['id'] ?? null;
        
        if (\$id) {
            \$record = \$this->model->read(\$id, \$this->table);
            echo json_encode(\$record ?: ['error' => 'Record not found']);
        } else {
            \$records = \$this->model->readAll(\$this->table);
            echo json_encode(\$records);
        }
    }

    private function apiPost(): void
    {
        \$input = json_decode(file_get_contents('php://input'), true);
        \$id = \$this->model->create(\$input, \$this->table);
        
        if (\$id) {
            \$record = \$this->model->read(\$id, \$this->table);
            echo json_encode(['success' => true, 'data' => \$record]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Failed to create record']);
        }
    }

    private function apiPut(): void
    {
        \$input = json_decode(file_get_contents('php://input'), true);
        \$id = \$input['id'] ?? null;
        
        if (!\$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID is required']);
            return;
        }
        
        unset(\$input['id']);
        \$success = \$this->model->update(\$id, \$input, \$this->table);
        
        if (\$success) {
            \$record = \$this->model->read(\$id, \$this->table);
            echo json_encode(['success' => true, 'data' => \$record]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Failed to update record']);
        }
    }

    private function apiDelete(): void
    {
        \$input = json_decode(file_get_contents('php://input'), true);
        \$id = \$input['id'] ?? \$_GET['id'] ?? null;
        
        if (!\$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID is required']);
            return;
        }
        
        \$success = \$this->model->delete(\$id, \$this->table);
        echo json_encode(['success' => \$success]);
    }
}
PHP;
    }

    private function getBasicModelTemplate(): string
    {
        return <<<PHP
<?php
namespace {$this->namespace};

use App\Common\\Bmvc\\BaseModel;

/**
 * {$this->namespace} Model
 * 
 * Handles data operations for {$this->namespace} module
 */
class Model extends BaseModel
{
    protected \$table = '{$this->config['table_name']}';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all records with optional filtering
     */
    public function getAllRecords(\$filters = []): array
    {
        \$sql = "SELECT * FROM {\$this->table}";
        \$params = [];
        
        if (!empty(\$filters)) {
            \$conditions = [];
            foreach (\$filters as \$field => \$value) {
                \$conditions[] = "{\$field} = :{\$field}";
                \$params[":\$field"] = \$value;
            }
            \$sql .= " WHERE " . implode(" AND ", \$conditions);
        }
        
        \$sql .= " ORDER BY created_at DESC";
        
        \$stmt = \$this->conn->prepare(\$sql);
        \$stmt->execute(\$params);
        
        return \$stmt->fetchAll();
    }

    /**
     * Search records by keyword
     */
    public function search(\$keyword): array
    {
        \$sql = "SELECT * FROM {\$this->table} WHERE ";
        
        // Add search conditions for text fields
        \$searchFields = ['name']; // Customize based on your fields
        \$conditions = [];
        
        foreach (\$searchFields as \$field) {
            \$conditions[] = "{\$field} LIKE :keyword";
        }
        
        \$sql .= implode(" OR ", \$conditions);
        \$sql .= " ORDER BY created_at DESC";
        
        \$stmt = \$this->conn->prepare(\$sql);
        \$stmt->bindValue(':keyword', "%{\$keyword}%");
        \$stmt->execute();
        
        return \$stmt->fetchAll();
    }
}
PHP;
    }

    private function getCrudModelTemplate(): string
    {
        return <<<PHP
<?php
namespace {$this->namespace};

use App\Common\\Bmvc\\BaseModel;

/**
 * {$this->namespace} CRUD Model
 * 
 * Handles all database operations for {$this->namespace} module
 */
class Model extends BaseModel
{
    protected \$table = '{$this->config['table_name']}';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create a new record with validation
     */
    public function createRecord(array \$data): int|false
    {
        // Validate required fields
        if (!\$this->validateData(\$data)) {
            return false;
        }
        
        return \$this->create(\$data, \$this->table);
    }

    /**
     * Update record with validation
     */
    public function updateRecord(int \$id, array \$data): bool
    {
        if (!\$this->validateData(\$data)) {
            return false;
        }
        
        return \$this->update(\$id, \$data, \$this->table);
    }

    /**
     * Get records with search and pagination
     */
    public function getRecordsWithSearch(\$search = '', \$page = 1, \$pageSize = 10): array
    {
        \$offset = (\$page - 1) * \$pageSize;
        \$sql = "SELECT * FROM {\$this->table}";
        \$params = [];
        
        if (!\$search) {
            \$searchFields = [];
            foreach (['{$this->generateSearchFields()}'] as \$field) {
                \$searchFields[] = "{\$field} LIKE :search";
            }
            
            if (\$searchFields) {
                \$sql .= " WHERE " . implode(" OR ", \$searchFields);
                \$params[':search'] = "%{\$search}%";
            }
        }
        
        \$sql .= " ORDER BY created_at DESC LIMIT :offset, :pageSize";
        
        \$stmt = \$this->conn->prepare(\$sql);
        \$stmt->bindValue(':offset', \$offset, \\PDO::PARAM_INT);
        \$stmt->bindValue(':pageSize', \$pageSize, \\PDO::PARAM_INT);
        
        foreach (\$params as \$key => \$value) {
            \$stmt->bindValue(\$key, \$value);
        }
        
        \$stmt->execute();
        return \$stmt->fetchAll();
    }

    /**
     * Get total count for pagination
     */
    public function getTotalCount(\$search = ''): int
    {
        \$sql = "SELECT COUNT(*) FROM {\$this->table}";
        \$params = [];
        
        if (!\$search) {
            \$searchFields = [];
            foreach (['{$this->generateSearchFields()}'] as \$field) {
                \$searchFields[] = "{\$field} LIKE :search";
            }
            
            if (\$searchFields) {
                \$sql .= " WHERE " . implode(" OR ", \$searchFields);
                \$params[':search'] = "%{\$search}%";
            }
        }
        
        \$stmt = \$this->conn->prepare(\$sql);
        foreach (\$params as \$key => \$value) {
            \$stmt->bindValue(\$key, \$value);
        }
        \$stmt->execute();
        
        return (int) \$stmt->fetchColumn();
    }

    /**
     * Validate input data
     */
    private function validateData(array \$data): bool
    {
        // Add your validation logic here
        // Example: check required fields
        \$requiredFields = ['{$this->generateRequiredFields()}'];
        
        foreach (\$requiredFields as \$field) {
            if (empty(\$data[\$field])) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get statistics for dashboard
     */
    public function getStatistics(): array
    {
        \$stats = [];
        
        // Total records
        \$stmt = \$this->conn->prepare("SELECT COUNT(*) FROM {\$this->table}");
        \$stmt->execute();
        \$stats['total'] = \$stmt->fetchColumn();
        
        // Records created today
        \$stmt = \$this->conn->prepare("SELECT COUNT(*) FROM {\$this->table} WHERE DATE(created_at) = CURDATE()");
        \$stmt->execute();
        \$stats['today'] = \$stmt->fetchColumn();
        
        // Records created this week
        \$stmt = \$this->conn->prepare("SELECT COUNT(*) FROM {\$this->table} WHERE WEEK(created_at) = WEEK(NOW())");
        \$stmt->execute();
        \$stats['this_week'] = \$stmt->fetchColumn();
        
        return \$stats;
    }
}
PHP;
    }

    private function generateSearchFields(): string
    {
        $searchFields = [];
        foreach ($this->config['fields'] as $field) {
            if (in_array($field['html_type'], ['text', 'email', 'textarea'])) {
                $searchFields[] = "'{$field['name']}'";
            }
        }
        return implode(', ', $searchFields);
    }

    private function generateRequiredFields(): string
    {
        $requiredFields = [];
        foreach ($this->config['fields'] as $field) {
            $requiredFields[] = "'{$field['name']}'";
        }
        return implode("', '", $requiredFields);
    }

    // Continue with remaining template methods...
    private function getBasicViewTemplate(): string
    {
        return <<<PHP
<?php
namespace {$this->namespace};

use App\Common\\Bmvc\\BaseView;

/**
 * {$this->namespace} View
 * 
 * Handles all view rendering for {$this->namespace} module
 */
class View extends BaseView
{
    /**
     * Render a template with data
     */
    public function render(string \$template, array \$data = []): void
    {
        // Extract data to make variables available in templates
        extract(\$data);
        
        // Include header
        include __DIR__ . '/views/layouts/header.php';
        
        // Include main template
        \$templateFile = __DIR__ . "/views/{\$template}.php";
        if (file_exists(\$templateFile)) {
            include \$templateFile;
        } else {
            echo "<div class='alert alert-danger'>Template {\$template} not found.</div>";
        }
        
        // Include footer
        include __DIR__ . '/views/layouts/footer.php';
    }

    /**
     * Render JSON response for AJAX requests
     */
    public function renderJson(array \$data): void
    {
        header('Content-Type: application/json');
        echo json_encode(\$data);
        exit;
    }

    /**
     * Display flash messages
     */
    public function renderFlashMessages(): void
    {
        if (isset(\$_SESSION['flash_message'])) {
            \$type = \$_SESSION['flash_type'] ?? 'info';
            \$message = \$_SESSION['flash_message'];
            
            echo "<div class='alert alert-{\$type} alert-dismissible fade show' role='alert'>";
            echo htmlspecialchars(\$message);
            echo "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>";
            echo "</div>";
            
            unset(\$_SESSION['flash_message'], \$_SESSION['flash_type']);
        }
    }
}
PHP;
    }

    private function getRoutesTemplate(): string
    {
        $routes = [];
        
        switch ($this->config['type']) {
            case 'crud':
                $routes[] = "        \$router->addRoute('/{$this->config['route_name']}', Controller::class, 'display');";
                $routes[] = "        \$router->addRoute('/{$this->config['route_name']}/api', Controller::class, 'api');";
                break;
            case 'api':
                $routes[] = "        \$router->addRoute('/api/{$this->config['route_name']}', Controller::class, 'api');";
                break;
            default:
                $routes[] = "        \$router->addRoute('/{$this->config['route_name']}', Controller::class, 'display');";
                $routes[] = "        \$router->addRoute('/{$this->config['route_name']}/about', Controller::class, 'about');";
        }

        return <<<PHP
<?php
namespace {$this->namespace}\\Routes;

use {$this->namespace}\\Controller;

/**
 * {$this->namespace} Routes
 * 
 * Defines all routes for {$this->namespace} module
 */
class Routes
{
    /**
     * Register routes with the router
     */
    public function Routes(\$router): void
    {
{implode("\n", $routes)}
    }
}
PHP;
    }

    // Additional template methods for different module types...
    private function getApiControllerTemplate(): string
    {
        return "<?php\n// API Controller template - to be implemented\n";
    }

    private function getAuthControllerTemplate(): string
    {
        return "<?php\n// Auth Controller template - to be implemented\n";
    }

    private function getDashboardControllerTemplate(): string
    {
        return "<?php\n// Dashboard Controller template - to be implemented\n";
    }

    private function getAuthModelTemplate(): string
    {
        return "<?php\n// Auth Model template - to be implemented\n";
    }

    private function getCrudViewTemplate(): string
    {
        return $this->getBasicViewTemplate();
    }

    private function getDashboardViewTemplate(): string
    {
        return $this->getBasicViewTemplate();
    }

    private function getHeaderTemplate(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo \$title ?? '{$this->namespace}'; ?> - upMVC</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/modules/{$this->namespace}/assets/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <i class="fas fa-home"></i> upMVC
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?php echo BASE_URL; ?>/{$this->config['route_name']}">
                    <i class="fas fa-list"></i> {$this->namespace}
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (method_exists(\$this, 'renderFlashMessages')) \$this->renderFlashMessages(); ?>
HTML;
    }

    private function getFooterTemplate(): string
    {
        return <<<HTML
    </div>

    <footer class="bg-light text-center text-lg-start mt-5">
        <div class="text-center p-3">
            <small class="text-muted">
                © <?php echo date('Y'); ?> {$this->namespace} Module - Powered by upMVC
            </small>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo BASE_URL; ?>/modules/{$this->namespace}/assets/script.js"></script>
</body>
</html>
HTML;
    }

    private function getBasicIndexViewTemplate(): string
    {
        return <<<HTML
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-home"></i> <?php echo \$title; ?></h3>
            </div>
            <div class="card-body">
                <p class="lead"><?php echo \$message ?? 'Welcome to the {$this->namespace} module!'; ?></p>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title">Getting Started</h5>
                                <p class="card-text">
                                    This is a basic {$this->namespace} module. You can customize this view by editing 
                                    the files in <code>modules/{$this->namespace}/views/</code>.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title">Next Steps</h5>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success"></i> Customize the controller</li>
                                    <li><i class="fas fa-check text-success"></i> Add your business logic</li>
                                    <li><i class="fas fa-check text-success"></i> Style your views</li>
                                    <li><i class="fas fa-check text-success"></i> Add more routes</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
HTML;
    }

    private function getCrudIndexViewTemplate(): string
    {
        $tableHeaders = '';
        $tableRows = '';
        
        foreach ($this->config['fields'] as $field) {
            $tableHeaders .= "                    <th>" . ucfirst($field['name']) . "</th>\n";
            $tableRows .= "                        <td><?php echo htmlspecialchars(\$record['{$field['name']}']); ?></td>\n";
        }

        return <<<HTML
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3><i class="fas fa-list"></i> <?php echo \$title; ?></h3>
                <a href="<?php echo \$moduleRoute; ?>?action=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New {$this->namespace}
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty(\$records)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
{$tableHeaders}                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (\$records as \$record): ?>
                                <tr>
{$tableRows}                    <td><?php echo date('M j, Y', strtotime(\$record['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?php echo \$moduleRoute; ?>?action=edit&id=<?php echo \$record['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="<?php echo \$moduleRoute; ?>?action=delete&id=<?php echo \$record['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Are you sure you want to delete this item?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if (\$totalPages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if (\$currentPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo \$moduleRoute; ?>?page=<?php echo \$currentPage - 1; ?>">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for (\$i = 1; \$i <= \$totalPages; \$i++): ?>
                            <li class="page-item <?php echo \$i == \$currentPage ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo \$moduleRoute; ?>?page=<?php echo \$i; ?>"><?php echo \$i; ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if (\$currentPage < \$totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo \$moduleRoute; ?>?page=<?php echo \$currentPage + 1; ?>">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No {$this->namespace} records found</h4>
                        <p class="text-muted">Get started by creating your first {$this->namespace}.</p>
                        <a href="<?php echo \$moduleRoute; ?>?action=create" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create First {$this->namespace}
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
HTML;
    }

    private function getCrudCreateViewTemplate(): string
    {
        $formFields = '';
        
        foreach ($this->config['fields'] as $field) {
            $label = ucfirst($field['name']);
            $required = 'required';
            
            switch ($field['html_type']) {
                case 'textarea':
                    $formFields .= <<<HTML
                    <div class="mb-3">
                        <label for="{$field['name']}" class="form-label">{$label}</label>
                        <textarea class="form-control" id="{$field['name']}" name="{$field['name']}" rows="3" {$required}></textarea>
                    </div>

HTML;
                    break;
                case 'select':
                    $formFields .= <<<HTML
                    <div class="mb-3">
                        <label for="{$field['name']}" class="form-label">{$label}</label>
                        <select class="form-control" id="{$field['name']}" name="{$field['name']}" {$required}>
                            <option value="">Select {$label}</option>
                            <!-- Add your options here -->
                        </select>
                    </div>

HTML;
                    break;
                default:
                    $formFields .= <<<HTML
                    <div class="mb-3">
                        <label for="{$field['name']}" class="form-label">{$label}</label>
                        <input type="{$field['html_type']}" class="form-control" id="{$field['name']}" name="{$field['name']}" {$required}>
                    </div>

HTML;
            }
        }

        return <<<HTML
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-plus"></i> <?php echo \$title; ?></h3>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo \$moduleRoute; ?>">
                    <input type="hidden" name="action" value="create">
                    
{$formFields}                    
                    <div class="d-flex justify-content-between">
                        <a href="<?php echo \$moduleRoute; ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create {$this->namespace}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
HTML;
    }

    private function getCrudEditViewTemplate(): string
    {
        $formFields = '';
        
        foreach ($this->config['fields'] as $field) {
            $label = ucfirst($field['name']);
            $required = 'required';
            
            switch ($field['html_type']) {
                case 'textarea':
                    $formFields .= <<<HTML
                    <div class="mb-3">
                        <label for="{$field['name']}" class="form-label">{$label}</label>
                        <textarea class="form-control" id="{$field['name']}" name="{$field['name']}" rows="3" {$required}><?php echo htmlspecialchars(\$record['{$field['name']}']); ?></textarea>
                    </div>

HTML;
                    break;
                case 'select':
                    $formFields .= <<<HTML
                    <div class="mb-3">
                        <label for="{$field['name']}" class="form-label">{$label}</label>
                        <select class="form-control" id="{$field['name']}" name="{$field['name']}" {$required}>
                            <option value="">Select {$label}</option>
                            <!-- Add your options here with selected state -->
                        </select>
                    </div>

HTML;
                    break;
                default:
                    $formFields .= <<<HTML
                    <div class="mb-3">
                        <label for="{$field['name']}" class="form-label">{$label}</label>
                        <input type="{$field['html_type']}" class="form-control" id="{$field['name']}" name="{$field['name']}" value="<?php echo htmlspecialchars(\$record['{$field['name']}']); ?>" {$required}>
                    </div>

HTML;
            }
        }

        return <<<HTML
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-edit"></i> <?php echo \$title; ?></h3>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo \$moduleRoute; ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?php echo \$record['id']; ?>">
                    
{$formFields}                    
                    <div class="d-flex justify-content-between">
                        <a href="<?php echo \$moduleRoute; ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update {$this->namespace}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
HTML;
    }

    // CSS and JS templates
    private function getCssTemplate(): string
    {
        return <<<CSS
/* {$this->namespace} Module Styles */

.{$this->namespace}-container {
    padding: 20px 0;
}

.{$this->namespace}-card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: none;
    border-radius: 8px;
}

.{$this->namespace}-table {
    margin-top: 20px;
}

.{$this->namespace}-form {
    max-width: 600px;
    margin: 0 auto;
}

.{$this->namespace}-btn-group {
    gap: 5px;
}

/* Responsive design */
@media (max-width: 768px) {
    .{$this->namespace}-table {
        font-size: 14px;
    }
    
    .{$this->namespace}-btn-group {
        flex-direction: column;
    }
}

/* Flash messages */
.alert {
    border-radius: 6px;
    margin-bottom: 20px;
}

/* Loading states */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

.spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255,255,255,.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
CSS;
    }

    private function getJsTemplate(): string
    {
        return <<<JS
// {$this->namespace} Module JavaScript

document.addEventListener('DOMContentLoaded', function() {
    console.log('{$this->namespace} module loaded');
    
    // Initialize module
    {$this->namespace}Module.init();
});

const {$this->namespace}Module = {
    init: function() {
        this.bindEvents();
        this.initializeComponents();
    },
    
    bindEvents: function() {
        // Bind form submissions
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', this.handleFormSubmit.bind(this));
        });
        
        // Bind delete confirmations
        const deleteButtons = document.querySelectorAll('a[href*="action=delete"]');
        deleteButtons.forEach(button => {
            button.addEventListener('click', this.confirmDelete);
        });
    },
    
    initializeComponents: function() {
        // Initialize any JavaScript components
        this.initializeTooltips();
        this.initializeModals();
    },
    
    handleFormSubmit: function(event) {
        const form = event.target;
        const submitButton = form.querySelector('button[type="submit"]');
        
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner"></span> Processing...';
        }
        
        // Form validation can be added here
        return true;
    },
    
    confirmDelete: function(event) {
        if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
            event.preventDefault();
            return false;
        }
        return true;
    },
    
    initializeTooltips: function() {
        // Initialize Bootstrap tooltips if available
        if (typeof bootstrap !== 'undefined') {
            const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltips.forEach(tooltip => {
                new bootstrap.Tooltip(tooltip);
            });
        }
    },
    
    initializeModals: function() {
        // Initialize any modals
        if (typeof bootstrap !== 'undefined') {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                new bootstrap.Modal(modal);
            });
        }
    },
    
    // AJAX helpers
    ajax: {
        get: function(url, callback) {
            fetch(url)
                .then(response => response.json())
                .then(data => callback(data))
                .catch(error => console.error('Error:', error));
        },
        
        post: function(url, data, callback) {
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => callback(data))
            .catch(error => console.error('Error:', error));
        }
    },
    
    // Utility functions
    showAlert: function(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-\${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            \${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.container');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);
        }
    },
    
    hideAlert: function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.remove();
        });
    }
};

// Export for use in other scripts
window.{$this->namespace}Module = {$this->namespace}Module;
JS;
    }

    private function getApiDocsTemplate(): string
    {
        return <<<MD
# {$this->namespace} API Documentation

## Overview
This document describes the API endpoints available for the {$this->namespace} module.

## Base URL
```
{BASE_URL}/api/{$this->config['route_name']}
```

## Authentication
All API endpoints require authentication. Include session cookies or authorization headers.

## Endpoints

### GET /api/{$this->config['route_name']}
Get all {$this->namespace} records.

**Response:**
```json
[
    {
        "id": 1,
        "created_at": "2023-01-01 12:00:00",
        "updated_at": "2023-01-01 12:00:00"
    }
]
```

### GET /api/{$this->config['route_name']}?id=:id
Get a specific {$this->namespace} record.

**Parameters:**
- `id` (integer): The record ID

**Response:**
```json
{
    "id": 1,
    "created_at": "2023-01-01 12:00:00",
    "updated_at": "2023-01-01 12:00:00"
}
```

### POST /api/{$this->config['route_name']}
Create a new {$this->namespace} record.

**Request Body:**
```json
{
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "created_at": "2023-01-01 12:00:00",
        "updated_at": "2023-01-01 12:00:00"
    }
}
```

### PUT /api/{$this->config['route_name']}
Update an existing {$this->namespace} record.

**Request Body:**
```json
{
    "id": 1
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "created_at": "2023-01-01 12:00:00",
        "updated_at": "2023-01-01 12:00:00"
    }
}
```

### DELETE /api/{$this->config['route_name']}
Delete a {$this->namespace} record.

**Request Body:**
```json
{
    "id": 1
}
```

**Response:**
```json
{
    "success": true
}
```

## Error Responses
All endpoints may return error responses in the following format:

```json
{
    "error": "Error message description"
}
```

Common HTTP status codes:
- `400` - Bad Request
- `404` - Not Found
- `405` - Method Not Allowed
- `500` - Internal Server Error
MD;
    }

    // Additional template methods for auth and dashboard modules would go here...
    private function getLoginViewTemplate(): string
    {
        return "<!-- Login view template -->";
    }

    private function getRegisterViewTemplate(): string
    {
        return "<!-- Register view template -->";
    }

    private function getDashboardViewTemplate(): string
    {
        return "<!-- Dashboard view template -->";
    }
}




