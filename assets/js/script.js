/**
 * AD Manager - Scripts principais
 */

// Configurações globais
const ADManager = {
    config: {
        sessionCheckInterval: 300000, // 5 minutos
        autoSaveInterval: 30000,     // 30 segundos
        requestTimeout: 30000,       // 30 segundos
        maxRetries: 3
    },
    
    // Cache para armazenar dados temporários
    cache: new Map(),
    
    // Intervalos ativos
    intervals: new Map(),
    
    // Estado da aplicação
    state: {
        isOnline: true,
        lastActivity: Date.now(),
        sessionExpiring: false
    }
};

/**
 * Utilitários gerais
 */
const Utils = {
    // Formatar data
    formatDate(date) {
        if (!date) return 'N/A';
        return new Date(date).toLocaleString('pt-BR');
    },
    
    // Formatar tempo relativo
    timeAgo(date) {
        if (!date) return 'Nunca';
        
        const now = new Date();
        const past = new Date(date);
        const diff = now - past;
        
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);
        
        if (minutes < 1) return 'agora mesmo';
        if (minutes < 60) return `${minutes} min atrás`;
        if (hours < 24) return `${hours}h atrás`;
        if (days < 30) return `${days}d atrás`;
        
        return past.toLocaleDateString('pt-BR');
    },
    
    // Validar email
    validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },
    
    // Escapar HTML
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },
    
    // Gerar ID único
    generateId() {
        return '_' + Math.random().toString(36).substr(2, 9);
    },
    
    // Debounce para otimizar eventos
    debounce(func, wait) {
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
};

/**
 * Gerenciador de requisições AJAX
 */
const API = {
    // Requisição GET
    async get(url, options = {}) {
        return this.request('GET', url, null, options);
    },
    
    // Requisição POST
    async post(url, data, options = {}) {
        return this.request('POST', url, data, options);
    },
    
    // Requisição genérica
    async request(method, url, data = null, options = {}) {
        const config = {
            method,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers
            },
            timeout: options.timeout || ADManager.config.requestTimeout
        };
        
        if (data) {
            if (data instanceof FormData) {
                delete config.headers['Content-Type']; // Let browser set it
                config.body = data;
            } else if (typeof data === 'object') {
                config.body = new URLSearchParams(data).toString();
            } else {
                config.body = data;
            }
        }
        
        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), config.timeout);
            
            config.signal = controller.signal;
            
            const response = await fetch(url, config);
            clearTimeout(timeoutId);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const contentType = response.headers.get('content-type');
            
            if (contentType && contentType.includes('application/json')) {
                return await response.json();
            }
            
            return await response.text();
            
        } catch (error) {
            if (error.name === 'AbortError') {
                throw new Error('Requisição cancelada por timeout');
            }
            throw error;
        }
    }
};

/**
 * Gerenciador de notificações
 */
const Notifications = {
    container: null,
    
    init() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.className = 'notifications-container';
            this.container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                max-width: 400px;
            `;
            document.body.appendChild(this.container);
        }
    },
    
    show(message, type = 'info', duration = 5000) {
        this.init();
        
        const id = Utils.generateId();
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible`;
        notification.id = id;
        notification.style.cssText = `
            margin-bottom: 10px;
            animation: slideInRight 0.3s ease-out;
        `;
        
        const icon = this.getIcon(type);
        
        notification.innerHTML = `
            <i class="${icon}"></i>
            ${Utils.escapeHtml(message)}
            <button type="button" class="close" onclick="Notifications.hide('${id}')">
                <span>&times;</span>
            </button>
        `;
        
        this.container.appendChild(notification);
        
        if (duration > 0) {
            setTimeout(() => this.hide(id), duration);
        }
        
        return id;
    },
    
    hide(id) {
        const notification = document.getElementById(id);
        if (notification) {
            notification.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        }
    },
    
    getIcon(type) {
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };
        return icons[type] || icons.info;
    },
    
    success(message, duration) { return this.show(message, 'success', duration); },
    error(message, duration) { return this.show(message, 'danger', duration); },
    warning(message, duration) { return this.show(message, 'warning', duration); },
    info(message, duration) { return this.show(message, 'info', duration); }
};

/**
 * Gerenciador de modais
 */
const Modal = {
    current: null,
    
    show(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('show');
            this.current = modal;
            
            // Focar no primeiro input
            const firstInput = modal.querySelector('input, select, textarea');
            if (firstInput) {
                setTimeout(() => firstInput.focus(), 100);
            }
        }
    },
    
    hide(modalId) {
        const modal = modalId ? document.getElementById(modalId) : this.current;
        if (modal) {
            modal.classList.remove('show');
            this.current = null;
        }
    },
    
    confirm(title, message, callback) {
        const modalId = 'confirm-modal';
        let modal = document.getElementById(modalId);
        
        if (!modal) {
            modal = document.createElement('div');
            modal.id = modalId;
            modal.className = 'modal';
            modal.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-header">
                        <h5 class="modal-title">${Utils.escapeHtml(title)}</h5>
                    </div>
                    <div class="modal-body">
                        <p>${Utils.escapeHtml(message)}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="Modal.hide('${modalId}')">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="confirm-btn">Confirmar</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        } else {
            modal.querySelector('.modal-title').textContent = title;
            modal.querySelector('.modal-body p').textContent = message;
        }
        
        const confirmBtn = modal.querySelector('#confirm-btn');
        confirmBtn.onclick = () => {
            this.hide(modalId);
            if (callback) callback();
        };
        
        this.show(modalId);
    }
};

/**
 * Gerenciador de sessão
 */
const Session = {
    init() {
        this.startSessionCheck();
        this.trackActivity();
    },
    
    startSessionCheck() {
        const interval = setInterval(async () => {
            try {
                const response = await API.get('index.php?page=auth&action=checkSession');
                
                if (!response.logged_in || response.expired) {
                    this.handleExpiredSession();
                    return;
                }
                
                // Avisar se sessão está expirando (5 minutos restantes)
                if (response.remaining_time < 300 && !ADManager.state.sessionExpiring) {
                    this.showExpirationWarning(response.remaining_time);
                }
                
            } catch (error) {
                console.error('Erro ao verificar sessão:', error);
            }
        }, ADManager.config.sessionCheckInterval);
        
        ADManager.intervals.set('sessionCheck', interval);
    },
    
    trackActivity() {
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'];
        
        const updateActivity = Utils.debounce(() => {
            ADManager.state.lastActivity = Date.now();
        }, 1000);
        
        events.forEach(event => {
            document.addEventListener(event, updateActivity, true);
        });
    },
    
    showExpirationWarning(remainingTime) {
        ADManager.state.sessionExpiring = true;
        
        const minutes = Math.floor(remainingTime / 60);
        
        Modal.confirm(
            'Sessão Expirando',
            `Sua sessão expirará em ${minutes} minuto(s). Deseja renovar?`,
            () => this.renewSession()
        );
    },
    
    async renewSession() {
        try {
            const response = await API.post('index.php?page=auth&action=renewSession');
            
            if (response.success) {
                ADManager.state.sessionExpiring = false;
                Notifications.success('Sessão renovada com sucesso');
            } else {
                throw new Error(response.message);
            }
        } catch (error) {
            Notifications.error('Erro ao renovar sessão: ' + error.message);
            this.handleExpiredSession();
        }
    },
    
    handleExpiredSession() {
        // Limpar intervalos
        ADManager.intervals.forEach(interval => clearInterval(interval));
        ADManager.intervals.clear();
        
        Notifications.warning('Sua sessão expirou. Redirecionando para login...', 3000);
        
        setTimeout(() => {
            window.location.href = 'index.php?page=login&error=session_expired';
        }, 3000);
    }
};

/**
 * Gerenciador de formulários
 */
const Forms = {
    // Serializar formulário
    serialize(form) {
        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            if (data[key]) {
                if (Array.isArray(data[key])) {
                    data[key].push(value);
                } else {
                    data[key] = [data[key], value];
                }
            } else {
                data[key] = value;
            }
        }
        
        return data;
    },
    
    // Validar formulário
    validate(form) {
        const errors = [];
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                errors.push(`O campo "${input.labels[0]?.textContent || input.name}" é obrigatório`);
                this.showFieldError(input, 'Campo obrigatório');
            } else {
                this.clearFieldError(input);
            }
            
            // Validações específicas
            if (input.type === 'email' && input.value && !Utils.validateEmail(input.value)) {
                errors.push('Email inválido');
                this.showFieldError(input, 'Email inválido');
            }
        });
        
        return errors;
    },
    
    // Mostrar erro no campo
    showFieldError(input, message) {
        this.clearFieldError(input);
        
        input.classList.add('is-invalid');
        
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        feedback.textContent = message;
        feedback.style.cssText = 'display: block; color: var(--error-red); font-size: 12px; margin-top: 5px;';
        
        input.parentNode.appendChild(feedback);
    },
    
    // Limpar erro do campo
    clearFieldError(input) {
        input.classList.remove('is-invalid');
        
        const feedback = input.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.remove();
        }
    },
    
    // Submeter formulário via AJAX
    async submit(form, options = {}) {
        const errors = this.validate(form);
        
        if (errors.length > 0) {
            Notifications.error('Corrija os erros no formulário:\n' + errors.join('\n'));
            return false;
        }
        
        const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
        const originalText = submitBtn ? submitBtn.innerHTML : '';
        
        try {
            // Mostrar loading
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner"></span> Processando...';
            }
            
            const formData = new FormData(form);
            const response = await API.post(form.action || window.location.href, formData);
            
            if (options.onSuccess) {
                options.onSuccess(response);
            } else if (response.success) {
                Notifications.success(response.message || 'Operação realizada com sucesso');
            } else {
                throw new Error(response.message || 'Erro desconhecido');
            }
            
            return response;
            
        } catch (error) {
            if (options.onError) {
                options.onError(error);
            } else {
                Notifications.error('Erro: ' + error.message);
            }
            return false;
            
        } finally {
            // Restaurar botão
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }
    }
};

/**
 * Gerenciador de tabelas
 */
const Tables = {
    // Aplicar filtros de busca em tempo real
    enableSearch(tableId, searchInputId) {
        const table = document.getElementById(tableId);
        const searchInput = document.getElementById(searchInputId);
        
        if (!table || !searchInput) return;
        
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        const search = Utils.debounce((term) => {
            const searchTerm = term.toLowerCase();
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
            
            this.updateNoResultsMessage(tbody, rows, searchTerm);
        }, 300);
        
        searchInput.addEventListener('input', (e) => {
            search(e.target.value);
        });
    },
    
    updateNoResultsMessage(tbody, rows, searchTerm) {
        const visibleRows = rows.filter(row => row.style.display !== 'none');
        
        // Remover mensagem existente
        const existingMsg = tbody.querySelector('.no-results-message');
        if (existingMsg) existingMsg.remove();
        
        // Adicionar nova mensagem se necessário
        if (visibleRows.length === 0 && searchTerm) {
            const colCount = tbody.querySelector('tr')?.children.length || 1;
            const messageRow = document.createElement('tr');
            messageRow.className = 'no-results-message';
            messageRow.innerHTML = `
                <td colspan="${colCount}" class="text-center text-muted" style="padding: 40px;">
                    <i class="fas fa-search" style="font-size: 24px; margin-bottom: 10px; opacity: 0.5;"></i><br>
                    Nenhum resultado encontrado para "${Utils.escapeHtml(searchTerm)}"
                </td>
            `;
            tbody.appendChild(messageRow);
        }
    },
    
    // Seleção múltipla
    enableMultiSelect(tableId, checkboxClass = 'row-checkbox') {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        const selectAllCheckbox = table.querySelector('.select-all-checkbox');
        const rowCheckboxes = table.querySelectorAll('.' + checkboxClass);
        
        // Selecionar/desselecionar todos
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', (e) => {
                rowCheckboxes.forEach(checkbox => {
                    checkbox.checked = e.target.checked;
                    this.toggleRowSelection(checkbox.closest('tr'), checkbox.checked);
                });
                this.updateBulkActions();
            });
        }
        
        // Seleção individual
        rowCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                this.toggleRowSelection(e.target.closest('tr'), e.target.checked);
                this.updateSelectAllState(selectAllCheckbox, rowCheckboxes);
                this.updateBulkActions();
            });
        });
    },
    
    toggleRowSelection(row, selected) {
        if (selected) {
            row.classList.add('table-row-selected');
        } else {
            row.classList.remove('table-row-selected');
        }
    },
    
    updateSelectAllState(selectAllCheckbox, rowCheckboxes) {
        if (!selectAllCheckbox) return;
        
        const checkedCount = Array.from(rowCheckboxes).filter(cb => cb.checked).length;
        
        selectAllCheckbox.checked = checkedCount === rowCheckboxes.length;
        selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < rowCheckboxes.length;
    },
    
    updateBulkActions() {
        const selectedCount = document.querySelectorAll('.row-checkbox:checked').length;
        const bulkActions = document.querySelector('.bulk-actions');
        
        if (bulkActions) {
            bulkActions.style.display = selectedCount > 0 ? 'block' : 'none';
            
            const countDisplay = bulkActions.querySelector('.selected-count');
            if (countDisplay) {
                countDisplay.textContent = selectedCount;
            }
        }
    },
    
    getSelectedValues(checkboxClass = 'row-checkbox') {
        const checked = document.querySelectorAll('.' + checkboxClass + ':checked');
        return Array.from(checked).map(cb => cb.value);
    }
};

/**
 * Inicialização da aplicação
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar componentes
    Session.init();
    
    // Adicionar estilos para animações
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        .table-row-selected {
            background-color: var(--light-blue) !important;
        }
        .is-invalid {
            border-color: var(--error-red) !important;
        }
    `;
    document.head.appendChild(style);
    
    // Fechar modais ao clicar fora
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) {
            Modal.hide();
        }
    });
    
    // Fechar modais com ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && Modal.current) {
            Modal.hide();
        }
    });
    
    // Auto-submit de formulários de busca
    const searchForms = document.querySelectorAll('.auto-search');
    searchForms.forEach(form => {
        const input = form.querySelector('input[type="search"], input[name="search"]');
        if (input) {
            const search = Utils.debounce(() => {
                if (input.value.length >= 2 || input.value.length === 0) {
                    form.submit();
                }
            }, 500);
            
            input.addEventListener('input', search);
        }
    });
    
    // Tooltips simples
    const elementsWithTitle = document.querySelectorAll('[title]');
    elementsWithTitle.forEach(element => {
        element.addEventListener('mouseenter', function(e) {
            const tooltip = document.createElement('div');
            tooltip.className = 'simple-tooltip';
            tooltip.textContent = this.title;
            tooltip.style.cssText = `
                position: absolute;
                background: #333;
                color: white;
                padding: 5px 10px;
                border-radius: 4px;
                font-size: 12px;
                pointer-events: none;
                z-index: 10000;
                max-width: 200px;
            `;
            
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
            tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
            
            this.tooltipElement = tooltip;
            this.title = ''; // Remove o title nativo
        });
        
        element.addEventListener('mouseleave', function() {
            if (this.tooltipElement) {
                this.tooltipElement.remove();
                this.title = this.tooltipElement.textContent; // Restaura o title
                this.tooltipElement = null;
            }
        });
    });
    
    console.log('AD Manager inicializado com sucesso');
});

// Expor objetos globalmente para uso nas views
window.ADManager = ADManager;
window.Utils = Utils;
window.API = API;
window.Notifications = Notifications;
window.Modal = Modal;
window.Session = Session;
window.Forms = Forms;
window.Tables = Tables;