<?php
class ConfigController {
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
        
        if (!$this->authModel->isAdmin()) {
            header('Location: index.php?page=dashboard&error=access_denied');
            exit;
        }
        
        $currentUser = $this->authModel->getCurrentUser();
        $ldapConfig = getLdapConfig();
        
        // Mascarar senha
        if (!empty($ldapConfig['admin_pass'])) {
            $ldapConfig['admin_pass'] = '••••••••';
        }
        
        $data = [
            'title' => 'Configurações LDAP - ' . APP_NAME,
            'current_user' => $currentUser,
            'ldap_config' => $ldapConfig,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->loadView('config/index', $data);
    }
    
    public function save() {
        header('Content-Type: application/json');
        
        if (!$this->authModel->isLoggedIn() || !$this->authModel->isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            exit;
        }
        
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
            exit;
        }
        
        $config = [
            'server' => trim($_POST['server'] ?? ''),
            'port' => (int)($_POST['port'] ?? 389),
            'domain' => trim($_POST['domain'] ?? ''),
            'base_dn' => trim($_POST['base_dn'] ?? ''),
            'admin_user' => trim($_POST['admin_user'] ?? ''),
            'admin_pass' => $_POST['admin_pass'] ?? '',
            'use_ssl' => isset($_POST['use_ssl'])
        ];
        
        // Validações básicas
        if (empty($config['server'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Servidor LDAP é obrigatório'
            ]);
            exit;
        }
        
        if (empty($config['base_dn'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Base DN é obrigatório'
            ]);
            exit;
        }
        
        // Não sobrescrever senha se estiver vazia (manter a anterior)
        if (empty($config['admin_pass'])) {
            $currentConfig = getLdapConfig();
            $config['admin_pass'] = $currentConfig['admin_pass'] ?? '';
        }
        
        try {
            if (saveLdapConfig($config)) {
                logMessage('INFO', 'Configurações LDAP salvas por ' . $this->authModel->getCurrentUser()['username']);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Configurações salvas com sucesso'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Erro ao salvar configurações'
                ]);
            }
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao salvar configurações: ' . $e->getMessage());
            
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao salvar: ' . $e->getMessage()
            ]);
        }
    }
    
    public function testConnection() {
        header('Content-Type: application/json');
        
        if (!$this->authModel->isLoggedIn() || !$this->authModel->isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            exit;
        }
        
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
            exit;
        }
        
        $testConfig = [
            'server' => trim($_POST['server'] ?? ''),
            'port' => (int)($_POST['port'] ?? 389),
            'domain' => trim($_POST['domain'] ?? ''),
            'base_dn' => trim($_POST['base_dn'] ?? ''),
            'admin_user' => trim($_POST['admin_user'] ?? ''),
            'admin_pass' => $_POST['admin_pass'] ?? '',
            'use_ssl' => isset($_POST['use_ssl'])
        ];
        
        // Se a senha estiver vazia, usar a senha atual salva
        if (empty($testConfig['admin_pass'])) {
            $currentConfig = getLdapConfig();
            $testConfig['admin_pass'] = $currentConfig['admin_pass'] ?? '';
        }
        
        try {
            logMessage('INFO', 'Testando conexão LDAP para servidor: ' . $testConfig['server']);
            
            $result = $this->ldapModel->testConnection($testConfig);
            
            if ($result['success']) {
                logMessage('INFO', 'Teste de conexão LDAP bem-sucedido');
            } else {
                logMessage('WARNING', 'Teste de conexão LDAP falhou: ' . $result['message']);
            }
            
            echo json_encode($result);
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro durante teste de conexão: ' . $e->getMessage());
            
            echo json_encode([
                'success' => false,
                'message' => 'Erro durante o teste: ' . $e->getMessage(),
                'error' => 'EXCEPTION'
            ]);
        }
    }
    
    private function loadView($view, $data = []) {
        extract($data);
        include VIEWS_PATH . '/' . $view . '.php';
    }
}