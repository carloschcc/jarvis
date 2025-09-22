<?php
/**
 * Controller do Dashboard
 */

class DashboardController {
    private $authModel;
    private $ldapModel;
    
    public function __construct() {
        $this->authModel = new AuthModel();
        $this->ldapModel = new LdapModel();
    }
    
    /**
     * Página principal do dashboard
     */
    public function index() {
        try {
            // Verificar se usuário está logado
            if (!$this->authModel->isLoggedIn()) {
                header('Location: index.php?page=login');
                exit;
            }
            
            // Verificar se sessão expirou
            if ($this->authModel->isSessionExpired()) {
                header('Location: index.php?page=login&error=session_expired');
                exit;
            }
            
            // Obter dados do usuário logado
            $currentUser = $this->authModel->getCurrentUser();
            
            // Obter configuração LDAP
            $ldapConfig = getLdapConfig();
            $ldapConfigured = $ldapConfig['configured'] ?? false;
            
            // Obter estatísticas dos usuários
            $userStats = ['total' => 0, 'active' => 0, 'blocked' => 0, 'never_logged' => 0];
            $connectionStatus = 'disconnected';
            $lastSync = null;
            
            if ($ldapConfigured) {
                try {
                    $userStats = $this->ldapModel->getUserStats();
                    $connectionStatus = 'connected';
                    
                    // Atualizar última sincronização
                    $systemConfig = getSystemConfig();
                    $systemConfig['last_sync'] = date('Y-m-d H:i:s');
                    saveSystemConfig($systemConfig);
                    $lastSync = $systemConfig['last_sync'];
                    
                } catch (Exception $e) {
                    $connectionStatus = 'error';
                    logMessage('ERROR', 'Erro ao obter estatísticas do LDAP: ' . $e->getMessage());
                }
            }
            
            // Obter logs de atividade recentes
            $recentLogs = getActivityLogs(10);
            
            // Obter configuração do sistema
            $systemConfig = getSystemConfig();
            
            $data = [
                'title' => 'Dashboard - ' . APP_NAME,
                'current_user' => $currentUser,
                'user_stats' => $userStats,
                'ldap_configured' => $ldapConfigured,
                'ldap_config' => $ldapConfig,
                'connection_status' => $connectionStatus,
                'last_sync' => $lastSync,
                'recent_logs' => $recentLogs,
                'system_config' => $systemConfig,
                'app_name' => APP_NAME,
                'app_version' => APP_VERSION
            ];
            
            $this->loadView('dashboard/index', $data);
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro no dashboard: ' . $e->getMessage());
            
            $data = [
                'title' => 'Erro - ' . APP_NAME,
                'error' => $e->getMessage(),
                'current_user' => $this->authModel->getCurrentUser()
            ];
            
            $this->loadView('dashboard/error', $data);
        }
    }
    
    /**
     * Obter estatísticas em tempo real (AJAX)
     */
    public function getStats() {
        header('Content-Type: application/json');
        
        try {
            // Verificar se usuário está logado
            if (!$this->authModel->isLoggedIn()) {
                throw new Exception('Usuário não autenticado');
            }
            
            $ldapConfig = getLdapConfig();
            $stats = ['total' => 0, 'active' => 0, 'blocked' => 0, 'never_logged' => 0];
            $connectionStatus = 'disconnected';
            
            if ($ldapConfig['configured']) {
                try {
                    $stats = $this->ldapModel->getUserStats();
                    $connectionStatus = 'connected';
                } catch (Exception $e) {
                    $connectionStatus = 'error';
                }
            }
            
            echo json_encode([
                'success' => true,
                'stats' => $stats,
                'connection_status' => $connectionStatus,
                'last_update' => date('d/m/Y H:i:s'),
                'ldap_configured' => $ldapConfig['configured']
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Sincronizar dados com LDAP (AJAX)
     */
    public function syncLdap() {
        header('Content-Type: application/json');
        
        try {
            // Verificar se usuário está logado e é admin
            if (!$this->authModel->isLoggedIn() || !$this->authModel->isAdmin()) {
                throw new Exception('Acesso negado');
            }
            
            $ldapConfig = getLdapConfig();
            
            if (!$ldapConfig['configured']) {
                throw new Exception('LDAP não configurado');
            }
            
            // Testar conexão LDAP
            $testResult = $this->ldapModel->testConnection($ldapConfig);
            
            if (!$testResult['success']) {
                throw new Exception('Falha na conexão LDAP: ' . $testResult['message']);
            }
            
            // Obter estatísticas atualizadas
            $stats = $this->ldapModel->getUserStats();
            
            // Atualizar configuração do sistema
            $systemConfig = getSystemConfig();
            $systemConfig['last_sync'] = date('Y-m-d H:i:s');
            $systemConfig['sync_enabled'] = true;
            saveSystemConfig($systemConfig);
            
            // Registrar log
            saveActivityLog('LDAP_SYNC', $_SESSION['username'], 'Sincronização manual com LDAP');
            
            echo json_encode([
                'success' => true,
                'message' => 'Sincronização realizada com sucesso',
                'stats' => $stats,
                'last_sync' => $systemConfig['last_sync']
            ]);
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro na sincronização LDAP: ' . $e->getMessage());
            
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Obter logs de sistema (AJAX)
     */
    public function getLogs() {
        header('Content-Type: application/json');
        
        try {
            // Verificar se usuário está logado e é admin
            if (!$this->authModel->isLoggedIn() || !$this->authModel->isAdmin()) {
                throw new Exception('Acesso negado');
            }
            
            $limit = (int)($_GET['limit'] ?? 20);
            $limit = max(1, min(100, $limit)); // Limitar entre 1 e 100
            
            $logs = getActivityLogs($limit);
            
            echo json_encode([
                'success' => true,
                'logs' => $logs,
                'total' => count($logs)
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Limpar logs antigos (AJAX)
     */
    public function clearOldLogs() {
        header('Content-Type: application/json');
        
        try {
            // Verificar se usuário está logado e é admin
            if (!$this->authModel->isLoggedIn() || !$this->authModel->isAdmin()) {
                throw new Exception('Acesso negado');
            }
            
            $days = (int)($_POST['days'] ?? 30);
            $days = max(1, min(365, $days)); // Limitar entre 1 dia e 1 ano
            
            $result = cleanOldLogs($days);
            
            if ($result) {
                saveActivityLog('SYSTEM_MAINTENANCE', $_SESSION['username'], "Logs mais antigos que {$days} dias foram removidos");
                
                echo json_encode([
                    'success' => true,
                    'message' => "Logs mais antigos que {$days} dias foram removidos com sucesso"
                ]);
            } else {
                throw new Exception('Erro ao limpar logs');
            }
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Obter informações do sistema (AJAX)
     */
    public function getSystemInfo() {
        header('Content-Type: application/json');
        
        try {
            // Verificar se usuário está logado
            if (!$this->authModel->isLoggedIn()) {
                throw new Exception('Usuário não autenticado');
            }
            
            $phpVersion = PHP_VERSION;
            $ldapExtension = extension_loaded('ldap');
            $systemConfig = getSystemConfig();
            $ldapConfig = getLdapConfig();
            
            $info = [
                'app_name' => APP_NAME,
                'app_version' => APP_VERSION,
                'php_version' => $phpVersion,
                'ldap_extension' => $ldapExtension,
                'installation_date' => $systemConfig['installation_date'] ?? 'N/A',
                'last_sync' => $systemConfig['last_sync'] ?? 'Nunca',
                'ldap_configured' => $ldapConfig['configured'] ?? false,
                'ldap_server' => $ldapConfig['server'] ?? 'N/A',
                'ldap_domain' => $ldapConfig['domain'] ?? 'N/A',
                'server_time' => date('d/m/Y H:i:s'),
                'timezone' => date_default_timezone_get(),
                'memory_usage' => formatBytes(memory_get_usage()),
                'memory_peak' => formatBytes(memory_get_peak_usage())
            ];
            
            echo json_encode([
                'success' => true,
                'system_info' => $info
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