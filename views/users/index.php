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
                    
                    <button onclick="showCreateUser()" class="btn btn-info">
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
                                <button onclick="editUser('<?= htmlspecialchars($user['username']) ?>')" 
                                        class="action-btn btn-edit" title="Editar usuário">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <!-- Botão Reset Senha -->
                                <button onclick="resetPassword('<?= htmlspecialchars($user['username']) ?>')" 
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

<!-- Scripts -->
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
            body: `username=${username}&action=${enable ? 'enable' : 'disable'}&csrf_token=<?= $csrf_token ?>`
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
    
    // Mostrar modal usando vanilla JavaScript
    const modal = document.getElementById('resetPasswordModal');
    if (modal) {
        modal.style.display = 'block';
        modal.classList.add('show');
        document.body.classList.add('modal-open');
        
        if (!document.getElementById('modal-backdrop')) {
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = 'modal-backdrop';
            document.body.appendChild(backdrop);
        }
    }
}

// Executar reset de senha
function executePasswordReset(username) {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const forceChange = document.getElementById('forceChange').checked;
    
    if (newPassword !== confirmPassword) {
        showNotification('As senhas não coincidem', 'error');
        return;
    }
    
    if (newPassword.length < 8) {
        showNotification('A senha deve ter pelo menos 8 caracteres', 'error');
        return;
    }
    
    fetch('index.php?page=users&action=resetPassword', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `username=${username}&new_password=${encodeURIComponent(newPassword)}&force_change=${forceChange}&csrf_token=<?= $csrf_token ?>`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fechar modal
            const modal = document.getElementById('resetPasswordModal');
            const backdrop = document.getElementById('modal-backdrop');
            if (modal) {
                modal.style.display = 'none';
                modal.classList.remove('show');
                modal.remove();
            }
            if (backdrop) backdrop.remove();
            document.body.classList.remove('modal-open');
            
            showNotification(data.message, 'success');
        } else {
            showNotification('Erro: ' + data.message, 'error');
        }
    })
    .catch(error => {
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
                        <button type="button" class="btn btn-primary" onclick="editUser('${user.username}')">Editar</button>
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
    
    // Mostrar modal usando vanilla JavaScript
    const modal = document.getElementById('userModal');
    if (modal) {
        modal.style.display = 'block';
        modal.classList.add('show');
        document.body.classList.add('modal-open');
        
        if (!document.getElementById('modal-backdrop')) {
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = 'modal-backdrop';
            document.body.appendChild(backdrop);
        }
    }
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

function showCreateUser() {
    const modalHtml = `
        <div class="modal fade" id="createUserModal" tabindex="-1" style="z-index: 9999;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header" style="background: #0078d4; color: white;">
                        <h5 class="modal-title">
                            <i class="fas fa-user-plus"></i> Criar Novo Usuário no Active Directory
                        </h5>
                        <button type="button" class="close" onclick="closeCreateUserModal()" style="color: white; opacity: 0.8;">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info" style="border-left: 4px solid #0078d4;">
                            <i class="fas fa-info-circle"></i>
                            <strong>Campos Obrigatórios:</strong> Nome Sobrenome, Nome de Usuário e Senha inicial são obrigatórios para criar o usuário no AD.
                        </div>
                        
                        <form id="createUserForm">
                            <!-- Seção: Dados Pessoais -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-section">
                                        <h6 class="section-title"><i class="fas fa-user"></i> Dados Pessoais</h6>
                                        
                                        <div class="form-group">
                                            <label for="createFirstName">Nome (Obrigatório):</label>
                                            <input type="text" class="form-control" id="createFirstName" placeholder="Ex: Carlos" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="createLastName">Sobrenome (Obrigatório):</label>
                                            <input type="text" class="form-control" id="createLastName" placeholder="Ex: Silva" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="createDisplayName">Nome Completo:</label>
                                            <input type="text" class="form-control" id="createDisplayName" placeholder="Será preenchido automaticamente" readonly>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="createEmail">Email:</label>
                                            <input type="email" class="form-control" id="createEmail" placeholder="usuario@empresa.com">
                                        </div>
                                        
                                        <div class="form-group mt-3">
                                            <label for="createDescription" style="font-weight: 600; color: #495057;">
                                                <i class="fas fa-comment" style="color: #6c757d;"></i> Observações:
                                            </label>
                                            <textarea class="form-control" id="createDescription" rows="3" placeholder="Informações adicionais sobre o usuário..." style="font-size: 0.85rem; resize: vertical; min-height: 60px;"></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Seção: Dados do Sistema -->
                                <div class="col-md-4">
                                    <div class="form-section">
                                        <h6 class="section-title"><i class="fas fa-cog"></i> Dados do Sistema</h6>
                                        
                                        <div class="form-group">
                                            <label for="createUsername">Nome de Usuário (Obrigatório):</label>
                                            <input type="text" class="form-control" id="createUsername" placeholder="Ex: usuario.sobrenome" required>
                                            <small class="form-text text-muted">Mínimo 3-4 caracteres, apenas letras e números</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="createPassword">Senha Inicial (Obrigatório):</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="createPassword" placeholder="Mínimo 8 caracteres" required>
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('createPassword')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary" onclick="generateRandomPassword()">
                                                        <i class="fas fa-dice"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group form-check">
                                            <input type="checkbox" class="form-check-input" id="createForcePasswordChange" checked>
                                            <label class="form-check-label" for="createForcePasswordChange">
                                                Forçar mudança de senha no primeiro login
                                            </label>
                                        </div>
                                        
                                        <div class="form-group form-check">
                                            <input type="checkbox" class="form-check-input" id="createAccountEnabled" checked>
                                            <label class="form-check-label" for="createAccountEnabled">
                                                Conta ativa inicialmente
                                            </label>
                                        </div>
                                        
                                        <!-- OU Selection Section - RESPONSIVA -->
                                        <div class="form-group ou-selector-section" style="background: linear-gradient(135deg, #e3f2fd, #f8f9fa); border: 2px solid #2196f3; border-radius: 6px; padding: 12px; margin: 10px 0; box-shadow: 0 2px 8px rgba(33, 150, 243, 0.15);">
                                            <label for="createOU" style="font-weight: 600; font-size: 0.9rem; color: #1976d2; margin-bottom: 8px; display: block;">
                                                <i class="fas fa-sitemap" style="color: #2196f3;"></i> 📂 OU/Container:
                                            </label>
                                            <select class="form-control" id="createOU" style="background: #ffffff; border: 2px solid #2196f3; font-size: 0.85rem; padding: 6px 8px; height: auto; min-height: 32px;">
                                                <option value="">🔍 Detectar automaticamente</option>
                                                <optgroup label="📁 Containers Padrão">
                                                    <option value="CN=Users">CN=Users (Padrão)</option>
                                                    <option value="OU=Users">OU=Users</option>
                                                </optgroup>
                                                <optgroup label="⚙️ Personalizado">
                                                    <option value="custom">✏️ Digitar OU específica</option>
                                                </optgroup>
                                            </select>
                                            <small class="form-text" style="color: #1976d2; font-size: 0.75rem; margin-top: 4px; line-height: 1.2;">
                                                <i class="fas fa-info-circle"></i>
                                                <strong>NOVA:</strong> Especifique onde criar no AD ou deixe automático.
                                            </small>
                                        </div>
                                        
                                        <div class="form-group" id="customOUGroup" style="display: none; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; padding: 8px; margin-top: 6px;">
                                            <label for="createCustomOU" style="font-weight: 600; color: #856404; font-size: 0.85rem; margin-bottom: 4px;">
                                                <i class="fas fa-edit" style="color: #ffc107;"></i> OU Personalizada:
                                            </label>
                                            <input type="text" class="form-control" id="createCustomOU" 
                                                   placeholder="Ex: OU=TI,OU=Departamentos" style="border: 1px solid #ffc107; font-family: monospace; font-size: 0.8rem; padding: 4px 6px;">
                                            <small class="form-text" style="color: #856404; font-size: 0.7rem; margin-top: 2px; line-height: 1.1;">
                                                <strong>Ex:</strong> OU=TI, OU=TI,OU=Departamentos, CN=Users
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Seção: Dados Profissionais -->
                                <div class="col-md-4">
                                    <div class="form-section">
                                        <h6 class="section-title"><i class="fas fa-briefcase"></i> Dados Profissionais</h6>
                                        
                                        <div class="form-group">
                                            <label for="createTitle">Função/Cargo:</label>
                                            <input type="text" class="form-control" id="createTitle" placeholder="Ex: Analista Principal">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="createDepartment">Departamento:</label>
                                            <input type="text" class="form-control" id="createDepartment" placeholder="Ex: TI, RH, Financeiro...">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="createCompany">Empresa:</label>
                                            <input type="text" class="form-control" id="createCompany" placeholder="Ex: Empresa Principal, Filial...">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="createManager">Gestor/Chefe:</label>
                                            <input type="text" class="form-control" id="createManager" placeholder="Nome do gestor">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="createCity">Cidade:</label>
                                            <input type="text" class="form-control" id="createCity" placeholder="Ex: São Paulo, Rio de Janeiro...">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="createOffice">Escritório:</label>
                                            <input type="text" class="form-control" id="createOffice" placeholder="Ex: Sede Principal">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="createPhone">Telefone:</label>
                                            <input type="tel" class="form-control" id="createPhone" placeholder="(11) 99999-9999">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="createMobile">Celular:</label>
                                            <input type="tel" class="form-control" id="createMobile" placeholder="(11) 99999-9999">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Seção Grupos - Linha Dedicada -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="form-section" style="background: linear-gradient(135deg, #f8f9fa, #e9ecef); border: 2px solid #007bff; border-radius: 8px;">
                                        <h6 class="section-title" style="color: #007bff;"><i class="fas fa-users"></i> 👥 Grupos do Active Directory</h6>
                                        
                                        <div class="form-group">
                                            <label for="createGroups" style="font-weight: 600; color: #495057;">Adicionar aos Grupos:</label>
                                            <!-- Lista de Grupos Dinâmica -->
                                            <div id="groupsList" class="groups-container" style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 6px; padding: 10px; background: #ffffff;">
                                                
                                                <!-- Grupo Obrigatório Domain Users -->
                                                <div class="form-check group-item" style="margin-bottom: 8px; padding: 8px; background: #e8f5e8; border-radius: 4px; border: 1px solid #c3e6c3;">
                                                    <input class="form-check-input" type="checkbox" value="Domain Users" id="group_domain_users" checked disabled>
                                                    <label class="form-check-label" for="group_domain_users" style="font-weight: 600; color: #28a745; display: flex; align-items: center;">
                                                        <i class="fas fa-users" style="margin-right: 6px; color: #28a745;"></i>
                                                        ✓ Domain Users (Padrão)
                                                    </label>
                                                    <small class="d-block text-muted" style="margin-left: 20px; font-size: 0.75rem;">Grupo padrão obrigatório</small>
                                                </div>

                                                <!-- Grupos Comuns do AD -->
                                                <div class="form-check group-item" style="margin-bottom: 8px; padding: 8px; background: #f8f9fa; border-radius: 4px; border: 1px solid #e9ecef;">
                                                    <input class="form-check-input" type="checkbox" value="Funcionarios" id="group_funcionarios">
                                                    <label class="form-check-label" for="group_funcionarios" style="color: #495057; display: flex; align-items: center;">
                                                        <i class="fas fa-building" style="margin-right: 6px; color: #6c757d;"></i>
                                                        👥 Funcionários
                                                    </label>
                                                    <small class="d-block text-muted" style="margin-left: 20px; font-size: 0.75rem;">Acesso geral da empresa</small>
                                                </div>

                                                <div class="form-check group-item" style="margin-bottom: 8px; padding: 8px; background: #f8f9fa; border-radius: 4px; border: 1px solid #e9ecef;">
                                                    <input class="form-check-input" type="checkbox" value="VPN Users" id="group_vpn_users">
                                                    <label class="form-check-label" for="group_vpn_users" style="color: #495057; display: flex; align-items: center;">
                                                        <i class="fas fa-shield-alt" style="margin-right: 6px; color: #6c757d;"></i>
                                                        🔐 VPN Users
                                                    </label>
                                                    <small class="d-block text-muted" style="margin-left: 20px; font-size: 0.75rem;">Acesso remoto via VPN</small>
                                                </div>

                                                <div class="form-check group-item" style="margin-bottom: 8px; padding: 8px; background: #f8f9fa; border-radius: 4px; border: 1px solid #e9ecef;">
                                                    <input class="form-check-input" type="checkbox" value="Administrators" id="group_administrators">
                                                    <label class="form-check-label" for="group_administrators" style="color: #495057; display: flex; align-items: center;">
                                                        <i class="fas fa-crown" style="margin-right: 6px; color: #dc3545;"></i>
                                                        👑 Administrators
                                                    </label>
                                                    <small class="d-block text-muted" style="margin-left: 20px; font-size: 0.75rem;">Acesso administrativo total</small>
                                                </div>

                                                <div class="form-check group-item" style="margin-bottom: 8px; padding: 8px; background: #f8f9fa; border-radius: 4px; border: 1px solid #e9ecef;">
                                                    <input class="form-check-input" type="checkbox" value="Remote Desktop Users" id="group_rdp_users">
                                                    <label class="form-check-label" for="group_rdp_users" style="color: #495057; display: flex; align-items: center;">
                                                        <i class="fas fa-desktop" style="margin-right: 6px; color: #6c757d;"></i>
                                                        💻 Remote Desktop Users
                                                    </label>
                                                    <small class="d-block text-muted" style="margin-left: 20px; font-size: 0.75rem;">Acesso via RDP</small>
                                                </div>

                                                <div class="form-check group-item" style="margin-bottom: 8px; padding: 8px; background: #f8f9fa; border-radius: 4px; border: 1px solid #e9ecef;">
                                                    <input class="form-check-input" type="checkbox" value="Power Users" id="group_power_users">
                                                    <label class="form-check-label" for="group_power_users" style="color: #495057; display: flex; align-items: center;">
                                                        <i class="fas fa-bolt" style="margin-right: 6px; color: #ffc107;"></i>
                                                        ⚡ Power Users
                                                    </label>
                                                    <small class="d-block text-muted" style="margin-left: 20px; font-size: 0.75rem;">Usuários avançados</small>
                                                </div>

                                                <div class="form-check group-item" style="margin-bottom: 8px; padding: 8px; background: #f8f9fa; border-radius: 4px; border: 1px solid #e9ecef;">
                                                    <input class="form-check-input" type="checkbox" value="Backup Operators" id="group_backup_ops">
                                                    <label class="form-check-label" for="group_backup_ops" style="color: #495057; display: flex; align-items: center;">
                                                        <i class="fas fa-hdd" style="margin-right: 6px; color: #6c757d;"></i>
                                                        💾 Backup Operators
                                                    </label>
                                                    <small class="d-block text-muted" style="margin-left: 20px; font-size: 0.75rem;">Operadores de backup</small>
                                                </div>

                                                <!-- Botão para carregar mais grupos -->
                                                <div class="text-center mt-2">
                                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="loadMoreGroups()" style="font-size: 0.8rem; padding: 4px 12px;">
                                                        <i class="fas fa-sync-alt"></i> Carregar grupos do AD
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="alert alert-info mt-2" style="margin-bottom: 0; padding: 8px 12px;">
                                                <i class="fas fa-info-circle"></i>
                                                <strong>Importante:</strong> Domain Users é adicionado automaticamente. Selecione grupos adicionais conforme necessário.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeCreateUserModal()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-success" onclick="createNewUser()">
                            <i class="fas fa-user-plus"></i> Criar Usuário
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remover modal existente
    const existingModal = document.getElementById('createUserModal');
    if (existingModal) existingModal.remove();
    
    // Adicionar e mostrar novo modal
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Configurar auto-preenchimento do nome completo
    setupAutoFillDisplayName();
    
    // Configurar auto-preenchimento do email
    setupAutoFillEmail();
    
    // Configurar controle da OU personalizada
    setupOUSelector();
    
    // Mostrar modal usando vanilla JavaScript
    const modal = document.getElementById('createUserModal');
    if (modal) {
        modal.style.display = 'block';
        modal.classList.add('show');
        document.body.classList.add('modal-open');
        
        // Adicionar backdrop
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.id = 'modal-backdrop';
        backdrop.onclick = () => closeModal('createUserModal');
        document.body.appendChild(backdrop);
        
        // Fechar modal com ESC
        const handleEscape = (e) => {
            if (e.key === 'Escape') {
                closeModal('createUserModal');
                document.removeEventListener('keydown', handleEscape);
            }
        };
        document.addEventListener('keydown', handleEscape);
    }
}

// Função genérica para fechar modais
function closeModal(modalId) {
    const modal = modalId ? document.getElementById(modalId) : document.querySelector('.modal.show');
    const backdrop = document.getElementById('modal-backdrop');
    
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
        modal.remove(); // Remove completamente do DOM
    }
    
    if (backdrop) {
        backdrop.remove();
    }
    
    document.body.classList.remove('modal-open');
}

// Função específica para fechar o modal de criação
function closeCreateUserModal() {
    closeModal('createUserModal');
}

// Função para editar usuário
function editUser(username) {
    fetch(`index.php?page=users&action=getUser&username=${username}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showEditUserModal(data.user);
        } else {
            showNotification('Erro ao carregar usuário: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Erro de comunicação: ' + error.message, 'error');
    });
}

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
                        <button type="button" class="btn btn-primary" onclick="saveUserEdit('${user.username}')">Salvar Alterações</button>
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
    
    // Mostrar modal usando vanilla JavaScript
    const modal = document.getElementById('editUserModal');
    if (modal) {
        modal.style.display = 'block';
        modal.classList.add('show');
        document.body.classList.add('modal-open');
        
        // Adicionar backdrop se não existir
        if (!document.getElementById('modal-backdrop')) {
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = 'modal-backdrop';
            document.body.appendChild(backdrop);
        }
    }
}

// Salvar edição do usuário
function saveUserEdit(username) {
    const userData = {
        name: document.getElementById('editName').value,
        email: document.getElementById('editEmail').value,
        phone: document.getElementById('editPhone').value,
        title: document.getElementById('editTitle').value,
        department: document.getElementById('editDepartment').value,
        company: document.getElementById('editCompany').value,
        city: document.getElementById('editCity').value,
        office: document.getElementById('editOffice').value,
        description: document.getElementById('editDescription').value
    };
    
    fetch('index.php?page=users&action=updateUser', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `username=${username}&user_data=${encodeURIComponent(JSON.stringify(userData))}&csrf_token=<?= $csrf_token ?>`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fechar modal
            const modal = document.getElementById('editUserModal');
            const backdrop = document.getElementById('modal-backdrop');
            if (modal) {
                modal.style.display = 'none';
                modal.classList.remove('show');
                modal.remove();
            }
            if (backdrop) backdrop.remove();
            document.body.classList.remove('modal-open');
            
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
    
    // Mostrar modal usando vanilla JavaScript
    const modal = document.getElementById('userGroupsModal');
    if (modal) {
        modal.style.display = 'block';
        modal.classList.add('show');
        document.body.classList.add('modal-open');
        
        if (!document.getElementById('modal-backdrop')) {
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = 'modal-backdrop';
            document.body.appendChild(backdrop);
        }
    }
}

// Função para excluir usuário
function deleteUser(username) {
    if (confirm(`ATENÇÃO: Deseja realmente EXCLUIR o usuário ${username}?\n\nEsta ação não pode ser desfeita!`)) {
        fetch('index.php?page=users&action=deleteUser', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `username=${username}&csrf_token=<?= $csrf_token ?>`
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

// Função para configurar preenchimento automático do nome completo
function setupAutoFillDisplayName() {
    const firstNameInput = document.getElementById('createFirstName');
    const lastNameInput = document.getElementById('createLastName');
    const displayNameInput = document.getElementById('createDisplayName');
    
    function updateDisplayName() {
        const firstName = firstNameInput.value.trim();
        const lastName = lastNameInput.value.trim();
        
        if (firstName && lastName) {
            displayNameInput.value = `${firstName} ${lastName}`;
        } else if (firstName) {
            displayNameInput.value = firstName;
        } else {
            displayNameInput.value = '';
        }
    }
    
    firstNameInput.addEventListener('input', updateDisplayName);
    lastNameInput.addEventListener('input', updateDisplayName);
}

// Função para configurar preenchimento automático do email
function setupAutoFillEmail() {
    const firstNameInput = document.getElementById('createFirstName');
    const lastNameInput = document.getElementById('createLastName');
    const emailInput = document.getElementById('createEmail');
    
    function updateEmail() {
        const firstName = firstNameInput.value.trim().toLowerCase();
        const lastName = lastNameInput.value.trim().toLowerCase();
        
        if (firstName && lastName) {
            // Remover acentos e caracteres especiais
            const cleanFirstName = firstName.normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/[^a-z]/g, "");
            const cleanLastName = lastName.normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/[^a-z]/g, "");
            
            if (cleanFirstName && cleanLastName) {
                emailInput.value = `${cleanFirstName}.${cleanLastName}@empresa.local`;
            }
        }
    }
    
    firstNameInput.addEventListener('blur', updateEmail);
    lastNameInput.addEventListener('blur', updateEmail);
}

// Função para configurar seletor de OU
function setupOUSelector() {
    const ouSelect = document.getElementById('createOU');
    const customOUGroup = document.getElementById('customOUGroup');
    const customOUInput = document.getElementById('createCustomOU');
    
    ouSelect.addEventListener('change', function() {
        if (this.value === 'custom') {
            customOUGroup.style.display = 'block';
            customOUInput.focus();
        } else {
            customOUGroup.style.display = 'none';
            customOUInput.value = '';
        }
    });
}

// Função para alternar visibilidade da senha
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const icon = event.target.closest('button').querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Função para gerar senha aleatória
function generateRandomPassword() {
    const length = 12;
    const charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
    let password = '';
    
    // Garantir pelo menos um de cada tipo
    const upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const lower = 'abcdefghijklmnopqrstuvwxyz';
    const numbers = '0123456789';
    const symbols = '!@#$%^&*';
    
    password += upper.charAt(Math.floor(Math.random() * upper.length));
    password += lower.charAt(Math.floor(Math.random() * lower.length));
    password += numbers.charAt(Math.floor(Math.random() * numbers.length));
    password += symbols.charAt(Math.floor(Math.random() * symbols.length));
    
    // Preencher o restante
    for (let i = 4; i < length; i++) {
        password += charset.charAt(Math.floor(Math.random() * charset.length));
    }
    
    // Embaralhar
    password = password.split('').sort(() => Math.random() - 0.5).join('');
    
    document.getElementById('createPassword').value = password;
    
    showNotification('Senha gerada automaticamente!', 'success');
}

// Função principal para criar usuário
function createNewUser() {
    const form = document.getElementById('createUserForm');
    
    // Validar campos obrigatórios
    const firstName = document.getElementById('createFirstName').value.trim();
    const lastName = document.getElementById('createLastName').value.trim();
    const username = document.getElementById('createUsername').value.trim();
    const password = document.getElementById('createPassword').value;
    
    if (!firstName) {
        showNotification('Nome é obrigatório', 'error');
        document.getElementById('createFirstName').focus();
        return;
    }
    
    if (!lastName) {
        showNotification('Sobrenome é obrigatório', 'error');
        document.getElementById('createLastName').focus();
        return;
    }
    
    if (!username || username.length < 3) {
        showNotification('Nome de usuário deve ter pelo menos 3 caracteres', 'error');
        document.getElementById('createUsername').focus();
        return;
    }
    
    if (!password || password.length < 8) {
        showNotification('Senha deve ter pelo menos 8 caracteres', 'error');
        document.getElementById('createPassword').focus();
        return;
    }
    
    // Validar formato do username (apenas letras, números e alguns caracteres especiais)
    if (!/^[a-zA-Z0-9._-]+$/.test(username)) {
        showNotification('Nome de usuário deve conter apenas letras, números, pontos, hífens ou underscores', 'error');
        document.getElementById('createUsername').focus();
        return;
    }
    
    // Obter OU selecionada
    const ouSelect = document.getElementById('createOU');
    const customOU = document.getElementById('createCustomOU').value.trim();
    let targetOU = '';
    
    if (ouSelect.value === 'custom' && customOU) {
        targetOU = customOU;
    } else if (ouSelect.value) {
        targetOU = ouSelect.value;
    }
    
    // Coletar dados do formulário
    const userData = {
        firstName: firstName,
        lastName: lastName,
        displayName: document.getElementById('createDisplayName').value.trim(),
        username: username,
        password: password,
        email: document.getElementById('createEmail').value.trim(),
        title: document.getElementById('createTitle').value.trim(),
        department: document.getElementById('createDepartment').value.trim(),
        company: document.getElementById('createCompany').value.trim(),
        manager: document.getElementById('createManager').value.trim(),
        city: document.getElementById('createCity').value.trim(),
        office: document.getElementById('createOffice').value.trim(),
        phone: document.getElementById('createPhone').value.trim(),
        mobile: document.getElementById('createMobile').value.trim(),
        description: document.getElementById('createDescription').value.trim(),
        forcePasswordChange: document.getElementById('createForcePasswordChange').checked,
        accountEnabled: document.getElementById('createAccountEnabled').checked,
        targetOU: targetOU,
        groups: getSelectedGroups()
    };
    
    // Confirmar criação
    const confirmMessage = `Confirmar criação do usuário?\n\nNome: ${userData.displayName}\nUsuário: ${userData.username}\nEmail: ${userData.email}\nDepartamento: ${userData.department || 'N/I'}`;
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    // Mostrar loading
    const createBtn = event.target;
    const originalText = createBtn.innerHTML;
    createBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Criando...';
    createBtn.disabled = true;
    
    // Enviar dados para o servidor
    fetch('index.php?page=users&action=createUser', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `user_data=${encodeURIComponent(JSON.stringify(userData))}&csrf_token=<?= $csrf_token ?>`
    })
    .then(response => response.json())
    .then(data => {
        createBtn.innerHTML = originalText;
        createBtn.disabled = false;
        
        if (data.success) {
            // Fechar modal
            const modal = document.getElementById('createUserModal');
            const backdrop = document.getElementById('modal-backdrop');
            if (modal) {
                modal.style.display = 'none';
                modal.classList.remove('show');
                modal.remove();
            }
            if (backdrop) backdrop.remove();
            document.body.classList.remove('modal-open');
            
            // Verificar se é modo simulação
            if (data.mode === 'simulation') {
                showSimulationSuccess(data);
            } else {
                showNotification(data.message || 'Usuário criado com sucesso!', 'success');
                setTimeout(() => window.location.reload(), 2000);
            }
        } else {
            // Melhorar exibição de erros
            let errorMsg = data.message || 'Falha ao criar usuário';
            if (data.suggestion) {
                errorMsg += '\n\n💡 Sugestão: ' + data.suggestion;
            }
            if (data.attempted_dn) {
                console.error('DN tentado:', data.attempted_dn);
            }
            showNotification('Erro: ' + errorMsg, 'error');
        }
    })
    .catch(error => {
        createBtn.innerHTML = originalText;
        createBtn.disabled = false;
        showNotification('Erro de comunicação: ' + error.message, 'error');
    });
}

// Função para coletar grupos selecionados
function getSelectedGroups() {
    const checkboxes = document.querySelectorAll('#createUserModal .form-check-input:checked');
    const groups = [];
    
    checkboxes.forEach(checkbox => {
        if (checkbox.value && checkbox.value !== '') {
            groups.push(checkbox.value);
        }
    });
    
    return groups;
}

// Função para mostrar sucesso da simulação
function showSimulationSuccess(data) {
    const modalHtml = `
        <div class="modal fade" id="simulationResultModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #28a745, #20c997); color: white;">
                        <h5 class="modal-title">
                            <i class="fas fa-check-circle"></i> Simulação de Criação Realizada com Sucesso!
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" style="color: white;">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-success" style="border-left: 4px solid #28a745;">
                            <h5><i class="fas fa-info-circle"></i> Modo Demonstração</h5>
                            <p><strong>${data.message}</strong></p>
                            ${data.note ? `<p><em>${data.note}</em></p>` : ''}
                        </div>
                        
                        <h6><i class="fas fa-user"></i> Dados do Usuário Simulado:</h6>
                        <table class="table table-sm table-bordered">
                            <tr><td><strong>Nome de Usuário:</strong></td><td>${data.username}</td></tr>
                            <tr><td><strong>Nome Completo:</strong></td><td>${data.details?.display_name || 'N/A'}</td></tr>
                            <tr><td><strong>Email:</strong></td><td>${data.details?.email || 'N/A'}</td></tr>
                            <tr><td><strong>Departamento:</strong></td><td>${data.details?.department || 'N/A'}</td></tr>
                            <tr><td><strong>Função:</strong></td><td>${data.details?.title || 'N/A'}</td></tr>
                            <tr><td><strong>Empresa:</strong></td><td>${data.details?.company || 'N/A'}</td></tr>
                            <tr><td><strong>Cidade:</strong></td><td>${data.details?.city || 'N/A'}</td></tr>
                            <tr><td><strong>DN Simulado:</strong></td><td style="font-family: monospace; font-size: 11px;">${data.dn}</td></tr>
                        </table>
                        
                        <div class="alert alert-info">
                            <h6><i class="fas fa-cogs"></i> Para usar com Active Directory real:</h6>
                            <ol>
                                <li>Acesse <strong>Configurações</strong> no menu</li>
                                <li>Configure as <strong>credenciais LDAP/AD</strong></li>
                                <li>Teste a <strong>conectividade</strong></li>
                                <li>Execute novamente a <strong>criação de usuário</strong></li>
                            </ol>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Fechar
                        </button>
                        <button type="button" class="btn btn-primary" onclick="goToConfig()">
                            <i class="fas fa-cog"></i> Ir para Configurações
                        </button>
                        <button type="button" class="btn btn-success" data-dismiss="modal" onclick="createAnotherUser()">
                            <i class="fas fa-user-plus"></i> Criar Outro Usuário
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remover modal existente
    const existingModal = document.getElementById('simulationResultModal');
    if (existingModal) existingModal.remove();
    
    // Adicionar e mostrar novo modal
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Mostrar modal usando vanilla JavaScript
    const modal = document.getElementById('simulationResultModal');
    if (modal) {
        modal.style.display = 'block';
        modal.classList.add('show');
        document.body.classList.add('modal-open');
        
        if (!document.getElementById('modal-backdrop')) {
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = 'modal-backdrop';
            document.body.appendChild(backdrop);
        }
    }
    
    // Também mostrar notificação
    showNotification('🎭 Simulação realizada com sucesso! Usuário seria criado no AD.', 'success');
}

// Função para ir às configurações
function goToConfig() {
    window.location.href = 'index.php?page=config';
}

// Função para criar outro usuário
function createAnotherUser() {
    setTimeout(() => {
        showCreateUser();
    }, 500);
}

// Função para carregar mais grupos do AD
function loadMoreGroups() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    // Mostrar loading
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Carregando...';
    button.disabled = true;
    
    // Simular busca no AD (em produção, faria uma requisição AJAX real)
    setTimeout(() => {
        // Grupos adicionais que podem existir no AD
        const additionalGroups = [
            {id: 'print_operators', name: 'Print Operators', icon: 'fas fa-print', color: '#6c757d', description: 'Operadores de impressão'},
            {id: 'account_operators', name: 'Account Operators', icon: 'fas fa-user-cog', color: '#6c757d', description: 'Operadores de contas'},
            {id: 'server_operators', name: 'Server Operators', icon: 'fas fa-server', color: '#6c757d', description: 'Operadores de servidor'},
            {id: 'network_operators', name: 'Network Configuration Operators', icon: 'fas fa-network-wired', color: '#6c757d', description: 'Operadores de rede'},
            {id: 'guests', name: 'Guests', icon: 'fas fa-user-friends', color: '#6c757d', description: 'Usuários convidados'},
            {id: 'users', name: 'Users', icon: 'fas fa-users', color: '#6c757d', description: 'Usuários locais'},
            {id: 'replicator', name: 'Replicator', icon: 'fas fa-copy', color: '#6c757d', description: 'Replicação de diretório'},
            {id: 'crypto_operators', name: 'Cryptographic Operators', icon: 'fas fa-key', color: '#6c757d', description: 'Operadores criptográficos'}
        ];
        
        const groupsContainer = document.getElementById('groupsList');
        const loadButton = button.parentElement;
        
        // Adicionar novos grupos
        additionalGroups.forEach(group => {
            const groupHtml = `
                <div class="form-check group-item" style="margin-bottom: 8px; padding: 8px; background: #f8f9fa; border-radius: 4px; border: 1px solid #e9ecef;">
                    <input class="form-check-input" type="checkbox" value="${group.name}" id="group_${group.id}">
                    <label class="form-check-label" for="group_${group.id}" style="color: #495057; display: flex; align-items: center;">
                        <i class="${group.icon}" style="margin-right: 6px; color: ${group.color};"></i>
                        ${group.name}
                    </label>
                    <small class="d-block text-muted" style="margin-left: 20px; font-size: 0.75rem;">${group.description}</small>
                </div>
            `;
            
            // Inserir antes do botão
            loadButton.insertAdjacentHTML('beforebegin', groupHtml);
        });
        
        // Remover botão após carregar
        loadButton.remove();
        
        showNotification('✅ Grupos do AD carregados com sucesso!', 'success');
        
    }, 1500); // Simula tempo de carregamento
}

// Função para coletar grupos selecionados (atualizada)
function getSelectedGroups() {
    const checkboxes = document.querySelectorAll('#groupsList .form-check-input:checked:not(:disabled)');
    const groups = [];
    
    checkboxes.forEach(checkbox => {
        if (checkbox.value && checkbox.value !== '') {
            groups.push(checkbox.value);
        }
    });
    
    return groups;
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

/* Estilos gerais para modais */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1050;
    display: none;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal.show {
    display: flex !important;
    align-items: center;
    justify-content: center;
}

.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 1040;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-backdrop.show {
    opacity: 0.5;
}

.modal-open {
    overflow: hidden;
}

/* Estilos para o Modal de Criação de Usuário */
#createUserModal .modal-dialog {
    max-width: 1400px;
    margin: 0.5rem auto;
}

#createUserModal .modal-content {
    border: none;
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

#createUserModal .modal-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px 8px 0 0;
}

#createUserModal .modal-title {
    font-weight: 600;
    font-size: 1.1rem;
}

#createUserModal .modal-body {
    padding: 15px;
    max-height: 85vh;
    overflow-y: auto;
}

/* Garantir que campos da OU sejam visíveis, destacados e responsivos */
#createOU, #customOUGroup {
    margin-bottom: 8px !important;
    z-index: 9999 !important;
}

#createOU {
    border: 2px solid #2196f3 !important;
    background-color: #ffffff !important;
    font-size: 0.85rem !important;
    padding: 6px 8px !important;
    height: auto !important;
    min-height: 32px !important;
    word-wrap: break-word !important;
}

/* Responsividade para opções do select */
#createOU option,
#createOU optgroup {
    font-size: 0.8rem !important;
    padding: 4px 6px !important;
    white-space: normal !important;
    word-wrap: break-word !important;
}

.ou-selector-section {
    background: linear-gradient(135deg, #e3f2fd, #f8f9fa) !important;
    border: 2px solid #2196f3 !important;
    border-radius: 8px !important;
    padding: 20px !important;
    margin: 15px 0 !important;
    box-shadow: 0 2px 10px rgba(33, 150, 243, 0.1) !important;
    position: relative !important;
}

/* Destacar ainda mais a seção OU */
.ou-selector-section::before {
    content: "🆕 NOVO!";
    position: absolute;
    top: -8px;
    right: 10px;
    background: #ff5722;
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 0.7rem;
    font-weight: bold;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

/* Garantir visibilidade da seção de Grupos */
.groups-section {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef) !important;
    border: 2px solid #007bff !important;
    border-radius: 8px !important;
    padding: 20px !important;
    margin-bottom: 20px !important;
}

.groups-section .form-check {
    transition: all 0.2s ease;
}

.groups-section .form-check:hover {
    transform: translateX(5px);
    box-shadow: 0 2px 8px rgba(0,123,255,0.2);
}

/* Estilos para a lista de grupos dinâmica */
.groups-container {
    scrollbar-width: thin;
    scrollbar-color: #007bff #f8f9fa;
}

.groups-container::-webkit-scrollbar {
    width: 6px;
}

.groups-container::-webkit-scrollbar-track {
    background: #f8f9fa;
    border-radius: 3px;
}

.groups-container::-webkit-scrollbar-thumb {
    background: #007bff;
    border-radius: 3px;
}

.groups-container::-webkit-scrollbar-thumb:hover {
    background: #0056b3;
}

.group-item {
    transition: all 0.2s ease;
    cursor: pointer;
}

.group-item:hover {
    background: #e3f2fd !important;
    border-color: #2196f3 !important;
    transform: translateX(3px);
}

.group-item input[type="checkbox"]:checked + label {
    font-weight: 600;
    color: #007bff !important;
}

.group-item input[type="checkbox"]:disabled + label {
    opacity: 1;
}

/* Animação para novos grupos adicionados */
@keyframes slideInGroup {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.group-item.new-group {
    animation: slideInGroup 0.3s ease-out;
}

#createUserModal .form-section {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 15px;
    height: 100%;
}

#createUserModal .section-title {
    color: #0078d4;
    font-weight: 600;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 2px solid #e9ecef;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

#createUserModal .form-group {
    margin-bottom: 15px;
}

#createUserModal .form-group label {
    font-weight: 500;
    color: #495057;
    font-size: 0.9rem;
    margin-bottom: 5px;
    display: block;
}

#createUserModal .form-control {
    font-size: 0.9rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
    padding: 8px 12px;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

#createUserModal .form-control:focus {
    border-color: #0078d4;
    box-shadow: 0 0 0 0.2rem rgba(0, 120, 212, 0.25);
}

#createUserModal .form-control[readonly] {
    background-color: #f8f9fa;
    border-color: #e9ecef;
    color: #6c757d;
}

#createUserModal .input-group-append .btn {
    border-left: none;
    font-size: 0.85rem;
    padding: 8px 10px;
}

#createUserModal .form-check {
    margin-bottom: 8px;
}

#createUserModal .form-check-label {
    font-size: 0.9rem;
    color: #495057;
    cursor: pointer;
}

#createUserModal .checkbox-group {
    background: white;
}

#createUserModal .checkbox-group .form-check {
    padding: 5px 0;
    margin: 0;
}

#createUserModal .alert {
    margin-bottom: 20px;
    font-size: 0.9rem;
}

#createUserModal .modal-footer {
    border-top: 1px solid #e9ecef;
    padding: 15px 25px;
}

#createUserModal .btn {
    font-size: 0.9rem;
    font-weight: 500;
    padding: 8px 16px;
    border-radius: 4px;
}

#createUserModal .btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

#createUserModal .btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
}

/* Garantir que o modal use o máximo da viewport */
#createUserModal {
    padding: 0 !important;
}

#createUserModal .modal-dialog {
    height: 98vh;
    margin: 1vh auto;
}

#createUserModal .modal-content {
    height: 100%;
    display: flex;
    flex-direction: column;
}

#createUserModal .modal-body {
    flex: 1;
    overflow-y: auto;
}

/* Responsividade do modal */
@media (max-width: 1200px) {
    #createUserModal .modal-dialog {
        max-width: 95%;
        height: 98vh;
    }
}

@media (max-width: 768px) {
    #createUserModal .modal-dialog {
        margin: 0.5rem;
        max-width: none;
        width: auto;
    }
    
    #createUserModal .modal-body {
        padding: 15px;
        max-height: 85vh;
    }
    
    #createUserModal .form-section {
        padding: 10px;
        margin-bottom: 10px;
    }
    
    #createUserModal .section-title {
        font-size: 0.9rem;
    }
    
    #createUserModal .form-group {
        margin-bottom: 10px;
    }
}
</style>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>