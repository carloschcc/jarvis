<?php
/**
 * Configurações da aplicação
 */

// Configurações gerais da aplicação
define('APP_NAME', 'AD Manager');
define('APP_VERSION', '1.0.0-beta');
define('APP_DESCRIPTION', 'Sistema de Gestão de Usuários Active Directory');
define('APP_BUILD_DATE', '2024-01-15');
define('APP_BUILD_NUMBER', '001');

// Configurações de segurança
define('SESSION_TIMEOUT', 3600); // 1 hora em segundos
define('DEFAULT_ADMIN_USER', 'admin');
define('DEFAULT_ADMIN_PASS', 'admin123');

// Configurações LDAP padrão
define('DEFAULT_LDAP_PORT', 636);
define('DEFAULT_LDAP_USE_SSL', true);

// Configurações de interface
define('ITEMS_PER_PAGE', 20);
define('MAX_SEARCH_RESULTS', 100);

// Configurações de logs
define('LOG_LEVEL', 'INFO');
define('LOG_FILE', STORAGE_PATH . '/logs/app.log');

// Funções auxiliares
function logMessage($level, $message, $context = []) {
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = empty($context) ? '' : ' - ' . json_encode($context);
    $logEntry = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;
    
    // Criar diretório de logs se não existir
    $logDir = dirname(LOG_FILE);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents(LOG_FILE, $logEntry, FILE_APPEND | LOCK_EX);
}

function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'agora mesmo';
    if ($time < 3600) return floor($time/60) . ' minutos atrás';
    if ($time < 86400) return floor($time/3600) . ' horas atrás';
    if ($time < 2592000) return floor($time/86400) . ' dias atrás';
    
    return date('d/m/Y H:i', strtotime($datetime));
}