# 🔧 Como Configurar LDAP no XAMPP

## ⚠️ Problema Identificado
Você está vendo a mensagem: **"A extensão PHP LDAP não está instalada ou habilitada"**

Isso significa que o XAMPP não tem a extensão LDAP habilitada por padrão.

## ✅ Solução Passo a Passo

### 1. **Localizar o arquivo php.ini**
   - Abra o **XAMPP Control Panel**
   - Clique em **Config** ao lado do **Apache**
   - Selecione **PHP (php.ini)**

### 2. **Editar o arquivo php.ini**
   - Procure pela linha que contém:
     ```ini
     ;extension=ldap
     ```
     **ou**
     ```ini
     ;extension=php_ldap
     ```

### 3. **Habilitar a extensão**
   - **Remova o ponto-e-vírgula (;)** do início da linha:
     ```ini
     extension=ldap
     ```
     **ou**
     ```ini
     extension=php_ldap
     ```

### 4. **Salvar e Reiniciar**
   - **Salve** o arquivo php.ini
   - **Pare** o Apache no XAMPP Control Panel
   - **Inicie** o Apache novamente

### 5. **Verificar se funcionou**
   - Acesse: `http://localhost/ad-manager/xampp-ldap-diagnostic.php`
   - Ou recarregue a página de configurações do sistema

## 🔍 Localizações Comuns do php.ini no XAMPP

### Windows:
```
C:\xampp\php\php.ini
C:\xampp\apache\bin\php.ini
```

### Linux:
```
/opt/lampp/etc/php.ini
/opt/lampp/php/php.ini
```

### macOS:
```
/Applications/XAMPP/etc/php.ini
/Applications/XAMPP/xamppfiles/etc/php.ini
```

## 🚨 Se Ainda Não Funcionar

### Verificar se o arquivo DLL existe (Windows):
1. Verifique se existe o arquivo: `C:\xampp\php\ext\php_ldap.dll`
2. Se não existir, você precisará:
   - Baixar uma versão mais recente do XAMPP
   - Ou instalar a extensão manualmente

### Para Linux:
```bash
# Ubuntu/Debian
sudo apt-get install php-ldap

# CentOS/RHEL
sudo yum install php-ldap
```

## 🧪 Teste Rápido
Execute este código em um arquivo PHP para testar:
```php
<?php
if (extension_loaded('ldap')) {
    echo "✅ LDAP está funcionando!";
} else {
    echo "❌ LDAP não está disponível";
}
?>
```

## 📞 Ainda com Problemas?

1. **Verifique a versão do XAMPP** - Versões muito antigas podem não incluir LDAP
2. **Tente uma instalação limpa** do XAMPP
3. **Use o diagnóstico automatizado**: `xampp-ldap-diagnostic.php`

---

## ⚡ Após Habilitar o LDAP

Quando a extensão estiver funcionando, configure seu Active Directory:

### Exemplo de Configuração:
- **Servidor**: `192.168.1.10` (IP do seu controlador de domínio)
- **Porta**: `636` (LDAPS) ou `389` (LDAP)
- **Domínio**: `empresa.local`
- **Base DN**: `DC=empresa,DC=local`
- **Usuário Admin**: `administrador@empresa.local`
- **Senha**: (senha do administrador)

✅ **Use sempre SSL/LDAPS (porta 636) quando possível para maior segurança!**