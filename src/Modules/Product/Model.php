<?php
namespace App\Modules\Product;

use App\Common\Bmvc\BaseModel;

/**
 * Enhanced App\Modules\Product CRUD Model
 * 
 * Full database operations with graceful fallback
 */
class Model extends BaseModel
{
    protected $table = 'products';
    protected $enableCaching = true;
    private array $configuredFields = array (
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

    public function __construct()
    {
        $this->enableCaching = \App\Etc\Config\Environment::get('ROUTE_USE_CACHE', 'true') === 'true';
    }

    /**
     * Get all items
     */
    public function getAll(): array
    {
        if (!$this->checkConnection()) {
            return $this->getDemoData();
        }

        try {
            return $this->readAll($this->table) ?? [];
        } catch (\Exception $e) {
            error_log("Error reading App\Modules\Product: " . $e->getMessage());
            return $this->getDemoData();
        }
    }

    /**
     * Get paginated items
     */
    public function getAllPaginated(int $page = 1, int $pageSize = 10): array
    {
        if (!$this->checkConnection()) {
            $demoData = $this->getDemoData();
            return array_slice($demoData, ($page - 1) * $pageSize, $pageSize);
        }

        try {
            return $this->readWithPagination($this->table, $page, $pageSize) ?? [];
        } catch (\Exception $e) {
            error_log("Error reading App\Modules\Product: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total count for pagination
     */
    public function getTotalCount(): int
    {
        if (!$this->checkConnection()) {
            return count($this->getDemoData());
        }

        try {
            $all = $this->readAll($this->table) ?? [];
            return count($all);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get item by ID
     */
    public function getById(int $id): ?array
    {
        if (!$this->checkConnection()) {
            $demoData = $this->getDemoData();
            foreach ($demoData as $item) {
                if ($item['id'] == $id) return $item;
            }
            return null;
        }

        try {
            return $this->read($id, $this->table);
        } catch (\Exception $e) {
            error_log("Error reading App\Modules\Product: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new item (calls parent with table)
     */
    public function createItem(array $data): bool
    {
        if (!$this->checkConnection()) {
            $_SESSION['warning'] = 'Demo mode: Database not connected. Changes will not be saved.';
            return true; // Simulate success
        }

        try {
            $result = parent::create($data, $this->table);
            return $result !== false;
        } catch (\Exception $e) {
            error_log("Error creating App\Modules\Product: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update existing item (calls parent with table)
     */
    public function updateItem(int $id, array $data): bool
    {
        if (!$this->checkConnection()) {
            $_SESSION['warning'] = 'Demo mode: Database not connected. Changes will not be saved.';
            return true; // Simulate success
        }

        try {
            return parent::update($id, $data, $this->table);
        } catch (\Exception $e) {
            error_log("Error updating App\Modules\Product: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete item (calls parent with table)
     */
    public function deleteItem(int $id): bool
    {
        if (!$this->checkConnection()) {
            $_SESSION['warning'] = 'Demo mode: Database not connected. Changes will not be saved.';
            return true; // Simulate success
        }

        try {
            return parent::delete($id, $this->table);
        } catch (\Exception $e) {
            error_log("Error deleting App\Modules\Product: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if database connection is available
     */
    private function checkConnection(): bool
    {
        return $this->conn !== null && $this->conn instanceof \PDO;
    }

    /**
     * Get demo data for testing without database
     */
    private function getDemoData(): array
    {
        $demoData = [];
        for ($i = 1; $i <= 3; $i++) {
            $row = ['id' => $i, 'created_at' => date('Y-m-d H:i:s')];
            
            // Generate demo data for each configured field
            foreach ($this->configuredFields as $field) {
                $fieldName = $field['name'];
                
                if (stripos($fieldName, 'name') !== false || stripos($fieldName, 'title') !== false) {
                    $row[$fieldName] = "Demo {$fieldName} {$i}";
                } elseif (stripos($fieldName, 'description') !== false) {
                    $row[$fieldName] = "This is demo data. Configure database in .env to persist changes.";
                } elseif (stripos($fieldName, 'price') !== false) {
                    $row[$fieldName] = number_format(rand(10, 999), 2);
                } elseif (stripos($fieldName, 'status') !== false) {
                    $row[$fieldName] = $i === 3 ? 'inactive' : 'active';
                } else {
                    $row[$fieldName] = "Sample {$i}";
                }
            }
            
            $demoData[] = $row;
        }
        
        return $demoData;
    }
}