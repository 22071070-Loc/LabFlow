<?php
session_start();

require_once __DIR__ . '/config/config.php';
require_once BASE_PATH . '/app/core/Database.php';
require_once BASE_PATH . '/app/core/Auth.php';
require_once BASE_PATH . '/app/core/Controller.php';
require_once BASE_PATH . '/app/core/Schema.php';
require_once BASE_PATH . '/app/models/BaseModel.php';
require_once BASE_PATH . '/app/models/AuthModel.php';
require_once BASE_PATH . '/app/models/BorrowModel.php';
require_once BASE_PATH . '/app/models/MaintenanceModel.php';
require_once BASE_PATH . '/app/models/DamageModel.php';
require_once BASE_PATH . '/app/controllers/CrudController.php';

$route = $_GET['route'] ?? (Auth::check() ? 'dashboard/index' : 'auth/login');
[$module, $action] = array_pad(explode('/', $route, 2), 2, 'index');

$map = [
    'auth' => 'AuthController',
    'dashboard' => 'DashboardController',
    'departments' => 'DepartmentsController',
    'users' => 'UsersController',
    'labs' => 'LabsController',
    'equipment_categories' => 'EquipmentCategoriesController',
    'suppliers' => 'SuppliersController',
    'equipment' => 'EquipmentController',
    'borrow_requests' => 'BorrowRequestsController',
    'borrow_records' => 'BorrowRecordsController',
    'maintenance_schedules' => 'MaintenanceSchedulesController',
    'maintenance_logs' => 'MaintenanceLogsController',
    'damage_reports' => 'DamageReportsController',
    'penalty_payments' => 'PenaltyPaymentsController',
];

if (!isset($map[$module])) {
    http_response_code(404);
    die('404 Module not found.');
}

$controllerClass = $map[$module];
$controllerFile = BASE_PATH . '/app/controllers/' . $controllerClass . '.php';
if (!file_exists($controllerFile)) {
    http_response_code(500);
    die('Controller file not found: ' . e($controllerFile));
}
require_once $controllerFile;

$controller = new $controllerClass();
$method = $action === 'view' ? 'viewAction' : $action;

if (!method_exists($controller, $method)) {
    http_response_code(404);
    die('404 Action not found.');
}

$controller->$method();
