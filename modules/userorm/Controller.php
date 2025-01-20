<?php

namespace Userorm;

use Common\Bmvc\BaseControllerOrm;

class Controller extends BaseControllerOrm
{
    private $model;
    private $table = 'users';
    private $viewPath = 'userorm/views/';

    public function __construct()
    {
        // parent::__construct();
        $this->model = new Model();
    }


    public function display($reqRoute, $reqMet)
    {
        $needle = $reqRoute;

        if (isset($_SESSION["logged"])) {
            $this->switch($reqRoute, $reqMet);
        } else {
            
            header('Location: ' . BASE_URL . '/');
            //$view->notLoggedIn();
        }
    }

    private function switch($reqRoute, $reqMet)
    {
        $needle = $reqRoute;
        //echo $needle;
        //echo $reqMet;
        // print_r($_GET);
        $newView = new View();
        $newView->header();
        switch (true) {
            case stristr($needle, 'create'):
                $this->create();
                break;
            case stristr($needle, 'edit'):
                $this->edit($reqRoute, $reqMet);
                break;
            case stristr($needle, 'update'):
                $this->update($reqRoute, $reqMet);
                break;
            case stristr($needle, 'delete'):
                $this->delete($reqRoute, $reqMet);
                break;
            case stristr($needle, 'index'):
                $this->index();
                break;
            case stristr($needle, 'store'):
                $this->store($reqRoute, $reqMet);
                break;
            default:
                $this->index();
        }
        $newView->footer();
    }

    /**
     * Display users list with pagination
     */
    private function index()
    {

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $pageSize = 3;

        $users = $this->model->getUsersWithPagination($this->table, $page, $pageSize);

        return $this->view($this->viewPath . 'list', [
            'users' => $users['data'],
            'pagination' => [
                'current' => $page,
                'total' => $users['total_pages'],
                'hasMore' => $users['has_more']
            ]
        ]);
    }

    /**
     * Display user creation form
     */
    private function create()
    {
        return $this->view($this->viewPath . 'create');
    }

    /**
     * Store new user
     */
    private function store($reqRoute, $reqMet)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $array = \explode('/', trim($reqRoute, '/'));
            return $this->redirect($array[0], $reqMet);
        }

        $userData = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $result = $this->model->createUser($userData, $this->table);

        if ($result) {
            $_SESSION['success'] = 'User created successfully';
        } else {
            $_SESSION['error'] = 'Error creating user';
        }
        $array = \explode('/', trim($reqRoute, '/'));
        return $this->redirect($array[0], $reqMet);
    }

    /**
     * Display user edit form
     */
    private function edit($reqRoute, $reqMet)
    {
        //echo $reqRoute;
        //print_r($_GET);
        $id = $_GET['param'];

        $user = $this->model->getUserById($id, $this->table);
        //print_r($user);

        if (!$user) {
            $_SESSION['error'] = 'User not found';
            $array = \explode('/', trim($reqRoute, '/'));
            return $this->redirect($array[0], $reqMet);
        }

        return $this->view($this->viewPath . 'edit', ['user' => $user]);
    }

    /**
     * Update user
     */
    private function update($reqRoute, $reqMet)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect($reqRoute, $reqMet);
        }
        $id = $_GET['param'];
        $userData = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (!empty($_POST['password'])) {
            $userData['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        $result = $this->model->updateUser($id, $userData, $this->table);

        if ($result) {
            $_SESSION['success'] = 'User updated successfully';
        } else {
            $_SESSION['error'] = 'Error updating user';
        }
        $array = \explode('/', trim($reqRoute, '/'));
        return $this->redirect($array[0], $reqMet);
    }

    /**
     * Delete user
     */
    private function delete($reqRoute, $reqMet)
    {
        $id = $_GET['param'];
        $result = $this->model->deleteUser($id, $this->table);

        if ($result) {
            $_SESSION['success'] = 'User deleted successfully';
        } else {
            $_SESSION['error'] = 'Error deleting user';
        }

        $array = \explode('/', trim($reqRoute, '/'));
        return $this->redirect($array[0], $reqMet);
    }
}
