<?php
namespace App\Tools\CrudGenerator;

use PDO;
use PDOException;

class CrudModuleGenerator
{
    private string $moduleName;
    private array $fields;
    private string $tableName;
    private string $routeName;

    /**
     * Expected format for fields:
     * [
     *   ['name' => 'fieldname', 'sql_type' => 'VARCHAR(255)', 'html_type' => 'text'],
     *   ['name' => 'age', 'sql_type' => 'INT', 'html_type' => 'number'],
     *   ...
     * ]
     * sql_type defaults to VARCHAR(255) if not specified.
     * html_type defaults to 'text' if not specified.
     */
    private string $basePath = __DIR__ . '/../../modules/';

    public function __construct(string $moduleName, array $fields)
    {
        $this->moduleName = ucfirst($moduleName);
        $this->fields = $fields;
        $this->tableName = strtolower($moduleName) . 's'; // plural for table and view
        $this->routeName = strtolower($moduleName) . 's'; // use plural for route as well
    }

    public function generate(): void
    {
        $modulePath = $this->basePath . $this->moduleName . '/';
        $routesPath = $modulePath . 'routes/';
        $etcPath = $modulePath . 'etc/';

        // Create directories
        $this->createDir($modulePath);
        $this->createDir($routesPath);
        $this->createDir($etcPath);

        // Generate files
        $this->generateController($modulePath);
        $this->generateModel($modulePath);
        $this->generateView($modulePath);
        $this->generateRoutes($routesPath);

        // Update composer.json autoload and etc/InitMods.php
        $this->updateComposerAutoload();
        $this->updateInitMods();

        // Update composer.json autoload and etc/InitMods.php
        $this->updateComposerAutoload();
        $this->updateInitMods();

        echo "CRUD module '{$this->moduleName}' generated successfully.\n";
    }

    private function createDir(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    private function updateComposerAutoload(): void
    {
        $composerFile = __DIR__ . '/../../composer.json';
        if (!file_exists($composerFile)) {
            echo "composer.json not found.\n";
            return;
        }

        $composerJson = file_get_contents($composerFile);
        $composerData = json_decode($composerJson, true);
        if ($composerData === null) {
            echo "Failed to decode composer.json.\n";
            return;
        }

        $psr4 = $composerData['autoload']['psr-4'] ?? [];

        $moduleNamespace = $this->moduleName . '\\';
        $moduleRouteNamespace = $this->moduleName . '\\Routes\\';

        $modulePath = "modules/" . strtolower($this->moduleName) . "/";
        $moduleRoutePath = $modulePath . "routes/";

        // Add namespaces if not already present
        if (!array_key_exists($moduleNamespace, $psr4)) {
            $psr4[$moduleNamespace] = $modulePath;
        }
        if (!array_key_exists($moduleRouteNamespace, $psr4)) {
            $psr4[$moduleRouteNamespace] = $moduleRoutePath;
        }

        $composerData['autoload']['psr-4'] = $psr4;

        $newComposerJson = json_encode($composerData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($composerFile, $newComposerJson);

        echo "composer.json autoload updated with {$moduleNamespace} and {$moduleRouteNamespace}.\n";
    }

    private function updateInitMods(): void
    {
        $initModsFile = __DIR__ . '/../../etc/InitMods.php';
        if (!file_exists($initModsFile)) {
            echo "etc/InitMods.php not found.\n";
            return;
        }

        $content = file_get_contents($initModsFile);
        $moduleName = $this->moduleName;
        $moduleRouteClass = $moduleName . '\\Routes\\Routes';
        $useStatement = "use {$moduleRouteClass} as {$moduleName}Routes;";

        // Add use statement if not present
        if (strpos($content, $useStatement) === false) {
            // Insert use statement after last use statement block
            $pattern = '/(use [^;]+;\s*)+/';
            if (preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                $lastUsePos = $matches[0][1] + strlen($matches[0][0]);
                $content = substr_replace($content, $useStatement . "\n", $lastUsePos, 0);
            } else {
                // If no use statements found, insert after namespace declaration
                $patternNamespace = '/namespace [^;]+;/';
                if (preg_match($patternNamespace, $content, $matches, PREG_OFFSET_CAPTURE)) {
                    $pos = $matches[0][1] + strlen($matches[0][0]);
                    $content = substr_replace($content, "\n" . $useStatement . "\n", $pos, 0);
                } else {
                    // fallback: prepend at start
                    $content = "<?php\n" . $useStatement . "\n" . substr($content, 5);
                }
            }
        }

        // Add new module route instantiation in getModules() array if not present
        $moduleInstantiation = "new {$moduleName}Routes()";
        if (strpos($content, $moduleInstantiation) === false) {
            // Find the getModules() method and insert before closing bracket of array
            $pattern = '/(private function getModules\(\): array\s*\{\s*return \[)(.*?)(\];\s*\})/s';
            if (preg_match($pattern, $content, $matches)) {
                $modulesList = trim($matches[2]);
                if ($modulesList !== '') {
                    $modulesList .= ",\n            " . $moduleInstantiation;
                } else {
                    $modulesList = "            " . $moduleInstantiation;
                }
                $replacement = $matches[1] . "\n" . $modulesList . "\n        " . $matches[3];
                $content = preg_replace($pattern, $replacement, $content);
            }
        }

        file_put_contents($initModsFile, $content);

        echo "etc/InitMods.php updated with {$moduleName}Routes.\n";
    }

    private function generateController(string $modulePath): void
    {
        $className = $this->moduleName . '\\Controller';

        $fieldsSanitize = '';
        $fieldsArray = '';
        foreach ($this->fields as $field) {
            $fieldName = $field['name'] ?? $field;
            $htmlType = $field['html_type'] ?? 'text';

            // Determine filter type based on html_type for sanitization
            $filter = 'FILTER_SANITIZE_SPECIAL_CHARS';
            if (in_array($htmlType, ['number', 'range'])) {
                $filter = 'FILTER_SANITIZE_NUMBER_INT';
            } elseif ($htmlType === 'email') {
                $filter = 'FILTER_SANITIZE_EMAIL';
            }

            $fieldsSanitize .= "        \${$fieldName} = filter_input(INPUT_POST, '{$fieldName}', $filter);\n";
            $fieldsArray .= "            '{$fieldName}' => \${$fieldName},\n";
        }

        $content = <<<PHP
<?php
namespace {$this->moduleName};

class Controller
{
    private \$model;
    private \$view;
    private \$table = '{$this->tableName}';
    private \$moduleRoute = BASE_URL . '/{$this->routeName}';

    public function __construct()
    {
        \$this->model = new Model();
        \$this->view = new View();
    }

    public function display(\$reqRoute, \$reqMet)
    {
        if (isset(\$_SESSION["username"])) {
            \$this->selectAction(\$reqMet);
            echo \$reqMet . " " .  \$reqRoute . " ";
        } else {
            header('Location: ' . BASE_URL . '/');
        }
    }

    public function selectAction(\$reqMet)
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
            }
        } else {
            \$action = \$_GET['action'] ?? '';
            switch (\$action) {
                case 'read':
                    \$this->read();
                    break;
                case 'update':
                    \$this->renderUpdateForm();
                    break;
                case 'delete':
                    \$this->delete();
                    break;
                case 'form':
                    \$this->renderCreateForm();
                    break;
                default:
                    \$this->read();
                    break;
            }
        }
    }

    private function create()
    {
{$fieldsSanitize}
        \$data = [
{$fieldsArray}        ];

        \$id = \$this->model->create(\$data, \$this->table);
        if (\$id) {
            echo "{$this->moduleName} created successfully! (ID: {\$id})";
            header('Location: ' . \$this->moduleRoute . '?action=read');
        } else {
            echo "Error creating {$this->moduleName}.";
            sleep(5);
            header('Location: ' . \$this->moduleRoute . '?action=read');
            
        }
    }

    private function read()
    {
        \$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT) ?: 1;
        \$pageSize = 5;
        \$records = \$this->model->readWithPagination(\$this->table, \$page, \$pageSize);
        \$totalRecords = count(\$this->model->readAll(\$this->table));
        \$totalPages = ceil(\$totalRecords / \$pageSize);
        \$this->view->renderReadTable(\$records, \$page, \$totalPages, \$this->moduleRoute);
    }

    private function update()
    {
        \$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
{$fieldsSanitize}
        \$data = [
{$fieldsArray}        ];

        \$success = \$this->model->update(\$id, \$data, \$this->table);
        if (\$success) {
            echo "{$this->moduleName} updated successfully!";
            header('Location: ' . \$this->moduleRoute . '?action=read');
        } else {
            \$route = \$this->moduleRoute . '?action=read';
            echo "<script language='javascript'>";
            echo " var name = '\$route';
            alert('Error updating data for {$this->moduleName}.');
            window.location.href=name;
            </script>";
        }
    }

    private function delete()
    {
        \$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        \$success = \$this->model->delete(\$id, \$this->table);
        if (\$success) {
            echo "{$this->moduleName} deleted successfully!";
            header('Location: ' . \$this->moduleRoute . '?action=read');
        } else {
             \$route = \$this->moduleRoute . '?action=read';
            echo "<script language='javascript'>";
            echo " var name = '\$route';
            alert('Error deleting data for {$this->moduleName}.');
            window.location.href=name;
            </script>";
        }
    }

    private function renderUpdateForm()
    {
        \$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        \$record = \$this->model->read(\$id, \$this->table);
        if (\$record) {
            \$this->view->renderUpdateForm(\$record, \$this->moduleRoute);
        } else {
            echo "{$this->moduleName} not found.";
        }
    }

    private function renderCreateForm()
    {
        \$this->view->renderCreateForm(\$this->moduleRoute);
    }
}
PHP;

        file_put_contents($modulePath . 'Controller.php', $content);
    }

    private function generateModel(string $modulePath): void
    {
        $className = $this->moduleName . '\\Model';
        $baseModel = 'Common\\Bmvc\\BaseModel';

        $content = <<<PHP
<?php
namespace {$this->moduleName};

use {$baseModel};

class Model extends BaseModel
{
    private \$table = '{$this->tableName}';

    public function create(array \$data, string \$table = null)
    {
        return parent::create(\$data, \$table ?? \$this->table);
    }

    public function read(int \$id, string \$table = null)
    {
        return parent::read(\$id, \$table ?? \$this->table);
    }

    public function readAll(string \$table = null)
    {
        return parent::readAll(\$table ?? \$this->table);
    }

    public function readWithPagination(string \$table = null, int \$page = 1, int \$pageSize = 5)
    {
        return parent::readWithPagination(\$table ?? \$this->table, \$page, \$pageSize);
    }

    public function update(int \$id, array \$data, string \$table = null)
    {
        return parent::update(\$id, \$data, \$table ?? \$this->table);
    }

    public function delete(int \$id, string \$table = null)
    {
        return parent::delete(\$id, \$table ?? \$this->table);
    }
}
PHP;

        file_put_contents($modulePath . 'Model.php', $content);
    }

    private function generateView(string $modulePath): void
    {
        $className = $this->moduleName . '\\View';

        $content = <<<PHP
<?php
namespace {$this->moduleName};

use App\Common\Bmvc\BaseView;

class View
{
    public \$title = "Create {$this->moduleName}";

    public function renderCreateForm(string \$moduleRoute)
    {
        \$view = new BaseView();
        \$this->title = "Create {$this->moduleName}s";
        \$view->startHead(\$this->title);
        // Add any additional head content here if needed
        \$view->endHead();
        \$view->startBody(\$this->title);
        echo '<div class="container">';
        echo '<form method="post" action="' . \$moduleRoute . '">';
        echo '<input type="hidden" name="action" value="create">';
PHP;

        foreach ($this->fields as $field) {
            $fieldName = $field['name'] ?? $field;
            $htmlType = $field['html_type'] ?? 'text';

            $content .= "        echo '<label for=\"$fieldName\">" . ucfirst($fieldName) . ":</label>';\n";

            if ($htmlType === 'select' && isset($field['options'])) {
                $content .= "        echo '<select name=\"$fieldName\" required>';\n";
                foreach ($field['options'] as $option) {
                    $value = htmlspecialchars($option['value']);
                    $label = htmlspecialchars($option['label']);
                    $content .= "        echo '<option value=\"$value\">$label</option>';\n";
                }
                $content .= "        echo '</select>';\n";
            } elseif ($htmlType === 'radio' && isset($field['options'])) {
                foreach ($field['options'] as $option) {
                    $value = htmlspecialchars($option['value']);
                    $label = htmlspecialchars($option['label']);
                    $content .= "        echo '<input type=\"radio\" name=\"$fieldName\" value=\"$value\" required> $label';\n";
                }
            } else {
                $content .= "        echo '<input type=\"$htmlType\" name=\"$fieldName\" required>';\n";
            }
            $content .= "        echo '<br>';\n";
        }

        $content .= <<<PHP
        echo '<input type="submit" value="Create {$this->moduleName}">';
        echo '</form>';
        echo '</div>';
        \$view->endBody();
        \$view->startFooter();
        \$view->endFooter();
    }

    public function renderReadTable(array \$records, int \$currentPage, int \$totalPages, string \$moduleRoute)
    {
        \$view = new BaseView();
        \$this->title = "Read {$this->moduleName}s";
        \$view->startHead(\$this->title);
        // Add any additional head content here if needed
        \$view->endHead();
        \$view->startBody(\$this->title);
        echo '<div class="container">';
        echo '<a href="' . \$moduleRoute . '?action=form">Create New {$this->moduleName}</a><br><br>';
        echo '<table border="1" cellpadding="5" cellspacing="0">';
        echo '<tr>';

PHP;

        foreach ($this->fields as $field) {
            $fieldName = $field['name'] ?? $field;
            $content .= "        echo '<th>" . ucfirst($fieldName) . "</th>';\n";
        }

        $content .= <<<PHP
        echo '<th>Actions</th>';
        echo '</tr>';

        foreach (\$records as \$record) {
            echo '<tr>';
PHP;

        foreach ($this->fields as $field) {
            $fieldName = $field['name'] ?? $field;
            $content .= "            echo '<td>' . htmlspecialchars(\$record['$fieldName']) . '</td>';\n";
        }

        $content .= <<<PHP
            echo '<td>';
            echo '<a href="' . \$moduleRoute . '?action=update&id=' . \$record['id'] . '">Edit</a> | ';
            echo '<a href="' . \$moduleRoute . '?action=delete&id=' . \$record['id'] . '" onclick="return confirm(\'Are you sure?\')">Delete</a>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';

        // Pagination links
        echo '<div>';
        if (\$currentPage > 1) {
            echo '<a href="' . \$moduleRoute . '?action=read&page=' . (\$currentPage - 1) . '">Previous</a> ';
        }
        if (\$currentPage < \$totalPages) {
            echo '<a href="' . \$moduleRoute . '?action=read&page=' . (\$currentPage + 1) . '">Next</a>';
        }
        echo '</div>';
        echo '</div>';
        \$view->startFooter();
        \$view->endFooter();
    }

    public function renderUpdateForm(array \$record, string \$moduleRoute)
    {
        \$view = new BaseView();
        \$this->title = "Update {$this->moduleName}s";
        \$view->startHead(\$this->title);
        // Add any additional head content here if needed
        \$view->endHead();
        \$view->startBody(\$this->title);
        echo '<div class="container">';
        echo '<form method="post" action="' . \$moduleRoute . '">';
        echo '<input type="hidden" name="action" value="update">';
        echo '<input type="hidden" name="id" value="' . htmlspecialchars(\$record['id']) . '">';
PHP;

        foreach ($this->fields as $field) {
            $fieldName = $field['name'] ?? $field;
            $htmlType = $field['html_type'] ?? 'text';

            $content .= "        echo '<label for=\"$fieldName\">" . ucfirst($fieldName) . ":</label>';\n";
            $content .= "        echo '<input type=\"$htmlType\" name=\"$fieldName\" value=\"' . htmlspecialchars(\$record['$fieldName']) . '\" required>';\n";
            $content .= "        echo '<br>';\n";
        }

        $content .= <<<PHP
        echo '<input type="submit" value="Update {$this->moduleName}">';
        echo '</form>';
        echo '</div>';
        \$view->endBody();
        \$view->startFooter();
        \$view->endFooter();
    }
}
PHP;

        file_put_contents($modulePath . 'View.php', $content);
    }

    private function generateRoutes(string $routesPath): void
    {
        $className = $this->moduleName . '\\Routes';

        $content = <<<PHP
<?php
namespace {$this->moduleName}\Routes;

use {$this->moduleName}\Controller;

class Routes
{
    public function routes(\$router)
    {
        \$router->addRoute('/{$this->tableName}', Controller::class, 'display');
    }
}
PHP;

        file_put_contents($routesPath . 'Routes.php', $content);
    }

    // New method to create database table for the new CRUD module
    public function createTable(): void
    {
        $tableName = strtolower($this->moduleName) . 's';

        // Build SQL for table creation
        $fieldsSql = "id INT AUTO_INCREMENT PRIMARY KEY, ";
        foreach ($this->fields as $field) {
            $fieldName = $field['name'] ?? $field;
            $sqlType = $field['sql_type'] ?? 'VARCHAR(255)';
            $fieldsSql .= "$fieldName $sqlType NOT NULL, ";
        }
        $fieldsSql = rtrim($fieldsSql, ', ');

        $sql = "CREATE TABLE IF NOT EXISTS $tableName ($fieldsSql) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        echo "\nSQL for table creation (you can run this manually):\n";
        echo $sql . "\n";

        // Try to create table if PDO MySQL is available
        if (extension_loaded('pdo_mysql')) {
            try {
                // Database connection parameters - adjust if needed
                $host = '127.0.0.1';
                $db   = 'test';
                $user = 'root';
                $pass = '';
                $charset = 'utf8mb4';

                $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];

                $pdo = new PDO($dsn, $user, $pass, $options);
                $pdo->exec($sql);
                echo "Table '$tableName' created or already exists.\n";
            } catch (PDOException $e) {
                echo "Database error: " . $e->getMessage() . "\n";
                echo "Please create the table manually using the SQL above.\n";
            }
        } else {
            echo "PDO MySQL driver not installed. Please create the table manually using the SQL above.\n";
        }
    }
}
?>




