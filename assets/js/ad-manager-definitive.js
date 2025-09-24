/**
 * AD MANAGER - SOLUÇÃO DEFINITIVA E ÚNICA
 * Sistema completo e robusto - substitui todos os outros arquivos JS
 * Compatível universalmente - funciona em localhost, IP, porta, XAMPP
 */

// === AGUARDAR CARREGAMENTO COMPLETO ===
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 AD MANAGER - Inicialização da Solução Definitiva');
    
    // Aguardar dependências essenciais
    waitForDependencies();
});

// === AGUARDAR DEPENDÊNCIAS ===
function waitForDependencies() {
    let attempts = 0;
    const maxAttempts = 50;
    
    function checkDependencies() {
        attempts++;
        
        if (typeof $ !== 'undefined' && typeof $.fn.modal !== 'undefined') {
            console.log('✅ Dependências carregadas - inicializando sistema');
            initializeFullSystem();
        } else if (attempts < maxAttempts) {
            console.log('⏳ Aguardando dependências... tentativa', attempts);
            setTimeout(checkDependencies, 100);
        } else {
            console.error('❌ Dependências não carregadas após 5 segundos');
            showFallbackNotification('ERRO: Dependências não carregadas. Recarregue a página.', 'error');
        }
    }
    
    checkDependencies();
}

// === INICIALIZAÇÃO COMPLETA DO SISTEMA ===
function initializeFullSystem() {
    console.log('🔧 Configurando sistema completo...');
    
    // Configurar todos os botões existentes
    setupAllButtons();
    
    // Configurar listeners globais
    setupGlobalListeners();
    
    // Configurar filtros avançados
    setupAdvancedFilters();
    
    // Configurar busca em tempo real
    setupRealTimeSearch();
    
    console.log('✅ Sistema AD Manager inicializado com sucesso!');
}

// === CONFIGURAÇÃO DE TODOS OS BOTÕES ===
function setupAllButtons() {
    // 1. Botão Novo Usuário
    setupCreateUserButton();
    
    // 2. Botões de Edição
    setupEditButtons();
    
    // 3. Botões de Reset de Senha
    setupPasswordResetButtons();
    
    // 4. Botões de Status (Ativar/Bloquear)
    setupStatusToggleButtons();
    
    // 5. Botões de Grupos
    setupGroupButtons();
    
    // 6. Botões de Exclusão
    setupDeleteButtons();
}

// === CONFIGURAR BOTÃO NOVO USUÁRIO ===
function setupCreateUserButton() {
    // Buscar por múltiplos seletores possíveis
    const selectors = [
        'button[onclick="openCreateUserModal()"]',
        'button[onclick="showCreateUser()"]',
        '#btn-create-user',
        '.btn-create-user'
    ];
    
    let newUserBtn = null;
    for (const selector of selectors) {
        newUserBtn = document.querySelector(selector);
        if (newUserBtn) break;
    }
    
    if (newUserBtn) {
        // Remover qualquer onclick existente
        newUserBtn.removeAttribute('onclick');
        
        // Remover listeners anteriores se existirem
        newUserBtn.replaceWith(newUserBtn.cloneNode(true));
        newUserBtn = document.querySelector('#btn-create-user') || document.querySelector('button:contains("Novo Usuário")');
        
        if (newUserBtn) {
            newUserBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('🆕 Criar novo usuário - botão clicado');
                openCreateUserModal();
            });
            console.log('✅ Botão Novo Usuário configurado');
        }
    } else {
        console.warn('⚠️ Botão Novo Usuário não encontrado');
    }
}

// === CONFIGURAR BOTÕES DE EDIÇÃO ===
function setupEditButtons() {
    // Buscar todos os botões de edição possíveis
    const editButtons = document.querySelectorAll('button[onclick*="openEditUserModal"], button[onclick*="editUser"], .btn-edit');
    
    editButtons.forEach(btn => {
        const onclickAttr = btn.getAttribute('onclick');
        let username = null;
        
        // Extrair username de diferentes formatos
        if (onclickAttr) {
            const matches = onclickAttr.match(/['"]([^'"]+)['"]/g);
            if (matches && matches.length > 0) {
                username = matches[0].replace(/['"`]/g, '');
            }
        }
        
        if (username) {
            // Remover onclick antigo
            btn.removeAttribute('onclick');
            
            // Adicionar novo listener
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('✏️ Editar usuário:', username);
                openEditUserModal(username);
            });
        }
    });
    
    console.log(`✅ ${editButtons.length} botões de edição configurados`);
}

// === CONFIGURAR BOTÕES DE RESET DE SENHA ===
function setupPasswordResetButtons() {
    const resetButtons = document.querySelectorAll('button[onclick*="openResetPasswordModal"], button[onclick*="resetPassword"], .btn-reset');
    
    resetButtons.forEach(btn => {
        const onclickAttr = btn.getAttribute('onclick');
        let username = null;
        
        if (onclickAttr) {
            const matches = onclickAttr.match(/['"]([^'"]+)['"]/g);
            if (matches && matches.length > 0) {
                username = matches[0].replace(/['"`]/g, '');
            }
        }
        
        if (username) {
            btn.removeAttribute('onclick');
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('🔑 Reset senha para:', username);
                openResetPasswordModal(username);
            });
        }
    });
    
    console.log(`✅ ${resetButtons.length} botões de reset de senha configurados`);
}

// === CONFIGURAR BOTÕES DE STATUS ===
function setupStatusToggleButtons() {
    const statusButtons = document.querySelectorAll('button[onclick*="toggleStatus"], .btn-block, .btn-activate');
    
    statusButtons.forEach(btn => {
        const onclickAttr = btn.getAttribute('onclick');
        if (onclickAttr) {
            // Manter funcionalidade original para toggle status
            console.log('✅ Botão de status mantido:', onclickAttr);
        }
    });
}

// === CONFIGURAR BOTÕES DE GRUPOS ===
function setupGroupButtons() {
    const groupButtons = document.querySelectorAll('button[onclick*="viewGroups"], .btn-groups');
    
    groupButtons.forEach(btn => {
        const onclickAttr = btn.getAttribute('onclick');
        if (onclickAttr) {
            // Manter funcionalidade original
            console.log('✅ Botão de grupos mantido:', onclickAttr);
        }
    });
}

// === CONFIGURAR BOTÕES DE EXCLUSÃO ===
function setupDeleteButtons() {
    const deleteButtons = document.querySelectorAll('button[onclick*="deleteUser"], .btn-delete');
    
    deleteButtons.forEach(btn => {
        const onclickAttr = btn.getAttribute('onclick');
        if (onclickAttr) {
            // Manter funcionalidade original
            console.log('✅ Botão de exclusão mantido:', onclickAttr);
        }
    });
}

// === MODAL CRIAR USUÁRIO - VERSÃO DEFINITIVA ===
function openCreateUserModal() {
    console.log('📝 Abrindo modal de criação - versão definitiva');
    
    const modalId = 'createUserModalDefinitive_' + Date.now();
    const formId = 'createUserFormDefinitive_' + Date.now();
    
    const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h4 class="modal-title">
                            <i class="fas fa-user-plus"></i> Criar Novo Usuário no Active Directory
                        </h4>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Campos Obrigatórios:</strong> Nome, Sobrenome, Nome de Usuário, Email e Senha são obrigatórios para criar o usuário no AD.
                        </div>
                        
                        <form id="${formId}">
                            <div class="row">
                                <!-- Coluna 1: Dados Pessoais -->
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6><i class="fas fa-user"></i> Dados Pessoais</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label>Nome (GivenName): <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="createGivenName" placeholder="Ex: Carlos" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Sobrenome (Surname): <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="createSurname" placeholder="Ex: Silva" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Nome Completo: <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="createDisplayName" placeholder="Será preenchido automaticamente" readonly>
                                            </div>
                                            <div class="form-group">
                                                <label>Email: <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control" id="createEmail" placeholder="Ex: carlos.silva@empresa.com" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Coluna 2: Dados de Sistema -->
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6><i class="fas fa-cog"></i> Dados do Sistema</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label>Nome de Usuário: <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="createUsername" placeholder="Ex: carlos.silva" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Senha Inicial: <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" id="createPassword" placeholder="Mínimo 8 caracteres" required>
                                                    <div class="input-group-append">
                                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('createPassword')">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-outline-info" type="button" onclick="generatePassword()">
                                                            <i class="fas fa-random"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <small class="form-text text-muted">Deve conter letras, números e caracteres especiais</small>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="createForcePasswordChange" checked>
                                                <label class="form-check-label">Forçar mudança de senha no primeiro login</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="createAccountEnabled" checked>
                                                <label class="form-check-label">Conta ativa (habilitada)</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Coluna 3: Dados Profissionais -->
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6><i class="fas fa-briefcase"></i> Dados Profissionais</h6>
                                        </div>
                                        <div class="card-body">
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
                                </div>
                            </div>
                            
                            <div class="form-group mt-3">
                                <label>Observações/Descrição:</label>
                                <textarea class="form-control" id="createDescription" rows="3" placeholder="Informações adicionais sobre o usuário..."></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-success" onclick="executeCreateUser('${modalId}', '${formId}')">
                            <i class="fas fa-user-plus"></i> Criar Usuário
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remover modais existentes
    $('[id*="createUserModal"]').remove();
    
    // Adicionar ao DOM
    $('body').append(modalHtml);
    
    // Configurar auto-preenchimento
    setupAutoFill();
    
    // Mostrar modal
    $(`#${modalId}`).modal('show');
    
    console.log('✅ Modal de criação exibido com sucesso');
}

// === AUTO-PREENCHIMENTO INTELIGENTE ===
function setupAutoFill() {
    // Auto-preencher nome completo
    $('#createGivenName, #createSurname').on('input', function() {
        const givenName = $('#createGivenName').val().trim();
        const surname = $('#createSurname').val().trim();
        
        if (givenName && surname) {
            $('#createDisplayName').val(`${givenName} ${surname}`);
        }
    });
    
    // Auto-sugerir nome de usuário
    $('#createGivenName, #createSurname').on('input', function() {
        const givenName = $('#createGivenName').val().trim().toLowerCase();
        const surname = $('#createSurname').val().trim().toLowerCase();
        
        if (givenName && surname) {
            const username = `${givenName}.${surname}`.replace(/[^a-z.]/g, '');
            if ($('#createUsername').val() === '') {
                $('#createUsername').val(username);
            }
        }
    });
    
    // Auto-sugerir email
    $('#createGivenName, #createSurname').on('input', function() {
        const givenName = $('#createGivenName').val().trim().toLowerCase();
        const surname = $('#createSurname').val().trim().toLowerCase();
        
        if (givenName && surname && $('#createEmail').val() === '') {
            const emailSuggestion = `${givenName}.${surname}@empresa.com`.replace(/[^a-z.@]/g, '');
            $('#createEmail').attr('placeholder', emailSuggestion);
        }
    });
}

// === EXECUTAR CRIAÇÃO DE USUÁRIO ===
function executeCreateUser(modalId, formId) {
    console.log('💾 Executando criação de usuário - versão definitiva');
    
    // Coletar dados com validação
    const userData = collectAndValidateUserData();
    
    if (!userData) {
        return; // Validação falhou
    }
    
    // Mostrar loading
    const createBtn = document.querySelector(`#${modalId} .btn-success`);
    const originalText = createBtn.innerHTML;
    createBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Criando usuário...';
    createBtn.disabled = true;
    
    // Preparar requisição - SEM CSRF para compatibilidade universal
    const requestData = `user_data=${encodeURIComponent(JSON.stringify(userData))}`;
    
    console.log('📤 Enviando dados:', userData);
    
    // Enviar requisição
    fetch('index.php?page=users&action=createUser', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: requestData
    })
    .then(response => {
        console.log('📥 Resposta recebida:', response.status);
        return response.text();
    })
    .then(text => {
        console.log('📄 Texto da resposta:', text);
        
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            throw new Error('Resposta inválida do servidor: ' + text.substring(0, 200));
        }
        
        createBtn.innerHTML = originalText;
        createBtn.disabled = false;
        
        if (data.success) {
            $(`#${modalId}`).modal('hide');
            showNotification(data.message || 'Usuário criado com sucesso!', 'success');
            setTimeout(() => window.location.reload(), 2000);
        } else {
            showNotification('Erro ao criar usuário: ' + (data.message || 'Erro desconhecido'), 'error');
        }
    })
    .catch(error => {
        createBtn.innerHTML = originalText;
        createBtn.disabled = false;
        console.error('❌ Erro na criação:', error);
        showNotification('Erro de comunicação: ' + error.message, 'error');
    });
}

// === COLETAR E VALIDAR DADOS DO USUÁRIO ===
function collectAndValidateUserData() {
    const givenName = $('#createGivenName').val().trim();
    const surname = $('#createSurname').val().trim();
    const username = $('#createUsername').val().trim();
    const email = $('#createEmail').val().trim();
    const password = $('#createPassword').val();
    
    // Validações obrigatórias
    const errors = [];
    
    if (!givenName) errors.push('Nome é obrigatório');
    if (!surname) errors.push('Sobrenome é obrigatório');
    if (!username) errors.push('Nome de usuário é obrigatório');
    if (!email) errors.push('Email é obrigatório');
    if (!password) errors.push('Senha é obrigatória');
    
    // Validação de email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (email && !emailRegex.test(email)) {
        errors.push('Email deve ter um formato válido');
    }
    
    // Validação de senha
    if (password && password.length < 8) {
        errors.push('Senha deve ter pelo menos 8 caracteres');
    }
    
    // Validação de username (sem espaços, caracteres especiais)
    const usernameRegex = /^[a-zA-Z0-9._-]+$/;
    if (username && !usernameRegex.test(username)) {
        errors.push('Nome de usuário pode conter apenas letras, números, pontos, hífens e sublinhados');
    }
    
    if (errors.length > 0) {
        showNotification('Corrija os erros:\n• ' + errors.join('\n• '), 'error');
        return null;
    }
    
    // Retornar dados coletados
    return {
        givenName: givenName,
        surname: surname,
        name: `${givenName} ${surname}`,
        displayName: $('#createDisplayName').val().trim() || `${givenName} ${surname}`,
        username: username,
        email: email,
        password: password,
        department: $('#createDepartment').val().trim(),
        company: $('#createCompany').val().trim(),
        title: $('#createTitle').val().trim(),
        phone: $('#createPhone').val().trim(),
        city: $('#createCity').val().trim(),
        description: $('#createDescription').val().trim(),
        forcePasswordChange: $('#createForcePasswordChange').prop('checked'),
        accountEnabled: $('#createAccountEnabled').prop('checked')
    };
}

// === MODAL EDITAR USUÁRIO ===
function openEditUserModal(username) {
    console.log('📝 Carregando dados para edição:', username);
    
    // Mostrar loading
    showNotification('Carregando dados do usuário...', 'info');
    
    // Buscar dados do usuário
    fetch(`index.php?page=users&action=getUser&username=${encodeURIComponent(username)}`)
    .then(response => response.text())
    .then(text => {
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            throw new Error('Resposta inválida: ' + text.substring(0, 200));
        }
        
        if (data.success && data.user) {
            showEditUserModal(data.user);
        } else {
            showNotification('Erro ao carregar usuário: ' + (data.message || 'Usuário não encontrado'), 'error');
        }
    })
    .catch(error => {
        console.error('❌ Erro ao buscar usuário:', error);
        showNotification('Erro ao carregar dados: ' + error.message, 'error');
    });
}

// === MOSTRAR MODAL DE EDIÇÃO ===
function showEditUserModal(user) {
    console.log('📋 Exibindo modal de edição para:', user.username);
    
    const modalId = 'editUserModalDefinitive_' + Date.now();
    const formId = 'editUserFormDefinitive_' + Date.now();
    
    const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog" data-backdrop="static">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h4 class="modal-title">
                            <i class="fas fa-edit"></i> Editar Usuário - ${escapeHtml(user.username)}
                        </h4>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Atenção:</strong> Apenas informações complementares podem ser editadas. 
                            Nome de usuário e senha devem ser alterados por outras funções.
                        </div>
                        
                        <form id="${formId}">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nome de Usuário (somente leitura):</label>
                                        <input type="text" class="form-control" value="${escapeHtml(user.username || '')}" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Nome Completo:</label>
                                        <input type="text" class="form-control" value="${escapeHtml(user.name || '')}" readonly title="Não editável via LDAP">
                                        <small class="form-text text-muted">Nome completo não pode ser alterado via LDAP</small>
                                    </div>
                                    <div class="form-group">
                                        <label>Email:</label>
                                        <input type="email" class="form-control" id="editEmail" value="${escapeHtml(user.email || '')}">
                                    </div>
                                    <div class="form-group">
                                        <label>Telefone:</label>
                                        <input type="tel" class="form-control" id="editPhone" value="${escapeHtml(user.phone || '')}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Função/Cargo:</label>
                                        <input type="text" class="form-control" id="editTitle" value="${escapeHtml(user.title || '')}">
                                    </div>
                                    <div class="form-group">
                                        <label>Departamento:</label>
                                        <input type="text" class="form-control" id="editDepartment" value="${escapeHtml(user.department || '')}">
                                    </div>
                                    <div class="form-group">
                                        <label>Empresa:</label>
                                        <input type="text" class="form-control" id="editCompany" value="${escapeHtml(user.company || '')}">
                                    </div>
                                    <div class="form-group">
                                        <label>Cidade:</label>
                                        <input type="text" class="form-control" id="editCity" value="${escapeHtml(user.city || '')}">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Escritório:</label>
                                <input type="text" class="form-control" id="editOffice" value="${escapeHtml(user.office || '')}">
                            </div>
                            
                            <div class="form-group">
                                <label>Descrição/Observações:</label>
                                <textarea class="form-control" id="editDescription" rows="3">${escapeHtml(user.description || '')}</textarea>
                            </div>
                        </form>
                        
                        <div class="mt-3">
                            <h6><i class="fas fa-info-circle"></i> Informações do Sistema:</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <small><strong>Status:</strong> ${escapeHtml(user.status || 'N/A')}</small><br>
                                    <small><strong>Último Login:</strong> ${escapeHtml(user.last_logon || 'Nunca')}</small>
                                </div>
                                <div class="col-md-6">
                                    <small><strong>Criado:</strong> ${escapeHtml(user.created || 'N/A')}</small><br>
                                    <small><strong>DN:</strong> <code style="font-size: 10px;">${escapeHtml(user.dn || 'N/A')}</code></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-primary" onclick="executeUserEdit('${escapeHtml(user.username)}', '${modalId}', '${formId}')">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remover modais de edição existentes
    $('[id*="editUserModal"]').remove();
    
    // Adicionar e mostrar
    $('body').append(modalHtml);
    $(`#${modalId}`).modal('show');
    
    console.log('✅ Modal de edição exibido');
}

// === EXECUTAR EDIÇÃO DE USUÁRIO ===
function executeUserEdit(username, modalId, formId) {
    console.log('💾 Executando edição para:', username);
    
    // Coletar apenas campos editáveis (não-RDN)
    const userData = {
        email: $('#editEmail').val().trim(),
        phone: $('#editPhone').val().trim(),
        title: $('#editTitle').val().trim(),
        department: $('#editDepartment').val().trim(),
        company: $('#editCompany').val().trim(),
        city: $('#editCity').val().trim(),
        office: $('#editOffice').val().trim(),
        description: $('#editDescription').val().trim()
    };
    
    // Validar email se fornecido
    if (userData.email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(userData.email)) {
            showNotification('Email deve ter formato válido', 'error');
            return;
        }
    }
    
    // Mostrar loading
    const saveBtn = document.querySelector(`#${modalId} .btn-primary`);
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
    saveBtn.disabled = true;
    
    console.log('📤 Enviando dados de edição:', userData);
    
    // Enviar requisição (usando método específico para LDAP)
    fetch('index.php?page=users&action=updateUserInfo', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `username=${encodeURIComponent(username)}&user_data=${encodeURIComponent(JSON.stringify(userData))}`
    })
    .then(response => response.text())
    .then(text => {
        console.log('📥 Resposta da edição:', text);
        
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            throw new Error('Resposta inválida: ' + text.substring(0, 200));
        }
        
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
        
        if (data.success) {
            $(`#${modalId}`).modal('hide');
            showNotification(data.message || 'Usuário atualizado com sucesso!', 'success');
            setTimeout(() => window.location.reload(), 2000);
        } else {
            showNotification('Erro ao salvar: ' + (data.message || 'Erro desconhecido'), 'error');
        }
    })
    .catch(error => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
        console.error('❌ Erro na edição:', error);
        showNotification('Erro de comunicação: ' + error.message, 'error');
    });
}

// === MODAL RESET DE SENHA ===
function openResetPasswordModal(username) {
    console.log('🔑 Abrindo modal de reset de senha para:', username);
    
    const modalId = 'resetPasswordModalDefinitive_' + Date.now();
    const formId = 'resetPasswordFormDefinitive_' + Date.now();
    
    const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h4 class="modal-title">
                            <i class="fas fa-key"></i> Redefinir Senha - ${escapeHtml(username)}
                        </h4>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Atenção:</strong> Esta operação irá alterar a senha do usuário no Active Directory.
                        </div>
                        
                        <form id="${formId}">
                            <div class="form-group">
                                <label>Nova Senha: <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="newPassword" minlength="8" required>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('newPassword')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-info" type="button" onclick="generatePasswordForReset()">
                                            <i class="fas fa-random"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Mínimo 8 caracteres, deve conter letras e números</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Confirmar Nova Senha: <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="confirmPassword" minlength="8" required>
                            </div>
                            
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="forceChange" checked>
                                    <label class="form-check-label">Forçar alteração no próximo login</label>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <small><i class="fas fa-info-circle"></i> 
                                A senha será alterada imediatamente no Active Directory. 
                                O usuário receberá as novas credenciais.</small>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-danger" onclick="executePasswordReset('${escapeHtml(username)}', '${modalId}')">
                            <i class="fas fa-key"></i> Redefinir Senha
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remover modais existentes
    $('[id*="resetPasswordModal"]').remove();
    
    // Adicionar e mostrar
    $('body').append(modalHtml);
    $(`#${modalId}`).modal('show');
    
    // Configurar validação em tempo real
    $(`#${modalId} #confirmPassword`).on('input', function() {
        const newPass = $(`#${modalId} #newPassword`).val();
        const confirmPass = $(this).val();
        
        if (confirmPass && newPass !== confirmPass) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    console.log('✅ Modal de reset de senha exibido');
}

// === EXECUTAR RESET DE SENHA ===
function executePasswordReset(username, modalId) {
    console.log('🔑 Executando reset de senha para:', username);
    
    const newPassword = $(`#${modalId} #newPassword`).val();
    const confirmPassword = $(`#${modalId} #confirmPassword`).val();
    const forceChange = $(`#${modalId} #forceChange`).prop('checked');
    
    // Validações
    const errors = [];
    
    if (!newPassword) errors.push('Nova senha é obrigatória');
    if (!confirmPassword) errors.push('Confirmação de senha é obrigatória');
    if (newPassword !== confirmPassword) errors.push('As senhas não coincidem');
    if (newPassword && newPassword.length < 8) errors.push('Senha deve ter pelo menos 8 caracteres');
    
    // Validação de complexidade
    if (newPassword) {
        const hasLetter = /[a-zA-Z]/.test(newPassword);
        const hasNumber = /\d/.test(newPassword);
        if (!hasLetter) errors.push('Senha deve conter pelo menos uma letra');
        if (!hasNumber) errors.push('Senha deve conter pelo menos um número');
    }
    
    if (errors.length > 0) {
        showNotification('Corrija os erros:\n• ' + errors.join('\n• '), 'error');
        return;
    }
    
    // Confirmar operação
    if (!confirm(`Confirma a alteração de senha do usuário ${username}?\n\nEsta ação será aplicada imediatamente no Active Directory.`)) {
        return;
    }
    
    // Mostrar loading
    const resetBtn = document.querySelector(`#${modalId} .btn-danger`);
    const originalText = resetBtn.innerHTML;
    resetBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Alterando senha...';
    resetBtn.disabled = true;
    
    console.log('📤 Enviando requisição de reset de senha');
    
    // Enviar requisição
    fetch('index.php?page=users&action=resetPassword', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `username=${encodeURIComponent(username)}&new_password=${encodeURIComponent(newPassword)}&force_change=${forceChange}`
    })
    .then(response => response.text())
    .then(text => {
        console.log('📥 Resposta do reset:', text);
        
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            throw new Error('Resposta inválida: ' + text.substring(0, 200));
        }
        
        resetBtn.innerHTML = originalText;
        resetBtn.disabled = false;
        
        if (data.success) {
            $(`#${modalId}`).modal('hide');
            showNotification(data.message || 'Senha alterada com sucesso!', 'success');
        } else {
            showNotification('Erro ao alterar senha: ' + (data.message || 'Erro desconhecido'), 'error');
        }
    })
    .catch(error => {
        resetBtn.innerHTML = originalText;
        resetBtn.disabled = false;
        console.error('❌ Erro no reset de senha:', error);
        showNotification('Erro de comunicação: ' + error.message, 'error');
    });
}

// === CONFIGURAR FILTROS AVANÇADOS ===
function setupAdvancedFilters() {
    // Aplicar filtros automaticamente em mudanças
    const filterElements = [
        '#filter-department', '#filter-company', '#filter-city', 
        '#filter-status', '#filter-title', '#filter-office'
    ];
    
    filterElements.forEach(selector => {
        const element = document.querySelector(selector);
        if (element) {
            element.addEventListener('change', debounce(() => {
                applyFilters();
            }, 500));
        }
    });
    
    // Configurar botões de filtros
    const applyBtn = document.querySelector('button[onclick="applyFilters()"]');
    if (applyBtn) {
        applyBtn.removeAttribute('onclick');
        applyBtn.addEventListener('click', applyFilters);
    }
    
    const clearBtn = document.querySelector('button[onclick="clearAllFilters()"]');
    if (clearBtn) {
        clearBtn.removeAttribute('onclick');
        clearBtn.addEventListener('click', clearAllFilters);
    }
}

// === CONFIGURAR BUSCA EM TEMPO REAL ===
function setupRealTimeSearch() {
    const searchInput = document.querySelector('#user-search');
    if (searchInput) {
        searchInput.addEventListener('input', debounce((e) => {
            const term = e.target.value;
            if (term.length >= 2 || term.length === 0) {
                applyFilters();
            }
        }, 800));
    }
}

// === CONFIGURAR LISTENERS GLOBAIS ===
function setupGlobalListeners() {
    // Fechar modais com ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            $('.modal.show').modal('hide');
        }
    });
    
    // Prevenir envio de formulários com Enter (exceto em textareas)
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA' && e.target.type !== 'submit') {
            e.preventDefault();
        }
    });
}

// === FUNÇÕES UTILITÁRIAS ===

// Escapar HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Debounce
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Mostrar/Ocultar senha
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.nextElementSibling.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Gerar senha segura
function generatePassword() {
    const length = 12;
    const charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789@#$%&*!';
    let password = '';
    
    // Garantir pelo menos um de cada tipo
    password += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[Math.floor(Math.random() * 26)]; // Maiúscula
    password += 'abcdefghijklmnopqrstuvwxyz'[Math.floor(Math.random() * 26)]; // Minúscula
    password += '0123456789'[Math.floor(Math.random() * 10)]; // Número
    password += '@#$%&*!'[Math.floor(Math.random() * 7)]; // Especial
    
    // Completar o restante
    for (let i = password.length; i < length; i++) {
        password += charset[Math.floor(Math.random() * charset.length)];
    }
    
    // Embaralhar
    password = password.split('').sort(() => 0.5 - Math.random()).join('');
    
    document.getElementById('createPassword').value = password;
    showNotification('Senha gerada automaticamente!', 'success');
}

// Gerar senha para reset
function generatePasswordForReset() {
    const length = 10;
    const charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let password = '';
    
    for (let i = 0; i < length; i++) {
        password += charset[Math.floor(Math.random() * charset.length)];
    }
    
    const modal = $('.modal.show');
    modal.find('#newPassword').val(password);
    modal.find('#confirmPassword').val(password);
    
    showNotification('Senha gerada! Copie antes de prosseguir.', 'info');
}

// Aplicar filtros
function applyFilters() {
    console.log('🔍 Aplicando filtros...');
    
    const filters = {
        search: document.querySelector('#user-search')?.value || '',
        department: document.querySelector('#filter-department')?.value || '',
        company: document.querySelector('#filter-company')?.value || '',
        city: document.querySelector('#filter-city')?.value || '',
        status: document.querySelector('#filter-status')?.value || '',
        title: document.querySelector('#filter-title')?.value || '',
        office: document.querySelector('#filter-office')?.value || '',
        limit: document.querySelector('#items-per-page')?.value || '50'
    };
    
    const params = new URLSearchParams();
    
    // Adicionar apenas filtros com valor
    Object.keys(filters).forEach(key => {
        if (filters[key] && filters[key] !== '' && filters[key] !== 'all') {
            params.append(key, filters[key]);
        }
    });
    
    // Mostrar loading
    const loading = document.querySelector('#filter-loading');
    if (loading) loading.style.display = 'inline-block';
    
    // Redirecionar
    const url = `index.php?page=users${params.toString() ? '&' + params.toString() : ''}`;
    window.location.href = url;
}

// Limpar filtros
function clearAllFilters() {
    console.log('🧹 Limpando todos os filtros...');
    
    document.querySelector('#user-search').value = '';
    document.querySelector('#filter-department').value = '';
    document.querySelector('#filter-company').value = '';
    document.querySelector('#filter-city').value = '';
    document.querySelector('#filter-status').value = '';
    document.querySelector('#filter-title').value = '';
    document.querySelector('#filter-office').value = '';
    
    // Aplicar filtros limpos
    applyFilters();
}

// === SISTEMA DE NOTIFICAÇÕES ROBUSTO ===
function showNotification(message, type = 'info', duration = 5000) {
    // Remover notificações existentes
    document.querySelectorAll('[data-notification="true"]').forEach(el => el.remove());
    
    const alertClasses = {
        'success': 'alert-success',
        'error': 'alert-danger', 
        'danger': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    };
    
    const icons = {
        'success': 'fas fa-check-circle',
        'error': 'fas fa-exclamation-circle',
        'danger': 'fas fa-exclamation-circle', 
        'warning': 'fas fa-exclamation-triangle',
        'info': 'fas fa-info-circle'
    };
    
    const alertClass = alertClasses[type] || 'alert-info';
    const icon = icons[type] || 'fas fa-info-circle';
    
    const notification = document.createElement('div');
    notification.className = `alert ${alertClass} alert-dismissible fade show`;
    notification.setAttribute('data-notification', 'true');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 350px;
        max-width: 500px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border: none;
        border-radius: 8px;
    `;
    
    notification.innerHTML = `
        <div style="display: flex; align-items: flex-start; gap: 10px;">
            <i class="${icon}" style="margin-top: 2px; font-size: 16px;"></i>
            <div style="flex: 1; white-space: pre-line; font-size: 14px; line-height: 1.4;">${escapeHtml(message)}</div>
            <button type="button" class="close" style="padding: 0; margin: -2px 0 0 10px; font-size: 18px; opacity: 0.7;" onclick="this.parentElement.parentElement.remove()">
                <span>&times;</span>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remover
    if (duration > 0) {
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.animation = 'fadeOut 0.3s ease-out';
                setTimeout(() => notification.remove(), 300);
            }
        }, duration);
    }
    
    console.log(`📢 Notificação [${type}]:`, message);
}

// Fallback notification (quando jQuery não está disponível)
function showFallbackNotification(message, type) {
    alert(`[${type.toUpperCase()}] ${message}`);
}

// === EXPOR FUNÇÕES GLOBALMENTE ===
// Para compatibilidade com códigos existentes
window.openCreateUserModal = openCreateUserModal;
window.executeCreateUser = executeCreateUser;
window.openEditUserModal = openEditUserModal;
window.executeUserEdit = executeUserEdit;
window.openResetPasswordModal = openResetPasswordModal;
window.executePasswordReset = executePasswordReset;
window.showNotification = showNotification;
window.applyFilters = applyFilters;
window.clearAllFilters = clearAllFilters;
window.togglePasswordVisibility = togglePasswordVisibility;
window.generatePassword = generatePassword;
window.generatePasswordForReset = generatePasswordForReset;

console.log('✅ AD MANAGER - Solução Definitiva carregada com sucesso!');