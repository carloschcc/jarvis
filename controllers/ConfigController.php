<?php
/**
 * Controller de Configurações
 */

class ConfigController {
    private $authModel;
    private $ldapModel;
    
    public function __construct() {
        $this->authModel = new AuthModel();
        $this->ldapModel = new LdapModel();
    }
    
    /**
     * Página de configurações LDAP
     */
    public function index() {
        try {
            // Verificar autenticação
            if (!$this->authModel->isLoggedIn()) {
                header('Location: index.php?page=login');
                exit;
            }
            
            // Verificar se é admin
            if (!$this->authModel->isAdmin()) {
                header('Location: index.php?page=dashboard&error=access_denied');
                exit;
            }
            
            $currentUser = $this->authModel->getCurrentUser();
            $ldapConfig = getLdapConfig();
            
            // Remover senha da configuração para exibição
            $displayConfig = $ldapConfig;
            if (!empty($displayConfig['admin_pass'])) {
                $displayConfig['admin_pass'] = '••••••••';
            }
            
            $data = [
                'title' => 'Configurações LDAP - ' . APP_NAME,
                'current_user' => $currentUser,
                'ldap_config' => $displayConfig,
                'csrf_token' => generateCSRFToken(),
                'ldap_extension_loaded' => extension_loaded('ldap')
            ];
            
            $this->loadView('config/index', $data);
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro na página de configurações: ' . $e->getMessage());
            
            $data = [
                'title' => 'Erro - ' . APP_NAME,
                'error' => $e->getMessage(),
                'current_user' => $this->authModel->getCurrentUser()
            ];
            
            $this->loadView('config/error', $data);
        }
    }
    
    /**
     * Salvar configurações LDAP (AJAX)
     */
    public function save() {
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
            
            // Validar dados de entrada
            $config = [
                'server' => sanitizeInput($_POST['server'] ?? ''),
                'port' => (int)($_POST['port'] ?? DEFAULT_LDAP_PORT),
                'domain' => sanitizeInput($_POST['domain'] ?? ''),
                'base_dn' => sanitizeInput($_POST['base_dn'] ?? ''),
                'admin_user' => sanitizeInput($_POST['admin_user'] ?? ''),
                'use_ssl' => filter_var($_POST['use_ssl'] ?? false, FILTER_VALIDATE_BOOLEAN)
            ];
            
            // Validar senha (apenas se fornecida)
            $password = $_POST['admin_pass'] ?? '';
            if (!empty($password) && $password !== '••••••••') {
                $config['admin_pass'] = $password;
            } else {
                // Manter senha existente se não foi alterada
                $currentConfig = getLdapConfig();
                $config['admin_pass'] = $currentConfig['admin_pass'] ?? '';
            }
            
            // Validações básicas
            if (empty($config['server'])) {
                throw new Exception('Servidor LDAP é obrigatório');
            }
            
            if (empty($config['domain'])) {
                throw new Exception('Domínio é obrigatório');
            }
            
            if (empty($config['base_dn'])) {
                throw new Exception('Base DN é obrigatório');
            }
            
            if (empty($config['admin_user'])) {
                throw new Exception('Usuário administrador é obrigatório');
            }
            
            if (empty($config['admin_pass'])) {
                throw new Exception('Senha do administrador é obrigatória');
            }
            
            // Validar porta
            if ($config['port'] < 1 || $config['port'] > 65535) {
                throw new Exception('Porta deve estar entre 1 e 65535');
            }
            
            // Validar formato do Base DN
            if (!preg_match('/^DC=.+/i', $config['base_dn'])) {
                throw new Exception('Base DN deve começar com DC=');
            }
            
            // Salvar configuração
            if (saveLdapConfig($config)) {
                // Registrar log de atividade
                saveActivityLog('LDAP_CONFIG_UPDATED', $_SESSION['username'], 
                    'Configurações LDAP atualizadas');
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Configurações salvas com sucesso',
                    'config' => [
                        'server' => $config['server'],
                        'port' => $config['port'],
                        'domain' => $config['domain'],
                        'base_dn' => $config['base_dn'],
                        'admin_user' => $config['admin_user'],
                        'use_ssl' => $config['use_ssl'],
                        'configured' => true
                    ]
                ]);
            } else {
                throw new Exception('Erro ao salvar configurações');
            }
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao salvar configurações LDAP: ' . $e->getMessage());
            
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Testar conexão LDAP (AJAX)
     */
    public function testConnection() {
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
            
            // Obter configurações do formulário
            $config = [
                'server' => sanitizeInput($_POST['server'] ?? ''),
                'port' => (int)($_POST['port'] ?? DEFAULT_LDAP_PORT),
                'domain' => sanitizeInput($_POST['domain'] ?? ''),
                'base_dn' => sanitizeInput($_POST['base_dn'] ?? ''),
                'admin_user' => sanitizeInput($_POST['admin_user'] ?? ''),
                'admin_pass' => $_POST['admin_pass'] ?? '',
                'use_ssl' => filter_var($_POST['use_ssl'] ?? false, FILTER_VALIDATE_BOOLEAN)
            ];
            
            // Se senha não foi fornecida, usar a existente
            if (empty($config['admin_pass']) || $config['admin_pass'] === '••••••••') {
                $currentConfig = getLdapConfig();
                $config['admin_pass'] = $currentConfig['admin_pass'] ?? '';
            }
            
            // Validações básicas
            if (empty($config['server'])) {
                throw new Exception('Servidor LDAP é obrigatório');
            }
            
            if (empty($config['admin_user']) || empty($config['admin_pass'])) {
                throw new Exception('Credenciais do administrador são obrigatórias');
            }
            
            // Testar conexão
            $result = $this->ldapModel->testConnection($config);
            
            if ($result['success']) {
                // Registrar log de atividade
                saveActivityLog('LDAP_CONNECTION_TEST', $_SESSION['username'], 
                    'Teste de conexão LDAP realizado com sucesso');
                
                echo json_encode([
                    'success' => true,
                    'message' => $result['message'],
                    'connection_details' => [
                        'server' => $config['server'],
                        'port' => $config['port'],
                        'ssl' => $config['use_ssl'],
                        'test_time' => date('d/m/Y H:i:s')
                    ]
                ]);
            } else {
                throw new Exception($result['message']);
            }
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro no teste de conexão LDAP: ' . $e->getMessage());
            
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'error_details' => [
                    'error_type' => 'connection_failed',
                    'test_time' => date('d/m/Y H:i:s')
                ]
            ]);
        }
    }
    
    /**
     * Obter configurações atuais (AJAX)
     */
    public function getCurrent() {
        header('Content-Type: application/json');
        
        try {
            if (!$this->authModel->isLoggedIn()) {
                throw new Exception('Usuário não autenticado');
            }
            
            // Verificar se é admin
            if (!$this->authModel->isAdmin()) {
                throw new Exception('Acesso negado');
            }
            
            $ldapConfig = getLdapConfig();
            
            // Remover senha da resposta
            $safeConfig = $ldapConfig;
            if (!empty($safeConfig['admin_pass'])) {
                $safeConfig['admin_pass'] = '••••••••';
            }
            
            echo json_encode([
                'success' => true,
                'config' => $safeConfig,
                'ldap_extension' => extension_loaded('ldap')
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Resetar configurações para padrão (AJAX)
     */
    public function reset() {
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
            
            // Configuração padrão
            $defaultConfig = [
                'server' => '',
                'port' => DEFAULT_LDAP_PORT,
                'domain' => '',
                'base_dn' => '',
                'admin_user' => '',
                'admin_pass' => '',
                'use_ssl' => DEFAULT_LDAP_USE_SSL,
                'configured' => false
            ];
            
            if (saveLdapConfig($defaultConfig)) {
                // Registrar log de atividade
                saveActivityLog('LDAP_CONFIG_RESET', $_SESSION['username'], 
                    'Configurações LDAP resetadas para padrão');
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Configurações resetadas para padrão com sucesso'
                ]);
            } else {
                throw new Exception('Erro ao resetar configurações');
            }
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Validar Base DN (AJAX)
     */
    public function validateBaseDN() {
        header('Content-Type: application/json');
        
        try {
            if (!$this->authModel->isLoggedIn()) {
                throw new Exception('Usuário não autenticado');
            }
            
            $baseDN = sanitizeInput($_POST['base_dn'] ?? '');
            
            if (empty($baseDN)) {
                throw new Exception('Base DN não fornecido');
            }
            
            $isValid = true;
            $suggestions = [];
            $warnings = [];
            
            // Validar formato básico
            if (!preg_match('/^DC=.+/i', $baseDN)) {
                $isValid = false;
                $suggestions[] = 'Base DN deve começar com DC=';
            }
            
            // Verificar componentes válidos
            $components = explode(',', $baseDN);
            foreach ($components as $component) {
                $component = trim($component);
                if (!preg_match('/^(DC|OU|CN)=/i', $component)) {
                    $isValid = false;
                    $suggestions[] = "Componente inválido: {$component}";
                }
            }
            
            // Sugestões baseadas no domínio se fornecido
            $domain = sanitizeInput($_POST['domain'] ?? '');
            if (!empty($domain) && $isValid) {
                $domainParts = explode('.', $domain);
                $suggestedDN = 'DC=' . implode(',DC=', $domainParts);
                
                if (strtolower($baseDN) !== strtolower($suggestedDN)) {
                    $warnings[] = "Baseado no domínio '{$domain}', sugerimos: {$suggestedDN}";
                }
            }
            
            echo json_encode([
                'success' => true,
                'valid' => $isValid,
                'suggestions' => $suggestions,
                'warnings' => $warnings,
                'base_dn' => $baseDN
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Backup das configurações (AJAX)
     */
    public function backup() {
        header('Content-Type: application/json');
        
        try {
            if (!$this->authModel->isLoggedIn()) {
                throw new Exception('Usuário não autenticado');
            }
            
            // Verificar se é admin
            if (!$this->authModel->isAdmin()) {
                throw new Exception('Acesso negado');
            }
            
            $ldapConfig = getLdapConfig();
            $systemConfig = getSystemConfig();
            
            // Remover informações sensíveis
            unset($ldapConfig['admin_pass']);
            
            $backup = [
                'timestamp' => date('Y-m-d H:i:s'),
                'app_version' => APP_VERSION,
                'ldap_config' => $ldapConfig,
                'system_config' => $systemConfig
            ];
            
            $backupJson = json_encode($backup, JSON_PRETTY_PRINT);
            
            // Registrar log
            saveActivityLog('CONFIG_BACKUP', $_SESSION['username'], 
                'Backup das configurações gerado');
            
            echo json_encode([
                'success' => true,
                'backup' => $backupJson,
                'filename' => 'ad_manager_config_backup_' . date('Y-m-d_H-i-s') . '.json'
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