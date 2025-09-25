<?php
/**
 * AD Manager - Sistema de Gestão de Usuários Active Directory
 * 
 * @version 1.0
 * @author Sistema AD Manager
 * @description Sistema completo para gestão de usuários do Active Directory
 */

// Servir arquivos estáticos quando usando servidor built-in do PHP
if (php_sapi_name() === 'cli-server') {
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $file_path = __DIR__ . $request_uri;
    
    // Se for um arquivo estático e existe, servir diretamente
    if (is_file($file_path) && preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/i', $file_path)) {
        return false; // Deixa o servidor built-in servir o arquivo
    }
}

// Configurar sessões para funcionar em diferentes IPs
ini_set('session.cookie_domain', '');
ini_set('session.cookie_path', '/');
ini_set('session.cookie_secure', '0'); // Permitir HTTP
ini_set('session.cookie_httponly', '1'); // Segurança XSS
ini_set('session.use_strict_mode', '1'); // Segurança

session_start();

// Configurações básicas
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('America/Sao_Paulo');

// Definir constantes do sistema
define('ROOT_PATH', __DIR__);
define('CONFIG_PATH', ROOT_PATH . '/config');
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');
define('MODELS_PATH', ROOT_PATH . '/models');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('STORAGE_PATH', ROOT_PATH . '/storage');

// Incluir arquivos de configuração
require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/app.php';

// Autoloader simples para classes
spl_autoload_register(function ($class) {
    $paths = [
        CONTROLLERS_PATH . '/',
        MODELS_PATH . '/',
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Roteador simples
$page = $_GET['page'] ?? 'login';
$action = $_GET['action'] ?? 'index';

// Verificar se usuário está logado (exceto para login e auth)
if ($page !== 'login' && $page !== 'auth' && !isset($_SESSION['user_logged'])) {
    header('Location: index.php?page=login');
    exit;
}

// Mapear páginas para controllers
$controllers = [
    'login' => 'AuthController',
    'dashboard' => 'DashboardController',
    'users' => 'UsersController',
    'groups' => 'GroupsController',
    'config' => 'ConfigController',
    'auth' => 'AuthController'
];

try {
    if (isset($controllers[$page])) {
        $controllerClass = $controllers[$page];
        
        if (class_exists($controllerClass)) {
            $controller = new $controllerClass();
            
            if (method_exists($controller, $action)) {
                $controller->$action();
            } else if ($action !== 'index' && method_exists($controller, 'index')) {
                $controller->index();
            } else {
                throw new Exception("Método '$action' não encontrado no controller '$controllerClass'");
            }
        } else {
            throw new Exception("Controller '$controllerClass' não encontrado");
        }
    } else {
        // Página não encontrada, redirecionar para dashboard se logado, senão para login
        if (isset($_SESSION['user_logged'])) {
            header('Location: index.php?page=dashboard');
        } else {
            header('Location: index.php?page=login');
        }
        exit;
    }
} catch (Exception $e) {
    // Log do erro
    logMessage('ERROR', 'Erro no roteador: ' . $e->getMessage());
    
    // Se for uma requisição AJAX, retornar JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Erro interno do sistema: ' . $e->getMessage()
        ]);
        exit;
    }
    
    // Caso contrário, mostrar página de erro
    echo "<h1>Erro no Sistema</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><a href='index.php?page=login'>Voltar ao Login</a></p>";
}