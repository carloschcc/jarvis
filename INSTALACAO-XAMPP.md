# 🚀 INSTALAÇÃO NO XAMPP - AD Manager

## 📋 **PASSOS PARA INSTALAÇÃO**

### 1. **Baixar e Descompactar**
- Baixe o repositório do GitHub
- Descompacte na pasta `htdocs` do XAMPP
- Exemplo: `C:\xampp\htdocs\ad-manager\`

### 2. **Testar Instalação**
- Abra o navegador e acesse: `http://localhost/ad-manager/test.php`
- Se aparecer a página de teste com "✅", está funcionando!

### 3. **Se Não Funcionar:**

#### **Problema 1: Erro 500 ou página em branco**
- Renomeie `.htaccess` para `.htaccess-backup`
- Renomeie `.htaccess-simple` para `.htaccess`
- Tente acessar novamente

#### **Problema 2: PHP não funciona**
- Verifique se o Apache está rodando no XAMPP
- Verifique se o módulo PHP está ativo

#### **Problema 3: Permissões**
- Certifique-se que a pasta `storage` tem permissões de escrita
- No Windows: Botão direito → Propriedades → Segurança → Dar acesso total

### 4. **Acessar o Sistema**
- URL: `http://localhost/ad-manager/`
- **Usuário:** admin
- **Senha:** admin123

### 5. **Estrutura de Pastas**
```
ad-manager/
├── assets/           # CSS, JS, imagens
├── config/           # Configurações
├── controllers/      # Controladores PHP
├── models/           # Modelos de dados
├── views/           # Templates HTML/PHP
├── storage/         # Logs e dados
├── test.php         # Teste de funcionamento
└── index.php        # Arquivo principal
```

## 🔧 **PROBLEMAS COMUNS**

### **Erro: "Class not found"**
- Verifique se todos os arquivos foram descompactados
- Execute o teste: `http://localhost/ad-manager/test.php`

### **Página em branco**
- Ative display_errors no PHP
- Verifique logs do Apache no XAMPP
- Use .htaccess-simple

### **Assets não carregam (CSS/JS)**
- Verifique se a pasta `assets` existe
- Certifique-se que não há bloqueios no .htaccess

## ✅ **VERIFICAÇÃO RÁPIDA**
1. Acesse: `http://localhost/ad-manager/test.php`
2. Todos os itens devem estar "✅ OK"
3. Clique em "Ir para o Sistema AD Manager"
4. Faça login com admin/admin123

## 🆘 **AINDA COM PROBLEMAS?**
1. Verifique se o XAMPP está rodando (Apache + MySQL)
2. Teste se PHP funciona: `http://localhost/xampp/`
3. Verifique se não há outro software usando porta 80
4. Teste em modo anônimo do navegador