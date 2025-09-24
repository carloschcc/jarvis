<?php
/**
 * Script de teste para verificar conectividade e configurações
 * Acesse este arquivo via: http://IP/jarvis-main/test-connection.php
 */

// Configurar cabeçalhos
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Conectividade - AD Manager</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 800px; }
        .header { color: #0078d4; border-bottom: 2px solid #0078d4; padding-bottom: 10px; margin-bottom: 20px; }
        .success { color: #107c10; background: #f3ffff; padding: 10px; border-left: 4px solid #107c10; margin: 10px 0; }
        .error { color: #d13438; background: #fef7f7; padding: 10px; border-left: 4px solid #d13438; margin: 10px 0; }
        .info { color: #0078d4; background: #f3f9ff; padding: 10px; border-left: 4px solid #0078d4; margin: 10px 0; }
        .warning { color: #ff8c00; background: #fffaf0; padding: 10px; border-left: 4px solid #ff8c00; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #0078d4; color: white; }
        tr:hover { background-color: #f5f5f5; }
        .btn { background: #0078d4; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 5px; }
        .btn:hover { background: #106ebe; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="header">🔧 AD Manager - Teste de Conectividade</h1>
        
        <?php
        // Função para verificar status de um item
        function checkStatus($condition, $success_msg, $error_msg) {
            if ($condition) {
                echo "<div class='success'>✅ $success_msg</div>";
                return true;
            } else {
                echo "<div class='error'>❌ $error_msg</div>";
                return false;
            }
        }
        
        echo "<h2>📊 Informações do Servidor</h2>";
        
        // Informações básicas
        echo "<table>";
        echo "<tr><th>Parâmetro</th><th>Valor</th></tr>";
        echo "<tr><td><strong>Endereço do Servidor</strong></td><td>" . ($_SERVER['SERVER_ADDR'] ?? 'N/A') . "</td></tr>";
        echo "<tr><td><strong>Nome do Host</strong></td><td>" . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "</td></tr>";
        echo "<tr><td><strong>IP do Cliente</strong></td><td>" . ($_SERVER['REMOTE_ADDR'] ?? 'N/A') . "</td></tr>";
        echo "<tr><td><strong>User Agent</strong></td><td>" . ($_SERVER['HTTP_USER_AGENT'] ?? 'N/A') . "</td></tr>";
        echo "<tr><td><strong>Protocolo</strong></td><td>" . (isset($_SERVER['HTTPS']) ? 'HTTPS' : 'HTTP') . "</td></tr>";
        echo "<tr><td><strong>Porta</strong></td><td>" . ($_SERVER['SERVER_PORT'] ?? 'N/A') . "</td></tr>";
        echo "<tr><td><strong>Método</strong></td><td>" . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "</td></tr>";
        echo "<tr><td><strong>URI</strong></td><td>" . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "</td></tr>";
        echo "</table>";
        
        echo "<h2>🔍 Verificações do Sistema</h2>";
        
        // Verificar PHP
        $php_version = phpversion();
        checkStatus(version_compare($php_version, '7.4.0', '>='), 
                   "PHP $php_version instalado (compatível)", 
                   "PHP $php_version - versão muito antiga, recomenda-se 7.4+");
        
        // Verificar extensão LDAP
        checkStatus(extension_loaded('ldap'), 
                   "Extensão LDAP habilitada", 
                   "Extensão LDAP não encontrada - necessária para Active Directory");
        
        // Verificar outras extensões necessárias
        $extensions = ['json', 'mbstring', 'session'];
        foreach ($extensions as $ext) {
            checkStatus(extension_loaded($ext), 
                       "Extensão $ext disponível", 
                       "Extensão $ext não encontrada");
        }
        
        // Verificar permissões de escrita
        $storage_dir = __DIR__ . '/storage';
        checkStatus(is_dir($storage_dir) && is_writable($storage_dir), 
                   "Diretório storage/ tem permissões de escrita", 
                   "Diretório storage/ sem permissões de escrita");
        
        // Testar sessões
        session_start();
        $_SESSION['test'] = 'ok';
        checkStatus(isset($_SESSION['test']) && $_SESSION['test'] === 'ok', 
                   "Sessões PHP funcionando corretamente", 
                   "Problema com sessões PHP");
        
        echo "<h2>🌐 Teste de Acesso a Rede</h2>";
        
        // Verificar se é rede local
        $client_ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $is_local = (
            strpos($client_ip, '127.') === 0 ||
            strpos($client_ip, '10.') === 0 ||
            strpos($client_ip, '192.168.') === 0 ||
            preg_match('/^172\.(1[6-9]|2[0-9]|3[01])\./', $client_ip)
        );
        
        if ($is_local) {
            echo "<div class='success'>✅ Acesso via rede local detectado ($client_ip)</div>";
        } else {
            echo "<div class='warning'>⚠️ Acesso via IP público ($client_ip) - verifique configurações de segurança</div>";
        }
        
        echo "<h2>🔐 Teste de Configuração CSRF</h2>";
        
        // Simular o que acontece no AuthController
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $isDevMode = $host === 'localhost:8080' || 
                    strpos($host, 'localhost') !== false ||
                    strpos($host, '127.0.0.1') !== false ||
                    strpos($host, '.e2b.dev') !== false ||
                    preg_match('/^10\.\d+\.\d+\.\d+/', $host) ||
                    preg_match('/^192\.168\.\d+\.\d+/', $host) ||
                    preg_match('/^172\.(1[6-9]|2[0-9]|3[01])\.\d+\.\d+/', $host) ||
                    !isset($_SERVER['HTTPS']);
        
        if ($isDevMode) {
            echo "<div class='success'>✅ CSRF token será menos rigoroso para este host ($host)</div>";
        } else {
            echo "<div class='error'>❌ CSRF token será rigoroso - pode causar erro de 'Token de segurança inválido'</div>";
        }
        
        echo "<h2>🔗 Links de Teste</h2>";
        
        $base_url = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
        
        echo "<p>Use estes links para acessar o sistema:</p>";
        echo "<a href='$base_url/index.php' class='btn'>🏠 Página Principal</a>";
        echo "<a href='$base_url/index.php?page=login' class='btn'>🔐 Login</a>";
        echo "<a href='$base_url/xampp-ldap-diagnostic.php' class='btn'>🔍 Diagnóstico LDAP</a>";
        
        echo "<h2>📋 Resumo</h2>";
        
        if (extension_loaded('ldap') && is_writable($storage_dir) && $isDevMode) {
            echo "<div class='success'>✅ <strong>Sistema pronto para uso!</strong> Todas as verificações passaram.</div>";
        } else {
            echo "<div class='warning'>⚠️ <strong>Atenção:</strong> Algumas configurações precisam ser ajustadas antes do uso em produção.</div>";
        }
        
        echo "<div class='info'>
        <strong>Próximos passos:</strong><br>
        1. Acesse o sistema via link acima<br>
        2. Use credenciais: admin / admin123<br>
        3. Configure o LDAP/Active Directory em Configurações<br>
        4. Teste a conectividade com seu Domain Controller
        </div>";
        
        echo "<p><strong>Data/Hora:</strong> " . date('Y-m-d H:i:s T') . "</p>";
        ?>
    </div>
</body>
</html>