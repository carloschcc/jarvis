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

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-search"></i>
            Buscar Usuários
        </h3>
        
        <div style="display: flex; gap: 15px; align-items: center;">
            <input 
                type="search" 
                id="user-search" 
                class="form-control" 
                placeholder="Digite nome, usuário ou email..."
                value="<?= htmlspecialchars($search ?? '') ?>"
                style="width: 300px;"
            >
            
            <span class="text-muted">
                <?= count($users) ?> usuário(s) encontrado(s)
            </span>
            
            <button onclick="refreshUsers()" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-sync-alt"></i>
                Atualizar
            </button>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i>
            Lista de Usuários (<?= count($users) ?>)
        </h3>
    </div>
    
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Usuário</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Departamento</th>
                    <th>Último Login</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted" style="padding: 40px;">
                        <i class="fas fa-users" style="font-size: 48px; opacity: 0.3;"></i><br>
                        Nenhum usuário encontrado
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($user['username']) ?></strong></td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td>
                            <a href="mailto:<?= htmlspecialchars($user['email']) ?>" class="text-primary">
                                <?= htmlspecialchars($user['email']) ?>
                            </a>
                        </td>
                        <td>
                            <span class="status <?= $user['status'] === 'Ativo' ? 'status-ativo' : 'status-bloqueado' ?>">
                                <?= htmlspecialchars($user['status']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($user['department'] ?? '') ?></td>
                        <td>
                            <?php if (!empty($user['last_logon'])): ?>
                                <?= date('d/m/Y H:i', strtotime($user['last_logon'])) ?>
                            <?php else: ?>
                                <span class="text-muted">Nunca</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group" style="display: flex; gap: 5px;">
                                <?php if ($user['status'] === 'Ativo'): ?>
                                <button onclick="toggleStatus('<?= htmlspecialchars($user['username']) ?>', false)" 
                                        class="btn btn-danger btn-sm" title="Bloquear usuário">
                                    <i class="fas fa-user-times"></i>
                                </button>
                                <?php else: ?>
                                <button onclick="toggleStatus('<?= htmlspecialchars($user['username']) ?>', true)" 
                                        class="btn btn-success btn-sm" title="Ativar usuário">
                                    <i class="fas fa-user-check"></i>
                                </button>
                                <?php endif; ?>
                                
                                <button onclick="resetPassword('<?= htmlspecialchars($user['username']) ?>')" 
                                        class="btn btn-warning btn-sm" title="Redefinir senha">
                                    <i class="fas fa-key"></i>
                                </button>
                                
                                <button onclick="viewUser('<?= htmlspecialchars($user['username']) ?>')" 
                                        class="btn btn-info btn-sm" title="Ver detalhes">
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

<script>
function refreshUsers() {
    window.location.reload();
}

function toggleStatus(username, enable) {
    const action = enable ? 'ativar' : 'bloquear';
    
    if (confirm(`Deseja ${action} o usuário ${username}?`)) {
        fetch('index.php?page=users&action=toggleStatus', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `username=${username}&enable=${enable}&csrf_token=<?= $csrf_token ?>`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert('Erro: ' + data.message);
            }
        });
    }
}

function resetPassword(username) {
    if (confirm(`Deseja redefinir a senha do usuário ${username}?`)) {
        fetch('index.php?page=users&action=resetPassword', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `username=${username}&csrf_token=<?= $csrf_token ?>`
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
        });
    }
}

function viewUser(username) {
    fetch(`index.php?page=users&action=getUser&username=${username}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const user = data.user;
            alert(`Usuário: ${user.name}\nEmail: ${user.email}\nStatus: ${user.status}\nDepartamento: ${user.department}`);
        }
    });
}

// Busca em tempo real
document.getElementById('user-search').addEventListener('input', function(e) {
    const term = e.target.value;
    
    if (term.length > 2 || term.length === 0) {
        const url = new URL(window.location);
        if (term) {
            url.searchParams.set('search', term);
        } else {
            url.searchParams.delete('search');
        }
        window.location.href = url.toString();
    }
});
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>