<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'AD Manager') ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <!-- Meta tags -->
    <meta name="description" content="Sistema de Gestão de Usuários Active Directory">
    <meta name="author" content="AD Manager">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgdmlld0JveD0iMCAwIDMyIDMyIj48cGF0aCBmaWxsPSIjMDA3OGQ0IiBkPSJNMTYgMkM4LjI4IDIgMiA4LjI4IDIgMTZzNi4yOCAxNCAxNCAxNHMxNC02LjI4IDE0LTE0UzIzLjcyIDIgMTYgMnptMCAxLjVjNi45IDAgMTIuNSA1LjYgMTIuNSAxMi41UzIyLjkgMjguNSAxNiAyOC41UzMuNSAyMi45IDMuNSAxNlM5LjEgMy41IDE2IDMuNXoiLz48L3N2Zz4=">
</head>
<body>
    <!-- Cabeçalho -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-users-cog"></i>
                    <?= APP_NAME ?>
                </div>
                
                <div class="user-info">
                    <div class="user-details">
                        <div class="user-name">
                            <?= htmlspecialchars($current_user['display_name'] ?? 'Usuário') ?>
                        </div>
                        <div class="user-type">
                            <?php if (isset($current_user['is_default_admin']) && $current_user['is_default_admin']): ?>
                                <i class="fas fa-crown"></i> Administrador
                            <?php else: ?>
                                <i class="fas fa-user"></i> <?= htmlspecialchars($current_user['user_type'] ?? 'Usuário') ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="user-actions">
                        <a href="index.php?page=auth&action=logout" class="btn btn-outline-primary btn-sm" title="Sair do sistema">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Navegação -->
    <nav class="navigation">
        <div class="container">
            <div class="nav-container">
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="index.php?page=dashboard" class="nav-link <?= ($current_page ?? '') === 'dashboard' ? 'active' : '' ?>">
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?page=users" class="nav-link <?= ($current_page ?? '') === 'users' ? 'active' : '' ?>">
                            <i class="fas fa-users"></i>
                            Usuários
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?page=config" class="nav-link <?= ($current_page ?? '') === 'config' ? 'active' : '' ?>">
                            <i class="fas fa-cogs"></i>
                            Configurações
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Conteúdo principal -->
    <main class="main-content">
        <div class="container">
            <?php if (isset($content)): ?>
                <?= $content ?>
            <?php endif; ?>
        </div>
    </main>
    
    <!-- Rodapé -->
    <footer class="footer" style="background: var(--light-gray); padding: 20px 0; margin-top: 40px; border-top: 1px solid var(--medium-gray);">
        <div class="container">
            <div class="text-center text-muted">
                <p style="margin: 0;">
                    <?= APP_NAME ?> v<?= APP_VERSION ?> - 
                    Sistema de Gestão de Usuários Active Directory
                </p>
                <p style="margin: 5px 0 0 0; font-size: 12px;">
                    <i class="far fa-clock"></i> 
                    Horário do servidor: <?= date('d/m/Y H:i:s') ?>
                </p>
            </div>
        </div>
    </footer>
    
    <!-- Scripts Essenciais -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <!-- AD Manager - Solução Definitiva (substitui todos os outros JS) -->
    <script src="assets/js/ad-manager-definitive.js"></script>
    
    <?php if (isset($additional_scripts)): ?>
        <?= $additional_scripts ?>
    <?php endif; ?>
</body>
</html>