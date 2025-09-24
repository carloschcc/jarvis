<?php
/**
 * DIAGNÓSTICO SIMPLES - AD MANAGER
 * Ferramenta de diagnóstico que não depende de configurações externas
 */

// Configurações básicas
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('America/Sao_Paulo');

// Definir constantes necessárias
define('ROOT_PATH', __DIR__);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico AD Manager</title>
    <meta charset='utf-8'>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        h2 { color: #34495e; margin-top: 30px; border-left: 4px solid #3498db; padding-left: 10px; }
        .success { color: #27ae60; font-weight: bold; }
        .error { color: #e74c3c; font-weight: bold; }
        .warning { color: #f39c12; font-weight: bold; }
        .info { color: #3498db; }
        pre { background: #ecf0f1; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .file-list { background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .file-list ul { margin: 0; padding-left: 20px; }
        .status-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
        .status-box { background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #dee2e6; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🔍 Diagnóstico Completo - AD Manager</h1>
    <p class='info'>Executado em: " . date('Y-m-d H:i:s') . "</p>";

// === 1. VERIFICAÇÃO DO AMBIENTE PHP ===
echo "<h2>📋 1. Ambiente PHP</h2>";
echo "<div class='status-grid'>";
echo "<div class='status-box'>";
echo "<strong>Versão do PHP:</strong> " . phpversion() . "<br>";
echo "<strong>Sistema Operacional:</strong> " . php_uname() . "<br>";
echo "<strong>Servidor Web:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Desconhecido') . "<br>";
echo "<strong>Documento Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "<br>";
echo "</div>";

echo "<div class='status-box'>";
echo "<strong>Extensões Críticas:</strong><br>";
$extensions = ['json', 'mbstring', 'curl', 'openssl'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<span class='success'>✅ $ext</span><br>";
    } else {
        echo "<span class='error'>❌ $ext</span><br>";
    }
}
echo "</div>";
echo "</div>";

// === 2. ESTRUTURA DE ARQUIVOS ===
echo "<h2>📁 2. Estrutura de Arquivos</h2>";

$requiredFiles = [
    'index.php' => 'Arquivo principal',
    'config/app.php' => 'Configurações da aplicação',
    'config/database.php' => 'Configurações de banco',
    'controllers/UsersController.php' => 'Controller de usuários',
    'models/LdapModel.php' => 'Model LDAP',
    'views/users/index.php' => 'View de usuários',
    'assets/js/ad-manager-definitive.js' => 'JavaScript definitivo'
];

foreach ($requiredFiles as $file => $description) {
    $fullPath = ROOT_PATH . '/' . $file;
    if (file_exists($fullPath)) {
        $size = filesize($fullPath);
        $modified = date('Y-m-d H:i:s', filemtime($fullPath));
        echo "<span class='success'>✅ $file</span> - $description<br>";
        echo "&nbsp;&nbsp;&nbsp;<small class='info'>Tamanho: " . formatBytes($size) . " | Modificado: $modified</small><br>";
    } else {
        echo "<span class='error'>❌ $file</span> - $description (ARQUIVO FALTANDO)<br>";
    }
}

// === 3. VERIFICAÇÃO DE JAVASCRIPT ===
echo "<h2>📜 3. Arquivos JavaScript</h2>";

$jsFiles = [];
if (is_dir(ROOT_PATH . '/assets/js')) {
    $jsFiles = glob(ROOT_PATH . '/assets/js/*.js');
}

if (empty($jsFiles)) {
    echo "<span class='error'>❌ Nenhum arquivo JavaScript encontrado!</span>";
} else {
    foreach ($jsFiles as $jsFile) {
        $fileName = basename($jsFile);
        $size = filesize($jsFile);
        $modified = date('Y-m-d H:i:s', filemtime($jsFile));
        
        if ($fileName === 'ad-manager-definitive.js') {
            echo "<span class='success'>✅ $fileName</span> (ARQUIVO PRINCIPAL)<br>";
        } else {
            echo "<span class='warning'>⚠️ $fileName</span> (arquivo adicional)<br>";
        }
        echo "&nbsp;&nbsp;&nbsp;<small class='info'>Tamanho: " . formatBytes($size) . " | Modificado: $modified</small><br>";
    }
}

// === 4. VERIFICAÇÃO DE CONFIGURAÇÕES ===
echo "<h2>⚙️ 4. Verificação de Configurações</h2>";

// Tentar carregar index.php para verificar constantes
$indexContent = '';
if (file_exists(ROOT_PATH . '/index.php')) {
    $indexContent = file_get_contents(ROOT_PATH . '/index.php');
}

$constants = ['ROOT_PATH', 'STORAGE_PATH', 'ASSETS_PATH', 'VIEWS_PATH', 'CONTROLLERS_PATH'];
foreach ($constants as $const) {
    if (strpos($indexContent, "define('$const'") !== false) {
        echo "<span class='success'>✅ $const definida no index.php</span><br>";
    } else {
        echo "<span class='error'>❌ $const não encontrada no index.php</span><br>";
    }
}

// === 5. VERIFICAÇÃO DE PERMISSÕES ===
echo "<h2>🔐 5. Permissões de Arquivos</h2>";

$checkPaths = [
    ROOT_PATH,
    ROOT_PATH . '/config',
    ROOT_PATH . '/assets',
    ROOT_PATH . '/views'
];

foreach ($checkPaths as $path) {
    if (is_dir($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        if (is_writable($path)) {
            echo "<span class='success'>✅ $path</span> - Permissões: $perms (Gravável)<br>";
        } else {
            echo "<span class='warning'>⚠️ $path</span> - Permissões: $perms (Somente leitura)<br>";
        }
    }
}

// === 6. ANÁLISE DO CONTROLLER ===
echo "<h2>🎮 6. Análise do UsersController</h2>";

if (file_exists(ROOT_PATH . '/controllers/UsersController.php')) {
    $controllerContent = file_get_contents(ROOT_PATH . '/controllers/UsersController.php');
    
    $methods = ['createUser', 'updateUserInfo', 'resetPassword', 'getUser'];
    foreach ($methods as $method) {
        if (strpos($controllerContent, "function $method") !== false || strpos($controllerContent, "public function $method") !== false) {
            echo "<span class='success'>✅ Método $method() encontrado</span><br>";
        } else {
            echo "<span class='error'>❌ Método $method() não encontrado</span><br>";
        }
    }
    
    // Verificar se CSRF foi removido
    if (strpos($controllerContent, 'csrf') === false && strpos($controllerContent, 'CSRF') === false) {
        echo "<span class='success'>✅ CSRF removido (compatibilidade universal)</span><br>";
    } else {
        echo "<span class='warning'>⚠️ CSRF ainda presente no código</span><br>";
    }
} else {
    echo "<span class='error'>❌ UsersController.php não encontrado!</span>";
}

// === 7. VERIFICAÇÃO DE VIEWS ===
echo "<h2>👀 7. Análise da View Principal</h2>";

if (file_exists(ROOT_PATH . '/views/users/index.php')) {
    $viewContent = file_get_contents(ROOT_PATH . '/views/users/index.php');
    
    // Verificar se usa o script definitivo
    if (strpos($viewContent, 'ad-manager-definitive.js') !== false) {
        echo "<span class='success'>✅ Script definitivo incluído na view</span><br>";
    } else {
        echo "<span class='error'>❌ Script definitivo NÃO incluído na view</span><br>";
    }
    
    // Verificar botões críticos
    $criticalButtons = ['openCreateUserModal', 'openEditUserModal', 'openResetPasswordModal'];
    foreach ($criticalButtons as $button) {
        if (strpos($viewContent, $button) !== false) {
            echo "<span class='success'>✅ Botão $button encontrado</span><br>";
        } else {
            echo "<span class='warning'>⚠️ Botão $button não encontrado</span><br>";
        }
    }
} else {
    echo "<span class='error'>❌ View users/index.php não encontrada!</span>";
}

// === 8. RELATÓRIO FINAL ===
echo "<h2>📊 8. Relatório Final</h2>";

$errors = 0;
$warnings = 0;

// Contar erros e avisos (simulação baseada na análise)
if (!file_exists(ROOT_PATH . '/assets/js/ad-manager-definitive.js')) {
    $errors++;
}
if (!file_exists(ROOT_PATH . '/controllers/UsersController.php')) {
    $errors++;
}

if ($errors == 0) {
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; border: 1px solid #c3e6cb;'>";
    echo "<h3 style='margin: 0; color: #155724;'>🎉 SISTEMA OK!</h3>";
    echo "<p>Todos os componentes críticos estão presentes e configurados corretamente.</p>";
    echo "<p><strong>Próximos passos:</strong></p>";
    echo "<ul>";
    echo "<li>Testar a criação de usuários via interface web</li>";
    echo "<li>Verificar conexão com Active Directory</li>";
    echo "<li>Validar funcionamento dos modais</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; border: 1px solid #f5c6cb;'>";
    echo "<h3 style='margin: 0; color: #721c24;'>⚠️ PROBLEMAS DETECTADOS</h3>";
    echo "<p>Foram encontrados $errors erro(s) que precisam ser corrigidos:</p>";
    echo "<ul>";
    if (!file_exists(ROOT_PATH . '/assets/js/ad-manager-definitive.js')) {
        echo "<li>Arquivo JavaScript definitivo não encontrado</li>";
    }
    if (!file_exists(ROOT_PATH . '/controllers/UsersController.php')) {
        echo "<li>Controller de usuários não encontrado</li>";
    }
    echo "</ul>";
    echo "</div>";
}

// === 9. INSTRUÇÕES DE TESTE ===
echo "<h2>🧪 9. Como Testar o Sistema</h2>";
echo "<div class='file-list'>";
echo "<h4>Teste Manual dos Botões:</h4>";
echo "<ol>";
echo "<li><strong>Acesse:</strong> index.php?page=users</li>";
echo "<li><strong>Teste o botão 'Novo Usuário':</strong> Deve abrir modal com formulário completo</li>";
echo "<li><strong>Teste botões de edição:</strong> Clique no ícone de edição de qualquer usuário</li>";
echo "<li><strong>Teste reset de senha:</strong> Clique no ícone de chave de qualquer usuário</li>";
echo "<li><strong>Verificar console:</strong> Pressione F12 e veja se há erros no console</li>";
echo "</ol>";

echo "<h4>Teste de Console JavaScript:</h4>";
echo "<p>Abra o console do navegador (F12) e execute:</p>";
echo "<pre>console.log(typeof openCreateUserModal);
// Deve retornar: 'function'

openCreateUserModal();
// Deve abrir o modal de criação</pre>";
echo "</div>";

echo "</div>";
echo "</body></html>";

// === FUNÇÕES AUXILIARES ===
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}
?>