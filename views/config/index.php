<?php 
$current_page = 'config';
ob_start(); 
?>

<!-- Cabeçalho da página -->
<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-cogs"></i>
        Configurações LDAP
    </h1>
    <p class="page-subtitle">
        Configure a conexão com o servidor Active Directory
    </p>
</div>

<!-- Verificar extensão LDAP -->
<?php if (!$ldap_extension_loaded): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle"></i>
    <strong>⚠️ Extensão LDAP não encontrada!</strong><br>
    A extensão PHP LDAP não está instalada ou habilitada no seu XAMPP. 
    Para usar este sistema com Active Directory, você precisa habilitar a extensão LDAP.
    <br><br>
    <div style="display: flex; gap: 10px; margin-top: 10px;">
        <a href="xampp-ldap-diagnostic.php" target="_blank" class="btn btn-warning btn-sm">
            <i class="fas fa-tools"></i> 
            Diagnóstico XAMPP LDAP
        </a>
        <button onclick="window.location.reload()" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-sync"></i>
            Verificar Novamente
        </button>
    </div>
</div>
<?php else: ?>
<div class="alert alert-success" style="margin-bottom: 20px;">
    <i class="fas fa-check-circle"></i>
    <strong>✅ Extensão LDAP detectada!</strong>
    A extensão PHP LDAP está funcionando corretamente. Você pode configurar a conexão com o Active Directory.
</div>
<?php endif; ?>

<!-- Formulário de configuração -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-server"></i>
            Configuração do Servidor LDAP
        </h3>
        <div>
            <button onclick="testConnection()" class="btn btn-outline-primary btn-sm" id="test-btn">
                <i class="fas fa-plug"></i>
                Testar Conexão
            </button>
            <button onclick="resetConfig()" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-undo"></i>
                Resetar
            </button>
        </div>
    </div>
    <div class="card-body">
        <form id="ldap-config-form" method="POST" action="index.php?page=config&action=save">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            
            <!-- Conexão -->
            <fieldset style="border: 1px solid var(--medium-gray); padding: 20px; margin-bottom: 25px; border-radius: var(--border-radius);">
                <legend style="padding: 0 10px; font-weight: 600; color: var(--primary-blue);">
                    <i class="fas fa-network-wired"></i>
                    Conexão
                </legend>
                
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="server" class="form-label">
                            Servidor LDAP *
                        </label>
                        <input 
                            type="text" 
                            id="server" 
                            name="server" 
                            class="form-control" 
                            value="<?= htmlspecialchars($ldap_config['server'] ?? '') ?>"
                            placeholder="Ex: dc01.empresa.local ou 192.168.1.100"
                            required
                        >
                        <small class="text-muted">
                            Endereço IP ou nome do servidor Active Directory
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="port" class="form-label">
                            Porta
                        </label>
                        <input 
                            type="number" 
                            id="port" 
                            name="port" 
                            class="form-control" 
                            value="<?= htmlspecialchars($ldap_config['port'] ?? DEFAULT_LDAP_PORT) ?>"
                            min="1" 
                            max="65535"
                            required
                        >
                        <small class="text-muted">
                            389 (padrão) ou 636 (SSL)
                        </small>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="form-check">
                        <input 
                            type="checkbox" 
                            id="use_ssl" 
                            name="use_ssl" 
                            class="form-check-input" 
                            value="1"
                            <?= ($ldap_config['use_ssl'] ?? DEFAULT_LDAP_USE_SSL) ? 'checked' : '' ?>
                            onchange="toggleSSLPort()"
                        >
                        <label for="use_ssl" class="form-check-label">
                            <i class="fas fa-shield-alt"></i>
                            Usar TLS/SSL (Recomendado)
                        </label>
                    </div>
                    <small class="text-muted">
                        Habilita criptografia na conexão para maior segurança
                    </small>
                </div>
            </fieldset>
            
            <!-- Domínio -->
            <fieldset style="border: 1px solid var(--medium-gray); padding: 20px; margin-bottom: 25px; border-radius: var(--border-radius);">
                <legend style="padding: 0 10px; font-weight: 600; color: var(--primary-blue);">
                    <i class="fas fa-sitemap"></i>
                    Domínio
                </legend>
                
                <div class="form-group">
                    <label for="domain" class="form-label">
                        Domínio *
                    </label>
                    <input 
                        type="text" 
                        id="domain" 
                        name="domain" 
                        class="form-control" 
                        value="<?= htmlspecialchars($ldap_config['domain'] ?? '') ?>"
                        placeholder="Ex: empresa.local"
                        required
                        onblur="suggestBaseDN()"
                    >
                    <small class="text-muted">
                        Nome do domínio Active Directory
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="base_dn" class="form-label">
                        Base DN (Distinguished Name) *
                        <button type="button" onclick="validateBaseDN()" class="btn btn-outline-primary" style="font-size: 10px; padding: 2px 8px; margin-left: 10px;">
                            <i class="fas fa-check"></i>
                            Validar
                        </button>
                    </label>
                    <input 
                        type="text" 
                        id="base_dn" 
                        name="base_dn" 
                        class="form-control" 
                        value="<?= htmlspecialchars($ldap_config['base_dn'] ?? '') ?>"
                        placeholder="Ex: DC=empresa,DC=local"
                        required
                    >
                    <div id="base-dn-feedback"></div>
                    <small class="text-muted">
                        Ponto de partida para buscas no diretório
                    </small>
                </div>
            </fieldset>
            
            <!-- Autenticação -->
            <fieldset style="border: 1px solid var(--medium-gray); padding: 20px; margin-bottom: 25px; border-radius: var(--border-radius);">
                <legend style="padding: 0 10px; font-weight: 600; color: var(--primary-blue);">
                    <i class="fas fa-key"></i>
                    Autenticação
                </legend>
                
                <div class="form-group">
                    <label for="admin_user" class="form-label">
                        Usuário Administrador *
                    </label>
                    <input 
                        type="text" 
                        id="admin_user" 
                        name="admin_user" 
                        class="form-control" 
                        value="<?= htmlspecialchars($ldap_config['admin_user'] ?? '') ?>"
                        placeholder="Ex: administrador@empresa.local"
                        required
                        autocomplete="username"
                    >
                    <small class="text-muted">
                        Conta com privilégios para ler/modificar usuários no AD
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="admin_pass" class="form-label">
                        Senha do Administrador *
                        <button type="button" onclick="togglePasswordVisibility()" class="btn btn-outline-secondary" style="font-size: 10px; padding: 2px 8px; margin-left: 10px;">
                            <i class="fas fa-eye" id="password-eye"></i>
                        </button>
                    </label>
                    <input 
                        type="password" 
                        id="admin_pass" 
                        name="admin_pass" 
                        class="form-control" 
                        placeholder="Digite a senha do administrador"
                        autocomplete="current-password"
                    >
                    <small class="text-muted">
                        Senha será criptografada e armazenada com segurança
                    </small>
                </div>
            </fieldset>
            
            <!-- Botões de ação -->
            <div class="btn-group" style="width: 100%; justify-content: space-between;">
                <div>
                    <button type="button" onclick="loadCurrentConfig()" class="btn btn-outline-secondary">
                        <i class="fas fa-sync"></i>
                        Recarregar
                    </button>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="button" onclick="testConnection()" class="btn btn-warning" id="test-connection-btn">
                        <i class="fas fa-plug"></i>
                        Testar Conexão
                    </button>
                    
                    <button type="submit" class="btn btn-primary" id="save-config-btn">
                        <i class="fas fa-save"></i>
                        Salvar Configurações
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Card de ajuda -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-question-circle"></i>
            Ajuda e Exemplos
        </h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
            <div>
                <h5><i class="fas fa-server text-primary"></i> Configuração Típica</h5>
                <ul style="font-size: 13px; margin: 0; padding-left: 20px;">
                    <li><strong>Servidor:</strong> 192.168.1.10</li>
                    <li><strong>Porta:</strong> 636 (SSL) ou 389 (padrão)</li>
                    <li><strong>Domínio:</strong> empresa.local</li>
                    <li><strong>Base DN:</strong> DC=empresa,DC=local</li>
                    <li><strong>Admin:</strong> admin@empresa.local</li>
                </ul>
            </div>
            
            <div>
                <h5><i class="fas fa-shield-alt text-success"></i> Requisitos de Segurança</h5>
                <ul style="font-size: 13px; margin: 0; padding-left: 20px;">
                    <li>Use sempre SSL/TLS quando possível</li>
                    <li>Conta de serviço dedicada para LDAP</li>
                    <li>Senha forte para conta administrativa</li>
                    <li>Acesso restrito ao servidor AD</li>
                    <li>Monitoramento de logs de acesso</li>
                </ul>
            </div>
            
            <div>
                <h5><i class="fas fa-tools text-warning"></i> Solução de Problemas</h5>
                <ul style="font-size: 13px; margin: 0; padding-left: 20px;">
                    <li>Verifique conectividade de rede</li>
                    <li>Confirme credenciais do administrador</li>
                    <li>Teste com telnet na porta LDAP</li>
                    <li>Verifique logs do servidor AD</li>
                    <li>Confirme Base DN correto</li>
                </ul>
            </div>
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background: var(--light-blue); border-radius: var(--border-radius);">
            <h6 style="margin-bottom: 10px;">
                <i class="fas fa-lightbulb text-primary"></i>
                Dicas Importantes:
            </h6>
            <ul style="margin: 0; font-size: 13px;">
                <li>Sempre teste a conexão antes de salvar as configurações</li>
                <li>Use uma conta de serviço dedicada, não sua conta pessoal</li>
                <li>Se usar SSL, certifique-se que o certificado é válido</li>
                <li>O Base DN deve corresponder exatamente à estrutura do seu AD</li>
            </ul>
        </div>
    </div>
</div>

<!-- Scripts específicos da configuração -->
<script>
// Alternar visibilidade da senha
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('admin_pass');
    const eyeIcon = document.getElementById('password-eye');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.className = 'fas fa-eye-slash';
    } else {
        passwordInput.type = 'password';
        eyeIcon.className = 'fas fa-eye';
    }
}

// Alternar porta SSL automaticamente
function toggleSSLPort() {
    const sslCheckbox = document.getElementById('use_ssl');
    const portInput = document.getElementById('port');
    
    if (sslCheckbox.checked && portInput.value == '389') {
        portInput.value = '636';
    } else if (!sslCheckbox.checked && portInput.value == '636') {
        portInput.value = '389';
    }
}

// Sugerir Base DN baseado no domínio
function suggestBaseDN() {
    const domainInput = document.getElementById('domain');
    const baseDnInput = document.getElementById('base_dn');
    
    if (domainInput.value && !baseDnInput.value) {
        const domainParts = domainInput.value.split('.');
        const suggestedDN = 'DC=' + domainParts.join(',DC=');
        
        baseDnInput.value = suggestedDN;
        baseDnInput.focus();
        
        Notifications.info(`Base DN sugerido: ${suggestedDN}`);
    }
}

// Validar Base DN
async function validateBaseDN() {
    const baseDnInput = document.getElementById('base_dn');
    const domainInput = document.getElementById('domain');
    const feedbackDiv = document.getElementById('base-dn-feedback');
    
    try {
        const response = await API.post('index.php?page=config&action=validateBaseDN', {
            base_dn: baseDnInput.value,
            domain: domainInput.value,
            csrf_token: '<?= $csrf_token ?>'
        });
        
        if (response.success) {
            let html = '';
            
            if (response.valid) {
                html += '<div class="alert alert-success" style="margin-top: 10px; padding: 8px 12px; font-size: 12px;">';
                html += '<i class="fas fa-check-circle"></i> Base DN válido';
                html += '</div>';
            } else {
                html += '<div class="alert alert-danger" style="margin-top: 10px; padding: 8px 12px; font-size: 12px;">';
                html += '<i class="fas fa-exclamation-circle"></i> Base DN inválido<br>';
                html += response.suggestions.join('<br>');
                html += '</div>';
            }
            
            if (response.warnings.length > 0) {
                html += '<div class="alert alert-warning" style="margin-top: 10px; padding: 8px 12px; font-size: 12px;">';
                html += '<i class="fas fa-exclamation-triangle"></i> ' + response.warnings.join('<br>');
                html += '</div>';
            }
            
            feedbackDiv.innerHTML = html;
            
            setTimeout(() => {
                feedbackDiv.innerHTML = '';
            }, 5000);
        }
    } catch (error) {
        Notifications.error('Erro ao validar Base DN: ' + error.message);
    }
}

// Testar conexão LDAP
async function testConnection() {
    const form = document.getElementById('ldap-config-form');
    const testBtn = document.getElementById('test-connection-btn');
    const originalText = testBtn.innerHTML;
    
    try {
        // Validar campos obrigatórios
        const requiredFields = form.querySelectorAll('input[required]');
        let hasErrors = false;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                Forms.showFieldError(field, 'Campo obrigatório');
                hasErrors = true;
            } else {
                Forms.clearFieldError(field);
            }
        });
        
        if (hasErrors) {
            Notifications.error('Preencha todos os campos obrigatórios');
            return;
        }
        
        // Mostrar loading
        testBtn.disabled = true;
        testBtn.innerHTML = '<span class="spinner"></span> Testando...';
        
        // Enviar requisição
        const formData = new FormData(form);
        formData.set('csrf_token', '<?= $csrf_token ?>');
        
        const response = await API.post('index.php?page=config&action=testConnection', formData);
        
        if (response.success) {
            Notifications.success(response.message, 8000);
            
            // Mostrar detalhes da conexão
            if (response.connection_details) {
                const details = response.connection_details;
                const detailsHtml = `
                    <div class="alert alert-info" style="margin-top: 15px; font-size: 12px;">
                        <strong>Detalhes da Conexão:</strong><br>
                        Servidor: ${details.server}:${details.port}<br>
                        SSL: ${details.ssl ? 'Sim' : 'Não'}<br>
                        Testado em: ${details.test_time}
                    </div>
                `;
                
                const alertsArea = form.querySelector('.card-body');
                const existingAlert = alertsArea.querySelector('.connection-details');
                
                if (existingAlert) {
                    existingAlert.remove();
                }
                
                const detailsDiv = document.createElement('div');
                detailsDiv.className = 'connection-details';
                detailsDiv.innerHTML = detailsHtml;
                alertsArea.appendChild(detailsDiv);
                
                setTimeout(() => detailsDiv.remove(), 10000);
            }
            
        } else {
            throw new Error(response.message);
        }
        
    } catch (error) {
        let errorMessage = 'Erro no teste de conexão: ' + error.message;
        
        // Verificar se é erro de extensão LDAP
        if (error.response && error.response.error === 'LDAP_EXTENSION_MISSING') {
            errorMessage = `
                <div style="text-align: left;">
                    <strong>❌ Extensão LDAP não encontrada!</strong><br>
                    ${error.response.message}<br><br>
                    <div style="margin-top: 10px;">
                        <a href="xampp-ldap-diagnostic.php" target="_blank" class="btn btn-warning btn-sm">
                            🔧 Abrir Diagnóstico XAMPP
                        </a>
                        <a href="XAMPP-LDAP-SETUP.md" target="_blank" class="btn btn-info btn-sm">
                            📖 Ver Instruções
                        </a>
                    </div>
                </div>
            `;
            
            Notifications.error(errorMessage, 0); // Não desaparecer automaticamente
        } else {
            Notifications.error(errorMessage, 10000);
        }
        
        // Mostrar detalhes do erro se disponíveis
        if (error.response && error.response.error_details) {
            console.error('Detalhes do erro:', error.response.error_details);
        }
        
    } finally {
        testBtn.disabled = false;
        testBtn.innerHTML = originalText;
    }
}

// Carregar configuração atual
async function loadCurrentConfig() {
    try {
        const response = await API.get('index.php?page=config&action=getCurrent');
        
        if (response.success && response.config) {
            const config = response.config;
            
            document.getElementById('server').value = config.server || '';
            document.getElementById('port').value = config.port || 636;
            document.getElementById('domain').value = config.domain || '';
            document.getElementById('base_dn').value = config.base_dn || '';
            document.getElementById('admin_user').value = config.admin_user || '';
            document.getElementById('use_ssl').checked = config.use_ssl || false;
            
            Notifications.success('Configuração recarregada');
        }
    } catch (error) {
        Notifications.error('Erro ao carregar configuração: ' + error.message);
    }
}

// Resetar configuração
async function resetConfig() {
    Modal.confirm(
        'Resetar Configurações',
        'Tem certeza que deseja resetar todas as configurações para os valores padrão? Esta ação não pode ser desfeita.',
        async () => {
            try {
                const response = await API.post('index.php?page=config&action=reset', {
                    csrf_token: '<?= $csrf_token ?>'
                });
                
                if (response.success) {
                    Notifications.success(response.message);
                    
                    // Limpar formulário
                    document.getElementById('ldap-config-form').reset();
                    document.getElementById('port').value = '636';
                    document.getElementById('use_ssl').checked = true;
                    
                    // Limpar feedback
                    document.getElementById('base-dn-feedback').innerHTML = '';
                    
                } else {
                    throw new Error(response.message);
                }
            } catch (error) {
                Notifications.error('Erro ao resetar configurações: ' + error.message);
            }
        }
    );
}

// Submeter formulário
document.getElementById('ldap-config-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('save-config-btn');
    const originalText = submitBtn.innerHTML;
    
    try {
        // Validar formulário
        const errors = Forms.validate(this);
        if (errors.length > 0) {
            Notifications.error('Corrija os erros no formulário');
            return;
        }
        
        // Mostrar loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner"></span> Salvando...';
        
        // Enviar dados
        const formData = new FormData(this);
        const response = await API.post(this.action, formData);
        
        if (response.success) {
            Notifications.success(response.message);
            
            // Atualizar página após salvar
            setTimeout(() => {
                window.location.reload();
            }, 2000);
            
        } else {
            throw new Error(response.message);
        }
        
    } catch (error) {
        Notifications.error('Erro ao salvar configurações: ' + error.message);
        
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    // Auto-sugestão de Base DN quando digitar domínio
    const domainInput = document.getElementById('domain');
    domainInput.addEventListener('blur', suggestBaseDN);
    
    // Validação em tempo real do Base DN
    const baseDnInput = document.getElementById('base_dn');
    const validateDN = Utils.debounce(() => {
        if (baseDnInput.value.length > 5) {
            validateBaseDN();
        }
    }, 1000);
    
    baseDnInput.addEventListener('input', validateDN);
    
    // Verificar status da extensão LDAP
    <?php if (!$ldap_extension_loaded): ?>
    Notifications.error('Extensão PHP LDAP não encontrada. Instale php-ldap para usar este sistema.', 0);
    <?php endif; ?>
});
</script>

<?php
$content = ob_get_clean();
$current_page = 'config';
include VIEWS_PATH . '/layouts/main.php';
?>