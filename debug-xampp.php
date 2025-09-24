<?php
/**
 * Debug para XAMPP - Identificar problemas comuns
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Debug XAMPP - AD Manager</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .ok { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🔍 Debug XAMPP - AD Manager</h1>
    
    <h2>📋 Informações do Sistema</h2>
    <ul>
        <li><strong>PHP Version:</strong> <?= phpversion() ?></li>
        <li><strong>Server:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' ?></li>
        <li><strong>Document Root:</strong> <?= $_SERVER['DOCUMENT_ROOT'] ?? 'N/A' ?></li>
        <li><strong>Script Path:</strong> <?= __FILE__ ?></li>
        <li><strong>URL Atual:</strong> <?= 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $_SERVER['REQUEST_URI'] ?></li>
    </ul>

    <h2>🔧 Extensões PHP</h2>
    <?php
    $required = ['json', 'session', 'mbstring', 'curl', 'openssl'];
    foreach($required as $ext) {
        if(extension_loaded($ext)) {
            echo "<span class='ok'>✅ $ext</span><br>";
        } else {
            echo "<span class='error'>❌ $ext (FALTANDO)</span><br>";
        }
    }
    ?>

    <h2>📁 Estrutura de Arquivos</h2>
    <?php
    $dirs = ['config', 'controllers', 'models', 'views', 'assets', 'storage'];
    foreach($dirs as $dir) {
        if(is_dir(__DIR__ . '/' . $dir)) {
            $files = count(glob(__DIR__ . '/' . $dir . '/*'));
            echo "<span class='ok'>✅ /$dir ($files arquivos)</span><br>";
        } else {
            echo "<span class='error'>❌ /$dir (NÃO ENCONTRADO)</span><br>";
        }
    }
    ?>

    <h2>🔑 Arquivos Críticos</h2>
    <?php
    $files = [
        'index.php',
        'config/app.php',
        'config/database.php',
        'controllers/AuthController.php',
        'models/AuthModel.php',
        'views/auth/login.php',
        'assets/css/style.css',
        'assets/js/script.js'
    ];
    
    foreach($files as $file) {
        if(file_exists(__DIR__ . '/' . $file)) {
            $size = number_format(filesize(__DIR__ . '/' . $file));
            echo "<span class='ok'>✅ $file ($size bytes)</span><br>";
        } else {
            echo "<span class='error'>❌ $file (NÃO ENCONTRADO)</span><br>";
        }
    }
    ?>

    <h2>📝 Permissões</h2>
    <?php
    $storage = __DIR__ . '/storage';
    if(is_dir($storage)) {
        if(is_writable($storage)) {
            echo "<span class='ok'>✅ /storage (gravável)</span><br>";
        } else {
            echo "<span class='error'>❌ /storage (sem permissão de escrita)</span><br>";
        }
    } else {
        echo "<span class='warning'>⚠️ /storage (pasta não existe)</span><br>";
    }
    ?>

    <h2>🌐 Teste de Requisição</h2>
    <?php
    $test_url = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . dirname($_SERVER['REQUEST_URI']) . '/index.php';
    echo "<p>URL de teste: <code>$test_url</code></p>";
    
    if(function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $test_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HEADER, true);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if($http_code == 200) {
            echo "<span class='ok'>✅ Sistema respondendo (HTTP $http_code)</span>";
        } else {
            echo "<span class='error'>❌ Erro na resposta (HTTP $http_code)</span>";
        }
    } else {
        echo "<span class='warning'>⚠️ CURL não disponível para teste</span>";
    }
    ?>

    <h2>🔗 Próximos Passos</h2>
    <p>
        <a href="test.php">🧪 Teste Básico</a> |
        <a href="index.php">🚀 Acessar AD Manager</a>
    </p>

    <hr>
    <h2>🐛 Debug Info (Técnico)</h2>
    <pre><?php
    echo "PHP_SELF: " . ($_SERVER['PHP_SELF'] ?? 'N/A') . "\n";
    echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
    echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
    echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "\n";
    echo "SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'N/A') . "\n";
    echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
    ?></pre>
</body>
</html>