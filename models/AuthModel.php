<?php
/**
 * Modelo de Autenticação
 */

class AuthModel {
    private $ldapModel;
    
    public function __construct() {
        $this->ldapModel = new LdapModel();
    }
    
    /**
     * Autenticar usuário (admin padrão ou LDAP)
     */
    public function authenticate($username, $password) {
        try {
            // Verificar se é o admin padrão
            if ($this->isDefaultAdmin($username, $password)) {
                return $this->loginDefaultAdmin();
            }
            
            // Verificar se LDAP está configurado
            $ldapConfig = getLdapConfig();
            if (!$ldapConfig['configured']) {
                throw new Exception('Sistema não configurado. Use admin/admin123 para primeira configuração.');
            }
            
            // Tentar autenticação via LDAP
            if ($this->ldapModel->authenticateUser($username, $password)) {
                return $this->loginLdapUser($username);
            }
            
            throw new Exception('Usuário ou senha inválidos');
            
        } catch (Exception $e) {
            logMessage('WARNING', 'Tentativa de login falhada', [
                'username' => $username,
                'error' => $e->getMessage(),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Verificar se é o administrador padrão
     */
    private function isDefaultAdmin($username, $password) {
        return ($username === DEFAULT_ADMIN_USER && $password === DEFAULT_ADMIN_PASS);
    }
    
    /**
     * Login do administrador padrão
     */
    private function loginDefaultAdmin() {
        $_SESSION['user_logged'] = true;
        $_SESSION['user_type'] = 'admin';
        $_SESSION['username'] = DEFAULT_ADMIN_USER;
        $_SESSION['display_name'] = 'Administrador';
        $_SESSION['login_time'] = time();
        $_SESSION['is_default_admin'] = true;
        
        // Registrar log de atividade
        saveActivityLog('LOGIN', DEFAULT_ADMIN_USER, 'Login como administrador padrão');
        
        logMessage('INFO', 'Login realizado - Admin padrão', [
            'username' => DEFAULT_ADMIN_USER,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        return [
            'success' => true,
            'user_type' => 'admin',
            'username' => DEFAULT_ADMIN_USER,
            'display_name' => 'Administrador',
            'message' => 'Login realizado com sucesso'
        ];
    }
    
    /**
     * Login de usuário LDAP
     */
    private function loginLdapUser($username) {
        try {
            // Obter dados do usuário do LDAP
            $userData = $this->ldapModel->getUser($username);
            
            $_SESSION['user_logged'] = true;
            $_SESSION['user_type'] = 'ldap';
            $_SESSION['username'] = $userData['username'];
            $_SESSION['display_name'] = $userData['name'];
            $_SESSION['user_email'] = $userData['email'];
            $_SESSION['login_time'] = time();
            $_SESSION['is_default_admin'] = false;
            
            // Registrar log de atividade
            saveActivityLog('LOGIN', $userData['username'], 'Login via LDAP');
            
            logMessage('INFO', 'Login realizado - Usuário LDAP', [
                'username' => $userData['username'],
                'display_name' => $userData['name'],
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            
            return [
                'success' => true,
                'user_type' => 'ldap',
                'username' => $userData['username'],
                'display_name' => $userData['name'],
                'message' => 'Login realizado com sucesso'
            ];
            
        } catch (Exception $e) {
            throw new Exception('Erro ao obter dados do usuário: ' . $e->getMessage());
        }
    }
    
    /**
     * Logout do usuário
     */
    public function logout() {
        $username = $_SESSION['username'] ?? 'unknown';
        
        // Registrar log de atividade
        saveActivityLog('LOGOUT', $username, 'Logout do sistema');
        
        logMessage('INFO', 'Logout realizado', [
            'username' => $username,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        // Limpar sessão
        session_unset();
        session_destroy();
        
        return ['success' => true, 'message' => 'Logout realizado com sucesso'];
    }
    
    /**
     * Verificar se usuário está logado
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_logged']) && $_SESSION['user_logged'] === true;
    }
    
    /**
     * Verificar se sessão expirou
     */
    public function isSessionExpired() {
        if (!isset($_SESSION['login_time'])) {
            return true;
        }
        
        $sessionAge = time() - $_SESSION['login_time'];
        return $sessionAge > SESSION_TIMEOUT;
    }
    
    /**
     * Obter informações do usuário logado
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'username' => $_SESSION['username'] ?? '',
            'display_name' => $_SESSION['display_name'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'user_type' => $_SESSION['user_type'] ?? 'unknown',
            'is_default_admin' => $_SESSION['is_default_admin'] ?? false,
            'login_time' => $_SESSION['login_time'] ?? 0,
            'session_remaining' => SESSION_TIMEOUT - (time() - ($_SESSION['login_time'] ?? 0))
        ];
    }
    
    /**
     * Verificar se usuário tem permissões de administrador
     */
    public function isAdmin() {
        return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
    }
    
    /**
     * Renovar sessão
     */
    public function renewSession() {
        if ($this->isLoggedIn()) {
            $_SESSION['login_time'] = time();
            return true;
        }
        return false;
    }
    
    /**
     * Validar força da senha
     */
    public function validatePasswordStrength($password) {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Senha deve ter pelo menos 8 caracteres';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Senha deve conter pelo menos uma letra maiúscula';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Senha deve conter pelo menos uma letra minúscula';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Senha deve conter pelo menos um número';
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Senha deve conter pelo menos um caractere especial';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'score' => $this->calculatePasswordScore($password)
        ];
    }
    
    /**
     * Calcular pontuação da senha (0-100)
     */
    private function calculatePasswordScore($password) {
        $score = 0;
        $length = strlen($password);
        
        // Pontuação por comprimento
        $score += min($length * 4, 40);
        
        // Pontuação por variedade de caracteres
        if (preg_match('/[a-z]/', $password)) $score += 10;
        if (preg_match('/[A-Z]/', $password)) $score += 10;
        if (preg_match('/[0-9]/', $password)) $score += 10;
        if (preg_match('/[^A-Za-z0-9]/', $password)) $score += 15;
        
        // Bonificação por comprimento maior
        if ($length >= 12) $score += 10;
        if ($length >= 16) $score += 5;
        
        // Penalidade por padrões comuns
        if (preg_match('/(.)\1{2,}/', $password)) $score -= 10; // Caracteres repetidos
        if (preg_match('/123|abc|qwerty/i', $password)) $score -= 15; // Sequências comuns
        
        return max(0, min(100, $score));
    }
    
    /**
     * Gerar senha aleatória
     */
    public function generateRandomPassword($length = 12) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()';
        $password = '';
        
        // Garantir pelo menos um de cada tipo
        $password .= $chars[rand(0, 25)]; // Maiúscula
        $password .= $chars[rand(26, 51)]; // Minúscula
        $password .= $chars[rand(52, 61)]; // Número
        $password .= $chars[rand(62, strlen($chars) - 1)]; // Especial
        
        // Preencher o restante aleatoriamente
        for ($i = 4; $i < $length; $i++) {
            $password .= $chars[rand(0, strlen($chars) - 1)];
        }
        
        // Embaralhar a string
        return str_shuffle($password);
    }
}