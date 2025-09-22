<?php 
$current_page = 'config';
ob_start(); 
?>

<!-- Página de erro de configuração -->
<div class="text-center" style="padding: 60px 0;">
    <i class="fas fa-cogs" style="font-size: 72px; color: var(--error-red); margin-bottom: 30px;"></i>
    
    <h1 style="color: var(--error-red); margin-bottom: 15px;">
        Erro de Configuração
    </h1>
    
    <p style="font-size: 18px; color: var(--dark-gray); margin-bottom: 30px;">
        Não foi possível carregar a página de configurações.
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
        
        <a href="index.php?page=dashboard" class="btn btn-outline-primary">
            <i class="fas fa-tachometer-alt"></i>
            Voltar ao Dashboard
        </a>
    </div>
</div>

<?php
$content = ob_get_clean();
$current_page = 'config';
include VIEWS_PATH . '/layouts/main.php';
?>