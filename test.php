<?php
echo "<h1>TESTE XAMPP - AD Manager</h1>";
echo "<p>Se você consegue ver esta mensagem, o PHP está funcionando!</p>";
echo "<p>Versão PHP: " . phpversion() . "</p>";
echo "<p>Data/Hora: " . date('Y-m-d H:i:s') . "</p>";

// Testar se os diretórios existem
$dirs = ['config', 'controllers', 'models', 'views', 'assets', 'storage'];
echo "<h2>Teste de Diretórios:</h2>";
foreach($dirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    if(is_dir($path)) {
        echo "✅ $dir - OK<br>";
    } else {
        echo "❌ $dir - ERRO (não encontrado)<br>";
    }
}

// Testar se arquivos críticos existem
$files = [
    'config/app.php',
    'config/database.php', 
    'controllers/AuthController.php',
    'models/AuthModel.php',
    'views/auth/login.php'
];
echo "<h2>Teste de Arquivos:</h2>";
foreach($files as $file) {
    $path = __DIR__ . '/' . $file;
    if(file_exists($path)) {
        echo "✅ $file - OK<br>";
    } else {
        echo "❌ $file - ERRO (não encontrado)<br>";
    }
}

echo "<hr>";
echo "<p><a href='index.php'>Ir para o Sistema AD Manager</a></p>";
?>