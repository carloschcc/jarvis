<?php
/**
 * Diagnóstico XAMPP LDAP
 * Este arquivo ajuda a diagnosticar problemas com a extensão LDAP no XAMPP
 */

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico XAMPP LDAP - AD Manager</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            color: #2c5aa0;
            margin-bottom: 30px;
        }
        .status {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            font-weight: bold;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .section {
            margin: 25px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #2c5aa0;
        }
        .fix-steps {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .fix-steps h3 {
            color: #0066cc;
            margin-top: 0;
        }
        .fix-steps ol {
            margin: 0;
            padding-left: 20px;
        }
        .fix-steps li {
            margin: 10px 0;
            line-height: 1.6;
        }
        .code {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
            border: 1px solid #ddd;
        }
        .path {
            color: #666;
            font-family: monospace;
            background: #f8f8f8;
            padding: 2px 6px;
            border-radius: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #2c5aa0;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
            font-weight: bold;
        }
        .btn:hover {
            background: #1e3f73;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-danger {
            background: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Diagnóstico XAMPP LDAP</h1>
            <p>Sistema de Gestão Active Directory</p>
        </div>

        <?php
        // Informações básicas do PHP
        $phpVersion = PHP_VERSION;
        $xamppDetected = false;
        $phpIniPath = php_ini_loaded_file();
        $phpIniDir = dirname($phpIniPath);
        
        // Detectar se é XAMPP
        if (strpos($phpIniPath, 'xampp') !== false || strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'Apache') !== false) {
            $xamppDetected = true;
        }
        
        // Verificar extensão LDAP
        $ldapLoaded = extension_loaded('ldap');
        $ldapFunctions = function_exists('ldap_connect');
        
        // Obter informações da extensão
        $loadedExtensions = get_loaded_extensions();
        $phpExtensionsDir = ini_get('extension_dir');
        
        // Status geral
        echo '<div class="section">';
        echo '<h2>📋 Status Geral</h2>';
        
        if ($ldapLoaded && $ldapFunctions) {
            echo '<div class="status success">✅ Extensão LDAP está FUNCIONANDO corretamente!</div>';
        } else {
            echo '<div class="status error">❌ Extensão LDAP NÃO está disponível</div>';
        }
        
        echo "<table>";
        echo "<tr><th>Item</th><th>Status</th><th>Detalhes</th></tr>";
        echo "<tr><td>PHP Version</td><td>{$phpVersion}</td><td>" . (version_compare($phpVersion, '7.4.0') >= 0 ? '✅ Compatível' : '⚠️ Recomendado PHP 7.4+') . "</td></tr>";
        echo "<tr><td>XAMPP Detectado</td><td>" . ($xamppDetected ? 'Sim' : 'Não') . "</td><td>" . ($xamppDetected ? '✅ Ambiente XAMPP' : '⚠️ Ambiente não identificado') . "</td></tr>";
        echo "<tr><td>Extensão LDAP</td><td>" . ($ldapLoaded ? 'Carregada' : 'NÃO Carregada') . "</td><td>" . ($ldapLoaded ? '✅ OK' : '❌ Não encontrada') . "</td></tr>";
        echo "<tr><td>Funções LDAP</td><td>" . ($ldapFunctions ? 'Disponíveis' : 'Indisponíveis') . "</td><td>" . ($ldapFunctions ? '✅ OK' : '❌ Não funcionais') . "</td></tr>";
        echo "</table>";
        echo '</div>';

        // Configurações do PHP
        echo '<div class="section">';
        echo '<h2>⚙️ Configurações PHP</h2>';
        echo "<table>";
        echo "<tr><th>Configuração</th><th>Valor</th></tr>";
        echo "<tr><td>php.ini</td><td><span class='path'>{$phpIniPath}</span></td></tr>";
        echo "<tr><td>Extensions Dir</td><td><span class='path'>{$phpExtensionsDir}</span></td></tr>";
        
        if ($xamppDetected) {
            $xamppRoot = dirname(dirname($phpIniPath));
            echo "<tr><td>XAMPP Root</td><td><span class='path'>{$xamppRoot}</span></td></tr>";
        }
        echo "</table>";
        echo '</div>';

        // Extensões carregadas
        echo '<div class="section">';
        echo '<h2>📦 Extensões Carregadas</h2>';
        
        // Filtrar extensões relacionadas
        $relevantExtensions = array_filter($loadedExtensions, function($ext) {
            return in_array(strtolower($ext), ['ldap', 'openssl', 'mbstring', 'curl', 'json']);
        });
        
        if (!empty($relevantExtensions)) {
            echo '<div class="status info">Extensões Relevantes Encontradas:</div>';
            echo '<div class="code">' . implode(', ', $relevantExtensions) . '</div>';
        }
        
        if (in_array('ldap', $loadedExtensions)) {
            echo '<div class="status success">✅ LDAP encontrado na lista de extensões</div>';
        } else {
            echo '<div class="status error">❌ LDAP não encontrado na lista de extensões</div>';
        }
        echo '</div>';

        // Teste de funções LDAP
        if ($ldapLoaded) {
            echo '<div class="section">';
            echo '<h2>🧪 Teste de Funções LDAP</h2>';
            
            $ldapFunctionTests = [
                'ldap_connect' => 'Conectar ao servidor',
                'ldap_bind' => 'Autenticar',
                'ldap_search' => 'Buscar entradas',
                'ldap_get_entries' => 'Obter resultados',
                'ldap_close' => 'Fechar conexão',
                'ldap_escape' => 'Escapar valores (PHP 5.6+)',
                'ldap_set_option' => 'Definir opções'
            ];
            
            echo "<table>";
            echo "<tr><th>Função</th><th>Status</th><th>Descrição</th></tr>";
            
            foreach ($ldapFunctionTests as $func => $desc) {
                $exists = function_exists($func);
                $status = $exists ? '✅ Disponível' : '❌ Não encontrada';
                echo "<tr><td><code>{$func}</code></td><td>{$status}</td><td>{$desc}</td></tr>";
            }
            echo "</table>";
            echo '</div>';
        }

        // Instruções de correção se LDAP não estiver funcionando
        if (!$ldapLoaded || !$ldapFunctions) {
            echo '<div class="fix-steps">';
            echo '<h3>🔧 Como Habilitar LDAP no XAMPP</h3>';
            echo '<ol>';
            
            if ($xamppDetected) {
                $xamppRoot = dirname(dirname($phpIniPath));
                echo '<li><strong>Localize o arquivo php.ini:</strong><br>';
                echo "<span class='path'>{$phpIniPath}</span></li>";
                
                echo '<li><strong>Abra o php.ini em um editor de texto (como Notepad++)</strong></li>';
                
                echo '<li><strong>Procure pela linha:</strong><br>';
                echo '<div class="code">;extension=ldap</div>';
                echo 'ou<br>';
                echo '<div class="code">;extension=php_ldap.dll</div></li>';
                
                echo '<li><strong>Remova o ponto-e-vírgula (;) do início da linha:</strong><br>';
                echo '<div class="code">extension=ldap</div>';
                echo 'ou<br>';
                echo '<div class="code">extension=php_ldap.dll</div></li>';
                
                echo '<li><strong>Salve o arquivo php.ini</strong></li>';
                
                echo '<li><strong>Reinicie o Apache no XAMPP Control Panel</strong></li>';
                
                echo '<li><strong>Recarregue esta página para verificar</strong></li>';
                
                echo '</ol>';
                
                echo '<div class="status warning">';
                echo '<strong>⚠️ Importante:</strong> Certifique-se de que está editando o php.ini correto. ';
                echo 'O XAMPP pode ter múltiplos arquivos php.ini.';
                echo '</div>';
                
                // Verificação adicional de arquivos
                $possibleIniFiles = [
                    $xamppRoot . '/php/php.ini',
                    $xamppRoot . '/apache/bin/php.ini',
                    $phpIniPath
                ];
                
                echo '<div class="status info">';
                echo '<strong>📁 Possíveis localizações do php.ini:</strong><br>';
                foreach ($possibleIniFiles as $file) {
                    $exists = file_exists($file) ? '✅' : '❌';
                    echo "<span class='path'>{$exists} {$file}</span><br>";
                }
                echo '</div>';
                
            } else {
                echo '<li><strong>Para ambientes não-XAMPP:</strong></li>';
                echo '<li>No Ubuntu/Debian: <div class="code">sudo apt-get install php-ldap</div></li>';
                echo '<li>No CentOS/RHEL: <div class="code">sudo yum install php-ldap</div></li>';
                echo '<li>No Windows: Habilite a extensão no php.ini</li>';
                echo '<li>Reinicie o servidor web</li>';
            }
            
            echo '</div>';
            
            // Verificação manual de arquivos DLL (Windows)
            if ($xamppDetected && PHP_OS_FAMILY === 'Windows') {
                echo '<div class="section">';
                echo '<h2>🔍 Verificação de Arquivos DLL (Windows)</h2>';
                
                $dllFiles = [
                    $phpExtensionsDir . '/php_ldap.dll',
                    dirname($phpExtensionsDir) . '/php_ldap.dll',
                    $xamppRoot . '/php/ext/php_ldap.dll'
                ];
                
                echo '<table>';
                echo '<tr><th>Arquivo DLL</th><th>Status</th></tr>';
                
                foreach ($dllFiles as $dll) {
                    $exists = file_exists($dll);
                    $status = $exists ? '✅ Encontrado' : '❌ Não encontrado';
                    echo "<tr><td><span class='path'>{$dll}</span></td><td>{$status}</td></tr>";
                }
                echo '</table>';
                
                if (!file_exists($phpExtensionsDir . '/php_ldap.dll')) {
                    echo '<div class="status error">';
                    echo '<strong>❌ Arquivo php_ldap.dll não encontrado!</strong><br>';
                    echo 'Você pode precisar baixar uma versão do XAMPP que inclua a extensão LDAP ou instalar a extensão manualmente.';
                    echo '</div>';
                }
                echo '</div>';
            }
        }

        // Teste de conexão simples (se LDAP estiver funcionando)
        if ($ldapLoaded && $ldapFunctions) {
            echo '<div class="section">';
            echo '<h2>🌐 Teste de Conexão Simples</h2>';
            
            echo '<div class="status info">';
            echo 'A extensão LDAP está funcionando. Você pode agora configurar a conexão com seu Active Directory.';
            echo '</div>';
            
            echo '<a href="index.php?page=config" class="btn btn-success">Ir para Configurações LDAP</a>';
            echo '<a href="index.php" class="btn">Voltar ao Sistema</a>';
            echo '</div>';
        }

        // Informações de contato/ajuda
        echo '<div class="section">';
        echo '<h2>📞 Precisa de Ajuda?</h2>';
        echo '<p>Se você seguiu todos os passos e ainda tem problemas:</p>';
        echo '<ul>';
        echo '<li>Verifique se está usando uma versão recente do XAMPP</li>';
        echo '<li>Tente reinstalar o XAMPP com todas as extensões</li>';
        echo '<li>Verifique se há conflitos com antivírus</li>';
        echo '<li>Consulte a documentação oficial do XAMPP</li>';
        echo '</ul>';
        echo '</div>';
        ?>

        <div style="text-align: center; margin-top: 30px;">
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn">🔄 Executar Diagnóstico Novamente</a>
        </div>
    </div>
</body>
</html>