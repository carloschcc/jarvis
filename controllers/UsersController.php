<?php
class UsersController {
    private $authModel;
    private $ldapModel;
    
    public function __construct() {
        $this->authModel = new AuthModel();
        $this->ldapModel = new LdapModel();
    }
    
    public function index() {
        if (!$this->authModel->isLoggedIn()) {
            header('Location: index.php?page=login');
            exit;
        }
        
        $currentUser = $this->authModel->getCurrentUser();
        $search = $_GET['search'] ?? '';
        $limit = (int)($_GET['limit'] ?? 50);
        
        try {
            // Buscar usuários do LDAP
            $users = $this->ldapModel->getUsers($search, $limit);
            
            logMessage('INFO', 'Carregados ' . count($users) . ' usuários do LDAP');
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao carregar usuários: ' . $e->getMessage());
            $users = [];
        }
        
        $data = [
            'title' => 'Usuários - ' . APP_NAME,
            'current_user' => $currentUser,
            'users' => $users,
            'search' => $search,
            'limit' => $limit,
            'total_users' => count($users),
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->loadView('users/index', $data);
    }
    
    public function search() {
        header('Content-Type: application/json');
        
        if (!$this->authModel->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Não autenticado']);
            exit;
        }
        
        $search = $_GET['q'] ?? '';
        $limit = (int)($_GET['limit'] ?? 20);
        
        try {
            $users = $this->ldapModel->getUsers($search, $limit);
            
            logMessage('INFO', 'Busca realizada: "' . $search . '" - ' . count($users) . ' resultados');
            
            echo json_encode([
                'success' => true,
                'users' => $users,
                'total' => count($users)
            ]);
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro na busca de usuários: ' . $e->getMessage());
            
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao buscar usuários: ' . $e->getMessage()
            ]);
        }
    }
    
    public function toggleStatus() {
        header('Content-Type: application/json');
        
        if (!$this->authModel->isLoggedIn() || !$this->authModel->isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            exit;
        }
        
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
            exit;
        }
        
        $username = $_POST['username'] ?? '';
        $enable = ($_POST['action'] ?? '') === 'enable';
        
        if (empty($username)) {
            echo json_encode(['success' => false, 'message' => 'Nome de usuário não informado']);
            exit;
        }
        
        try {
            $result = $this->ldapModel->toggleUserStatus($username, $enable);
            
            logMessage('INFO', "Status do usuário {$username} alterado por {$this->authModel->getCurrentUser()['username']}");
            
            echo json_encode($result);
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao alterar status do usuário: ' . $e->getMessage());
            
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao alterar status: ' . $e->getMessage()
            ]);
        }
    }
    
    public function resetPassword() {
        header('Content-Type: application/json');
        
        if (!$this->authModel->isLoggedIn() || !$this->authModel->isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            exit;
        }
        
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
            exit;
        }
        
        $username = $_POST['username'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        
        if (empty($username) || empty($newPassword)) {
            echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
            exit;
        }
        
        // Validar força da senha
        if (strlen($newPassword) < 8) {
            echo json_encode(['success' => false, 'message' => 'Senha deve ter pelo menos 8 caracteres']);
            exit;
        }
        
        try {
            $result = $this->ldapModel->resetPassword($username, $newPassword);
            
            logMessage('INFO', "Senha resetada para usuário {$username} por {$this->authModel->getCurrentUser()['username']}");
            
            echo json_encode($result);
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao resetar senha: ' . $e->getMessage());
            
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao resetar senha: ' . $e->getMessage()
            ]);
        }
    }
    
    public function getUser() {
        header('Content-Type: application/json');
        
        if (!$this->authModel->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Não autenticado']);
            exit;
        }
        
        $username = $_GET['username'] ?? '';
        
        if (empty($username)) {
            echo json_encode(['success' => false, 'message' => 'Nome de usuário não informado']);
            exit;
        }
        
        try {
            $user = $this->ldapModel->getUser($username);
            
            if (!$user) {
                echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
            } else {
                echo json_encode(['success' => true, 'user' => $user]);
            }
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao buscar usuário: ' . $e->getMessage());
            
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao buscar usuário: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Criar novo usuário
     */
    public function create() {
        header('Content-Type: application/json');
        
        if (!$this->authModel->isLoggedIn() || !$this->authModel->isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            exit;
        }
        
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
            exit;
        }
        
        $userData = [
            'username' => $_POST['username'] ?? '',
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'description' => $_POST['description'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'department' => $_POST['department'] ?? ''
        ];
        
        // Validações básicas
        if (empty($userData['username']) || empty($userData['name'])) {
            echo json_encode(['success' => false, 'message' => 'Nome de usuário e nome completo são obrigatórios']);
            exit;
        }
        
        try {
            $result = $this->ldapModel->createUser($userData);
            
            if ($result['success']) {
                logMessage('INFO', "Usuário {$userData['username']} criado por {$this->authModel->getCurrentUser()['username']}");
            }
            
            echo json_encode($result);
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao criar usuário: ' . $e->getMessage());
            
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao criar usuário: ' . $e->getMessage()
            ]);
        }
    }
    
    private function loadView($view, $data = []) {
        extract($data);
        include VIEWS_PATH . '/' . $view . '.php';
    }
}