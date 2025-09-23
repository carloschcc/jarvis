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
                            <div class="btn-group" style="display: flex; gap: 3px;">
                                <!-- Botão Ver Detalhes -->
                                <button onclick="viewUser('<?= htmlspecialchars($user['username']) ?>')" 
                                        class="btn btn-info btn-sm" title="Ver detalhes completos">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                <!-- Botão Status -->
                                <?php if ($user['status'] === 'Ativo'): ?>
                                <button onclick="toggleStatus('<?= htmlspecialchars($user['username']) ?>', false)" 
                                        class="btn btn-warning btn-sm" title="Bloquear usuário">
                                    <i class="fas fa-user-times"></i>
                                </button>
                                <?php else: ?>
                                <button onclick="toggleStatus('<?= htmlspecialchars($user['username']) ?>', true)" 
                                        class="btn btn-success btn-sm" title="Ativar usuário">
                                    <i class="fas fa-user-check"></i>
                                </button>
                                <?php endif; ?>
                                
                                <!-- Botão Reset Senha -->
                                <button onclick="resetPassword('<?= htmlspecialchars($user['username']) ?>')" 
                                        class="btn btn-danger btn-sm" title="Redefinir senha">
                                    <i class="fas fa-key"></i>
                                </button>
                                
                                <!-- Dropdown de mais ações -->
                                <div class="dropdown">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#" onclick="editUser('<?= htmlspecialchars($user['username']) ?>')">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        <a class="dropdown-item" href="#" onclick="viewGroups('<?= htmlspecialchars($user['username']) ?>')">
                                            <i class="fas fa-users"></i> Grupos
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item text-danger" href="#" onclick="deleteUser('<?= htmlspecialchars($user['username']) ?>')">
                                            <i class="fas fa-trash"></i> Excluir
                                        </a>
                                    </div>
                                </div>
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
        search: document.getElementById('user-search').value,
        department: document.getElementById('filter-department').value,
        company: document.getElementById('filter-company').value,
        city: document.getElementById('filter-city').value,
        status: document.getElementById('filter-status').value,
        title: document.getElementById('filter-title').value,
        office: document.getElementById('filter-office').value,
        manager: document.getElementById('filter-manager').value,
        limit: document.getElementById('items-per-page').value
    };
    
    const params = new URLSearchParams();
    
    // Adicionar apenas filtros não vazios
    Object.keys(filters).forEach(key => {
        if (filters[key] && filters[key] !== '' && filters[key] !== 'all') {
            params.append(key, filters[key]);
        }
    });
    
    // Mostrar loading
    document.getElementById('filter-loading').style.display = 'inline-block';
    
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

// Função para resetar senha
function resetPassword(username) {
    const newPassword = prompt(`Digite a nova senha para ${username}:`);
    
    if (newPassword && newPassword.length >= 8) {
        if (confirm(`Confirma o reset da senha para ${username}?`)) {
            fetch('index.php?page=users&action=resetPassword', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `username=${username}&new_password=${encodeURIComponent(newPassword)}&csrf_token=<?= $csrf_token ?>`
            })
            .then(response => response.json())
            .then(data => {
                showNotification(data.message, data.success ? 'success' : 'error');
            })
            .catch(error => {
                showNotification('Erro de comunicação: ' + error.message, 'error');
            });
        }
    } else if (newPassword !== null) {
        showNotification('A senha deve ter pelo menos 8 caracteres', 'warning');
    }
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
    
    const notification = document.createElement('div');
    notification.className = `alert ${alertClass} alert-dismissible`;
    notification.innerHTML = `
        ${message}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    `;
    
    document.querySelector('.page-header').appendChild(notification);
    
    // Auto remover após 5 segundos
    setTimeout(() => {
        if (notification.parentNode) {
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

// Funções placeholder para futuras implementações
function sortBy(field) {
    // TODO: Implementar ordenação
    console.log('Sorting by:', field);
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
    // TODO: Implementar criação de usuário
    console.log('Create new user');
}

function editUser(username) {
    // TODO: Implementar edição de usuário
    console.log('Edit user:', username);
}

function viewGroups(username) {
    // TODO: Implementar visualização de grupos
    console.log('View groups for:', username);
}

function deleteUser(username) {
    // TODO: Implementar exclusão de usuário
    console.log('Delete user:', username);
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

.btn-group .btn {
    padding: 4px 8px;
    font-size: 11px;
}

.dropdown-menu {
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
    
    .btn-group .btn {
        padding: 2px 6px;
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

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>