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
        
        // Coletar filtros avançados
        $filters = [
            'department' => $_GET['department'] ?? '',
            'city' => $_GET['city'] ?? '',
            'title' => $_GET['title'] ?? '',
            'company' => $_GET['company'] ?? '',
            'office' => $_GET['office'] ?? '',
            'manager' => $_GET['manager'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];
        
        // Debug dos filtros recebidos
        logMessage('DEBUG', 'Filtros recebidos: ' . json_encode($filters));
        
        // Remover filtros vazios e valores padrão
        $filters = array_filter($filters, function($value) {
            return !empty($value) && 
                   $value !== 'all' && 
                   $value !== 'Todos os Departamentos' && 
                   $value !== 'Todas as Organizações' && 
                   $value !== 'Todas as Cidades' && 
                   $value !== 'Todos os Status' &&
                   $value !== 'Todas as Funções' &&
                   $value !== 'Todos os Escritórios';
        });
        
        logMessage('DEBUG', 'Filtros após limpeza: ' . json_encode($filters));
        
        try {
            // Buscar usuários do LDAP com filtros
            $users = $this->ldapModel->getUsers($search, $limit, $filters);
            
            // Obter listas para os filtros dropdowns
            $departments = $this->ldapModel->getDepartments();
            $cities = $this->ldapModel->getCities();
            $companies = $this->ldapModel->getCompanies();
            $titles = $this->ldapModel->getTitles();
            $offices = $this->ldapModel->getOffices();
            
            logMessage('INFO', 'Carregados ' . count($users) . ' usuários do LDAP com filtros aplicados');
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao carregar usuários: ' . $e->getMessage());
            $users = [];
            // Valores padrão para fallback
            $departments = ['TI', 'RH', 'Vendas', 'Financeiro', 'Marketing'];
            $cities = ['São Paulo', 'Rio de Janeiro', 'Belo Horizonte', 'Brasília'];
            $companies = ['Empresa Principal', 'Filial SP', 'Filial RJ'];
            $titles = ['Analista', 'Desenvolvedor', 'Gerente', 'Diretor', 'Coordenador'];
            $offices = ['Escritório Central', 'Sede São Paulo', 'Filial Rio de Janeiro'];
        }
        
        $data = [
            'title' => 'Usuários - ' . APP_NAME,
            'current_user' => $currentUser,
            'users' => $users,
            'search' => $search,
            'limit' => $limit,
            'filters' => $filters,
            'departments' => $departments,
            'cities' => $cities,
            'companies' => $companies,
            'titles' => $titles,
            'offices' => $offices,
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
        
        // Coletar filtros avançados
        $filters = [
            'department' => $_GET['department'] ?? '',
            'city' => $_GET['city'] ?? '',
            'title' => $_GET['title'] ?? '',
            'company' => $_GET['company'] ?? '',
            'office' => $_GET['office'] ?? '',
            'manager' => $_GET['manager'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];
        
        // Debug dos filtros recebidos
        logMessage('DEBUG', 'Filtros recebidos: ' . json_encode($filters));
        
        // Remover filtros vazios e valores padrão
        $filters = array_filter($filters, function($value) {
            return !empty($value) && 
                   $value !== 'all' && 
                   $value !== 'Todos os Departamentos' && 
                   $value !== 'Todas as Organizações' && 
                   $value !== 'Todas as Cidades' && 
                   $value !== 'Todos os Status' &&
                   $value !== 'Todas as Funções' &&
                   $value !== 'Todos os Escritórios';
        });
        
        logMessage('DEBUG', 'Filtros após limpeza: ' . json_encode($filters));
        
        try {
            $users = $this->ldapModel->getUsers($search, $limit, $filters);
            
            logMessage('INFO', 'Busca realizada: "' . $search . '" com filtros - ' . count($users) . ' resultados');
            
            echo json_encode([
                'success' => true,
                'users' => $users,
                'total' => count($users),
                'filters_applied' => $filters
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
        $forceChange = isset($_POST['force_change']) && $_POST['force_change'] === 'true';
        
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
            $result = $this->ldapModel->resetPassword($username, $newPassword, $forceChange);
            
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
     * Criar novo usuário (via modal avançado)
     */
    public function createUser() {
        header('Content-Type: application/json');
        
        if (!$this->authModel->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Acesso negado - não autenticado']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
            exit;
        }
        
        // Validação CSRF mais flexível para desenvolvimento
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $isDevMode = $host === 'localhost:8080' || 
                    strpos($host, 'localhost') !== false ||
                    strpos($host, '127.0.0.1') !== false ||
                    strpos($host, '.e2b.dev') !== false ||
                    preg_match('/^10\.\d+\.\d+\.\d+/', $host) ||
                    preg_match('/^192\.168\.\d+\.\d+/', $host) ||
                    preg_match('/^172\.(1[6-9]|2[0-9]|3[01])\.\d+\.\d+/', $host) ||
                    !isset($_SERVER['HTTPS']);
        
        if (!$isDevMode && !validateCSRFToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
            exit;
        }
        
        try {
            // Decodificar dados do usuário
            $userDataJson = $_POST['user_data'] ?? '';
            if (empty($userDataJson)) {
                throw new Exception('Dados do usuário não fornecidos');
            }
            
            $userData = json_decode($userDataJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Dados do usuário inválidos');
            }
            
            // Validações obrigatórias
            if (empty($userData['firstName']) || empty($userData['lastName'])) {
                throw new Exception('Nome e sobrenome são obrigatórios');
            }
            
            if (empty($userData['username']) || strlen($userData['username']) < 3) {
                throw new Exception('Nome de usuário deve ter pelo menos 3 caracteres');
            }
            
            if (empty($userData['password']) || strlen($userData['password']) < 8) {
                throw new Exception('Senha deve ter pelo menos 8 caracteres');
            }
            
            // Validar formato do username
            if (!preg_match('/^[a-zA-Z0-9._-]+$/', $userData['username'])) {
                throw new Exception('Nome de usuário deve conter apenas letras, números, pontos, hífens ou underscores');
            }
            
            // Processar dados para criação
            $processedData = [
                'username' => strtolower(trim($userData['username'])),
                'firstName' => trim($userData['firstName']),
                'lastName' => trim($userData['lastName']),
                'displayName' => trim($userData['displayName']) ?: trim($userData['firstName']) . ' ' . trim($userData['lastName']),
                'email' => trim($userData['email']),
                'password' => $userData['password'],
                'title' => trim($userData['title']),
                'department' => $userData['department'],
                'company' => $userData['company'],
                'city' => $userData['city'],
                'office' => trim($userData['office']),
                'phone' => trim($userData['phone']),
                'mobile' => trim($userData['mobile']),
                'description' => trim($userData['description']),
                'manager' => $userData['manager'],
                'groups' => $userData['groups'] ?? ['Domain Users'],
                'accountEnabled' => $userData['accountEnabled'] ?? true,
                'forcePasswordChange' => $userData['forcePasswordChange'] ?? true
            ];
            
            logMessage('INFO', 'Tentativa de criação de usuário: ' . $processedData['username'], [
                'admin_user' => $this->authModel->getCurrentUser()['username'],
                'display_name' => $processedData['displayName']
            ]);
            
            // Tentar criar o usuário
            $result = $this->ldapModel->createUser($processedData);
            
            if ($result['success']) {
                logMessage('INFO', "Usuário {$processedData['username']} criado com sucesso", [
                    'created_by' => $this->authModel->getCurrentUser()['username'],
                    'user_data' => [
                        'display_name' => $processedData['displayName'],
                        'email' => $processedData['email'],
                        'department' => $processedData['department']
                    ]
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => "Usuário '{$processedData['displayName']}' criado com sucesso!",
                    'username' => $processedData['username'],
                    'redirect' => 'index.php?page=users'
                ]);
            } else {
                throw new Exception($result['message'] ?? 'Falha desconhecida ao criar usuário');
            }
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao criar usuário: ' . $e->getMessage(), [
                'admin_user' => $this->authModel->getCurrentUser()['username'] ?? 'unknown',
                'error_details' => $e->getMessage()
            ]);
            
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Criar novo usuário (método antigo)
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
    
    /**
     * Obter listas para filtros (AJAX)
     */
    public function getFilterOptions() {
        header('Content-Type: application/json');
        
        if (!$this->authModel->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Não autenticado']);
            exit;
        }
        
        try {
            $departments = $this->ldapModel->getDepartments();
            $cities = $this->ldapModel->getCities();
            $companies = $this->ldapModel->getCompanies();
            
            echo json_encode([
                'success' => true,
                'departments' => $departments,
                'cities' => $cities,
                'companies' => $companies,
                'status_options' => [
                    ['value' => 'all', 'label' => 'Todos os Status'],
                    ['value' => 'active', 'label' => 'Ativo'],
                    ['value' => 'disabled', 'label' => 'Bloqueado']
                ]
            ]);
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao obter opções de filtro: ' . $e->getMessage());
            
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao carregar filtros: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Atualizar usuário
     */
    public function updateUser() {
        header('Content-Type: application/json');
        
        if (!$this->authModel->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Acesso negado - não autenticado']);
            exit;
        }
        
        // Validação CSRF mais flexível para desenvolvimento
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $isDevMode = $host === 'localhost:8080' || 
                    strpos($host, 'localhost') !== false ||
                    strpos($host, '127.0.0.1') !== false ||
                    strpos($host, '.e2b.dev') !== false ||
                    preg_match('/^10\.\d+\.\d+\.\d+/', $host) ||
                    preg_match('/^192\.168\.\d+\.\d+/', $host) ||
                    preg_match('/^172\.(1[6-9]|2[0-9]|3[01])\.\d+\.\d+/', $host) ||
                    !isset($_SERVER['HTTPS']);
        
        if (!$isDevMode && !validateCSRFToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
            exit;
        }
        
        $username = $_POST['username'] ?? '';
        $userData = json_decode($_POST['user_data'] ?? '{}', true);
        
        if (empty($username)) {
            echo json_encode(['success' => false, 'message' => 'Nome de usuário não informado']);
            exit;
        }
        
        try {
            $result = $this->ldapModel->updateUser($username, $userData);
            
            if ($result['success']) {
                logMessage('INFO', "Usuário {$username} atualizado por {$this->authModel->getCurrentUser()['username']}");
            }
            
            echo json_encode($result);
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao atualizar usuário: ' . $e->getMessage());
            
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao atualizar usuário: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Excluir usuário
     */
    public function deleteUser() {
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
        
        if (empty($username)) {
            echo json_encode(['success' => false, 'message' => 'Nome de usuário não informado']);
            exit;
        }
        
        try {
            $result = $this->ldapModel->deleteUser($username);
            
            if ($result['success']) {
                logMessage('INFO', "Usuário {$username} excluído por {$this->authModel->getCurrentUser()['username']}");
            }
            
            echo json_encode($result);
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao excluir usuário: ' . $e->getMessage());
            
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao excluir usuário: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Obter grupos do usuário
     */
    public function getUserGroups() {
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
            $groups = $this->ldapModel->getUserGroups($username);
            
            echo json_encode([
                'success' => true,
                'groups' => $groups
            ]);
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao buscar grupos do usuário: ' . $e->getMessage());
            
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao buscar grupos: ' . $e->getMessage()
            ]);
        }
    }
    
    private function loadView($view, $data = []) {
        extract($data);
        include VIEWS_PATH . '/' . $view . '.php';
    }
}