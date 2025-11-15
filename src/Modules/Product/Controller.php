<?php
namespace App\Modules\Product;

use App\Common\Bmvc\BaseController;

/**
 * Enhanced App\Modules\Product CRUD Controller
 * 
 * Auto-discovered by InitModsImproved.php
 * Full CRUD operations: Create, Read, Update, Delete
 */
class Controller extends BaseController
{
    private $model;
    private $view;
    private array $fields;

    public function __construct()
    {
        $this->model = new Model();
        $this->view = new View();
        $this->fields = array (
  0 => 
  array (
    'name' => 'name',
    'sql_type' => 'VARCHAR(255)',
    'html_type' => 'text',
  ),
  1 => 
  array (
    'name' => 'description',
    'sql_type' => 'TEXT',
    'html_type' => 'textarea',
  ),
  2 => 
  array (
    'name' => 'price',
    'sql_type' => 'DECIMAL(10,2)',
    'html_type' => 'number',
  ),
  3 => 
  array (
    'name' => 'status',
    'sql_type' => 'ENUM("active","inactive")',
    'html_type' => 'select',
  ),
);
    }

    /**
     * Display list of all items (READ)
     */
    public function display($reqRoute, $reqMet): void
    {
        // Handle action-based routing
        $action = $_GET['action'] ?? 'index';
        
        switch ($action) {
            case 'create':
                $this->create($reqRoute, $reqMet);
                return;
            case 'edit':
                $this->edit($reqRoute, $reqMet);
                return;
            case 'delete':
                $this->delete($reqRoute, $reqMet);
                return;
            case 'store':
                $this->store($reqRoute, $reqMet);
                return;
            case 'update':
                $this->update($reqRoute, $reqMet);
                return;
            default:
                // Show list with pagination
                $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT) ?: 1;
                $pageSize = 10;
                
                $items = $this->model->getAllPaginated($page, $pageSize);
                $totalItems = $this->model->getTotalCount();
                $totalPages = ceil($totalItems / $pageSize);
                
                $data = [
                    'title' => 'App\Modules\Product Management',
                    'items' => $items,
                    'fields' => $this->fields,
                    'module' => 'App\Modules\Product',
                    'pagination' => [
                        'current_page' => $page,
                        'total_pages' => $totalPages,
                        'total_items' => $totalItems,
                        'page_size' => $pageSize
                    ]
                ];
                
                $this->view->render('index', $data);
        }
    }

    /**
     * Show create form
     */
    public function create($reqRoute, $reqMet): void
    {
        $data = [
            'title' => 'Create New App\Modules\Product',
            'fields' => $this->fields,
            'action' => 'store'
        ];
        
        $this->view->render('form', $data);
    }

    /**
     * Store new item (CREATE)
     */
    public function store($reqRoute, $reqMet): void
    {
        if ($reqMet !== 'POST') {
            header('Location: ' . BASE_URL . '/product');
            exit;
        }

        $data = $this->getPostData();
        
        if ($this->model->createItem($data)) {
            $_SESSION['success'] = 'App\Modules\Product created successfully!';
        } else {
            $_SESSION['error'] = 'Failed to create App\Modules\Product';
        }
        
        header('Location: ' . BASE_URL . '/product');
        exit;
    }

    /**
     * Show edit form
     */
    public function edit($reqRoute, $reqMet): void
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        if (!$id) {
            header('Location: ' . BASE_URL . '/product');
            exit;
        }

        $item = $this->model->getById($id);
        if (!$item) {
            $_SESSION['error'] = 'App\Modules\Product not found';
            header('Location: ' . BASE_URL . '/product');
            exit;
        }

        $data = [
            'title' => 'Edit App\Modules\Product',
            'fields' => $this->fields,
            'item' => $item,
            'action' => 'update'
        ];
        
        $this->view->render('form', $data);
    }

    /**
     * Update existing item (UPDATE)
     */
    public function update($reqRoute, $reqMet): void
    {
        if ($reqMet !== 'POST') {
            header('Location: ' . BASE_URL . '/product');
            exit;
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        if (!$id) {
            $_SESSION['error'] = 'Invalid ID';
            header('Location: ' . BASE_URL . '/product');
            exit;
        }

        $data = $this->getPostData();
        
        if ($this->model->updateItem($id, $data)) {
            $_SESSION['success'] = 'App\Modules\Product updated successfully!';
        } else {
            $_SESSION['error'] = 'Failed to update App\Modules\Product';
        }
        
        header('Location: ' . BASE_URL . '/product');
        exit;
    }

    /**
     * Delete item (DELETE)
     */
    public function delete($reqRoute, $reqMet): void
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        if (!$id) {
            header('Location: ' . BASE_URL . '/product');
            exit;
        }

        if ($this->model->deleteItem($id)) {
            $_SESSION['success'] = 'App\Modules\Product deleted successfully!';
        } else {
            $_SESSION['error'] = 'Failed to delete App\Modules\Product';
        }
        
        header('Location: ' . BASE_URL . '/product');
        exit;
    }

    /**
     * Extract POST data for configured fields with proper sanitization
     */
    private function getPostData(): array
    {
        $data = [];
        foreach ($this->fields as $field) {
            $fieldName = $field['name'];
            $htmlType = $field['html_type'];
            
            // Determine filter type based on html_type
            $filter = FILTER_SANITIZE_SPECIAL_CHARS;
            if (in_array($htmlType, ['number', 'range'])) {
                $filter = FILTER_SANITIZE_NUMBER_INT;
            } elseif ($htmlType === 'email') {
                $filter = FILTER_SANITIZE_EMAIL;
            }
            
            $data[$fieldName] = filter_input(INPUT_POST, $fieldName, $filter) ?? '';
        }
        return $data;
    }
}