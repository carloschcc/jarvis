<?php
// Página de Download do Projeto Jarvis - Active Directory Manager
$files = glob("jarvis-projeto-completo-*.tar.gz");
$latestFile = !empty($files) ? $files[0] : null;
$fileSize = $latestFile ? round(filesize($latestFile) / 1024 / 1024, 2) : 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download - Projeto Jarvis AD Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .download-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        .download-icon {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .btn-download {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 50px;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
            color: white;
        }
        .feature-list {
            text-align: left;
            margin: 20px 0;
        }
        .feature-item {
            display: flex;
            align-items: center;
            margin: 10px 0;
            padding: 8px;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 10px;
        }
        .feature-icon {
            color: #667eea;
            margin-right: 10px;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="download-card">
            <div class="download-icon">
                <i class="fas fa-download"></i>
            </div>
            
            <h2 class="mb-3">
                <i class="fas fa-server text-primary"></i>
                Projeto Jarvis - AD Manager
            </h2>
            
            <p class="text-muted mb-4">
                Sistema completo de gerenciamento do Active Directory com funcionalidade de seleção OU implementada e corrigida!
            </p>
            
            <div class="feature-list">
                <div class="feature-item">
                    <i class="fas fa-check-circle feature-icon"></i>
                    <span><strong>✅ Campo OU Visível</strong> - Problema de visibilidade corrigido</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-sitemap feature-icon"></i>
                    <span><strong>📂 Seleção OU/Container</strong> - Dropdown completo implementado</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-users feature-icon"></i>
                    <span><strong>👥 Gestão Usuários AD</strong> - CRUD completo</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-shield-alt feature-icon"></i>
                    <span><strong>🔒 Autenticação</strong> - Sistema seguro</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-mobile-alt feature-icon"></i>
                    <span><strong>📱 Responsivo</strong> - Interface moderna</span>
                </div>
            </div>
            
            <?php if ($latestFile): ?>
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle"></i>
                <strong>Arquivo:</strong> <?= $latestFile ?><br>
                <strong>Tamanho:</strong> <?= $fileSize ?> MB<br>
                <strong>Gerado:</strong> <?= date('d/m/Y H:i:s') ?>
            </div>
            
            <a href="<?= $latestFile ?>" class="btn-download" download>
                <i class="fas fa-download me-2"></i>
                Baixar Projeto Completo
            </a>
            
            <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                Nenhum arquivo disponível para download no momento.
            </div>
            <?php endif; ?>
            
            <div class="mt-4">
                <small class="text-muted">
                    <i class="fas fa-code"></i>
                    Versão com correção OU implementada • <?= date('Y') ?>
                </small>
            </div>
            
            <div class="mt-3">
                <a href="index.php" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-left"></i> Voltar ao Sistema
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>