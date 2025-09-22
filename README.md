# AD Manager - Sistema de Gestão de Usuários Active Directory

Um sistema completo e moderno para gerenciamento de usuários do Active Directory via interface web, desenvolvido em PHP com design inspirado no Hyper-V Manager.

## 🚀 Características Principais

- **Interface moderna** com design estilo Hyper-V (azul/branco)
- **Autenticação dupla**: Admin padrão + LDAP/Active Directory
- **Gerenciamento completo** de usuários AD
- **Operações em tempo real** via AJAX
- **Design responsivo** para desktop e mobile
- **Logs de auditoria** e monitoramento de atividades
- **Configuração via web** do servidor LDAP
- **Segurança avançada** com CSRF protection e validações

## 📋 Funcionalidades

### Dashboard
- Estatísticas em tempo real dos usuários
- Status da conexão LDAP
- Logs de atividade recentes
- Informações do sistema
- Ações rápidas

### Gerenciamento de Usuários
- Listagem com busca em tempo real
- Ativar/Bloquear usuários individuais
- Operações em massa (múltiplos usuários)
- Redefinir senhas com geração automática
- Visualizar detalhes completos
- Exportar para CSV

### Configuração LDAP
- Interface intuitiva para configuração
- Teste de conexão em tempo real
- Validação de Base DN
- Suporte SSL/TLS
- Backup/restore de configurações

## 🛠️ Requisitos do Sistema

### Servidor Web
- **Apache** 2.4+ ou **Nginx** 1.18+
- **PHP** 7.4+ ou 8.0+ (recomendado)
- **Extensão LDAP** habilitada no PHP
- **OpenSSL** para conexões seguras

### Extensões PHP Necessárias
```bash
php-ldap
php-json
php-mbstring
php-session
php-filter
```

### Servidor Active Directory
- Windows Server 2012+ com AD DS
- Conta de serviço com permissões de leitura/escrita
- Conectividade de rede (porta 389/636)

## 🔧 Instalação

### 1. Preparar o Ambiente

**Windows com XAMPP:**
```batch
# Baixar e instalar XAMPP
# Habilitar extensão LDAP no php.ini
extension=ldap

# Reiniciar Apache
```

**Linux (Ubuntu/Debian):**
```bash
sudo apt update
sudo apt install apache2 php php-ldap php-mbstring php-json
sudo systemctl enable apache2
sudo systemctl start apache2
```

### 2. Instalar o Sistema

```bash
# Clone ou extraia os arquivos para o diretório web
cd /var/www/html  # ou C:\xampp\htdocs
git clone <repositorio> ad-manager
cd ad-manager

# Configurar permissões (Linux)
sudo chown -R www-data:www-data storage/
sudo chmod -R 755 storage/

# Configurar Virtual Host (opcional)
```

### 3. Configuração Inicial

1. Acesse: `http://localhost/ad-manager`
2. Use as credenciais padrão:
   - **Usuário:** `admin`
   - **Senha:** `admin123`
3. Configure a conexão LDAP em **Configurações**

## ⚙️ Configuração LDAP

### Exemplo de Configuração Típica

```
Servidor LDAP: 192.168.1.10
Porta: 636 (SSL) ou 389 (padrão)
Domínio: empresa.local
Base DN: DC=empresa,DC=local
Usuário Admin: administrador@empresa.local
Senha: [senha_segura]
SSL/TLS: ✓ Habilitado (recomendado)
```

### Conta de Serviço AD

Crie uma conta dedicada no AD com as seguintes permissões:
- **Leitura** de usuários e grupos
- **Escrita** para modificar senhas e status
- **Não expira** a senha da conta
- **Logon como serviço** permitido

## 🔐 Segurança

### Medidas Implementadas
- **CSRF Protection** em todos os formulários
- **Sanitização** de inputs
- **Sessões seguras** com timeout
- **Logs de auditoria** de todas as ações
- **Validação** server-side
- **Headers de segurança** HTTP

### Recomendações de Segurança
1. **Altere as credenciais padrão** imediatamente
2. **Use HTTPS** em produção
3. **Mantenha o sistema atualizado**
4. **Configure firewall** apropriado
5. **Monitore logs** regularmente
6. **Use conta de serviço** dedicada para LDAP

## 📁 Estrutura do Projeto

```
ad-manager/
├── assets/
│   ├── css/
│   │   └── style.css          # Estilos principais
│   └── js/
│       └── script.js          # JavaScript principal
├── config/
│   ├── app.php               # Configurações da aplicação
│   └── database.php          # Armazenamento de dados
├── controllers/
│   ├── AuthController.php    # Autenticação
│   ├── DashboardController.php
│   ├── UsersController.php   # Gerenciamento de usuários
│   └── ConfigController.php  # Configurações LDAP
├── models/
│   ├── AuthModel.php         # Modelo de autenticação
│   └── LdapModel.php         # Modelo LDAP/AD
├── views/
│   ├── layouts/
│   │   └── main.php          # Layout principal
│   ├── auth/
│   ├── dashboard/
│   ├── users/
│   └── config/
├── storage/
│   ├── config/               # Configurações JSON
│   ├── logs/                 # Logs do sistema
│   └── sessions/             # Sessões PHP
├── index.php                 # Ponto de entrada
├── .htaccess                 # Configurações Apache
└── README.md
```

## 🔍 Solução de Problemas

### Erro: "Extensão LDAP não encontrada"
```bash
# Ubuntu/Debian
sudo apt install php-ldap
sudo systemctl restart apache2

# CentOS/RHEL
sudo yum install php-ldap
sudo systemctl restart httpd

# XAMPP Windows
# Descomente ;extension=ldap no php.ini
```

### Erro de Conexão LDAP
1. Verifique conectividade de rede: `telnet servidor 389`
2. Confirme credenciais do administrador
3. Teste Base DN no AD Users and Computers
4. Verifique firewall do servidor AD
5. Confirme se SSL está configurado corretamente

### Erro de Permissões
```bash
# Linux - ajustar permissões
sudo chown -R www-data:www-data storage/
sudo chmod -R 755 storage/

# Windows - verificar permissões da pasta
```

### Performance Lenta
1. Verifique latência de rede com AD
2. Otimize Base DN para ser mais específico
3. Limite número de resultados de busca
4. Configure cache no navegador

## 📝 Logs e Monitoramento

### Localização dos Logs
- **Sistema:** `storage/logs/app.log`
- **PHP Errors:** `storage/logs/php_errors.log`
- **Atividades:** `storage/config/activity_logs.json`

### Tipos de Logs
- **LOGIN/LOGOUT:** Autenticações
- **USER_STATUS_CHANGE:** Alterações de status
- **PASSWORD_RESET:** Redefinições de senha
- **LDAP_CONFIG_UPDATED:** Mudanças de configuração
- **LDAP_SYNC:** Sincronizações manuais

## 🔄 Backup e Manutenção

### Backup das Configurações
```bash
# Fazer backup da pasta storage
tar -czf backup_$(date +%Y%m%d).tar.gz storage/

# Ou use a funcionalidade web em Configurações
```

### Manutenção Regular
1. **Limpar logs antigos** (>30 dias)
2. **Verificar espaço em disco**
3. **Testar conexões LDAP**
4. **Atualizar sistema operacional**
5. **Monitorar performance**

## 🆘 Suporte e Contribuição

### Reportar Problemas
1. Descreva o erro detalhadamente
2. Inclua logs relevantes
3. Informe versões (PHP, SO, AD)
4. Passos para reproduzir

### Contribuições
- Fork o projeto
- Crie branch para feature
- Commit com mensagens descritivas
- Abra Pull Request

## 📄 Licença

Este projeto está licenciado sob a MIT License - veja o arquivo [LICENSE](LICENSE) para detalhes.

## 🙏 Créditos

- **Design inspirado no:** Microsoft Hyper-V Manager
- **Ícones:** Font Awesome
- **Framework CSS:** Custom (baseado em variáveis CSS)
- **JavaScript:** Vanilla JS (sem dependências)

---

**AD Manager** - Sistema profissional para gestão de usuários Active Directory
Desenvolvido com ❤️ para administradores de sistema