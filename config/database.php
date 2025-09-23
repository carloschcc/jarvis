<?php
/**
 * Configurações de banco de dados e armazenamento
 * Para este sistema, usaremos arquivos JSON para persistência simples
 */

class SimpleFileDB {
    private $dataDir;
    
    public function __construct() {
        $this->dataDir = STORAGE_PATH . '/config/';
        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0755, true);
        }
    }
    
    public function save($key, $data) {
        $file = $this->dataDir . $key . '.json';
        $json = json_encode($data, JSON_PRETTY_PRINT);
        return file_put_contents($file, $json, LOCK_EX) !== false;
    }
    
    public function load($key, $default = null) {
        $file = $this->dataDir . $key . '.json';
        if (!file_exists($file)) {
            return $default;
        }
        
        $json = file_get_contents($file);
        $data = json_decode($json, true);
        
        return $data !== null ? $data : $default;
    }
    
    public function exists($key) {
        $file = $this->dataDir . $key . '.json';
        return file_exists($file);
    }
    
    public function delete($key) {
        $file = $this->dataDir . $key . '.json';
        if (file_exists($file)) {
            return unlink($file);
        }
        return true;
    }
}

// Inicializar banco de dados simples
$db = new SimpleFileDB();

// Função para obter configurações LDAP
function getLdapConfig() {
    global $db;
    
    // Configuração padrão para demonstração
    $defaultConfig = [
        'server' => '',
        'port' => 389,
        'domain' => '',
        'base_dn' => '',
        'admin_user' => '',
        'admin_pass' => '',
        'use_ssl' => false,
        'configured' => false
    ];
    
    return $db->load('ldap_config', $defaultConfig);
}

// Função para salvar configurações LDAP
function saveLdapConfig($config) {
    global $db;
    $config['configured'] = true;
    $config['updated_at'] = date('Y-m-d H:i:s');
    return $db->save('ldap_config', $config);
}

// Função para obter configurações do sistema
function getSystemConfig() {
    global $db;
    return $db->load('system_config', [
        'installation_date' => date('Y-m-d H:i:s'),
        'admin_configured' => false,
        'last_sync' => null,
        'sync_enabled' => false
    ]);
}

// Função para salvar configurações do sistema
function saveSystemConfig($config) {
    global $db;
    return $db->save('system_config', $config);
}

// Função para salvar logs de atividade
function saveActivityLog($action, $user, $details = '') {
    global $db;
    
    $logs = $db->load('activity_logs', []);
    
    $newLog = [
        'id' => uniqid(),
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => $action,
        'user' => $user,
        'details' => $details,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    // Manter apenas os últimos 1000 logs
    array_unshift($logs, $newLog);
    $logs = array_slice($logs, 0, 1000);
    
    return $db->save('activity_logs', $logs);
}

// Função para obter logs de atividade
function getActivityLogs($limit = 50) {
    global $db;
    $logs = $db->load('activity_logs', []);
    return array_slice($logs, 0, $limit);
}

// Função para limpar logs antigos
function cleanOldLogs($days = 30) {
    global $db;
    $logs = $db->load('activity_logs', []);
    $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
    
    $filteredLogs = array_filter($logs, function($log) use ($cutoffDate) {
        return $log['timestamp'] >= $cutoffDate;
    });
    
    return $db->save('activity_logs', array_values($filteredLogs));
}