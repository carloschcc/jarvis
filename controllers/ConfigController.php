<?php
class ConfigController {
    private $authModel;
    
    public function __construct() {
        $this->authModel = new AuthModel();
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
        
        $config = [
            'server' => $_POST['server'] ?? '',
            'port' => (int)($_POST['port'] ?? 389),
            'domain' => $_POST['domain'] ?? '',
            'base_dn' => $_POST['base_dn'] ?? '',
            'admin_user' => $_POST['admin_user'] ?? '',
            'admin_pass' => $_POST['admin_pass'] ?? '',
            'use_ssl' => isset($_POST['use_ssl'])
        ];
        
        if (saveLdapConfig($config)) {
            echo json_encode([
                'success' => true,
                'message' => 'Configurações salvas com sucesso (demonstração)'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao salvar configurações'
            ]);
        }
    }
    
    public function testConnection() {
        header('Content-Type: application/json');
        
        // Simular teste bem-sucedido
        echo json_encode([
            'success' => true,
            'message' => 'Conexão testada com sucesso (modo demonstração)',
            'connection_details' => [
                'server' => $_POST['server'] ?? 'demo.empresa.com',
                'port' => $_POST['port'] ?? 389,
                'ssl' => isset($_POST['use_ssl']),
                'test_time' => date('d/m/Y H:i:s')
            ]
        ]);
    }
    
    private function loadView($view, $data = []) {
        extract($data);
        include VIEWS_PATH . '/' . $view . '.php';
    }
}