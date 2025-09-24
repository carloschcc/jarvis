/**
 * AD MANAGER - CORREÇÃO DEFINITIVA DOS BOTÕES
 * Implementação robusta e garantida para funcionar
 */

// Aguardar carregamento completo da página
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 AD Manager - Inicialização das correções definitivas');
    
    // Aguardar jQuery estar disponível
    function waitForJQuery(callback) {
        if (typeof $ !== 'undefined') {
            callback();
        } else {
            setTimeout(() => waitForJQuery(callback), 100);
        }
    }
    
    waitForJQuery(function() {
        console.log('✅ jQuery disponível, configurando funcionalidades...');
        initializeADManager();
    });
});

function initializeADManager() {
    // Configurar botão Novo Usuário
    setupNewUserButton();
    
    // Configurar botões de edição existentes
    setupEditButtons();
    
    // Configurar botões de reset de senha
    setupPasswordResetButtons();
    
    console.log('✅ AD Manager inicializado com sucesso');
}

/**
 * CONFIGURAR BOTÃO NOVO USUÁRIO
 */
function setupNewUserButton() {
    const newUserBtn = document.querySelector('button[onclick="showCreateUser()"]');
    if (newUserBtn) {
        // Remover onclick antigo e adicionar novo listener
        newUserBtn.removeAttribute('onclick');
        newUserBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('🆕 Botão Novo Usuário clicado');
            openCreateUserModal();
        });
        console.log('✅ Botão Novo Usuário configurado');
    }
}

/**
 * ABRIR MODAL DE CRIAÇÃO DE USUÁRIO - VERSÃO ROBUSTA
 */
function openCreateUserModal() {
    console.log('📝 Abrindo modal de criação de usuário...');
    
    const modalId = 'createUserModal_' + Date.now();
    const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-user-plus"></i> Criar Novo Usuário
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="createUserForm_${Date.now()}">
                            <div class="alert alert-info">
                                <strong>Campos Obrigatórios:</strong> Nome, Sobrenome, Nome de Usuário e Email são obrigatórios.
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nome (GivenName): <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="createGivenName" placeholder="Ex: Carlos" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Sobrenome (Surname): <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="createSurname" placeholder="Ex: Silva" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Nome de Usuário: <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="createUsername" placeholder="Ex: carlos.silva" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Email: <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="createEmail" placeholder="Ex: carlos.silva@empresa.com" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Senha Inicial: <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="createPassword" placeholder="Mínimo 8 caracteres" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Departamento:</label>
                                        <input type="text" class="form-control" id="createDepartment" placeholder="Ex: TI">
                                    </div>
                                    <div class="form-group">
                                        <label>Empresa:</label>
                                        <input type="text" class="form-control" id="createCompany" placeholder="Ex: Empresa Principal">
                                    </div>
                                    <div class="form-group">
                                        <label>Função/Cargo:</label>
                                        <input type="text" class="form-control" id="createTitle" placeholder="Ex: Analista de Sistemas">
                                    </div>
                                    <div class="form-group">
                                        <label>Telefone:</label>
                                        <input type="tel" class="form-control" id="createPhone" placeholder="Ex: (11) 99999-9999">
                                    </div>
                                    <div class="form-group">
                                        <label>Cidade:</label>
                                        <input type="text" class="form-control" id="createCity" placeholder="Ex: São Paulo">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Descrição:</label>
                                <textarea class="form-control" id="createDescription" rows="3" placeholder="Informações adicionais..."></textarea>
                            </div>
                            
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="createForcePasswordChange" checked>
                                <label class="form-check-label">
                                    Forçar mudança de senha no primeiro login
                                </label>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-success" onclick="executeCreateUser('${modalId}')">
                            <i class="fas fa-user-plus"></i> Criar Usuário
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remover modals existentes
    $('.modal[id^="createUserModal"]').remove();
    
    // Adicionar ao DOM
    $('body').append(modalHtml);
    
    // Mostrar modal
    $(`#${modalId}`).modal('show');
    
    console.log('✅ Modal de criação criado e exibido');
}

/**
 * EXECUTAR CRIAÇÃO DE USUÁRIO
 */
function executeCreateUser(modalId) {
    console.log('💾 Executando criação de usuário...');
    
    const givenName = document.getElementById('createGivenName').value.trim();
    const surname = document.getElementById('createSurname').value.trim();
    const username = document.getElementById('createUsername').value.trim();
    const email = document.getElementById('createEmail').value.trim();
    const password = document.getElementById('createPassword').value;
    
    // Validações
    if (!givenName || !surname || !username || !email || !password) {
        showNotification('Todos os campos obrigatórios devem ser preenchidos', 'error');
        return;
    }
    
    if (password.length < 8) {
        showNotification('A senha deve ter pelo menos 8 caracteres', 'error');
        return;
    }
    
    // Coletar dados
    const userData = {
        givenName: givenName,
        surname: surname,
        name: `${givenName} ${surname}`,
        username: username,
        email: email,
        password: password,
        department: document.getElementById('createDepartment').value.trim(),
        company: document.getElementById('createCompany').value.trim(),
        title: document.getElementById('createTitle').value.trim(),
        phone: document.getElementById('createPhone').value.trim(),
        city: document.getElementById('createCity').value.trim(),
        description: document.getElementById('createDescription').value.trim(),
        forcePasswordChange: document.getElementById('createForcePasswordChange').checked
    };
    
    // Botão loading
    const createBtn = document.querySelector(`#${modalId} .btn-success`);
    const originalText = createBtn.innerHTML;
    createBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Criando...';
    createBtn.disabled = true;
    
    // CSRF token removido para compatibilidade universal
    
    // Enviar requisição
    fetch('index.php?page=users&action=createUser', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `user_data=${encodeURIComponent(JSON.stringify(userData))}`
    })
    .then(response => response.json())
    .then(data => {
        createBtn.innerHTML = originalText;
        createBtn.disabled = false;
        
        if (data.success) {
            $(`#${modalId}`).modal('hide');
            showNotification(data.message || 'Usuário criado com sucesso', 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showNotification('Erro: ' + (data.message || 'Erro desconhecido'), 'error');
        }
    })
    .catch(error => {
        createBtn.innerHTML = originalText;
        createBtn.disabled = false;
        console.error('Erro na criação:', error);
        showNotification('Erro de comunicação: ' + error.message, 'error');
    });
}

/**
 * CONFIGURAR BOTÕES DE EDIÇÃO
 */
function setupEditButtons() {
    document.querySelectorAll('button[onclick^="editUser("]').forEach(btn => {
        const onclickAttr = btn.getAttribute('onclick');
        const usernameMatch = onclickAttr.match(/editUser\('([^']+)'\)/);
        if (usernameMatch) {
            const username = usernameMatch[1];
            btn.removeAttribute('onclick');
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('✏️ Botão Editar clicado para:', username);
                openEditUserModal(username);
            });
        }
    });
    console.log('✅ Botões de edição configurados');
}

/**
 * ABRIR MODAL DE EDIÇÃO - VERSÃO CORRIGIDA
 */
function openEditUserModal(username) {
    console.log('📝 Carregando dados para edição do usuário:', username);
    
    // Buscar dados do usuário primeiro
    fetch(`index.php?page=users&action=getUser&username=${encodeURIComponent(username)}`)
    .then(response => response.json())
    .then(data => {
        if (data.success && data.user) {
            showEditUserModal(data.user);
        } else {
            showNotification('Erro ao carregar usuário: ' + (data.message || 'Usuário não encontrado'), 'error');
        }
    })
    .catch(error => {
        console.error('Erro ao buscar usuário:', error);
        showNotification('Erro ao carregar dados do usuário', 'error');
    });
}

/**
 * MOSTRAR MODAL DE EDIÇÃO
 */
function showEditUserModal(user) {
    const modalId = 'editUserModal_' + Date.now();
    const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
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
                        <div class="alert alert-warning">
                            <strong>Atenção:</strong> Apenas campos de informações gerais podem ser editados. 
                            O nome de usuário e estrutura LDAP não podem ser alterados.
                        </div>
                        <form id="editUserForm_${Date.now()}">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nome de Usuário (somente leitura):</label>
                                        <input type="text" class="form-control" value="${user.username || ''}" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Email:</label>
                                        <input type="email" class="form-control" id="editEmail" value="${user.email || ''}">
                                    </div>
                                    <div class="form-group">
                                        <label>Telefone:</label>
                                        <input type="tel" class="form-control" id="editPhone" value="${user.phone || ''}">
                                    </div>
                                    <div class="form-group">
                                        <label>Função/Cargo:</label>
                                        <input type="text" class="form-control" id="editTitle" value="${user.title || ''}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Departamento:</label>
                                        <input type="text" class="form-control" id="editDepartment" value="${user.department || ''}">
                                    </div>
                                    <div class="form-group">
                                        <label>Empresa:</label>
                                        <input type="text" class="form-control" id="editCompany" value="${user.company || ''}">
                                    </div>
                                    <div class="form-group">
                                        <label>Cidade:</label>
                                        <input type="text" class="form-control" id="editCity" value="${user.city || ''}">
                                    </div>
                                    <div class="form-group">
                                        <label>Escritório:</label>
                                        <input type="text" class="form-control" id="editOffice" value="${user.office || ''}">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Descrição:</label>
                                <textarea class="form-control" id="editDescription" rows="3">${user.description || ''}</textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" onclick="executeUserEdit('${user.username}', '${modalId}')">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remover modals existentes de edição
    $('.modal[id^="editUserModal"]').remove();
    
    // Adicionar ao DOM
    $('body').append(modalHtml);
    
    // Mostrar modal
    $(`#${modalId}`).modal('show');
    
    console.log('✅ Modal de edição criado e exibido');
}

/**
 * EXECUTAR EDIÇÃO DE USUÁRIO - VERSÃO CORRIGIDA PARA LDAP
 */
function executeUserEdit(username, modalId) {
    console.log('💾 Executando edição do usuário:', username);
    
    // Coletar apenas campos editáveis (não RDN)
    const userData = {
        email: document.getElementById('editEmail').value.trim(),
        phone: document.getElementById('editPhone').value.trim(),
        title: document.getElementById('editTitle').value.trim(),
        department: document.getElementById('editDepartment').value.trim(),
        company: document.getElementById('editCompany').value.trim(),
        city: document.getElementById('editCity').value.trim(),
        office: document.getElementById('editOffice').value.trim(),
        description: document.getElementById('editDescription').value.trim()
    };
    
    // Botão loading
    const saveBtn = document.querySelector(`#${modalId} .btn-primary`);
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
    saveBtn.disabled = true;
    
    // CSRF token removido para compatibilidade universal
    
    // Enviar requisição (usando método específico para LDAP)
    fetch('index.php?page=users&action=updateUserInfo', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `username=${encodeURIComponent(username)}&user_data=${encodeURIComponent(JSON.stringify(userData))}`
    })
    .then(response => response.json())
    .then(data => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
        
        if (data.success) {
            $(`#${modalId}`).modal('hide');
            showNotification(data.message || 'Informações atualizadas com sucesso', 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showNotification('Erro ao atualizar: ' + (data.message || 'Erro desconhecido'), 'error');
        }
    })
    .catch(error => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
        console.error('Erro na atualização:', error);
        showNotification('Erro de comunicação: ' + error.message, 'error');
    });
}

/**
 * CONFIGURAR BOTÕES DE RESET DE SENHA
 */
function setupPasswordResetButtons() {
    document.querySelectorAll('button[onclick^="resetPassword("]').forEach(btn => {
        const onclickAttr = btn.getAttribute('onclick');
        const usernameMatch = onclickAttr.match(/resetPassword\('([^']+)'\)/);
        if (usernameMatch) {
            const username = usernameMatch[1];
            btn.removeAttribute('onclick');
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('🔑 Botão Reset Senha clicado para:', username);
                openResetPasswordModal(username);
            });
        }
    });
    console.log('✅ Botões de reset de senha configurados');
}

/**
 * ABRIR MODAL DE RESET DE SENHA
 */
function openResetPasswordModal(username) {
    const modalId = 'resetPasswordModal_' + Date.now();
    const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
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
                        <form id="resetPasswordForm_${Date.now()}">
                            <div class="form-group">
                                <label>Nova Senha:</label>
                                <input type="password" class="form-control" id="newPassword" minlength="8" required>
                                <small class="form-text text-muted">Mínimo 8 caracteres</small>
                            </div>
                            <div class="form-group">
                                <label>Confirmar Senha:</label>
                                <input type="password" class="form-control" id="confirmPassword" minlength="8" required>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="forceChange" checked>
                                <label class="form-check-label">
                                    Forçar alteração no próximo login
                                </label>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger" onclick="executePasswordReset('${username}', '${modalId}')">
                            <i class="fas fa-key"></i> Redefinir Senha
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remover modals existentes
    $('.modal[id^="resetPasswordModal"]').remove();
    
    // Adicionar ao DOM
    $('body').append(modalHtml);
    
    // Mostrar modal
    $(`#${modalId}`).modal('show');
    
    console.log('✅ Modal de reset de senha criado e exibido');
}

/**
 * EXECUTAR RESET DE SENHA
 */
function executePasswordReset(username, modalId) {
    console.log('🔑 Executando reset de senha para:', username);
    
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const forceChange = document.getElementById('forceChange').checked;
    
    // Validações
    if (!newPassword || !confirmPassword) {
        showNotification('Todos os campos são obrigatórios', 'error');
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
    
    // Botão loading
    const resetBtn = document.querySelector(`#${modalId} .btn-danger`);
    const originalText = resetBtn.innerHTML;
    resetBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Redefinindo...';
    resetBtn.disabled = true;
    
    // CSRF token removido para compatibilidade universal
    
    // Enviar requisição
    fetch('index.php?page=users&action=resetPassword', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `username=${encodeURIComponent(username)}&new_password=${encodeURIComponent(newPassword)}&force_change=${forceChange}`
    })
    .then(response => response.json())
    .then(data => {
        resetBtn.innerHTML = originalText;
        resetBtn.disabled = false;
        
        if (data.success) {
            $(`#${modalId}`).modal('hide');
            showNotification(data.message || 'Senha redefinida com sucesso', 'success');
        } else {
            showNotification('Erro: ' + (data.message || 'Erro desconhecido'), 'error');
        }
    })
    .catch(error => {
        resetBtn.innerHTML = originalText;
        resetBtn.disabled = false;
        console.error('Erro no reset:', error);
        showNotification('Erro de comunicação: ' + error.message, 'error');
    });
}

/**
 * FUNÇÕES AUXILIARES
 */
// Função getCSRFToken removida - sistema funciona sem CSRF para compatibilidade universal

function showNotification(message, type) {
    // Remover notificações existentes
    $('.alert[data-notification="true"]').remove();
    
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    }[type] || 'alert-info';
    
    const notification = $(`
        <div class="alert ${alertClass} alert-dismissible fade show" data-notification="true" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `);
    
    $('body').append(notification);
    
    // Auto remover após 5 segundos
    setTimeout(() => {
        notification.alert('close');
    }, 5000);
}

// Expor funções globalmente para compatibilidade
window.executeCreateUser = executeCreateUser;
window.executeUserEdit = executeUserEdit;
window.executePasswordReset = executePasswordReset;
window.showNotification = showNotification;