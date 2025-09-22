<?php 
$current_page = 'dashboard';
ob_start(); 
?>

<!-- Página de erro -->
<div class="text-center" style="padding: 60px 0;">
    <i class="fas fa-exclamation-triangle" style="font-size: 72px; color: var(--error-red); margin-bottom: 30px;"></i>
    
    <h1 style="color: var(--error-red); margin-bottom: 15px;">
        Ops! Algo deu errado
    </h1>
    
    <p style="font-size: 18px; color: var(--dark-gray); margin-bottom: 30px;">
        Ocorreu um erro ao carregar o dashboard.
    </p>
    
    <?php if (isset($error)): ?>
    <div class="alert alert-danger" style="max-width: 600px; margin: 0 auto 30px;">
        <i class="fas fa-exclamation-circle"></i>
        <strong>Detalhes do erro:</strong><br>
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>
    
    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
        <button onclick="window.location.reload()" class="btn btn-primary">
            <i class="fas fa-sync-alt"></i>
            Tentar Novamente
        </button>
        
        <a href="index.php?page=config" class="btn btn-outline-primary">
            <i class="fas fa-cogs"></i>
            Verificar Configurações
        </a>
        
        <a href="index.php?page=auth&action=logout" class="btn btn-outline-secondary">
            <i class="fas fa-sign-out-alt"></i>
            Sair do Sistema
        </a>
    </div>
    
    <div style="margin-top: 40px; padding: 20px; background: var(--light-gray); border-radius: var(--border-radius); max-width: 600px; margin-left: auto; margin-right: auto;">
        <h5 style="margin-bottom: 15px;">
            <i class="fas fa-lightbulb text-warning"></i>
            Possíveis soluções:
        </h5>
        
        <ul style="text-align: left; color: var(--dark-gray);">
            <li>Verifique se o servidor LDAP está acessível</li>
            <li>Confirme as configurações de conexão</li>
            <li>Teste as credenciais do administrador</li>
            <li>Verifique se a extensão PHP LDAP está instalada</li>
            <li>Consulte os logs do sistema para mais detalhes</li>
        </ul>
    </div>
</div>

<?php
$content = ob_get_clean();
$current_page = 'dashboard';
include VIEWS_PATH . '/layouts/main.php';
?>