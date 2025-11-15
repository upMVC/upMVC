<?php
namespace App\Modules\TestDashboard;

use App\Common\Bmvc\BaseController;

/**
 * Enhanced App\Modules\TestDashboard Dashboard Controller
 * 
 * Auto-discovered by InitModsImproved.php
 */
class Controller extends BaseController
{
    private $model;
    private $view;

    public function __construct()
    {
        $this->model = new Model();
        $this->view = new View();
    }

    /**
     * Display dashboard - auto-routed
     */
    public function display($reqRoute, $reqMet): void
    {
        $data = [
            'title' => 'App\Modules\TestDashboard Dashboard',
            'stats' => $this->model->getDashboardStats(),
            'recent_items' => $this->model->getRecentItems(5),
            'route_info' => [
                'current_route' => $reqRoute,
                'method' => $reqMet,
                'module' => 'App\Modules\TestDashboard'
            ]
        ];
        
        $this->view->render('dashboard', $data);
    }
}