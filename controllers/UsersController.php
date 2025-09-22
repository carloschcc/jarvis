<?php
class UsersController {
    private $authModel;
    
    public function __construct() {
        $this->authModel = new AuthModel();
    }
    
    public function index() {
        if (!$this->authModel->isLoggedIn()) {
            header('Location: index.php?page=login');
            exit;
        }
        
        $currentUser = $this->authModel->getCurrentUser();
        $search = $_GET['search'] ?? '';
        
        // Dados de demonstração
        $users = [
            ['username' => 'admin', 'name' => 'Administrador', 'email' => 'admin@empresa.com', 'status' => 'Ativo', 'department' => 'TI', 'last_logon' => '2024-01-15 10:30:00'],
            ['username' => 'joao.silva', 'name' => 'João Silva', 'email' => 'joao@empresa.com', 'status' => 'Ativo', 'department' => 'Vendas', 'last_logon' => '2024-01-14 16:20:00'],
            ['username' => 'maria.santos', 'name' => 'Maria Santos', 'email' => 'maria@empresa.com', 'status' => 'Bloqueado', 'department' => 'RH', 'last_logon' => '2024-01-10 09:15:00'],
            ['username' => 'carlos.pereira', 'name' => 'Carlos Pereira', 'email' => 'carlos@empresa.com', 'status' => 'Ativo', 'department' => 'Financeiro', 'last_logon' => '2024-01-13 14:45:00'],
            ['username' => 'ana.costa', 'name' => 'Ana Costa', 'email' => 'ana@empresa.com', 'status' => 'Ativo', 'department' => 'Marketing', 'last_logon' => '2024-01-12 11:30:00']
        ];
        
        if (!empty($search)) {
            $users = array_filter($users, function($user) use ($search) {
                return stripos($user['name'], $search) !== false || 
                       stripos($user['username'], $search) !== false ||
                       stripos($user['email'], $search) !== false;
            });
        }
        
        $data = [
            'title' => 'Usuários - ' . APP_NAME,
            'current_user' => $currentUser,
            'users' => $users,
            'search' => $search,
            'total_users' => count($users),
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->loadView('users/index', $data);
    }
    
    public function search() {
        header('Content-Type: application/json');
        
        $search = $_GET['q'] ?? '';
        $users = [
            ['username' => 'admin', 'name' => 'Administrador', 'email' => 'admin@empresa.com', 'status' => 'Ativo', 'department' => 'TI', 'last_logon' => '2024-01-15 10:30:00'],
            ['username' => 'joao.silva', 'name' => 'João Silva', 'email' => 'joao@empresa.com', 'status' => 'Ativo', 'department' => 'Vendas', 'last_logon' => '2024-01-14 16:20:00'],
            ['username' => 'maria.santos', 'name' => 'Maria Santos', 'email' => 'maria@empresa.com', 'status' => 'Bloqueado', 'department' => 'RH', 'last_logon' => '2024-01-10 09:15:00']
        ];
        
        if (!empty($search)) {
            $users = array_filter($users, function($user) use ($search) {
                return stripos($user['name'], $search) !== false || 
                       stripos($user['username'], $search) !== false;
            });
        }
        
        echo json_encode(['success' => true, 'users' => array_values($users)]);
    }
    
    public function toggleStatus() {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Status alterado com sucesso (simulação)']);
    }
    
    public function resetPassword() {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Senha redefinida com sucesso (simulação)']);
    }
    
    public function getUser() {
        header('Content-Type: application/json');
        $username = $_GET['username'] ?? '';
        
        $user = [
            'username' => $username,
            'name' => 'Usuário ' . $username,
            'email' => $username . '@empresa.com',
            'status' => 'Ativo',
            'department' => 'Demonstração',
            'created' => '2024-01-01 10:00:00',
            'last_logon' => '2024-01-15 14:30:00',
            'dn' => "CN=$username,OU=Users,DC=empresa,DC=local"
        ];
        
        echo json_encode(['success' => true, 'user' => $user]);
    }
    
    private function loadView($view, $data = []) {
        extract($data);
        include VIEWS_PATH . '/' . $view . '.php';
    }
}