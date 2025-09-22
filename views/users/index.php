<?php 
$current_page = 'users';
ob_start(); 
?>

<!-- Cabeçalho da página -->
<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-users"></i>
        Gerenciamento de Usuários
    </h1>
    <p class="page-subtitle">
        Gerencie usuários do Active Directory - visualizar, ativar, bloquear e redefinir senhas
    </p>
</div>

<!-- Alertas -->
<?php if (isset($error)): ?>
<div class="alert alert-danger alert-dismissible">
    <i class="fas fa-exclamation-circle"></i>
    <?= htmlspecialchars($error) ?>
    <button type="button" class="close" onclick="this.parentElement.remove()">
        <span>&times;</span>
    </button>
</div>
<?php endif; ?>

<!-- Toolbar de ações -->
<div class="card">
    <div class="card-header">
        <div class="btn-toolbar">
            <div style="display: flex; align-items: center; gap: 15px; flex: 1;">
                <h3 class="card-title" style="margin: 0;">
                    <i class="fas fa-search"></i>
                    Buscar Usuários
                </h3>
                
                <!-- Busca em tempo real -->
                <div style="flex: 1; max-width: 400px;">
                    <input 
                        type="search" 
                        id="user-search" 
                        class="form-control" 
                        placeholder="Digite nome, usuário ou email..."
                        value="<?= htmlspecialchars($search ?? '') ?>"
                        autocomplete="off"
                    >
                </div>
                
                <div class="text-muted">
                    <small id="search-status">
                        <?= count($users) ?> usuário(s) encontrado(s)
                    </small>
                </div>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button onclick="refreshUsers()" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-sync-alt"></i>
                    Atualizar
                </button>
                
                <a href="index.php?page=users&action=export<?= $search ? '&search=' . urlencode($search) : '' ?>" 
                   class="btn btn-outline-success btn-sm">
                    <i class="fas fa-download"></i>
                    Exportar CSV
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Ações em massa (oculto por padrão) -->
<div id="bulk-actions" class="card bulk-actions" style="display: none;">
    <div class="card-body" style="padding: 15px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <strong>
                    <i class="fas fa-check-square"></i>
                    <span id="selected-count">0</span> usuário(s) selecionado(s)
                </strong>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button onclick="bulkAction('activate')" class="btn btn-success btn-sm">
                    <i class="fas fa-user-check"></i>
                    Ativar Selecionados
                </button>
                
                <button onclick="bulkAction('block')" class="btn btn-danger btn-sm">
                    <i class="fas fa-user-times"></i>
                    Bloquear Selecionados
                </button>
                
                <button onclick="clearSelection()" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tabela de usuários -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i>
            Lista de Usuários
            <?php if ($total_users > 0): ?>
                <span class="text-muted">(<?= number_format($total_users) ?>)</span>
            <?php endif; ?>
        </h3>
        
        <?php if ($total_users > ITEMS_PER_PAGE): ?>
        <div style="display: flex; gap: 10px; align-items: center;">
            <small class="text-muted">
                Página <?= $current_page ?> de <?= $total_pages ?>
            </small>
            
            <div class="btn-group">
                <?php if ($current_page > 1): ?>
                <a href="?page=users&p=<?= $current_page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>" 
                   class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <?php endif; ?>
                
                <?php if ($current_page < $total_pages): ?>
                <a href="?page=users&p=<?= $current_page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>" 
                   class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-chevron-right"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="table-responsive">
        <table class="table" id="users-table">
            <thead>
                <tr>
                    <th style="width: 50px;">
                        <input type="checkbox" class="form-check-input select-all-checkbox" 
                               title="Selecionar todos">
                    </th>
                    <th>Usuário</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Último Login</th>
                    <th style="width: 200px;">Ações</th>
                </tr>
            </thead>
            <tbody id="users-tbody">
                <?php if (empty($users)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted" style="padding: 40px;">
                        <?php if (empty($search)): ?>
                            <i class="fas fa-users" style="font-size: 48px; opacity: 0.3; margin-bottom: 15px;"></i><br>
                            Nenhum usuário encontrado.<br>
                            <small>Verifique se o LDAP está configurado corretamente.</small>
                        <?php else: ?>
                            <i class="fas fa-search" style="font-size: 48px; opacity: 0.3; margin-bottom: 15px;"></i><br>
                            Nenhum resultado para "<?= htmlspecialchars($search) ?>"<br>
                            <small>Tente termos diferentes ou verifique a ortografia.</small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                    <tr data-username="<?= htmlspecialchars($user['username']) ?>">
                        <td>
                            <input type="checkbox" class="form-check-input row-checkbox" 
                                   value="<?= htmlspecialchars($user['username']) ?>">
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($user['username']) ?></strong>
                            <?php if (!empty($user['department'])): ?>
                            <br><small class="text-muted"><?= htmlspecialchars($user['department']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($user['name']) ?>
                            <?php if (!empty($user['description'])): ?>
                            <br><small class="text-muted" title="<?= htmlspecialchars($user['description']) ?>">
                                <?= htmlspecialchars(substr($user['description'], 0, 50)) ?><?= strlen($user['description']) > 50 ? '...' : '' ?>
                            </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($user['email'])): ?>
                                <a href="mailto:<?= htmlspecialchars($user['email']) ?>" class="text-primary">
                                    <?= htmlspecialchars($user['email']) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">N/A</span>
                            <?php endif; ?>
                            
                            <?php if (!empty($user['phone'])): ?>
                            <br><small class="text-muted">
                                <i class="fas fa-phone"></i> <?= htmlspecialchars($user['phone']) ?>
                            </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status <?= $user['status'] === 'Ativo' ? 'status-ativo' : 'status-bloqueado' ?>">
                                <?= htmlspecialchars($user['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($user['last_logon'])): ?>
                                <span title="<?= htmlspecialchars($user['last_logon']) ?>">
                                    <?= timeAgo($user['last_logon']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">Nunca</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group" style="display: flex; gap: 5px;">
                                <?php if ($user['status'] === 'Ativo'): ?>
                                <button onclick="toggleUserStatus('<?= htmlspecialchars($user['username']) ?>', false)" 
                                        class="btn btn-danger btn-sm" 
                                        title="Bloquear usuário">
                                    <i class="fas fa-user-times"></i>
                                </button>
                                <?php else: ?>
                                <button onclick="toggleUserStatus('<?= htmlspecialchars($user['username']) ?>', true)" 
                                        class="btn btn-success btn-sm" 
                                        title="Ativar usuário">
                                    <i class="fas fa-user-check"></i>
                                </button>
                                <?php endif; ?>
                                
                                <button onclick="resetUserPassword('<?= htmlspecialchars($user['username']) ?>')" 
                                        class="btn btn-warning btn-sm" 
                                        title="Redefinir senha">
                                    <i class="fas fa-key"></i>
                                </button>
                                
                                <button onclick="viewUserDetails('<?= htmlspecialchars($user['username']) ?>')" 
                                        class="btn btn-info btn-sm" 
                                        title="Ver detalhes">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de detalhes do usuário -->
<div id="user-details-modal" class="modal">
    <div class="modal-dialog" style="max-width: 600px;">
        <div class="modal-header">
            <h5 class="modal-title">
                <i class="fas fa-user"></i>
                Detalhes do Usuário
            </h5>
            <button type="button" class="close" onclick="Modal.hide('user-details-modal')">
                <span>&times;</span>
            </button>
        </div>
        <div class="modal-body" id="user-details-content">
            <div class="text-center">
                <span class="spinner spinner-primary"></span>
                Carregando...
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="Modal.hide('user-details-modal')">
                Fechar
            </button>
        </div>
    </div>
</div>

<!-- Modal de redefinir senha -->
<div id="reset-password-modal" class="modal">
    <div class="modal-dialog">
        <form id="reset-password-form">
            <input type="hidden" id="reset-username" name="username">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-key"></i>
                    Redefinir Senha
                </h5>
                <button type="button" class="close" onclick="Modal.hide('reset-password-modal')">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Atenção!</strong> Esta ação irá alterar a senha do usuário no Active Directory.
                </div>
                
                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" id="generate-random" name="generate_random" class="form-check-input" value="1" checked>
                        <label for="generate-random" class="form-check-label">
                            Gerar senha aleatória segura
                        </label>
                    </div>
                </div>
                
                <div class="form-group" id="manual-password-group" style="display: none;">
                    <label for="new-password" class="form-label">Nova Senha</label>
                    <input type="password" id="new-password" name="password" class="form-control" 
                           placeholder="Digite a nova senha">
                    <div id="password-strength"></div>
                </div>
                
                <div id="generated-password-display" style="display: none; margin-top: 15px;">
                    <div class="alert alert-success">
                        <strong>Nova senha gerada:</strong><br>
                        <code id="generated-password" style="font-size: 16px; background: rgba(255,255,255,0.8); padding: 5px; border-radius: 3px;"></code>
                        <button type="button" onclick="copyToClipboard('generated-password')" class="btn btn-outline-primary btn-sm" style="margin-left: 10px;">
                            <i class="fas fa-copy"></i>
                            Copiar
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="Modal.hide('reset-password-modal')">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-key"></i>
                    Redefinir Senha
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Scripts específicos de usuários -->
<script>
// Variáveis globais
let searchTimeout;
let currentUsers = <?= json_encode($users) ?>;

// Busca em tempo real
document.getElementById('user-search').addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    
    searchTimeout = setTimeout(() => {
        performSearch(e.target.value);
    }, 500);
});

// Realizar busca
async function performSearch(term) {
    try {
        const searchStatus = document.getElementById('search-status');
        searchStatus.innerHTML = '<span class="spinner spinner-primary"></span> Buscando...';
        
        const response = await API.get(`index.php?page=users&action=search&q=${encodeURIComponent(term)}&limit=50`);
        
        if (response.success) {
            currentUsers = response.users;
            updateUsersTable(response.users);
            
            searchStatus.textContent = `${response.users.length} usuário(s) encontrado(s)`;
            
            // Atualizar URL sem recarregar página
            const url = new URL(window.location);
            if (term) {
                url.searchParams.set('search', term);
            } else {
                url.searchParams.delete('search');
            }
            window.history.replaceState({}, '', url);
            
        } else {
            throw new Error(response.message);
        }
    } catch (error) {
        Notifications.error('Erro na busca: ' + error.message);
        document.getElementById('search-status').textContent = 'Erro na busca';
    }
}

// Atualizar tabela de usuários
function updateUsersTable(users) {
    const tbody = document.getElementById('users-tbody');
    
    if (users.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted" style="padding: 40px;">
                    <i class="fas fa-search" style="font-size: 48px; opacity: 0.3; margin-bottom: 15px;"></i><br>
                    Nenhum usuário encontrado<br>
                    <small>Tente termos diferentes ou verifique a ortografia.</small>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = users.map(user => `
        <tr data-username="${Utils.escapeHtml(user.username)}">
            <td>
                <input type="checkbox" class="form-check-input row-checkbox" 
                       value="${Utils.escapeHtml(user.username)}">
            </td>
            <td>
                <strong>${Utils.escapeHtml(user.username)}</strong>
                ${user.department ? '<br><small class="text-muted">' + Utils.escapeHtml(user.department) + '</small>' : ''}
            </td>
            <td>
                ${Utils.escapeHtml(user.name)}
                ${user.description ? '<br><small class="text-muted" title="' + Utils.escapeHtml(user.description) + '">' + 
                  Utils.escapeHtml(user.description.substr(0, 50)) + (user.description.length > 50 ? '...' : '') + '</small>' : ''}
            </td>
            <td>
                ${user.email ? 
                  '<a href="mailto:' + Utils.escapeHtml(user.email) + '" class="text-primary">' + Utils.escapeHtml(user.email) + '</a>' : 
                  '<span class="text-muted">N/A</span>'}
                ${user.phone ? '<br><small class="text-muted"><i class="fas fa-phone"></i> ' + Utils.escapeHtml(user.phone) + '</small>' : ''}
            </td>
            <td>
                <span class="status ${user.status === 'Ativo' ? 'status-ativo' : 'status-bloqueado'}">
                    ${Utils.escapeHtml(user.status)}
                </span>
            </td>
            <td>
                ${user.last_logon ? 
                  '<span title="' + Utils.escapeHtml(user.last_logon) + '">' + Utils.timeAgo(user.last_logon) + '</span>' :
                  '<span class="text-muted">Nunca</span>'}
            </td>
            <td>
                <div class="btn-group" style="display: flex; gap: 5px;">
                    ${user.status === 'Ativo' ? 
                      '<button onclick="toggleUserStatus(\'' + Utils.escapeHtml(user.username) + '\', false)" class="btn btn-danger btn-sm" title="Bloquear usuário"><i class="fas fa-user-times"></i></button>' :
                      '<button onclick="toggleUserStatus(\'' + Utils.escapeHtml(user.username) + '\', true)" class="btn btn-success btn-sm" title="Ativar usuário"><i class="fas fa-user-check"></i></button>'}
                    
                    <button onclick="resetUserPassword('${Utils.escapeHtml(user.username)}')" class="btn btn-warning btn-sm" title="Redefinir senha">
                        <i class="fas fa-key"></i>
                    </button>
                    
                    <button onclick="viewUserDetails('${Utils.escapeHtml(user.username)}')" class="btn btn-info btn-sm" title="Ver detalhes">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
    
    // Re-inicializar seleção múltipla
    Tables.enableMultiSelect('users-table', 'row-checkbox');
}

// Atualizar lista de usuários
async function refreshUsers() {
    const searchTerm = document.getElementById('user-search').value;
    
    try {
        Notifications.info('Atualizando lista de usuários...');
        
        if (searchTerm) {
            await performSearch(searchTerm);
        } else {
            window.location.reload();
        }
        
        Notifications.success('Lista atualizada com sucesso');
    } catch (error) {
        Notifications.error('Erro ao atualizar lista: ' + error.message);
    }
}

// Alterar status do usuário
async function toggleUserStatus(username, enable) {
    const action = enable ? 'ativar' : 'bloquear';
    
    Modal.confirm(
        `${enable ? 'Ativar' : 'Bloquear'} Usuário`,
        `Tem certeza que deseja ${action} o usuário "${username}"?`,
        async () => {
            try {
                const response = await API.post('index.php?page=users&action=toggleStatus', {
                    username: username,
                    enable: enable,
                    csrf_token: '<?= $csrf_token ?>'
                });
                
                if (response.success) {
                    Notifications.success(response.message);
                    
                    // Atualizar status na tabela
                    const row = document.querySelector(`tr[data-username="${username}"]`);
                    if (row) {
                        const statusCell = row.querySelector('.status');
                        statusCell.className = 'status ' + (enable ? 'status-ativo' : 'status-bloqueado');
                        statusCell.textContent = enable ? 'Ativo' : 'Bloqueado';
                        
                        // Atualizar botão de ação
                        const actionBtn = row.querySelector('.btn-danger, .btn-success');
                        if (enable) {
                            actionBtn.className = 'btn btn-danger btn-sm';
                            actionBtn.innerHTML = '<i class="fas fa-user-times"></i>';
                            actionBtn.title = 'Bloquear usuário';
                            actionBtn.onclick = () => toggleUserStatus(username, false);
                        } else {
                            actionBtn.className = 'btn btn-success btn-sm';
                            actionBtn.innerHTML = '<i class="fas fa-user-check"></i>';
                            actionBtn.title = 'Ativar usuário';
                            actionBtn.onclick = () => toggleUserStatus(username, true);
                        }
                    }
                    
                } else {
                    throw new Error(response.message);
                }
            } catch (error) {
                Notifications.error('Erro ao alterar status: ' + error.message);
            }
        }
    );
}

// Ação em massa
async function bulkAction(action) {
    const selectedUsers = Tables.getSelectedValues('row-checkbox');
    
    if (selectedUsers.length === 0) {
        Notifications.warning('Selecione pelo menos um usuário');
        return;
    }
    
    const actionText = action === 'activate' ? 'ativar' : 'bloquear';
    const enable = action === 'activate';
    
    Modal.confirm(
        `${enable ? 'Ativar' : 'Bloquear'} Usuários`,
        `Tem certeza que deseja ${actionText} ${selectedUsers.length} usuário(s) selecionado(s)?`,
        async () => {
            try {
                Notifications.info(`Processando ${selectedUsers.length} usuário(s)...`);
                
                const response = await API.post('index.php?page=users&action=bulkToggleStatus', {
                    usernames: selectedUsers,
                    enable: enable,
                    csrf_token: '<?= $csrf_token ?>'
                });
                
                if (response.success) {
                    Notifications.success(response.message);
                    
                    // Atualizar tabela
                    await refreshUsers();
                    
                    // Limpar seleção
                    clearSelection();
                    
                } else {
                    throw new Error(response.message);
                }
            } catch (error) {
                Notifications.error('Erro na operação em massa: ' + error.message);
            }
        }
    );
}

// Limpar seleção
function clearSelection() {
    document.querySelectorAll('.row-checkbox:checked').forEach(cb => cb.checked = false);
    document.querySelector('.select-all-checkbox').checked = false;
    Tables.updateBulkActions();
}

// Ver detalhes do usuário
async function viewUserDetails(username) {
    try {
        Modal.show('user-details-modal');
        
        const content = document.getElementById('user-details-content');
        content.innerHTML = `
            <div class="text-center">
                <span class="spinner spinner-primary"></span>
                Carregando detalhes...
            </div>
        `;
        
        const response = await API.get(`index.php?page=users&action=getUser&username=${encodeURIComponent(username)}`);
        
        if (response.success) {
            const user = response.user;
            
            content.innerHTML = `
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <h6><i class="fas fa-user text-primary"></i> Informações Básicas</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Usuário:</strong></td><td>${Utils.escapeHtml(user.username)}</td></tr>
                            <tr><td><strong>Nome:</strong></td><td>${Utils.escapeHtml(user.name)}</td></tr>
                            <tr><td><strong>Email:</strong></td><td>${user.email ? Utils.escapeHtml(user.email) : 'N/A'}</td></tr>
                            <tr><td><strong>Telefone:</strong></td><td>${user.phone ? Utils.escapeHtml(user.phone) : 'N/A'}</td></tr>
                            <tr><td><strong>Departamento:</strong></td><td>${user.department ? Utils.escapeHtml(user.department) : 'N/A'}</td></tr>
                        </table>
                    </div>
                    
                    <div>
                        <h6><i class="fas fa-info-circle text-primary"></i> Status e Atividade</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Status:</strong></td><td><span class="status ${user.status === 'Ativo' ? 'status-ativo' : 'status-bloqueado'}">${Utils.escapeHtml(user.status)}</span></td></tr>
                            <tr><td><strong>Criado em:</strong></td><td>${user.created ? Utils.formatDate(user.created) : 'N/A'}</td></tr>
                            <tr><td><strong>Último login:</strong></td><td>${user.last_logon ? Utils.formatDate(user.last_logon) : 'Nunca'}</td></tr>
                        </table>
                    </div>
                </div>
                
                ${user.description ? `
                <div style="margin-top: 20px;">
                    <h6><i class="fas fa-comment text-primary"></i> Descrição</h6>
                    <p style="background: var(--light-gray); padding: 10px; border-radius: 4px; margin: 0;">
                        ${Utils.escapeHtml(user.description)}
                    </p>
                </div>
                ` : ''}
                
                <div style="margin-top: 20px;">
                    <h6><i class="fas fa-code text-primary"></i> Informações Técnicas</h6>
                    <small class="text-muted">
                        <strong>DN:</strong> ${Utils.escapeHtml(user.dn)}<br>
                        <strong>Account Control:</strong> ${user.account_control || 'N/A'}
                    </small>
                </div>
            `;
        } else {
            throw new Error(response.message);
        }
    } catch (error) {
        document.getElementById('user-details-content').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                Erro ao carregar detalhes: ${error.message}
            </div>
        `;
    }
}

// Redefinir senha
function resetUserPassword(username) {
    document.getElementById('reset-username').value = username;
    document.querySelector('#reset-password-modal .modal-title').innerHTML = `
        <i class="fas fa-key"></i>
        Redefinir Senha - ${Utils.escapeHtml(username)}
    `;
    
    // Reset form
    document.getElementById('reset-password-form').reset();
    document.getElementById('generate-random').checked = true;
    togglePasswordMode();
    
    Modal.show('reset-password-modal');
}

// Alternar modo de senha
function togglePasswordMode() {
    const generateRandom = document.getElementById('generate-random').checked;
    const manualGroup = document.getElementById('manual-password-group');
    
    manualGroup.style.display = generateRandom ? 'none' : 'block';
    
    if (!generateRandom) {
        document.getElementById('new-password').focus();
    }
}

// Copiar para clipboard
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    const text = element.textContent;
    
    navigator.clipboard.writeText(text).then(() => {
        Notifications.success('Senha copiada para a área de transferência');
    }).catch(() => {
        // Fallback para navegadores mais antigos
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        
        Notifications.success('Senha copiada para a área de transferência');
    });
}

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    // Habilitar seleção múltipla
    Tables.enableMultiSelect('users-table', 'row-checkbox');
    
    // Configurar busca em tabela
    Tables.enableSearch('users-table', 'user-search');
    
    // Event listener para modo de senha
    document.getElementById('generate-random').addEventListener('change', togglePasswordMode);
    
    // Validação de senha em tempo real
    const passwordInput = document.getElementById('new-password');
    const strengthDiv = document.getElementById('password-strength');
    
    passwordInput.addEventListener('input', Utils.debounce(async function() {
        if (this.value.length === 0) {
            strengthDiv.innerHTML = '';
            return;
        }
        
        try {
            const response = await API.post('index.php?page=auth&action=validatePassword', {
                password: this.value
            });
            
            if (response.success) {
                const validation = response.validation;
                const score = validation.score;
                
                let color = 'danger';
                let text = 'Fraca';
                
                if (score >= 80) { color = 'success'; text = 'Forte'; }
                else if (score >= 60) { color = 'warning'; text = 'Média'; }
                
                strengthDiv.innerHTML = `
                    <div class="alert alert-${color}" style="padding: 5px 10px; margin-top: 5px; font-size: 12px;">
                        Força da senha: <strong>${text}</strong> (${score}%)
                        ${validation.errors.length > 0 ? '<br>' + validation.errors.join('<br>') : ''}
                    </div>
                `;
            }
        } catch (error) {
            console.error('Erro ao validar senha:', error);
        }
    }, 500));
    
    // Submeter formulário de redefinir senha
    document.getElementById('reset-password-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        try {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Redefinindo...';
            
            const formData = new FormData(this);
            const response = await API.post('index.php?page=users&action=resetPassword', formData);
            
            if (response.success) {
                Notifications.success(response.message);
                
                // Mostrar senha gerada se aplicável
                if (response.new_password) {
                    document.getElementById('generated-password').textContent = response.new_password;
                    document.getElementById('generated-password-display').style.display = 'block';
                } else {
                    Modal.hide('reset-password-modal');
                }
                
            } else {
                throw new Error(response.message);
            }
        } catch (error) {
            Notifications.error('Erro ao redefinir senha: ' + error.message);
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
});
</script>

<?php
$content = ob_get_clean();
$current_page = 'users';
include VIEWS_PATH . '/layouts/main.php';
?>