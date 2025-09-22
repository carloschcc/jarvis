<?php 
$current_page = 'dashboard';
ob_start(); 
?>

<!-- Cabeçalho da página -->
<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-tachometer-alt"></i>
        Dashboard
    </h1>
    <p class="page-subtitle">
        Visão geral do sistema de gestão de usuários Active Directory
    </p>
</div>

<!-- Alertas -->
<?php if (isset($_GET['error'])): ?>
<div class="alert alert-danger alert-dismissible">
    <i class="fas fa-exclamation-circle"></i>
    <?php 
    $error = $_GET['error'];
    $errorMessages = [
        'access_denied' => 'Acesso negado.',
        'ldap_error' => 'Erro de conexão com LDAP.',
        'config_error' => 'Erro nas configurações do sistema.'
    ];
    echo htmlspecialchars($errorMessages[$error] ?? $error);
    ?>
    <button type="button" class="close" onclick="this.parentElement.remove()">
        <span>&times;</span>
    </button>
</div>
<?php endif; ?>

<!-- Cartões de estatísticas -->
<div class="stats-grid">
    <div class="stat-card primary" onclick="window.location.href='index.php?page=users'">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-number" id="total-users">
            <?= number_format($user_stats['total'] ?? 0) ?>
        </div>
        <div class="stat-label">Total de Usuários</div>
    </div>
    
    <div class="stat-card success" onclick="window.location.href='index.php?page=users&filter=active'">
        <div class="stat-icon">
            <i class="fas fa-user-check"></i>
        </div>
        <div class="stat-number" id="active-users">
            <?= number_format($user_stats['active'] ?? 0) ?>
        </div>
        <div class="stat-label">Usuários Ativos</div>
    </div>
    
    <div class="stat-card danger" onclick="window.location.href='index.php?page=users&filter=blocked'">
        <div class="stat-icon">
            <i class="fas fa-user-times"></i>
        </div>
        <div class="stat-number" id="blocked-users">
            <?= number_format($user_stats['blocked'] ?? 0) ?>
        </div>
        <div class="stat-label">Usuários Bloqueados</div>
    </div>
    
    <div class="stat-card <?= $ldap_configured ? 'success' : 'warning' ?>">
        <div class="stat-icon">
            <i class="fas fa-<?= $ldap_configured ? 'check-circle' : 'exclamation-triangle' ?>"></i>
        </div>
        <div class="stat-number">
            <span class="status <?= $ldap_configured ? 'status-configurado' : 'status-nao-configurado' ?>">
                <?= $ldap_configured ? 'OK' : 'Pendente' ?>
            </span>
        </div>
        <div class="stat-label">Configuração LDAP</div>
    </div>
</div>

<!-- Ações rápidas -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-bolt"></i>
            Ações Rápidas
        </h3>
        <button onclick="refreshDashboard()" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-sync-alt"></i>
            Atualizar
        </button>
    </div>
    <div class="card-body">
        <div class="quick-actions">
            <a href="index.php?page=users" class="quick-action">
                <i class="fas fa-users"></i>
                <span>Gerenciar Usuários</span>
            </a>
            
            <a href="index.php?page=config" class="quick-action">
                <i class="fas fa-cogs"></i>
                <span>Configurações LDAP</span>
            </a>
            
            <a href="#" onclick="syncLdap()" class="quick-action">
                <i class="fas fa-sync"></i>
                <span>Sincronizar LDAP</span>
            </a>
            
            <a href="index.php?page=users&action=export" class="quick-action">
                <i class="fas fa-download"></i>
                <span>Exportar Dados</span>
            </a>
        </div>
    </div>
</div>

<!-- Informações do sistema -->
<div class="row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <!-- Status da conexão -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-network-wired"></i>
                Status da Conexão
            </h3>
        </div>
        <div class="card-body">
            <?php if ($ldap_configured): ?>
                <div class="mb-2">
                    <strong>Servidor LDAP:</strong><br>
                    <?= htmlspecialchars($ldap_config['server'] ?? 'N/A') ?>:<?= htmlspecialchars($ldap_config['port'] ?? 'N/A') ?>
                    <?php if ($ldap_config['use_ssl'] ?? false): ?>
                        <span class="status status-configurado">SSL</span>
                    <?php endif; ?>
                </div>
                
                <div class="mb-2">
                    <strong>Domínio:</strong><br>
                    <?= htmlspecialchars($ldap_config['domain'] ?? 'N/A') ?>
                </div>
                
                <div class="mb-2">
                    <strong>Base DN:</strong><br>
                    <code style="font-size: 11px;"><?= htmlspecialchars($ldap_config['base_dn'] ?? 'N/A') ?></code>
                </div>
                
                <div class="mb-2">
                    <strong>Status:</strong>
                    <span id="connection-status" class="status status-<?= $connection_status === 'connected' ? 'configurado' : ($connection_status === 'error' ? 'nao-configurado' : 'nao-configurado') ?>">
                        <?php 
                        switch($connection_status) {
                            case 'connected': echo 'Conectado'; break;
                            case 'error': echo 'Erro'; break;
                            default: echo 'Desconectado'; break;
                        }
                        ?>
                    </span>
                </div>
                
                <?php if ($last_sync): ?>
                <div class="mb-2">
                    <strong>Última sincronização:</strong><br>
                    <small class="text-muted" id="last-sync">
                        <?= date('d/m/Y H:i:s', strtotime($last_sync)) ?>
                    </small>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    LDAP não configurado. <a href="index.php?page=config">Configure agora</a>.
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Logs recentes -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-history"></i>
                Atividade Recente
            </h3>
            <button onclick="loadRecentLogs()" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-refresh"></i>
            </button>
        </div>
        <div class="card-body">
            <div id="recent-logs" style="max-height: 300px; overflow-y: auto;">
                <?php if (!empty($recent_logs)): ?>
                    <?php foreach (array_slice($recent_logs, 0, 5) as $log): ?>
                    <div class="log-entry" style="padding: 8px 0; border-bottom: 1px solid var(--medium-gray); font-size: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div style="flex: 1;">
                                <strong><?= htmlspecialchars($log['action']) ?></strong><br>
                                <span class="text-muted">por <?= htmlspecialchars($log['user']) ?></span>
                                <?php if (!empty($log['details'])): ?>
                                <br><small><?= htmlspecialchars($log['details']) ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="text-muted" style="font-size: 10px;">
                                <?= date('d/m H:i', strtotime($log['timestamp'])) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (count($recent_logs) > 5): ?>
                    <div class="text-center mt-2">
                        <small>
                            <a href="#" onclick="loadAllLogs()">Ver todos (<?= count($recent_logs) ?>)</a>
                        </small>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center text-muted" style="padding: 20px;">
                        <i class="fas fa-inbox"></i><br>
                        Nenhuma atividade recente
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Informações do sistema -->
<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-info-circle"></i>
            Informações do Sistema
        </h3>
        <button onclick="loadSystemInfo()" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-refresh"></i>
            Atualizar
        </button>
    </div>
    <div class="card-body">
        <div id="system-info" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div>
                <strong>Versão do Sistema:</strong><br>
                <?= APP_NAME ?> v<?= APP_VERSION ?>
            </div>
            
            <div>
                <strong>PHP:</strong><br>
                <?= PHP_VERSION ?>
            </div>
            
            <div>
                <strong>Extensão LDAP:</strong><br>
                <span class="status <?= extension_loaded('ldap') ? 'status-ativo' : 'status-bloqueado' ?>">
                    <?= extension_loaded('ldap') ? 'Disponível' : 'Não disponível' ?>
                </span>
            </div>
            
            <div>
                <strong>Data de Instalação:</strong><br>
                <?= isset($system_config['installation_date']) ? date('d/m/Y H:i', strtotime($system_config['installation_date'])) : 'N/A' ?>
            </div>
            
            <div>
                <strong>Horário do Servidor:</strong><br>
                <span id="server-time"><?= date('d/m/Y H:i:s') ?></span>
            </div>
            
            <div>
                <strong>Fuso Horário:</strong><br>
                <?= date_default_timezone_get() ?>
            </div>
        </div>
    </div>
</div>

<!-- Scripts específicos do dashboard -->
<script>
// Atualizar dashboard
async function refreshDashboard() {
    try {
        const response = await API.get('index.php?page=dashboard&action=getStats');
        
        if (response.success) {
            // Atualizar estatísticas
            document.getElementById('total-users').textContent = response.stats.total.toLocaleString();
            document.getElementById('active-users').textContent = response.stats.active.toLocaleString();
            document.getElementById('blocked-users').textContent = response.stats.blocked.toLocaleString();
            
            // Atualizar status da conexão
            const statusElement = document.getElementById('connection-status');
            if (statusElement) {
                statusElement.className = 'status status-' + (response.connection_status === 'connected' ? 'configurado' : 'nao-configurado');
                statusElement.textContent = response.connection_status === 'connected' ? 'Conectado' : 'Desconectado';
            }
            
            // Atualizar última atualização
            const lastUpdateElement = document.getElementById('last-sync');
            if (lastUpdateElement) {
                lastUpdateElement.textContent = response.last_update;
            }
            
            Notifications.success('Dashboard atualizado com sucesso');
        } else {
            throw new Error(response.message);
        }
    } catch (error) {
        Notifications.error('Erro ao atualizar dashboard: ' + error.message);
    }
}

// Sincronizar LDAP
async function syncLdap() {
    try {
        Notifications.info('Iniciando sincronização...');
        
        const response = await API.post('index.php?page=dashboard&action=syncLdap', {
            csrf_token: '<?= generateCSRFToken() ?>'
        });
        
        if (response.success) {
            Notifications.success(response.message);
            
            // Atualizar estatísticas
            if (response.stats) {
                document.getElementById('total-users').textContent = response.stats.total.toLocaleString();
                document.getElementById('active-users').textContent = response.stats.active.toLocaleString();
                document.getElementById('blocked-users').textContent = response.stats.blocked.toLocaleString();
            }
            
            // Atualizar última sincronização
            if (response.last_sync) {
                const lastSyncElement = document.getElementById('last-sync');
                if (lastSyncElement) {
                    lastSyncElement.textContent = response.last_sync;
                }
            }
            
        } else {
            throw new Error(response.message);
        }
    } catch (error) {
        Notifications.error('Erro na sincronização: ' + error.message);
    }
}

// Carregar logs recentes
async function loadRecentLogs() {
    try {
        const response = await API.get('index.php?page=dashboard&action=getLogs&limit=10');
        
        if (response.success) {
            const container = document.getElementById('recent-logs');
            
            if (response.logs.length > 0) {
                container.innerHTML = response.logs.map(log => `
                    <div class="log-entry" style="padding: 8px 0; border-bottom: 1px solid var(--medium-gray); font-size: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div style="flex: 1;">
                                <strong>${Utils.escapeHtml(log.action)}</strong><br>
                                <span class="text-muted">por ${Utils.escapeHtml(log.user)}</span>
                                ${log.details ? '<br><small>' + Utils.escapeHtml(log.details) + '</small>' : ''}
                            </div>
                            <div class="text-muted" style="font-size: 10px;">
                                ${Utils.timeAgo(log.timestamp)}
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = `
                    <div class="text-center text-muted" style="padding: 20px;">
                        <i class="fas fa-inbox"></i><br>
                        Nenhuma atividade recente
                    </div>
                `;
            }
        } else {
            throw new Error(response.message);
        }
    } catch (error) {
        Notifications.error('Erro ao carregar logs: ' + error.message);
    }
}

// Carregar informações do sistema
async function loadSystemInfo() {
    try {
        const response = await API.get('index.php?page=dashboard&action=getSystemInfo');
        
        if (response.success) {
            const info = response.system_info;
            
            // Atualizar horário do servidor
            const serverTimeElement = document.getElementById('server-time');
            if (serverTimeElement) {
                serverTimeElement.textContent = info.server_time;
            }
            
            Notifications.success('Informações do sistema atualizadas');
        } else {
            throw new Error(response.message);
        }
    } catch (error) {
        Notifications.error('Erro ao carregar informações: ' + error.message);
    }
}

// Atualizar horário do servidor a cada minuto
setInterval(() => {
    const serverTimeElement = document.getElementById('server-time');
    if (serverTimeElement) {
        const now = new Date();
        serverTimeElement.textContent = now.toLocaleString('pt-BR');
    }
}, 60000);

// Auto-refresh das estatísticas a cada 5 minutos
setInterval(refreshDashboard, 5 * 60 * 1000);
</script>

<?php
$content = ob_get_clean();
$current_page = 'dashboard';
include VIEWS_PATH . '/layouts/main.php';
?>