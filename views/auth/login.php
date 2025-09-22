<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Login - AD Manager') ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Meta tags -->
    <meta name="description" content="Login - Sistema de Gestão de Usuários Active Directory">
    <meta name="robots" content="noindex, nofollow">
</head>
<body class="login-page">
    <div class="login-card">
        <!-- Logo -->
        <div class="login-logo">
            <i class="fas fa-users-cog"></i>
        </div>
        
        <!-- Título -->
        <h1 class="login-title"><?= htmlspecialchars($app_name ?? 'AD Manager') ?></h1>
        
        <!-- Alertas -->
        <div id="login-alerts">
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php 
                    $error = $_GET['error'];
                    $errorMessages = [
                        'session_expired' => 'Sua sessão expirou. Faça login novamente.',
                        'access_denied' => 'Acesso negado. Você não tem permissão para acessar esta área.',
                        'invalid_credentials' => 'Usuário ou senha inválidos.',
                        'ldap_not_configured' => 'Sistema não configurado. Use admin/admin123 para primeira configuração.'
                    ];
                    echo htmlspecialchars($errorMessages[$error] ?? $error);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['message'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php 
                    $message = $_GET['message'];
                    $messages = [
                        'logout_success' => 'Logout realizado com sucesso.',
                        'config_updated' => 'Configurações atualizadas com sucesso.'
                    ];
                    echo htmlspecialchars($messages[$message] ?? $message);
                    ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Formulário de login -->
        <form id="login-form" class="login-form" method="POST" action="index.php?page=auth&action=login">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            
            <div class="form-group">
                <label for="username" class="form-label">Usuário</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="form-control" 
                    placeholder="Digite seu usuário"
                    required
                    autofocus
                    autocomplete="username"
                >
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Senha</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-control" 
                    placeholder="Digite sua senha"
                    required
                    autocomplete="current-password"
                >
            </div>
            
            <button type="submit" class="btn btn-primary login-btn">
                <i class="fas fa-sign-in-alt"></i>
                Entrar
            </button>
        </form>
        
        <!-- Rodapé do login -->
        <div class="login-footer">
            <p>
                <strong>Primeira instalação?</strong><br>
                Use: <code>admin</code> / <code>admin123</code>
            </p>
            
            <hr style="margin: 15px 0; border: none; border-top: 1px solid var(--medium-gray);">
            
            <p>
                <i class="fas fa-shield-alt"></i>
                Sistema seguro com criptografia TLS/SSL<br>
                
                <i class="fas fa-server"></i>
                Compatível com Active Directory e LDAP<br>
                
                <i class="fas fa-clock"></i>
                Sessão expira em 1 hora de inatividade
            </p>
            
            <div style="margin-top: 15px; font-size: 11px; opacity: 0.7;">
                <?= htmlspecialchars($app_name ?? 'AD Manager') ?> v<?= htmlspecialchars($app_version ?? '1.0.0') ?><br>
                © <?= date('Y') ?> - Sistema de Gestão de Usuários AD
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="assets/js/script.js"></script>
    
    <script>
    // Script específico da página de login
    document.addEventListener('DOMContentLoaded', function() {
        const loginForm = document.getElementById('login-form');
        const alertsContainer = document.getElementById('login-alerts');
        
        // Submeter formulário via AJAX
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalHtml = submitBtn.innerHTML;
            
            try {
                // Mostrar loading
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner"></span> Entrando...';
                
                // Limpar alertas anteriores
                alertsContainer.innerHTML = '';
                
                // Enviar requisição
                const formData = new FormData(this);
                const response = await API.post(this.action, formData);
                
                if (response.success) {
                    // Sucesso - mostrar mensagem e redirecionar
                    alertsContainer.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            ${response.message}
                        </div>
                    `;
                    
                    setTimeout(() => {
                        window.location.href = 'index.php?page=dashboard';
                    }, 1000);
                    
                } else {
                    throw new Error(response.message || 'Erro desconhecido');
                }
                
            } catch (error) {
                // Erro - mostrar mensagem
                alertsContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        ${error.message}
                    </div>
                `;
                
                // Focar no campo de usuário
                document.getElementById('username').focus();
                
            } finally {
                // Restaurar botão
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHtml;
            }
        });
        
        // Enter no campo senha submete o formulário
        document.getElementById('password').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                loginForm.dispatchEvent(new Event('submit'));
            }
        });
        
        // Limpar alertas ao digitar
        const inputs = loginForm.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                // Limpar mensagens de erro após 3 segundos de digitação
                setTimeout(() => {
                    const alerts = alertsContainer.querySelectorAll('.alert-danger');
                    alerts.forEach(alert => alert.remove());
                }, 3000);
            });
        });
        
        // Verificar se há parâmetros de URL para mostrar dicas
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('first_time') === '1') {
            alertsContainer.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Bem-vindo!</strong><br>
                    Esta é sua primeira vez aqui. Use as credenciais padrão para começar:
                    <br><strong>Usuário:</strong> admin | <strong>Senha:</strong> admin123
                </div>
            `;
        }
        
        // Auto-preencher em modo de desenvolvimento (apenas se hostname for localhost)
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            const developmentNote = document.createElement('div');
            developmentNote.className = 'alert alert-warning';
            developmentNote.style.fontSize = '11px';
            developmentNote.innerHTML = `
                <i class="fas fa-code"></i>
                <strong>Modo de desenvolvimento detectado</strong><br>
                Clique <a href="#" onclick="fillDevelopmentCredentials(); return false;">aqui</a> 
                para preencher credenciais padrão automaticamente.
            `;
            alertsContainer.appendChild(developmentNote);
        }
    });
    
    // Função para preencher credenciais em desenvolvimento
    function fillDevelopmentCredentials() {
        document.getElementById('username').value = 'admin';
        document.getElementById('password').value = 'admin123';
        document.getElementById('username').focus();
        
        Notifications.info('Credenciais de desenvolvimento preenchidas');
    }
    </script>
</body>
</html>