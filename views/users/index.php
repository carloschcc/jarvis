<?php 
$current_page = 'users';
ob_start(); 
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-users"></i>
        Gerenciamento de Usuários
    </h1>
    <p class="page-subtitle">
        Gerencie usuários do Active Directory - visualizar, ativar, bloquear e redefinir senhas
    </p>
</div>

<!-- Card de Filtros Avançados -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-filter"></i>
            Filtros Avançados
        </h3>
        
        <div style="display: flex; gap: 10px; align-items: center;">
            <span class="text-muted">
                <?= count($users) ?> usuário(s) encontrado(s)
            </span>
            
            <button onclick="clearAllFilters()" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-broom"></i>
                Limpar
            </button>
            
            <button onclick="refreshUsers()" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-sync-alt"></i>
                Atualizar
            </button>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Layout Horizontal dos Filtros (conforme imagem de referência) -->
        <div class="filters-horizontal-layout">
            <!-- Linha 1: Filtros principais em uma única linha -->
            <div class="filters-row">
                <div class="filter-group">
                    <label for="filter-department">
                        <i class="fas fa-building"></i> Departamento:
                    </label>
                    <select id="filter-department" class="filter-select">
                        <option value="">Todos os Departamentos</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= htmlspecialchars($dept) ?>" 
                                    <?= ($filters['department'] ?? '') === $dept ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dept) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="filter-company">
                        <i class="fas fa-industry"></i> Organização:
                    </label>
                    <select id="filter-company" class="filter-select">
                        <option value="">Todas as Organizações</option>
                        <?php foreach ($companies as $company): ?>
                            <option value="<?= htmlspecialchars($company) ?>" 
                                    <?= ($filters['company'] ?? '') === $company ? 'selected' : '' ?>>
                                <?= htmlspecialchars($company) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="filter-city">
                        <i class="fas fa-map-marker-alt"></i> Cidade:
                    </label>
                    <select id="filter-city" class="filter-select">
                        <option value="">Todas as Cidades</option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?= htmlspecialchars($city) ?>" 
                                    <?= ($filters['city'] ?? '') === $city ? 'selected' : '' ?>>
                                <?= htmlspecialchars($city) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="filter-status">
                        <i class="fas fa-toggle-on"></i> Status:
                    </label>
                    <select id="filter-status" class="filter-select">
                        <option value="">Todos os Status</option>
                        <option value="Ativo" <?= ($filters['status'] ?? '') === 'Ativo' ? 'selected' : '' ?>>
                            Ativo
                        </option>
                        <option value="Bloqueado" <?= ($filters['status'] ?? '') === 'Bloqueado' ? 'selected' : '' ?>>
                            Bloqueado
                        </option>
                    </select>
                </div>
                
                <div class="filter-actions">
                    <button onclick="applyFilters()" class="btn-apply">
                        <i class="fas fa-check"></i> Aplicar
                    </button>
                    <button onclick="clearAllFilters()" class="btn-clear">
                        <i class="fas fa-broom"></i> Limpar
                    </button>
                </div>
            </div>
            
            <!-- Linha 2: Busca e filtros adicionais -->
            <div class="search-row">
                <div class="search-group">
                    <label for="user-search">
                        <i class="fas fa-search"></i> Buscar:
                    </label>
                    <input 
                        type="search" 
                        id="user-search" 
                        class="search-input" 
                        placeholder="Nome, usuário, email, função..."
                        value="<?= htmlspecialchars($search ?? '') ?>"
                    >
                </div>
                
                <div class="filter-group">
                    <label for="filter-title">
                        <i class="fas fa-briefcase"></i> Função:
                    </label>
                    <select id="filter-title" class="filter-select">
                        <option value="">Todas as Funções</option>
                        <?php foreach ($titles as $title): ?>
                            <option value="<?= htmlspecialchars($title) ?>" 
                                    <?= ($filters['title'] ?? '') === $title ? 'selected' : '' ?>>
                                <?= htmlspecialchars($title) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="filter-office">
                        <i class="fas fa-door-open"></i> Escritório:
                    </label>
                    <select id="filter-office" class="filter-select">
                        <option value="">Todos os Escritórios</option>
                        <?php foreach ($offices as $office): ?>
                            <option value="<?= htmlspecialchars($office) ?>" 
                                    <?= ($filters['office'] ?? '') === $office ? 'selected' : '' ?>>
                                <?= htmlspecialchars($office) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="results-info">
                    <span class="results-count">
                        <i class="fas fa-users"></i> <?= count($users) ?> usuário(s)
                    </span>
                    <button onclick="refreshUsers()" class="btn-refresh">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Botões de ação -->
        <div class="row">
            <div class="col-12">
                <div style="display: flex; gap: 10px; align-items: center;">
                    <button onclick="applyFilters()" class="btn btn-primary">
                        <i class="fas fa-filter"></i>
                        Aplicar Filtros
                    </button>
                    
                    <button onclick="exportResults()" class="btn btn-success">
                        <i class="fas fa-download"></i>
                        Exportar
                    </button>
                    
                    <button onclick="openCreateUserModal()" class="btn btn-info" id="btn-create-user">
                        <i class="fas fa-user-plus"></i>
                        Novo Usuário
                    </button>
                    
                    <span id="filter-loading" class="spinner ms-2" style="display: none;"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Card da Lista de Usuários -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i>
            Lista de Usuários (<?= count($users) ?>)
        </h3>
        
        <div style="display: flex; gap: 10px; align-items: center;">
            <label for="items-per-page" class="form-label" style="margin: 0; font-size: 12px;">Itens por página:</label>
            <select id="items-per-page" class="form-control" style="width: 80px; font-size: 12px;">
                <option value="25" <?= ($limit ?? 50) == 25 ? 'selected' : '' ?>>25</option>
                <option value="50" <?= ($limit ?? 50) == 50 ? 'selected' : '' ?>>50</option>
                <option value="100" <?= ($limit ?? 50) == 100 ? 'selected' : '' ?>>100</option>
            </select>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all" onclick="toggleSelectAll()"></th>
                    <th onclick="sortBy('username')" style="cursor: pointer;">
                        Usuário <i class="fas fa-sort"></i>
                    </th>
                    <th onclick="sortBy('name')" style="cursor: pointer;">
                        Nome Completo <i class="fas fa-sort"></i>
                    </th>
                    <th onclick="sortBy('email')" style="cursor: pointer;">
                        Email <i class="fas fa-sort"></i>
                    </th>
                    <th>Função/Cargo</th>
                    <th>Departamento</th>
                    <th>Cidade</th>
                    <th onclick="sortBy('status')" style="cursor: pointer;">
                        Status <i class="fas fa-sort"></i>
                    </th>
                    <th onclick="sortBy('last_logon')" style="cursor: pointer;">
                        Último Login <i class="fas fa-sort"></i>
                    </th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                <tr>
                    <td colspan="10" class="text-center text-muted" style="padding: 60px;">
                        <div style="opacity: 0.5;">
                            <i class="fas fa-users" style="font-size: 64px; margin-bottom: 20px;"></i><br>
                            <h5>Nenhum usuário encontrado</h5>
                            <p>Tente ajustar os filtros de busca ou verifique a conexão com o Active Directory.</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($users as $index => $user): ?>
                    <tr class="user-row" data-username="<?= htmlspecialchars($user['username']) ?>">
                        <td>
                            <input type="checkbox" class="user-checkbox" value="<?= htmlspecialchars($user['username']) ?>">
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="user-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <strong><?= htmlspecialchars($user['username']) ?></strong>
                                    <?php if (!empty($user['employee_id'])): ?>
                                        <br><small class="text-muted">ID: <?= htmlspecialchars($user['employee_id']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <?= htmlspecialchars($user['name']) ?>
                                <?php if (!empty($user['description'])): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($user['description']) ?></small>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($user['email'])): ?>
                                <a href="mailto:<?= htmlspecialchars($user['email']) ?>" class="text-primary">
                                    <?= htmlspecialchars($user['email']) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-light">
                                <?= htmlspecialchars($user['title'] ?? 'N/I') ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-secondary">
                                <?= htmlspecialchars($user['department'] ?? 'N/I') ?>
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 5px;">
                                <i class="fas fa-map-marker-alt text-muted"></i>
                                <?= htmlspecialchars($user['city'] ?? 'N/I') ?>
                            </div>
                        </td>
                        <td>
                            <span class="status <?= $user['status'] === 'Ativo' ? 'status-ativo' : 'status-bloqueado' ?>">
                                <i class="fas <?= $user['status'] === 'Ativo' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                                <?= htmlspecialchars($user['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($user['last_logon'])): ?>
                                <span title="<?= date('d/m/Y H:i:s', strtotime($user['last_logon'])) ?>">
                                    <?= timeAgo($user['last_logon']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">Nunca</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="actions-container">
                                <!-- Botão Status -->
                                <?php if ($user['status'] === 'Ativo'): ?>
                                <button onclick="toggleStatus('<?= htmlspecialchars($user['username']) ?>', false)" 
                                        class="action-btn btn-block" title="Bloquear usuário">
                                    <i class="fas fa-ban"></i>
                                </button>
                                <?php else: ?>
                                <button onclick="toggleStatus('<?= htmlspecialchars($user['username']) ?>', true)" 
                                        class="action-btn btn-activate" title="Ativar usuário">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                                <?php endif; ?>
                                
                                <!-- Botão Editar -->
                                <button onclick="openEditUserModal('<?= htmlspecialchars($user['username']) ?>')" 
                                        class="action-btn btn-edit" title="Editar usuário">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <!-- Botão Reset Senha -->
                                <button onclick="openResetPasswordModal('<?= htmlspecialchars($user['username']) ?>')" 
                                        class="action-btn btn-reset" title="Redefinir senha">
                                    <i class="fas fa-key"></i>
                                </button>
                                
                                <!-- Botão Grupos -->
                                <button onclick="viewGroups('<?= htmlspecialchars($user['username']) ?>')" 
                                        class="action-btn btn-groups" title="Ver grupos">
                                    <i class="fas fa-users"></i>
                                </button>
                                
                                <!-- Botão Excluir -->
                                <button onclick="deleteUser('<?= htmlspecialchars($user['username']) ?>')" 
                                        class="action-btn btn-delete" title="Excluir usuário">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Paginação (se necessário) -->
    <?php if (count($users) >= ($limit ?? 50)): ?>
    <div class="card-footer">
        <div style="display: flex; justify-content: between; align-items: center;">
            <div>
                Mostrando <?= count($users) ?> de <?= count($users) ?> usuários
            </div>
            <div>
                <button onclick="loadMore()" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-plus"></i>
                    Carregar Mais
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- SCRIPT DEFINITIVO - AD MANAGER SOLUTION -->
<script src="<?= ASSETS_PATH ?>/js/ad-manager-definitive.js"></script>

<!-- Scripts complementares -->
<script>
// Variáveis globais
let currentSort = { field: 'name', direction: 'asc' };
let selectedUsers = [];

// Função para aplicar filtros
function applyFilters() {
    const filters = {
        search: document.getElementById('user-search')?.value || '',
        department: document.getElementById('filter-department')?.value || '',
        company: document.getElementById('filter-company')?.value || '',
        city: document.getElementById('filter-city')?.value || '',
        status: document.getElementById('filter-status')?.value || '',
        title: document.getElementById('filter-title')?.value || '',
        office: document.getElementById('filter-office')?.value || '',
        limit: document.getElementById('items-per-page')?.value || '50'
    };
    
    console.log('Filtros aplicados:', filters);
    
    const params = new URLSearchParams();
    
    // Adicionar apenas filtros não vazios
    Object.keys(filters).forEach(key => {
        if (filters[key] && filters[key] !== '' && filters[key] !== 'all' && filters[key] !== 'Todos os Departamentos' && filters[key] !== 'Todas as Organizações' && filters[key] !== 'Todas as Cidades' && filters[key] !== 'Todos os Status') {
            params.append(key, filters[key]);
        }
    });
    
    // Mostrar loading
    const loading = document.getElementById('filter-loading');
    if (loading) loading.style.display = 'inline-block';
    
    // Redirecionar com filtros
    window.location.href = `index.php?page=users&${params.toString()}`;
}

// Função para limpar filtros
function clearAllFilters() {
    document.getElementById('user-search').value = '';
    document.getElementById('filter-department').value = '';
    document.getElementById('filter-company').value = '';
    document.getElementById('filter-city').value = '';
    document.getElementById('filter-status').value = '';
    document.getElementById('filter-title').value = '';
    document.getElementById('filter-office').value = '';
    document.getElementById('filter-manager').value = '';
    
    applyFilters();
}

// Função para atualizar usuários
function refreshUsers() {
    window.location.reload();
}

// Função para alternar status do usuário
function toggleStatus(username, enable) {
    const action = enable ? 'ativar' : 'bloquear';
    
    if (confirm(`Deseja ${action} o usuário ${username}?`)) {
        fetch('index.php?page=users&action=toggleStatus', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `username=${username}&action=${enable ? 'enable' : 'disable'}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showNotification('Erro: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showNotification('Erro de comunicação: ' + error.message, 'error');
        });
    }
}

// Função para resetar senha com modal
function resetPassword(username) {
    showResetPasswordModal(username);
}

// Modal para resetar senha
function showResetPasswordModal(username) {
    const modalHtml = `
        <div class="modal fade" id="resetPasswordModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-key"></i> Redefinir Senha - ${username}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="resetPasswordForm">
                            <div class="form-group">
                                <label for="newPassword">Nova Senha:</label>
                                <input type="password" class="form-control" id="newPassword" minlength="8" required>
                                <small class="form-text text-muted">Mínimo 8 caracteres</small>
                            </div>
                            <div class="form-group">
                                <label for="confirmPassword">Confirmar Senha:</label>
                                <input type="password" class="form-control" id="confirmPassword" minlength="8" required>
                            </div>
                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" id="forceChange">
                                <label class="form-check-label" for="forceChange">
                                    Forçar alteração no próximo login
                                </label>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger" onclick="executePasswordReset('${username}')">Redefinir Senha</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remover modal existente
    const existingModal = document.getElementById('resetPasswordModal');
    if (existingModal) existingModal.remove();
    
    // Adicionar e mostrar novo modal
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    $('#resetPasswordModal').modal('show');
}

// Executar reset de senha
function executePasswordReset(username) {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const forceChange = document.getElementById('forceChange').checked;
    
    // Validações
    if (!newPassword || !confirmPassword) {
        showNotification('Todos os campos de senha são obrigatórios', 'error');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        showNotification('As senhas não coincidem', 'error');
        return;
    }
    
    if (newPassword.length < 8) {
        showNotification('A senha deve ter pelo menos 8 caracteres', 'error');
        return;
    }
    
    // Validação de complexidade básica
    const hasLetter = /[a-zA-Z]/.test(newPassword);
    const hasNumber = /\d/.test(newPassword);
    if (!hasLetter || !hasNumber) {
        showNotification('A senha deve conter pelo menos uma letra e um número', 'error');
        return;
    }
    
    // Mostrar loading
    const resetButton = document.querySelector('#resetPasswordModal .btn-danger');
    const originalText = resetButton.innerHTML;
    resetButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Redefinindo...';
    resetButton.disabled = true;
    
    fetch('index.php?page=users&action=resetPassword', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `username=${encodeURIComponent(username)}&new_password=${encodeURIComponent(newPassword)}&force_change=${forceChange}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        resetButton.innerHTML = originalText;
        resetButton.disabled = false;
        
        if (data.success) {
            $('#resetPasswordModal').modal('hide');
            showNotification(data.message || 'Senha redefinida com sucesso', 'success');
        } else {
            showNotification('Erro ao redefinir senha: ' + (data.message || 'Erro desconhecido'), 'error');
        }
    })
    .catch(error => {
        resetButton.innerHTML = originalText;
        resetButton.disabled = false;
        console.error('Erro no reset de senha:', error);
        showNotification('Erro de comunicação: ' + error.message, 'error');
    });
}

// Função para visualizar usuário
function viewUser(username) {
    fetch(`index.php?page=users&action=getUser&username=${username}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const user = data.user;
            showUserModal(user);
        } else {
            showNotification('Erro ao carregar usuário: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Erro de comunicação: ' + error.message, 'error');
    });
}

// Função para mostrar modal do usuário
function showUserModal(user) {
    const modalHtml = `
        <div class="modal fade" id="userModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-user"></i> Detalhes do Usuário
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-id-card"></i> Informações Básicas</h6>
                                <table class="table table-sm">
                                    <tr><td><strong>Usuário:</strong></td><td>${user.username}</td></tr>
                                    <tr><td><strong>Nome:</strong></td><td>${user.name}</td></tr>
                                    <tr><td><strong>Email:</strong></td><td>${user.email || 'N/I'}</td></tr>
                                    <tr><td><strong>Status:</strong></td><td><span class="status ${user.status === 'Ativo' ? 'status-ativo' : 'status-bloqueado'}">${user.status}</span></td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-briefcase"></i> Informações Profissionais</h6>
                                <table class="table table-sm">
                                    <tr><td><strong>Função:</strong></td><td>${user.title || 'N/I'}</td></tr>
                                    <tr><td><strong>Departamento:</strong></td><td>${user.department || 'N/I'}</td></tr>
                                    <tr><td><strong>Empresa:</strong></td><td>${user.company || 'N/I'}</td></tr>
                                    <tr><td><strong>Telefone:</strong></td><td>${user.phone || 'N/I'}</td></tr>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-map-marker-alt"></i> Localização</h6>
                                <table class="table table-sm">
                                    <tr><td><strong>Cidade:</strong></td><td>${user.city || 'N/I'}</td></tr>
                                    <tr><td><strong>Escritório:</strong></td><td>${user.office || 'N/I'}</td></tr>
                                    <tr><td><strong>Endereço:</strong></td><td>${user.address || 'N/I'}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-clock"></i> Informações do Sistema</h6>
                                <table class="table table-sm">
                                    <tr><td><strong>Criado:</strong></td><td>${user.created || 'N/I'}</td></tr>
                                    <tr><td><strong>Último Login:</strong></td><td>${user.last_logon || 'Nunca'}</td></tr>
                                    <tr><td><strong>DN:</strong></td><td style="font-size: 11px; font-family: monospace;">${user.dn || 'N/I'}</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        <button type="button" class="btn btn-primary" onclick="openEditUserModal('${user.username}')">Editar</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remover modal existente
    const existingModal = document.getElementById('userModal');
    if (existingModal) existingModal.remove();
    
    // Adicionar e mostrar novo modal
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    $('#userModal').modal('show');
}

// Função para mostrar notificações
function showNotification(message, type) {
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    }[type] || 'alert-info';
    
    // Remover notificações existentes
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());
    
    const notification = document.createElement('div');
    notification.className = `alert ${alertClass} alert-dismissible fade show`;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    notification.innerHTML = `
        ${message}
        <button type="button" class="close" onclick="this.parentElement.remove()">
            <span>&times;</span>
        </button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remover após 5 segundos
    setTimeout(() => {
        if (notification && notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Busca em tempo real
document.getElementById('user-search').addEventListener('input', function(e) {
    const term = e.target.value;
    
    // Aplicar filtros automaticamente após 500ms de inatividade
    clearTimeout(window.searchTimeout);
    window.searchTimeout = setTimeout(() => {
        if (term.length > 2 || term.length === 0) {
            applyFilters();
        }
    }, 500);
});

// Event listeners para mudanças nos filtros
document.addEventListener('DOMContentLoaded', function() {
    const filterElements = [
        'filter-department', 'filter-company', 'filter-city', 
        'filter-status', 'filter-title', 'filter-office', 'filter-manager'
    ];
    
    filterElements.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('change', applyFilters);
        }
    });
    
    // Listener para itens por página
    const itemsPerPage = document.getElementById('items-per-page');
    if (itemsPerPage) {
        itemsPerPage.addEventListener('change', applyFilters);
    }
});

// Função de ordenação implementada
function sortBy(field) {
    console.log('Sorting by:', field);
    
    // Alternar direção da ordenação
    if (currentSort.field === field) {
        currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
    } else {
        currentSort.field = field;
        currentSort.direction = 'asc';
    }
    
    // Obter todas as linhas da tabela
    const tbody = document.querySelector('.table tbody');
    const rows = Array.from(tbody.querySelectorAll('tr:not(.empty-state)'));
    
    // Função de comparação
    const compare = (a, b) => {
        let aValue, bValue;
        
        switch(field) {
            case 'username':
                aValue = a.querySelector('td:nth-child(2) strong')?.textContent || '';
                bValue = b.querySelector('td:nth-child(2) strong')?.textContent || '';
                break;
            case 'name':
                aValue = a.querySelector('td:nth-child(3)')?.textContent.trim() || '';
                bValue = b.querySelector('td:nth-child(3)')?.textContent.trim() || '';
                break;
            case 'email':
                aValue = a.querySelector('td:nth-child(4) a')?.textContent || a.querySelector('td:nth-child(4)')?.textContent.trim() || '';
                bValue = b.querySelector('td:nth-child(4) a')?.textContent || b.querySelector('td:nth-child(4)')?.textContent.trim() || '';
                break;
            case 'status':
                aValue = a.querySelector('td:nth-child(8) .status')?.textContent.trim() || '';
                bValue = b.querySelector('td:nth-child(8) .status')?.textContent.trim() || '';
                break;
            case 'last_logon':
                aValue = a.querySelector('td:nth-child(9)')?.textContent.trim() || '';
                bValue = b.querySelector('td:nth-child(9)')?.textContent.trim() || '';
                break;
            default:
                return 0;
        }
        
        // Normalizar valores
        aValue = aValue.toLowerCase();
        bValue = bValue.toLowerCase();
        
        if (aValue < bValue) return currentSort.direction === 'asc' ? -1 : 1;
        if (aValue > bValue) return currentSort.direction === 'asc' ? 1 : -1;
        return 0;
    };
    
    // Ordenar e reorganizar as linhas
    rows.sort(compare);
    
    // Reordenar no DOM
    rows.forEach(row => tbody.appendChild(row));
    
    // Atualizar ícones de ordenação
    updateSortIcons(field);
}

// Atualizar ícones de ordenação
function updateSortIcons(activeField) {
    // Remover classes de ordenação de todos os cabeçalhos
    document.querySelectorAll('th .fas.fa-sort, th .fas.fa-sort-up, th .fas.fa-sort-down').forEach(icon => {
        icon.className = 'fas fa-sort';
    });
    
    // Adicionar classe apropriada ao campo ativo
    const activeHeader = document.querySelector(`th[onclick="sortBy('${activeField}')"] .fas`);
    if (activeHeader) {
        activeHeader.className = currentSort.direction === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
    }
}

function toggleSelectAll() {
    // TODO: Implementar seleção múltipla
    console.log('Toggle select all');
}

function exportResults() {
    // TODO: Implementar exportação
    console.log('Export results');
}

// REMOVIDA: função duplicada - usando ad-manager-fix.js

// REMOVIDA: função duplicada - usando ad-manager-fix.js

// REMOVIDAS: funções duplicadas - usando ad-manager-fix.js

// Modal para editar usuário
function showEditUserModal(user) {
    const modalHtml = `
        <div class="modal fade" id="editUserModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-edit"></i> Editar Usuário - ${user.username}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="editUserForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="editName">Nome Completo:</label>
                                        <input type="text" class="form-control" id="editName" value="${user.name || ''}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="editEmail">Email:</label>
                                        <input type="email" class="form-control" id="editEmail" value="${user.email || ''}">
                                    </div>
                                    <div class="form-group">
                                        <label for="editPhone">Telefone:</label>
                                        <input type="text" class="form-control" id="editPhone" value="${user.phone || ''}">
                                    </div>
                                    <div class="form-group">
                                        <label for="editTitle">Função/Cargo:</label>
                                        <input type="text" class="form-control" id="editTitle" value="${user.title || ''}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="editDepartment">Departamento:</label>
                                        <input type="text" class="form-control" id="editDepartment" value="${user.department || ''}">
                                    </div>
                                    <div class="form-group">
                                        <label for="editCompany">Empresa:</label>
                                        <input type="text" class="form-control" id="editCompany" value="${user.company || ''}">
                                    </div>
                                    <div class="form-group">
                                        <label for="editCity">Cidade:</label>
                                        <input type="text" class="form-control" id="editCity" value="${user.city || ''}">
                                    </div>
                                    <div class="form-group">
                                        <label for="editOffice">Escritório:</label>
                                        <input type="text" class="form-control" id="editOffice" value="${user.office || ''}">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="editDescription">Descrição:</label>
                                <textarea class="form-control" id="editDescription" rows="3">${user.description || ''}</textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" onclick="executeUserEdit('${user.username}')">Salvar Alterações</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remover modal existente
    const existingModal = document.getElementById('editUserModal');
    if (existingModal) existingModal.remove();
    
    // Adicionar e mostrar novo modal
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    $('#editUserModal').modal('show');
}

// Salvar edição do usuário
function saveUserEdit(username) {
    console.log('saveUserEdit() chamada para usuário:', username);
    
    try {
        // Verificar se os campos existem
        const nameEl = document.getElementById('editName');
        const emailEl = document.getElementById('editEmail');
        const phoneEl = document.getElementById('editPhone');
        const titleEl = document.getElementById('editTitle');
        const departmentEl = document.getElementById('editDepartment');
        const companyEl = document.getElementById('editCompany');
        const cityEl = document.getElementById('editCity');
        const officeEl = document.getElementById('editOffice');
        const descriptionEl = document.getElementById('editDescription');
        
        if (!nameEl) {
            throw new Error('Campo nome não encontrado');
        }
        
        // Validar campos obrigatórios
        const name = nameEl.value.trim();
        if (!name) {
            showNotification('Nome completo é obrigatório', 'error');
            return;
        }
        
        // Validar email se fornecido
        const email = emailEl ? emailEl.value.trim() : '';
        if (email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showNotification('Email deve ter um formato válido', 'error');
                return;
            }
        }
        
        const userData = {
            name: name,
            email: email,
            phone: phoneEl ? phoneEl.value.trim() : '',
            title: titleEl ? titleEl.value.trim() : '',
            department: departmentEl ? departmentEl.value.trim() : '',
            company: companyEl ? companyEl.value.trim() : '',
            city: cityEl ? cityEl.value.trim() : '',
            office: officeEl ? officeEl.value.trim() : '',
            description: descriptionEl ? descriptionEl.value.trim() : ''
        };
        
        console.log('Dados do usuário coletados:', userData);
        
        // Mostrar loading
        const saveButton = document.querySelector('#editUserModal .btn-primary');
        if (!saveButton) {
            throw new Error('Botão salvar não encontrado');
        }
        
        const originalText = saveButton.innerHTML;
        saveButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
        saveButton.disabled = true;
        
        // CSRF token removido para compatibilidade universal
        const requestBody = `username=${encodeURIComponent(username)}&user_data=${encodeURIComponent(JSON.stringify(userData))}`;
        console.log('Enviando requisição...');
        
        fetch('index.php?page=users&action=updateUserInfo', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: requestBody
        })
        .then(response => {
            console.log('Resposta recebida:', response.status, response.statusText);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            return response.text().then(text => {
                console.log('Texto da resposta:', text);
                
                try {
                    return JSON.parse(text);
                } catch (parseError) {
                    console.error('Erro ao fazer parse do JSON:', parseError);
                    throw new Error('Resposta não é JSON válido: ' + text.substring(0, 200));
                }
            });
        })
        .then(data => {
            saveButton.innerHTML = originalText;
            saveButton.disabled = false;
            
            console.log('Dados recebidos:', data);
            
            if (data.success) {
                $('#editUserModal').modal('hide');
                showNotification(data.message || 'Usuário atualizado com sucesso', 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showNotification('Erro ao salvar: ' + (data.message || 'Erro desconhecido'), 'error');
            }
        })
        .catch(error => {
            saveButton.innerHTML = originalText;
            saveButton.disabled = false;
            console.error('Erro na atualização:', error);
            showNotification('Erro de comunicação: ' + error.message, 'error');
        });
        
    } catch (error) {
        console.error('Erro em saveUserEdit:', error);
        showNotification('Erro interno: ' + error.message, 'error');
    }
}

// Função para ver grupos do usuário
function viewGroups(username) {
    fetch(`index.php?page=users&action=getUserGroups&username=${username}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showUserGroupsModal(username, data.groups);
        } else {
            showNotification('Erro ao carregar grupos: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Erro de comunicação: ' + error.message, 'error');
    });
}

// Modal para ver/gerenciar grupos do usuário
function showUserGroupsModal(username, groups) {
    const groupsHtml = groups.length > 0 ? 
        groups.map(group => `
            <div class="group-item" style="display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid #ddd; margin: 5px 0; border-radius: 4px;">
                <div class="group-info">
                    <strong>${group.name}</strong>
                    <br><small class="text-muted">${group.description || 'Sem descrição'}</small>
                </div>
                <button class="btn btn-sm btn-outline-danger" onclick="removeUserFromGroup('${username}', '${group.dn}')">
                    <i class="fas fa-times"></i> Remover
                </button>
            </div>
        `).join('') : 
        '<p class="text-muted">Usuário não pertence a nenhum grupo.</p>';
    
    const modalHtml = `
        <div class="modal fade" id="userGroupsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-users"></i> Grupos de ${username}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <button class="btn btn-success" onclick="showAddGroupModal('${username}')">
                                <i class="fas fa-plus"></i> Adicionar a Grupo
                            </button>
                        </div>
                        <div id="groupsList">
                            ${groupsHtml}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remover modal existente
    const existingModal = document.getElementById('userGroupsModal');
    if (existingModal) existingModal.remove();
    
    // Adicionar e mostrar novo modal
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    $('#userGroupsModal').modal('show');
}

// Função para excluir usuário
function deleteUser(username) {
    if (confirm(`ATENÇÃO: Deseja realmente EXCLUIR o usuário ${username}?\n\nEsta ação não pode ser desfeita!`)) {
        fetch('index.php?page=users&action=deleteUser', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `username=${username}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showNotification('Erro: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showNotification('Erro de comunicação: ' + error.message, 'error');
        });
    }
}

function loadMore() {
    // TODO: Implementar carregamento de mais resultados
    console.log('Load more results');
}
</script>

<style>
/* Layout Horizontal dos Filtros */
.filters-horizontal-layout {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #e9ecef;
}

.filters-row {
    display: flex;
    align-items: end;
    gap: 15px;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.search-row {
    display: flex;
    align-items: end;
    gap: 15px;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    min-width: 180px;
    flex: 1;
}

.search-group {
    display: flex;
    flex-direction: column;
    min-width: 250px;
    flex: 2;
}

.filter-group label,
.search-group label {
    font-size: 12px;
    font-weight: 600;
    color: #495057;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.filter-select {
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    background-color: white;
    font-size: 13px;
    color: #495057;
    min-height: 36px;
    appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 8px center;
    background-repeat: no-repeat;
    background-size: 16px;
    padding-right: 32px;
}

.filter-select:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.search-input {
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    background-color: white;
    font-size: 13px;
    color: #495057;
    min-height: 36px;
}

.search-input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.filter-actions {
    display: flex;
    gap: 8px;
    align-items: end;
}

.btn-apply {
    background: #28a745;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    min-height: 36px;
    transition: all 0.2s;
}

.btn-apply:hover {
    background: #218838;
    transform: translateY(-1px);
}

.btn-clear {
    background: #6c757d;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    min-height: 36px;
    transition: all 0.2s;
}

.btn-clear:hover {
    background: #5a6268;
    transform: translateY(-1px);
}

.results-info {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-left: auto;
}

.results-count {
    font-size: 12px;
    color: #6c757d;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 5px;
}

.btn-refresh {
    background: transparent;
    border: 1px solid #ced4da;
    padding: 8px 10px;
    border-radius: 4px;
    color: #6c757d;
    cursor: pointer;
    min-height: 36px;
    transition: all 0.2s;
}

.btn-refresh:hover {
    background: #e9ecef;
    color: #495057;
}

/* Estilos da tabela */
.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--primary-blue);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
}

.badge {
    font-size: 11px;
    padding: 4px 8px;
}

.badge-light {
    background-color: #f8f9fa;
    color: #6c757d;
    border: 1px solid #e9ecef;
}

.badge-secondary {
    background-color: #6c757d;
    color: white;
}

.status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.status-ativo {
    background-color: #d4edda;
    color: #155724;
}

.status-bloqueado {
    background-color: #f8d7da;
    color: #721c24;
}

.table th {
    background-color: var(--light-blue);
    font-weight: 600;
    font-size: 12px;
    border-bottom: 2px solid var(--medium-gray);
    white-space: nowrap;
}

.table td {
    vertical-align: middle;
    font-size: 13px;
}

/* Container de ações dos usuários - Layout horizontal */
.actions-container {
    display: flex;
    gap: 3px;
    justify-content: flex-start;
    align-items: center;
    flex-wrap: nowrap;
}

/* Botões de ação uniformes */
.action-btn {
    width: 28px;
    height: 28px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    font-size: 11px;
    padding: 0;
    margin: 0;
}

.action-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
}

.action-btn:active {
    transform: translateY(0);
}

/* Cores específicas para cada ação conforme solicitado */
.btn-block {
    background-color: #ffc107;
    color: #212529;
}

.btn-block:hover {
    background-color: #e0a800;
}

.btn-activate {
    background-color: #28a745;
    color: white;
}

.btn-activate:hover {
    background-color: #218838;
}

.btn-edit {
    background-color: #007bff;
    color: white;
}

.btn-edit:hover {
    background-color: #0056b3;
}

.btn-reset {
    background-color: #dc3545;
    color: white;
}

.btn-reset:hover {
    background-color: #c82333;
}

.btn-groups {
    background-color: #6f42c1;
    color: white;
}

.btn-groups:hover {
    background-color: #5a32a3;
}

.btn-delete {
    background-color: #dc3545;
    color: white;
}

.btn-delete:hover {
    background-color: #c82333;
}

/* Ícones ajustados */
.action-btn i {
    font-size: 12px;
}

.user-row:hover {
    background-color: var(--light-blue);
}

/* Responsividade */
@media (max-width: 1200px) {
    .filters-row,
    .search-row {
        flex-wrap: wrap;
    }
    
    .filter-group {
        min-width: 140px;
    }
    
    .search-group {
        min-width: 200px;
    }
}

@media (max-width: 768px) {
    .filters-horizontal-layout {
        padding: 15px;
    }
    
    .filters-row,
    .search-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-group,
    .search-group {
        min-width: unset;
        width: 100%;
    }
    
    .filter-actions {
        justify-content: center;
        margin-top: 10px;
    }
    
    .results-info {
        margin-left: 0;
        justify-content: center;
        margin-top: 10px;
    }
    
    .table-responsive {
        font-size: 11px;
    }
    
    /* Ações responsivas */
    .actions-container {
        gap: 2px;
    }
    
    .action-btn {
        width: 24px;
        height: 24px;
        font-size: 9px;
    }
    
    .action-btn i {
        font-size: 10px;
    }
}

/* Loading state */
#filter-loading {
    width: 16px;
    height: 16px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    display: inline-block;
    margin-left: 10px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<!-- Script de Inicialização e Debug -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== AD MANAGER DEBUG INIT ===');
    
    // Testar dependências
    if (typeof $ === 'undefined') {
        console.error('❌ jQuery NÃO carregado!');
        alert('ERRO CRÍTICO: jQuery não carregado. Recarregue a página.');
        return;
    } else {
        console.log('✅ jQuery carregado:', $.fn.jquery);
    }
    
    if (typeof $.fn.modal === 'undefined') {
        console.error('❌ Bootstrap Modal NÃO carregado!');
        alert('ERRO CRÍTICO: Bootstrap não carregado. Recarregue a página.');
        return;
    } else {
        console.log('✅ Bootstrap Modal carregado');
    }
    
    // Testar funções principais
    if (typeof openCreateUserModal !== 'function') {
        console.error('❌ Função openCreateUserModal não definida!');
    } else {
        console.log('✅ Função openCreateUserModal definida');
    }
    
    if (typeof executeUserEdit !== 'function') {
        console.error('❌ Função executeUserEdit não definida!');
    } else {
        console.log('✅ Função executeUserEdit definida');
    }
    
    // Testar botão Novo Usuário
    const newUserBtn = document.querySelector('button[onclick="openCreateUserModal()"]');
    if (!newUserBtn) {
        console.error('❌ Botão Novo Usuário não encontrado!');
    } else {
        console.log('✅ Botão Novo Usuário encontrado:', newUserBtn);
    }
    
    console.log('=== FIM DEBUG INIT ===');
    
    // Adicionar listener de clique alternativo no botão (fallback)
    if (newUserBtn) {
        newUserBtn.addEventListener('click', function(e) {
            console.log('Click alternativo no botão Novo Usuário');
            if (typeof openCreateUserModal === 'function') {
                e.preventDefault();
                openCreateUserModal();
            }
        });
    }
});

// Função de teste global
window.testCreateUserModal = function() {
    console.log('Teste manual do modal...');
    if (typeof openCreateUserModal === 'function') {
        openCreateUserModal();
    } else {
        console.error('openCreateUserModal não está definida!');
    }
};
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>