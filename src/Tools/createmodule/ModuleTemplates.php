<?php
namespace App\Tools\CreateModule;

class ModuleTemplates {
    public static function getApiControllerTemplate(string $moduleName): string {
        return <<<PHP
<?php
namespace {$moduleName};

use App\Common\\Bmvc\\BaseController;

class Controller extends BaseController {
    private \$model;
    private \$table;

    public function __construct() {
        \$this->model = new Model();
        \$this->table = strtolower('{$moduleName}s');
    }

    public function display(\$reqRoute, \$reqMet) {
        header('Content-Type: application/json');
        
        switch (\$reqMet) {
            case 'GET':
                \$this->handleGet();
                break;
            case 'POST':
                \$this->handlePost();
                break;
            case 'PUT':
                \$this->handlePut();
                break;
            case 'DELETE':
                \$this->handleDelete();
                break;
            default:
                \$this->sendResponse(405, ['error' => 'Method not allowed']);
        }
    }

    private function handleGet() {
        if (isset(\$_GET['id'])) {
            \$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            \$item = \$this->model->read(\$id, \$this->table);
            \$this->sendResponse(200, \$item ?? ['error' => 'Not found']);
        } else {
            \$items = \$this->model->readAll(\$this->table);
            \$this->sendResponse(200, \$items);
        }
    }

    private function handlePost() {
        \$data = json_decode(file_get_contents('php://input'), true);
        if (\$data) {
            \$id = \$this->model->create(\$data, \$this->table);
            if (\$id) {
                \$this->sendResponse(201, ['id' => \$id, 'message' => 'Created successfully']);
            }
        }
        \$this->sendResponse(400, ['error' => 'Invalid data']);
    }

    private function handlePut() {
        \$data = json_decode(file_get_contents('php://input'), true);
        if (isset(\$data['id'])) {
            \$success = \$this->model->update(\$data['id'], \$data, \$this->table);
            if (\$success) {
                \$this->sendResponse(200, ['message' => 'Updated successfully']);
            }
        }
        \$this->sendResponse(400, ['error' => 'Invalid data']);
    }

    private function handleDelete() {
        if (isset(\$_GET['id'])) {
            \$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            \$success = \$this->model->delete(\$id, \$this->table);
            if (\$success) {
                \$this->sendResponse(200, ['message' => 'Deleted successfully']);
            }
        }
        \$this->sendResponse(400, ['error' => 'Invalid ID']);
    }

    private function sendResponse(int \$statusCode, array \$data) {
        http_response_code(\$statusCode);
        echo json_encode(\$data);
        exit;
    }
}
PHP;
    }

    public static function getReactControllerTemplate(string $moduleName): string {
        return <<<PHP
<?php
namespace {$moduleName};

use App\Common\\Bmvc\\BaseController;

class Controller extends BaseController {
    private \$model;

    public function __construct() {
        \$this->model = new Model();
    }

    public function display(\$reqRoute, \$reqMet) {
        // Serve React app
        \$view = new View();
        \$view->renderReactApp();
    }

    public function api(\$reqRoute, \$reqMet) {
        // Handle API requests for React
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');

        if (\$reqMet === 'OPTIONS') {
            exit(0);
        }

        // Handle API requests here
        // Add your API logic
    }
}
PHP;
    }

    public static function getReactViewTemplate(string $moduleName): string {
        return <<<PHP
<?php
namespace {$moduleName};

use App\Common\\Bmvc\\BaseView;

class View extends BaseView {
    public function renderReactApp() {
        // Set headers for React app
        header('X-Content-Type-Options: nosniff');
        
        // Include React app
        include __DIR__ . '/etc/index.html';
    }
}
PHP;
    }

    public static function getReactRoutesTemplate(string $moduleName): string {
        return <<<PHP
<?php
namespace {$moduleName}\\Routes;

use App\Etc\\Router;
use {$moduleName}\\Controller;

class Routes {
    public function Routes(Router \$router): void {
        // Main React app route
        \$router->addRoute('/{$moduleName}', Controller::class, 'display');
        
        // API routes for React
        \$router->addRoute('/{$moduleName}/api', Controller::class, 'api');
    }
}
PHP;
    }

    public static function getReactIndexTemplate(): string {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>React App</title>
    <script src="https://unpkg.com/react@17/umd/react.development.js"></script>
    <script src="https://unpkg.com/react-dom@17/umd/react-dom.development.js"></script>
    <script src="https://unpkg.com/babel-standalone@6.26.0/babel.min.js"></script>
</head>
<body>
    <div id="root"></div>
    <script type="text/babel" src="/modules/{MODULE_NAME}/etc/app.js"></script>
</body>
</html>
HTML;
    }

    public static function getReactAppTemplate(): string {
        return <<<JAVASCRIPT
// Basic React component
const App = () => {
    const [data, setData] = React.useState(null);

    React.useEffect(() => {
        // Add your data fetching logic here
        console.log('React app mounted');
    }, []);

    return (
        <div>
            <h1>Welcome to React Module</h1>
            <p>Start building your React components here!</p>
        </div>
    );
};

// Render the app
ReactDOM.render(<App />, document.getElementById('root'));
JAVASCRIPT;
    }
}





