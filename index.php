<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';


foreach (glob(__DIR__ . '/models/*.php') as $file) {
    require_once $file;
}


foreach (glob(__DIR__ . '/controllers/*.php') as $file) {
    require_once $file;
}

$page = $_GET['page'] ?? 'login';


switch (true) {
    case $page === 'login':
    case $page === 'logout':
        $controller = new AuthController();
        if ($page === 'login') $controller->login();
        else $controller->logout();
        break;
        
    case str_starts_with($page, 'admin.'):
        requireRole('admin');
        $controller = new AdminController();
        $action = explode('.', $page)[1];
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller->dashboard();
        }
        break;
        
    case str_starts_with($page, 'professor.'):
        requireRole('professor');
        $controller = new ProfessorController();
        $action = explode('.', $page)[1] ?? 'grades';
        if (method_exists($controller, $action)) {
            $controller->$action();
        }
        break;
        
    case str_starts_with($page, 'student.'):
        requireRole('student');
        $controller = new StudentController();
        $action = explode('.', $page)[1] ?? 'dashboard';
        if (method_exists($controller, $action)) {
            $controller->$action();
        }
        break;
        
    default:
        header('Location: index.php?page=login');
        exit;
}
