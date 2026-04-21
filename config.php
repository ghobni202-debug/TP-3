<?php
session_start();

define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', 'http://localhost/gpa-system');


function requireRole($expected) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        session_destroy();
        header('Location: ' . BASE_URL . '/index.php?page=login');
        exit;
    }
    
    
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        session_destroy();
        header('Location: ' . BASE_URL . '/index.php?page=login');
        exit;
    }
    
    if ($_SESSION['role'] !== $expected) {
        http_response_code(403);
        echo "Access Denied";
        exit;
    }
    
    $_SESSION['last_activity'] = time();
}


function flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}


function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
