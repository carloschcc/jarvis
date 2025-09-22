<?php
/**
 * Controller de Usuários
 */

class UsersController {
    private $authModel;
    private $ldapModel;
    
    public function __construct() {
        $this->authModel = new AuthModel();
        $this->ldapModel = new LdapModel();
    }
    
    /**
     * Página de listagem de usuários
     */
    public function index() {
        try {
            // Verificar autenticação
            if (!$this->authModel->isLoggedIn()) {
                header('Location: index.php?page=login');
                exit;
            }
            
            // Verificar se LDAP está configurado
            $ldapConfig = getLdapConfig();
            if (!$ldapConfig['configured']) {
                header('Location: index.php?page=config&error=ldap_not_configured');
                exit;
            }
            
            $currentUser = $this->authModel->getCurrentUser();
            
            // Parâmetros de busca e paginação
            $search = sanitizeInput($_GET['search'] ?? '');
            $page = (int)($_GET['p'] ?? 1);
            $limit = (int)($_GET['limit'] ?? ITEMS_PER_PAGE);
            
            $users = [];
            $totalUsers = 0;
            $error = null;
            
            try {
                // Buscar usuários no LDAP
                $users = $this->ldapModel->getUsers($search, $limit * 5); // Buscar mais para compensar paginação
                $totalUsers = count($users);
                
                // Aplicar paginação simples
                $offset = ($page - 1) * $limit;
                $users = array_slice($users, $offset, $limit);
                
            } catch (Exception $e) {
                $error = 'Erro ao conectar com LDAP: ' . $e->getMessage();
                logMessage('ERROR', 'Erro ao buscar usuários: ' . $e->getMessage());
            }
            
            $data = [
                'title' => 'Usuários - ' . APP_NAME,
                'current_user' => $currentUser,
                'users' => $users,
                'total_users' => $totalUsers,
                'search' => $search,
                'current_page' => $page,
                'items_per_page' => $limit,
                'total_pages' => ceil($totalUsers / $limit),
                'ldap_config' => $ldapConfig,
                'error' => $error,
                'csrf_token' => generateCSRFToken()
            ];
            
            $this->loadView('users/index', $data);
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro na listagem de usuários: ' . $e->getMessage());
            
            $data = [
                'title' => 'Erro - ' . APP_NAME,
                'error' => $e->getMessage(),
                'current_user' => $this->authModel->getCurrentUser()
            ];
            
            $this->loadView('users/error', $data);
        }
    }
    
    /**
     * Buscar usuários (AJAX)
     */
    public function search() {
        header('Content-Type: application/json');
        
        try {
            if (!$this->authModel->isLoggedIn()) {
                throw new Exception('Usuário não autenticado');
            }
            
            $search = sanitizeInput($_GET['q'] ?? '');
            $limit = (int)($_GET['limit'] ?? 20);
            
            $users = $this->ldapModel->getUsers($search, $limit);
            
            echo json_encode([
                'success' => true,
                'users' => $users,
                'total' => count($users),
                'search_term' => $search
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Obter detalhes de um usuário (AJAX)
     */
    public function getUser() {
        header('Content-Type: application/json');
        
        try {
            if (!$this->authModel->isLoggedIn()) {
                throw new Exception('Usuário não autenticado');
            }
            
            $username = sanitizeInput($_GET['username'] ?? '');
            
            if (empty($username)) {
                throw new Exception('Nome de usuário não fornecido');
            }
            
            $user = $this->ldapModel->getUser($username);
            
            echo json_encode([
                'success' => true,
                'user' => $user
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Alterar status do usuário (AJAX)
     */
    public function toggleStatus() {
        header('Content-Type: application/json');
        
        try {
            if (!$this->authModel->isLoggedIn()) {
                throw new Exception('Usuário não autenticado');
            }
            
            // Verificar se é admin ou tem permissões
            if (!$this->authModel->isAdmin()) {
                throw new Exception('Acesso negado');
            }
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método não permitido');
            }
            
            // Verificar CSRF token
            if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
                throw new Exception('Token de segurança inválido');
            }
            
            $username = sanitizeInput($_POST['username'] ?? '');
            $enable = filter_var($_POST['enable'] ?? true, FILTER_VALIDATE_BOOLEAN);
            
            if (empty($username)) {
                throw new Exception('Nome de usuário não fornecido');
            }
            
            // Alterar status do usuário
            $result = $this->ldapModel->toggleUserStatus($username, $enable);
            
            if ($result) {
                $action = $enable ? 'ativado' : 'bloqueado';
                
                // Registrar log de atividade
                saveActivityLog('USER_STATUS_CHANGE', $_SESSION['username'], 
                    "Usuário {$username} foi {$action}");
                
                echo json_encode([
                    'success' => true,
                    'message' => "Usuário {$username} foi {$action} com sucesso",
                    'new_status' => $enable ? 'Ativo' : 'Bloqueado'
                ]);
            } else {
                throw new Exception('Erro ao alterar status do usuário');
            }
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao alterar status do usuário: ' . $e->getMessage());
            
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Alterar status de múltiplos usuários (AJAX)
     */
    public function bulkToggleStatus() {
        header('Content-Type: application/json');
        
        try {
            if (!$this->authModel->isLoggedIn()) {
                throw new Exception('Usuário não autenticado');
            }
            
            // Verificar se é admin
            if (!$this->authModel->isAdmin()) {
                throw new Exception('Acesso negado');
            }
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método não permitido');
            }
            
            // Verificar CSRF token
            if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
                throw new Exception('Token de segurança inválido');
            }
            
            $usernames = $_POST['usernames'] ?? [];
            $enable = filter_var($_POST['enable'] ?? true, FILTER_VALIDATE_BOOLEAN);
            
            if (empty($usernames) || !is_array($usernames)) {
                throw new Exception('Nenhum usuário selecionado');
            }
            
            $results = [];
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($usernames as $username) {
                $username = sanitizeInput($username);
                
                try {
                    $result = $this->ldapModel->toggleUserStatus($username, $enable);
                    
                    if ($result) {
                        $results[$username] = 'sucesso';
                        $successCount++;
                    } else {
                        $results[$username] = 'erro';
                        $errorCount++;
                    }
                } catch (Exception $e) {
                    $results[$username] = 'erro: ' . $e->getMessage();
                    $errorCount++;
                }
            }
            
            $action = $enable ? 'ativados' : 'bloqueados';
            
            // Registrar log de atividade
            saveActivityLog('BULK_USER_STATUS_CHANGE', $_SESSION['username'], 
                "Operação em lote: {$successCount} usuários {$action}, {$errorCount} erros");
            
            echo json_encode([
                'success' => true,
                'message' => "{$successCount} usuários {$action} com sucesso" . 
                           ($errorCount > 0 ? ", {$errorCount} erros" : ""),
                'results' => $results,
                'success_count' => $successCount,
                'error_count' => $errorCount
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Resetar senha do usuário (AJAX)
     */
    public function resetPassword() {
        header('Content-Type: application/json');
        
        try {
            if (!$this->authModel->isLoggedIn()) {
                throw new Exception('Usuário não autenticado');
            }
            
            // Verificar se é admin
            if (!$this->authModel->isAdmin()) {
                throw new Exception('Acesso negado');
            }
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método não permitido');
            }
            
            // Verificar CSRF token
            if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
                throw new Exception('Token de segurança inválido');
            }
            
            $username = sanitizeInput($_POST['username'] ?? '');
            $newPassword = $_POST['password'] ?? '';
            $generateRandom = filter_var($_POST['generate_random'] ?? false, FILTER_VALIDATE_BOOLEAN);
            
            if (empty($username)) {
                throw new Exception('Nome de usuário não fornecido');
            }
            
            // Gerar senha aleatória se solicitado
            if ($generateRandom) {
                $newPassword = $this->authModel->generateRandomPassword(12);
            }
            
            if (empty($newPassword)) {
                throw new Exception('Nova senha não fornecida');
            }
            
            // Validar força da senha
            $validation = $this->authModel->validatePasswordStrength($newPassword);
            if (!$validation['valid']) {
                throw new Exception('Senha não atende aos critérios de segurança: ' . 
                                  implode(', ', $validation['errors']));
            }
            
            // Resetar senha no LDAP
            $result = $this->ldapModel->resetPassword($username, $newPassword);
            
            if ($result) {
                // Registrar log de atividade (sem mostrar a senha)
                saveActivityLog('PASSWORD_RESET', $_SESSION['username'], 
                    "Senha do usuário {$username} foi redefinida");
                
                echo json_encode([
                    'success' => true,
                    'message' => "Senha do usuário {$username} foi redefinida com sucesso",
                    'new_password' => $generateRandom ? $newPassword : null,
                    'password_strength' => $validation['score']
                ]);
            } else {
                throw new Exception('Erro ao redefinir senha do usuário');
            }
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao resetar senha: ' . $e->getMessage());
            
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Exportar lista de usuários (CSV)
     */
    public function export() {
        try {
            if (!$this->authModel->isLoggedIn()) {
                header('Location: index.php?page=login');
                exit;
            }
            
            // Verificar se é admin
            if (!$this->authModel->isAdmin()) {
                throw new Exception('Acesso negado');
            }
            
            $search = sanitizeInput($_GET['search'] ?? '');
            $users = $this->ldapModel->getUsers($search, 1000); // Exportar até 1000 usuários
            
            // Configurar headers para download CSV
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="usuarios_ad_' . date('Y-m-d_H-i-s') . '.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Criar arquivo CSV
            $output = fopen('php://output', 'w');
            
            // Escrever BOM para UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeçalhos do CSV
            fputcsv($output, [
                'Usuario',
                'Nome',
                'Email',
                'Status',
                'Departamento',
                'Telefone',
                'Data Criacao',
                'Ultimo Login'
            ], ';');
            
            // Dados dos usuários
            foreach ($users as $user) {
                fputcsv($output, [
                    $user['username'],
                    $user['name'],
                    $user['email'],
                    $user['status'],
                    $user['department'],
                    $user['phone'],
                    $user['created'],
                    $user['last_logon']
                ], ';');
            }
            
            fclose($output);
            
            // Registrar log
            saveActivityLog('USER_EXPORT', $_SESSION['username'], 
                'Exportação de ' . count($users) . ' usuários para CSV');
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro na exportação: ' . $e->getMessage());
            header('Location: index.php?page=users&error=' . urlencode($e->getMessage()));
            exit;
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