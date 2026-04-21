<?php
class AuthController {
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = sanitize($_POST['email']);
            $password = $_POST['password'];
            
            $user = User::findByEmail($email);
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['last_activity'] = time();
                
                switch ($user['role']) {
                    case 'admin':
                        header('Location: index.php?page=admin.dashboard');
                        break;
                    case 'professor':
                        header('Location: index.php?page=professor.grades');
                        break;
                    case 'student':
                        header('Location: index.php?page=student.dashboard');
                        break;
                }
                exit;
            } else {
                flash('danger', 'Invalid email or password');
                header('Location: index.php?page=login');
                exit;
            }
        }
        
        include BASE_PATH . '/views/login.php';
    }
    
    public function logout() {
        session_destroy();
        header('Location: index.php?page=login');
        exit;
    }
}
