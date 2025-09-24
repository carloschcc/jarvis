<?php
/**
 * AD Manager - Sistema de Gestão de Usuários Active Directory
 * 
 * @version 1.0
 * @author Sistema AD Manager
 * @description Sistema completo para gestão de usuários do Active Directory
 */

session_start();

// Configurações básicas para XAMPP
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
date_default_timezone_set('America/Sao_Paulo');

// Verificar se o sistema está funcionando
if (!function_exists('session_start')) {
    die('ERRO: PHP não está configurado corretamente. Verifique se o XAMPP está rodando.');
}

// Definir constantes do sistema
define('ROOT_PATH', __DIR__);
define('CONFIG_PATH', ROOT_PATH . '/config');
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');
define('MODELS_PATH', ROOT_PATH . '/models');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('STORAGE_PATH', ROOT_PATH . '/storage');

// Incluir arquivos de configuração com verificação
if (!file_exists(CONFIG_PATH . '/database.php')) {
    die('ERRO: Arquivo de configuração não encontrado. Verifique se descompactou todos os arquivos na pasta htdocs.');
}

if (!file_exists(CONFIG_PATH . '/app.php')) {
    die('ERRO: Arquivo app.php não encontrado. Verifique a instalação.');
}

// Incluir configurações XAMPP se existirem
if (file_exists(CONFIG_PATH . '/xampp.php')) {
    require_once CONFIG_PATH . '/xampp.php';
}

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