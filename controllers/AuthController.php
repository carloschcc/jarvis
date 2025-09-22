<?php
/**
 * Controller de Autenticação
 */

class AuthController {
    private $authModel;
    
    public function __construct() {
        $this->authModel = new AuthModel();
    }
    
    /**
     * Página de login
     */
    public function index() {
        // Se já estiver logado, redirecionar para dashboard
        if ($this->authModel->isLoggedIn() && !$this->authModel->isSessionExpired()) {
            header('Location: index.php?page=dashboard');
            exit;
        }
        
        // Se sessão expirou, limpar e mostrar mensagem
        if ($this->authModel->isSessionExpired()) {
            $this->authModel->logout();
            $data['error'] = 'Sua sessão expirou. Faça login novamente.';
        }
        
        $data = [
            'title' => 'Login - ' . APP_NAME,
            'app_name' => APP_NAME,
            'app_version' => APP_VERSION,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->loadView('auth/login', $data);
    }
    
    /**
     * Processar login
     */
    public function login() {
        header('Content-Type: application/json');
        
        try {
            // Verificar método POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método não permitido');
            }
            
            // Verificar CSRF token
            if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
                throw new Exception('Token de segurança inválido');
            }
            
            // Validar dados de entrada
            $username = sanitizeInput($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                throw new Exception('Usuário e senha são obrigatórios');
            }
            
            // Tentar autenticar
            $result = $this->authModel->authenticate($username, $password);
            
            echo json_encode($result);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Logout
     */
    public function logout() {
        try {
            $result = $this->authModel->logout();
            header('Location: index.php?page=login&message=logout_success');
            exit;
        } catch (Exception $e) {
            header('Location: index.php?page=login&error=' . urlencode($e->getMessage()));
            exit;
        }
    }
    
    /**
     * Verificar status da sessão (AJAX)
     */
    public function checkSession() {
        header('Content-Type: application/json');
        
        $user = $this->authModel->getCurrentUser();
        
        echo json_encode([
            'logged_in' => $this->authModel->isLoggedIn(),
            'expired' => $this->authModel->isSessionExpired(),
            'user' => $user,
            'remaining_time' => $user ? $user['session_remaining'] : 0
        ]);
    }
    
    /**
     * Renovar sessão (AJAX)
     */
    public function renewSession() {
        header('Content-Type: application/json');
        
        try {
            if ($this->authModel->renewSession()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Sessão renovada com sucesso',
                    'user' => $this->authModel->getCurrentUser()
                ]);
            } else {
                throw new Exception('Não foi possível renovar a sessão');
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Gerar nova senha aleatória (AJAX)
     */
    public function generatePassword() {
        header('Content-Type: application/json');
        
        try {
            $length = (int)($_POST['length'] ?? 12);
            $length = max(8, min(32, $length)); // Limitar entre 8 e 32 caracteres
            
            $password = $this->authModel->generateRandomPassword($length);
            $validation = $this->authModel->validatePasswordStrength($password);
            
            echo json_encode([
                'success' => true,
                'password' => $password,
                'strength' => $validation
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Validar força da senha (AJAX)
     */
    public function validatePassword() {
        header('Content-Type: application/json');
        
        try {
            $password = $_POST['password'] ?? '';
            
            if (empty($password)) {
                throw new Exception('Senha não fornecida');
            }
            
            $validation = $this->authModel->validatePasswordStrength($password);
            
            echo json_encode([
                'success' => true,
                'validation' => $validation
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Carregar view
     */
    private function loadView($view, $data = []) {
        extract($data);
        
        $viewFile = VIEWS_PATH . '/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new Exception("View não encontrada: {$view}");
        }
    }
}