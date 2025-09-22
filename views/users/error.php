<?php 
$current_page = 'users';
ob_start(); 
?>

<!-- Página de erro de usuários -->
<div class="text-center" style="padding: 60px 0;">
    <i class="fas fa-users" style="font-size: 72px; color: var(--error-red); margin-bottom: 30px;"></i>
    
    <h1 style="color: var(--error-red); margin-bottom: 15px;">
        Erro no Gerenciamento de Usuários
    </h1>
    
    <p style="font-size: 18px; color: var(--dark-gray); margin-bottom: 30px;">
        Não foi possível carregar a lista de usuários.
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
        
        <a href="index.php?page=config" class="btn btn-outline-warning">
            <i class="fas fa-cogs"></i>
            Configurar LDAP
        </a>
        
        <a href="index.php?page=dashboard" class="btn btn-outline-primary">
            <i class="fas fa-tachometer-alt"></i>
            Voltar ao Dashboard
        </a>
    </div>
    
    <div style="margin-top: 40px; padding: 20px; background: var(--light-gray); border-radius: var(--border-radius); max-width: 600px; margin-left: auto; margin-right: auto;">
        <h5 style="margin-bottom: 15px;">
            <i class="fas fa-info-circle text-primary"></i>
            Verifique estas configurações:
        </h5>
        
        <ul style="text-align: left; color: var(--dark-gray);">
            <li>Configuração LDAP está completa e válida</li>
            <li>Servidor Active Directory está acessível</li>
            <li>Credenciais do administrador estão corretas</li>
            <li>Base DN corresponde à estrutura do AD</li>
            <li>Firewall permite conexão na porta LDAP</li>
        </ul>
    </div>
</div>

<?php
$content = ob_get_clean();
$current_page = 'users';
include VIEWS_PATH . '/layouts/main.php';
?>