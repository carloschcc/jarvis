<?php
/**
 * Configurações específicas para XAMPP
 * Este arquivo contém ajustes para funcionar perfeitamente no ambiente XAMPP
 */

// Desabilitar algumas verificações rigorosas para ambiente local
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

// Configurar timezone
if (!ini_get('date.timezone')) {
    date_default_timezone_set('America/Sao_Paulo');
}

// Verificar se extensões necessárias estão disponíveis
$required_extensions = ['json', 'session', 'mbstring'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

if (!empty($missing_extensions)) {
    die('ERRO: Extensões PHP necessárias não encontradas: ' . implode(', ', $missing_extensions));
}

// Configurar sessão para XAMPP
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.gc_maxlifetime', 3600);
}

// Definir constantes de ambiente
define('IS_XAMPP', true);
define('DEBUG_MODE', true);

// Função para debug em ambiente XAMPP
if (!function_exists('xamppLog')) {
    function xamppLog($message) {
        if (DEBUG_MODE) {
            error_log('[AD Manager XAMPP] ' . $message);
        }
    }
}

// Log de inicialização
xamppLog('Sistema AD Manager iniciado no XAMPP');

// Verificar permissões da pasta storage
$storage_path = __DIR__ . '/../storage';
if (!is_dir($storage_path)) {
    mkdir($storage_path, 0755, true);
}

if (!is_writable($storage_path)) {
    xamppLog('Aviso: Pasta storage não tem permissões de escrita');
}

?>