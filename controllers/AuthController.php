<?php
/**
 * CLUBINHO PET - Controller Auth
 * Autenticação e registro
 * 
 * @package ClubinhoPet
 * @version 1.0.0
 */

class AuthController {
    
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Login
     */
    public function login() {
        // Se já está logado, redirecionar
        if (is_logged_in()) {
            redirect(BASE_URL . '/dashboard');
            return;
        }
        
        $error = '';
        $redirect = $_GET['redirect'] ?? 'dashboard';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Autenticar
            $user = $this->userModel->authenticate($email, $password);
            
            if ($user) {
                // Fazer login
                $this->userModel->login($user);
                
                // Redirecionar
                redirect(BASE_URL . '/' . $redirect);
                return;
            } else {
                $error = 'E-mail ou senha inválidos';
            }
        }
        
        require_once __DIR__ . '/../views/auth/login.php';
    }
    
    /**
     * Registro
     */
    public function register() {
        // Se já está logado, redirecionar
        if (is_logged_in()) {
            redirect(BASE_URL . '/dashboard');
            return;
        }
        
        $error = '';
        $success = false;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar dados
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';
            
            if (empty($name) || empty($email) || empty($password)) {
                $error = 'Preencha todos os campos obrigatórios';
            } elseif (!validate_email($email)) {
                $error = 'E-mail inválido';
            } elseif ($password !== $passwordConfirm) {
                $error = 'As senhas não coincidem';
            } elseif (strlen($password) < 6) {
                $error = 'A senha deve ter no mínimo 6 caracteres';
            } elseif ($this->userModel->emailExists($email)) {
                $error = 'Este e-mail já está cadastrado';
            } else {
                // Criar usuário
                $userId = $this->userModel->create([
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'phone' => $_POST['phone'] ?? ''
                ]);
                
                if ($userId) {
                    $success = true;
                    
                    // Fazer login automaticamente
                    $user = $this->userModel->findById($userId);
                    $this->userModel->login($user);
                    
                    // Redirecionar para dashboard
                    redirect(BASE_URL . '/dashboard');
                    return;
                } else {
                    $error = 'Erro ao criar conta. Tente novamente.';
                }
            }
        }
        
        require_once __DIR__ . '/../views/auth/register.php';
    }
    
    /**
     * Logout
     */
    public function logout() {
        $this->userModel->logout();
        redirect(BASE_URL);
    }
    
    /**
     * Esqueci minha senha
     */
    public function forgotPassword() {
        $success = false;
        $error = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            
            if (empty($email)) {
                $error = 'Informe seu e-mail';
            } elseif (!validate_email($email)) {
                $error = 'E-mail inválido';
            } else {
                // Criar token de recuperação
                $result = $this->userModel->createPasswordResetToken($email);
                
                if ($result) {
                    $success = true;
                } else {
                    $error = 'E-mail não encontrado';
                }
            }
        }
        
        require_once __DIR__ . '/../views/auth/forgot-password.php';
    }
    
    /**
     * Redefinir senha
     */
    public function resetPassword() {
        $token = $_GET['token'] ?? '';
        $success = false;
        $error = '';
        
        // Validar token
        if (empty($token) || !$this->userModel->validatePasswordResetToken($token)) {
            $error = 'Link inválido ou expirado';
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';
            
            if (empty($password)) {
                $error = 'Informe a nova senha';
            } elseif ($password !== $passwordConfirm) {
                $error = 'As senhas não coincidem';
            } elseif (strlen($password) < 6) {
                $error = 'A senha deve ter no mínimo 6 caracteres';
            } else {
                // Redefinir senha
                $result = $this->userModel->resetPassword($token, $password);
                
                if ($result) {
                    $success = true;
                } else {
                    $error = 'Erro ao redefinir senha. Tente novamente.';
                }
            }
        }
        
        require_once __DIR__ . '/../views/auth/reset-password.php';
    }
}
