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
                // Show list
                $items = $this->model->getAll();
                
                $data = [
                    'title' => 'App\Modules\Product Management',
                    'items' => $items,
                    'fields' => $this->fields,
                    'module' => 'App\Modules\Product'
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
            header('Location: /' . strtolower('App\Modules\Product'));
            exit;
        }

        $data = $this->getPostData();
        
        if ($this->model->create($data)) {
            $_SESSION['success'] = 'App\Modules\Product created successfully!';
        } else {
            $_SESSION['error'] = 'Failed to create App\Modules\Product';
        }
        
        header('Location: /' . strtolower('App\Modules\Product'));
        exit;
    }

    /**
     * Show edit form
     */
    public function edit($reqRoute, $reqMet): void
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: /' . strtolower('App\Modules\Product'));
            exit;
        }

        $item = $this->model->getById($id);
        if (!$item) {
            $_SESSION['error'] = 'App\Modules\Product not found';
            header('Location: /' . strtolower('App\Modules\Product'));
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
            header('Location: /' . strtolower('App\Modules\Product'));
            exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Invalid ID';
            header('Location: /' . strtolower('App\Modules\Product'));
            exit;
        }

        $data = $this->getPostData();
        
        if ($this->model->update($id, $data)) {
            $_SESSION['success'] = 'App\Modules\Product updated successfully!';
        } else {
            $_SESSION['error'] = 'Failed to update App\Modules\Product';
        }
        
        header('Location: /' . strtolower('App\Modules\Product'));
        exit;
    }

    /**
     * Delete item (DELETE)
     */
    public function delete($reqRoute, $reqMet): void
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: /' . strtolower('App\Modules\Product'));
            exit;
        }

        if ($this->model->delete($id)) {
            $_SESSION['success'] = 'App\Modules\Product deleted successfully!';
        } else {
            $_SESSION['error'] = 'Failed to delete App\Modules\Product';
        }
        
        header('Location: /' . strtolower('App\Modules\Product'));
        exit;
    }

    /**
     * Extract POST data for configured fields
     */
    private function getPostData(): array
    {
        $data = [];
        foreach ($this->fields as $field) {
            $fieldName = $field['name'];
            $data[$fieldName] = $_POST[$fieldName] ?? '';
        }
        return $data;
    }
}