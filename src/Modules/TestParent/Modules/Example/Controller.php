<?php
namespace App\Modules\TestParent\Modules\Example;

use App\Common\Bmvc\BaseController;

/**
 * Example Submodule Controller
 * 
 * Demonstrates nested module functionality with auto-discovery
 */
class Controller extends BaseController
{
    public function display($reqRoute, $reqMet): void
    {
        echo "<h1>Example Submodule</h1>";
        echo "<p>This is a submodule within App\Modules\TestParent module.</p>";
        echo "<p>Route: /testparent/example</p>";
        echo "<p>Auto-discovered by InitModsImproved.php!</p>";
    }

    public function test($reqRoute, $reqMet): void
    {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Submodule test endpoint working!',
            'parent_module' => 'App\Modules\TestParent',
            'submodule' => 'Example',
            'route' => $reqRoute
        ]);
    }
}