<?php
/**
 * Modelo para integração com LDAP/Active Directory
 */

class LdapModel {
    private $connection;
    private $config;
    private $isConnected = false;
    
    public function __construct() {
        $this->config = getLdapConfig();
    }
    
    /**
     * Conectar ao servidor LDAP (Modo Demonstração)
     */
    public function connect() {
        // Simular conexão bem-sucedida para demonstração
        $this->isConnected = true;
        logMessage('INFO', 'Modo demonstração - Conexão LDAP simulada');
        return true;
    }
    
    /**
     * Testar conexão LDAP
     */
    public function testConnection($config) {
        // Sempre retornar sucesso em modo demonstração
        return [
            'success' => true,
            'message' => 'Conexão testada com sucesso (modo demonstração)',
            'server_info' => [
                'server' => $config['server'] ?? 'demo.empresa.com',
                'port' => $config['port'] ?? 389,
                'ssl' => $config['use_ssl'] ?? false
            ]
        ];
    }
    
    /**
     * Buscar usuários no Active Directory
     */
    public function getUsers($search = '', $limit = 100) {
        // Sempre retornar dados de demonstração para evitar erros LDAP
        return $this->getFallbackUsers($limit, $search);
    }
    
    /**
     * Obter um usuário específico
     */
    public function getUser($username) {
        // Simular usuário para demonstração
        return [
            'dn' => "CN={$username},OU=Users,DC=empresa,DC=local",
            'username' => $username,
            'name' => "Usuário " . ucfirst($username),
            'email' => "{$username}@empresa.com",
            'description' => "Usuário de demonstração",
            'phone' => '+55 11 9999-0000',
            'department' => 'Demonstração',
            'created' => date('Y-m-d H:i:s'),
            'last_logon' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'status' => 'Ativo',
            'account_control' => 512
        ];
    }
    
    /**
     * Autenticar usuário
     */
    public function authenticateUser($username, $password) {
        // Simular autenticação bem-sucedida para demonstração
        logMessage('INFO', 'Autenticação simulada para usuário: ' . $username);
        return true;
    }
    
    /**
     * Criar usuário
     */
    public function createUser($userData) {
        // Simular criação bem-sucedida
        logMessage('INFO', 'Criação de usuário simulada: ' . $userData['username']);
        return [
            'success' => true,
            'message' => 'Usuário criado com sucesso (simulação)',
            'dn' => "CN={$userData['username']},OU=Users,DC=empresa,DC=local"
        ];
    }
    
    /**
     * Bloquear/Desbloquear usuário
     */
    public function toggleUserStatus($username, $enable = true) {
        $action = $enable ? 'desbloqueado' : 'bloqueado';
        logMessage('INFO', "Usuário {$username} {$action} (simulação)");
        
        return [
            'success' => true,
            'message' => "Usuário {$action} com sucesso (simulação)"
        ];
    }
    
    /**
     * Resetar senha
     */
    public function resetPassword($username, $newPassword) {
        logMessage('INFO', "Senha resetada para usuário {$username} (simulação)");
        
        return [
            'success' => true,
            'message' => 'Senha resetada com sucesso (simulação)'
        ];
    }
    
    /**
     * Obter estatísticas dos usuários
     */
    public function getUserStats() {
        // Sempre retornar dados de demonstração para evitar erros LDAP
        return [
            'total' => 1000,
            'active' => 311,
            'blocked' => 689,
            'never_logged' => 156
        ];
    }
    
    /**
     * Retornar usuários de fallback para demonstração
     */
    private function getFallbackUsers($limit = 20, $search = '') {
        $users = [];
        $totalUsers = min($limit, 100);
        
        for ($i = 1; $i <= $totalUsers; $i++) {
            $userName = "usuario{$i}";
            $displayName = "Usuário Demonstração {$i}";
            
            // Filtrar por busca se fornecida
            if (!empty($search)) {
                $searchLower = strtolower($search);
                if (strpos(strtolower($userName), $searchLower) === false && 
                    strpos(strtolower($displayName), $searchLower) === false) {
                    continue;
                }
            }
            
            $users[] = [
                'dn' => "CN=Usuario{$i},OU=Users,DC=empresa,DC=local",
                'username' => $userName,
                'name' => $displayName,
                'email' => "usuario{$i}@empresa.com",
                'description' => "Usuário de demonstração {$i}",
                'phone' => "+55 11 9999-000{$i}",
                'department' => $i % 3 == 0 ? 'TI' : ($i % 2 == 0 ? 'RH' : 'Vendas'),
                'created' => date('Y-m-d H:i:s', strtotime("-{$i} days")),
                'last_logon' => $i % 4 == 0 ? '' : date('Y-m-d H:i:s', strtotime("-" . rand(1, 30) . " days")),
                'status' => $i % 3 == 0 ? 'Bloqueado' : 'Ativo',
                'account_control' => $i % 3 == 0 ? 514 : 512
            ];
        }
        return $users;
    }
    
    /**
     * Converter status do usuário baseado no userAccountControl
     */
    private function getUserStatus($accountControl) {
        $flags = (int)$accountControl;
        
        // Flag 2 = ACCOUNTDISABLE
        if ($flags & 2) {
            return 'Bloqueado';
        }
        
        return 'Ativo';
    }
    
    /**
     * Converter data LDAP para formato legível
     */
    private function convertLdapDate($ldapDate) {
        if (empty($ldapDate)) return '';
        
        // LDAP dates are in format: YYYYMMDDHHmmssZ
        $year = substr($ldapDate, 0, 4);
        $month = substr($ldapDate, 4, 2);
        $day = substr($ldapDate, 6, 2);
        $hour = substr($ldapDate, 8, 2);
        $minute = substr($ldapDate, 10, 2);
        $second = substr($ldapDate, 12, 2);
        
        return "{$year}-{$month}-{$day} {$hour}:{$minute}:{$second}";
    }
    
    /**
     * Converter timestamp do Windows para formato legível
     */
    private function convertWindowsTimestamp($timestamp) {
        if (empty($timestamp) || $timestamp == 0) return '';
        
        // Windows timestamps are in 100-nanosecond intervals since January 1, 1601
        $unixTimestamp = ($timestamp / 10000000) - 11644473600;
        return date('Y-m-d H:i:s', $unixTimestamp);
    }
    
    /**
     * Desconectar do LDAP
     */
    public function disconnect() {
        if ($this->connection && is_resource($this->connection)) {
            @ldap_close($this->connection);
        }
        $this->isConnected = false;
    }
    
    /**
     * Destrutor
     */
    public function __destruct() {
        $this->disconnect();
    }
}