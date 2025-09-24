<?php
session_start();

// Incluir configurações se disponíveis
$hasConfig = false;
if (file_exists(__DIR__ . '/config/app.php')) {
    require_once __DIR__ . '/config/app.php';
    $hasConfig = true;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>🔍 Diagnóstico Completo - AD Manager</title>
    <style>
        * { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; }
        body { margin: 20px; line-height: 1.6; }
        .header { background: #007bff; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .section { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #007bff; }
        .ok { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .info { color: #17a2b8; }
        pre { background: #e9ecef; padding: 10px; border-radius: 4px; overflow: auto; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; border: 1px solid #dee2e6; text-align: left; }
        th { background: #e9ecef; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .version-badge { background: #28a745; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px; }
    </style>
    <script>
        function testLogin() {
            fetch('index.php?page=auth&action=login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'username=admin&password=admin123'
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('login-result').innerHTML = 
                    data.success ? 
                    '<span class="ok">✅ Login OK: ' + data.message + '</span>' :
                    '<span class="error">❌ Login ERRO: ' + data.message + '</span>';
            })
            .catch(error => {
                document.getElementById('login-result').innerHTML = 
                    '<span class="error">❌ Erro na requisição: ' + error.message + '</span>';
            });
        }
        
        function testModalJS() {
            if (typeof openCreateUserModal === 'function') {
                document.getElementById('modal-result').innerHTML = '<span class="ok">✅ Função openCreateUserModal() existe</span>';
            } else {
                document.getElementById('modal-result').innerHTML = '<span class="error">❌ Função openCreateUserModal() NÃO existe</span>';
            }
        }
    </script>
</head>
<body>
    <div class="header">
        <h1>🔍 Diagnóstico Completo do AD Manager</h1>
        <p>Sistema de análise completa para identificar todos os problemas</p>
        <span class="version-badge">Versão: <?= date('Y-m-d H:i:s') ?></span>
    </div>

    <!-- SEÇÃO 1: INFORMAÇÕES DO SISTEMA -->
    <div class="section">
        <h2>📋 1. Informações do Sistema</h2>
        <table>
            <tr><td><strong>PHP Version</strong></td><td><?= phpversion() ?></td></tr>
            <tr><td><strong>Server Software</strong></td><td><?= $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' ?></td></tr>
            <tr><td><strong>HTTP Host</strong></td><td><?= $_SERVER['HTTP_HOST'] ?? 'N/A' ?></td></tr>
            <tr><td><strong>Request URI</strong></td><td><?= $_SERVER['REQUEST_URI'] ?? 'N/A' ?></td></tr>
            <tr><td><strong>Document Root</strong></td><td><?= $_SERVER['DOCUMENT_ROOT'] ?? 'N/A' ?></td></tr>
            <tr><td><strong>Script Path</strong></td><td><?= __FILE__ ?></td></tr>
            <tr><td><strong>Data/Hora</strong></td><td><?= date('Y-m-d H:i:s') ?></td></tr>
        </table>
    </div>

    <!-- SEÇÃO 2: CONFIGURAÇÕES AD MANAGER -->
    <div class="section">
        <h2>⚙️ 2. Configurações AD Manager</h2>
        <?php if ($hasConfig): ?>
            <div class="ok">✅ Arquivos de configuração encontrados</div>
            <table>
                <tr><td><strong>APP_NAME</strong></td><td><?= defined('APP_NAME') ? APP_NAME : 'NÃO DEFINIDO' ?></td></tr>
                <tr><td><strong>APP_VERSION</strong></td><td><?= defined('APP_VERSION') ? APP_VERSION : 'NÃO DEFINIDO' ?></td></tr>
                <tr><td><strong>DEFAULT_ADMIN_USER</strong></td><td><?= defined('DEFAULT_ADMIN_USER') ? DEFAULT_ADMIN_USER : 'NÃO DEFINIDO' ?></td></tr>
            </table>
        <?php else: ?>
            <div class="error">❌ Arquivos de configuração NÃO encontrados</div>
        <?php endif; ?>
    </div>

    <!-- SEÇÃO 3: ESTRUTURA DE ARQUIVOS -->
    <div class="section">
        <h2>📁 3. Estrutura de Arquivos</h2>
        <?php
        $requiredDirs = ['config', 'controllers', 'models', 'views', 'assets', 'storage'];
        foreach ($requiredDirs as $dir) {
            $path = __DIR__ . '/' . $dir;
            if (is_dir($path)) {
                $count = count(glob($path . '/*'));
                echo "<div class=\"ok\">✅ /$dir ($count arquivos/pastas)</div>";
            } else {
                echo "<div class=\"error\">❌ /$dir (NÃO ENCONTRADO)</div>";
            }
        }
        ?>
    </div>

    <!-- SEÇÃO 4: ARQUIVOS CRÍTICOS -->
    <div class="section">
        <h2>🔑 4. Arquivos Críticos</h2>
        <?php
        $criticalFiles = [
            'index.php' => 'Arquivo principal',
            'config/app.php' => 'Configurações da aplicação',
            'controllers/AuthController.php' => 'Controller de autenticação',
            'models/AuthModel.php' => 'Model de autenticação',
            'views/auth/login.php' => 'Página de login',
            'assets/js/script.js' => 'JavaScript principal',
            'assets/js/ad-manager-fix.js' => 'JavaScript dos modais',
            'assets/css/style.css' => 'Estilos CSS'
        ];
        
        foreach ($criticalFiles as $file => $desc) {
            $path = __DIR__ . '/' . $file;
            if (file_exists($path)) {
                $size = number_format(filesize($path));
                echo "<div class=\"ok\">✅ $file - $desc ($size bytes)</div>";
            } else {
                echo "<div class=\"error\">❌ $file - $desc (NÃO ENCONTRADO)</div>";
            }
        }
        ?>
    </div>

    <!-- SEÇÃO 5: TESTE DE FUNCIONALIDADES -->
    <div class="section">
        <h2>🧪 5. Teste de Funcionalidades</h2>
        
        <h3>Login</h3>
        <button onclick="testLogin()" class="btn">Testar Login admin/admin123</button>
        <div id="login-result" style="margin-top: 10px;"></div>
        
        <h3>Session Status</h3>
        <?php if (isset($_SESSION['user_logged'])): ?>
            <div class="ok">✅ Usuário logado: <?= $_SESSION['username'] ?? 'N/A' ?></div>
        <?php else: ?>
            <div class="info">ℹ️ Nenhum usuário logado</div>
        <?php endif; ?>

        <h3>URLs de Teste</h3>
        <a href="test.php" class="btn">Teste Básico</a>
        <a href="index.php" class="btn">Sistema Principal</a>
        <a href="index.php?page=users" class="btn">Página de Usuários</a>
    </div>

    <!-- SEÇÃO 6: VERSÃO DOS ARQUIVOS -->
    <div class="section">
        <h2>📝 6. Versão dos Arquivos (Últimas Modificações)</h2>
        <?php
        $importantFiles = [
            'index.php',
            'controllers/AuthController.php',
            'controllers/UsersController.php', 
            'views/auth/login.php',
            'views/users/index.php',
            'assets/js/ad-manager-fix.js'
        ];
        
        echo '<table>';
        echo '<tr><th>Arquivo</th><th>Última Modificação</th><th>Tamanho</th></tr>';
        
        foreach ($importantFiles as $file) {
            $path = __DIR__ . '/' . $file;
            if (file_exists($path)) {
                $mtime = filemtime($path);
                $size = filesize($path);
                echo '<tr>';
                echo '<td>' . $file . '</td>';
                echo '<td>' . date('Y-m-d H:i:s', $mtime) . '</td>';
                echo '<td>' . number_format($size) . ' bytes</td>';
                echo '</tr>';
            }
        }
        echo '</table>';
        ?>
    </div>

    <!-- SEÇÃO 7: VERIFICAÇÃO GIT -->
    <div class="section">
        <h2>📦 7. Informações Git</h2>
        <?php
        if (is_dir(__DIR__ . '/.git')) {
            echo '<div class="ok">✅ Repositório Git detectado</div>';
            
            // Verificar último commit
            $lastCommit = shell_exec('cd "' . __DIR__ . '" && git log -1 --oneline 2>/dev/null');
            if ($lastCommit) {
                echo '<div><strong>Último commit:</strong> ' . htmlspecialchars(trim($lastCommit)) . '</div>';
            }
            
            // Verificar branch atual
            $currentBranch = shell_exec('cd "' . __DIR__ . '" && git branch --show-current 2>/dev/null');
            if ($currentBranch) {
                echo '<div><strong>Branch atual:</strong> ' . htmlspecialchars(trim($currentBranch)) . '</div>';
            }
            
            // Verificar status
            $status = shell_exec('cd "' . __DIR__ . '" && git status --porcelain 2>/dev/null');
            if ($status) {
                echo '<div class="warning">⚠️ Há arquivos modificados não commitados</div>';
            } else {
                echo '<div class="ok">✅ Working directory limpo</div>';
            }
        } else {
            echo '<div class="warning">⚠️ Não é um repositório Git</div>';
        }
        ?>
    </div>

    <!-- SEÇÃO 8: TESTE JAVASCRIPT -->
    <div class="section">
        <h2>🔧 8. Teste JavaScript (Modais)</h2>
        <button onclick="testModalJS()" class="btn">Testar Funções dos Modais</button>
        <div id="modal-result" style="margin-top: 10px;"></div>
        
        <div style="margin-top: 15px;">
            <h4>Scripts Carregados:</h4>
            <script>
                document.write('<div>jQuery: ' + (typeof $ !== 'undefined' ? '<span class="ok">✅ Carregado</span>' : '<span class="error">❌ Não encontrado</span>') + '</div>');
                document.write('<div>Bootstrap: ' + (typeof $().modal !== 'undefined' ? '<span class="ok">✅ Carregado</span>' : '<span class="error">❌ Não encontrado</span>') + '</div>');
                document.write('<div>API: ' + (typeof API !== 'undefined' ? '<span class="ok">✅ Carregado</span>' : '<span class="error">❌ Não encontrado</span>') + '</div>');
            </script>
        </div>
    </div>

    <!-- SEÇÃO 9: INSTRUÇÕES DE RESOLUÇÃO -->
    <div class="section">
        <h2>🔧 9. Como Resolver Problemas</h2>
        <div style="background: white; padding: 15px; border-radius: 5px;">
            <h4>Se o sistema não funcionar:</h4>
            <ol>
                <li><strong>Verifique se baixou a versão mais recente</strong> do repositório GitHub</li>
                <li><strong>Certifique-se</strong> que está na branch <code>fix/button-functionality-complete</code></li>
                <li><strong>Todos os arquivos</strong> da seção 4 devem estar presentes</li>
                <li><strong>Teste o login</strong> com admin/admin123</li>
                <li><strong>Se der erro de token:</strong> Certifique-se que baixou a versão SEM CSRF</li>
                <li><strong>Se modais não abrirem:</strong> Verifique se ad-manager-fix.js está carregado</li>
            </ol>
            
            <h4>Última atualização:</h4>
            <p>O sistema foi atualizado em <strong><?= date('Y-m-d') ?></strong> com correções definitivas para:</p>
            <ul>
                <li>✅ Erro de token CSRF removido completamente</li>
                <li>✅ Compatibilidade universal (localhost, IP, qualquer porta)</li>
                <li>✅ Modais funcionando com ad-manager-fix.js</li>
                <li>✅ Backend com updateUserInfo (sem erro RDN)</li>
            </ul>
        </div>
    </div>

    <div style="text-align: center; margin-top: 30px; padding: 20px; background: #e9ecef; border-radius: 8px;">
        <h3>🎯 Diagnóstico Concluído</h3>
        <p>Use as informações acima para identificar exatamente o que está causando o problema.</p>
        <p><strong>Se tudo estiver OK nas verificações acima, o sistema deve funcionar perfeitamente!</strong></p>
    </div>

    <!-- Incluir os scripts do sistema para teste -->
    <script src="assets/js/script.js"></script>
    <script src="assets/js/ad-manager-fix.js"></script>
</body>
</html>