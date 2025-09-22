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
     * Conectar ao servidor LDAP
     */
    public function connect() {
        try {
            // Verificar se a extensão LDAP está instalada
            if (!extension_loaded('ldap')) {
                logMessage('ERROR', 'Extensão LDAP não está instalada');
                return false;
            }
            
            // Validar configuração
            if (empty($this->config['server'])) {
                logMessage('ERROR', 'Servidor LDAP não configurado');
                return false;
            }
            
            $server = $this->config['server'];
            $port = $this->config['port'] ?? 389;
            
            // Conectar ao servidor LDAP
            if ($this->config['use_ssl']) {
                $ldapUri = "ldaps://{$server}:{$port}";
            } else {
                $ldapUri = "ldap://{$server}:{$port}";
            }
            
            logMessage('INFO', "Tentando conectar ao LDAP: {$ldapUri}");
            
            $this->connection = ldap_connect($ldapUri);
            
            if (!$this->connection) {
                logMessage('ERROR', 'Falha ao conectar ao servidor LDAP: ' . ldap_error($this->connection));
                return false;
            }
            
            // Configurar opções LDAP
            ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($this->connection, LDAP_OPT_REFERRALS, 0);
            
            if ($this->config['use_ssl']) {
                ldap_set_option($this->connection, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_NEVER);
            }
            
            // Autenticar com usuário administrador
            if (!empty($this->config['admin_user']) && !empty($this->config['admin_pass'])) {
                $adminDn = $this->buildUserDn($this->config['admin_user']);
                
                logMessage('INFO', "Tentando autenticar como: {$adminDn}");
                
                $bind = @ldap_bind($this->connection, $adminDn, $this->config['admin_pass']);
                
                if (!$bind) {
                    $error = ldap_error($this->connection);
                    logMessage('ERROR', "Falha na autenticação LDAP: {$error}");
                    return false;
                }
            }
            
            $this->isConnected = true;
            logMessage('INFO', 'Conexão LDAP estabelecida com sucesso');
            return true;
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro na conexão LDAP: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Testar conexão LDAP
     */
    public function testConnection($config) {
        try {
            // Verificar se a extensão LDAP está instalada
            if (!extension_loaded('ldap')) {
                return [
                    'success' => false,
                    'message' => 'Extensão LDAP não está instalada no PHP',
                    'error' => 'LDAP_EXTENSION_MISSING'
                ];
            }
            
            // Validar configuração básica
            if (empty($config['server'])) {
                return [
                    'success' => false,
                    'message' => 'Servidor LDAP não informado',
                    'error' => 'MISSING_SERVER'
                ];
            }
            
            $server = $config['server'];
            $port = $config['port'] ?? 389;
            
            // Construir URI de conexão
            if ($config['use_ssl']) {
                $ldapUri = "ldaps://{$server}:{$port}";
            } else {
                $ldapUri = "ldap://{$server}:{$port}";
            }
            
            // Tentar conectar
            $connection = ldap_connect($ldapUri);
            
            if (!$connection) {
                return [
                    'success' => false,
                    'message' => 'Não foi possível conectar ao servidor LDAP',
                    'error' => 'CONNECTION_FAILED',
                    'ldap_error' => ldap_error($connection)
                ];
            }
            
            // Configurar opções
            ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);
            
            if ($config['use_ssl']) {
                ldap_set_option($connection, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_NEVER);
            }
            
            // Testar bind se credenciais fornecidas
            if (!empty($config['admin_user']) && !empty($config['admin_pass'])) {
                $adminDn = $this->buildUserDnFromConfig($config['admin_user'], $config);
                
                $bind = @ldap_bind($connection, $adminDn, $config['admin_pass']);
                
                if (!$bind) {
                    $ldapError = ldap_error($connection);
                    ldap_close($connection);
                    
                    return [
                        'success' => false,
                        'message' => 'Falha na autenticação: ' . $ldapError,
                        'error' => 'AUTH_FAILED',
                        'ldap_error' => $ldapError
                    ];
                }
            } else {
                // Testar bind anônimo
                $bind = @ldap_bind($connection);
                
                if (!$bind) {
                    $ldapError = ldap_error($connection);
                    ldap_close($connection);
                    
                    return [
                        'success' => false,
                        'message' => 'Conexão estabelecida mas bind falhou: ' . $ldapError,
                        'error' => 'BIND_FAILED',
                        'ldap_error' => $ldapError
                    ];
                }
            }
            
            ldap_close($connection);
            
            return [
                'success' => true,
                'message' => 'Conexão testada com sucesso!',
                'server_info' => [
                    'server' => $server,
                    'port' => $port,
                    'ssl' => $config['use_ssl'] ?? false,
                    'test_time' => date('d/m/Y H:i:s')
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro durante o teste: ' . $e->getMessage(),
                'error' => 'EXCEPTION',
                'exception' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Buscar usuários no Active Directory
     */
    public function getUsers($search = '', $limit = 100) {
        try {
            if (!$this->isConnected && !$this->connect()) {
                logMessage('WARNING', 'Conexão LDAP não disponível, usando dados de fallback');
                return $this->getFallbackUsers($limit, $search);
            }
            
            $baseDn = $this->config['base_dn'] ?? 'DC=empresa,DC=local';
            
            // Construir filtro de busca
            $filter = '(&(objectClass=user)(!(objectClass=computer))(!(userAccountControl:1.2.840.113556.1.4.803:=2)))';
            
            if (!empty($search)) {
                $searchEscaped = ldap_escape($search, '', LDAP_ESCAPE_FILTER);
                $filter = "(&(objectClass=user)(!(objectClass=computer))(|(cn=*{$searchEscaped}*)(sAMAccountName=*{$searchEscaped}*)(mail=*{$searchEscaped}*)))";
            }
            
            // Atributos a serem retornados
            $attributes = [
                'cn', 'sAMAccountName', 'displayName', 'mail', 'description',
                'telephoneNumber', 'department', 'whenCreated', 'lastLogon',
                'userAccountControl', 'distinguishedName'
            ];
            
            logMessage('INFO', "Executando busca LDAP: {$filter} em {$baseDn}");
            
            // Executar busca
            $result = @ldap_search($this->connection, $baseDn, $filter, $attributes, 0, $limit);
            
            if (!$result) {
                $error = ldap_error($this->connection);
                logMessage('ERROR', "Erro na busca LDAP: {$error}");
                return $this->getFallbackUsers($limit, $search);
            }
            
            $entries = ldap_get_entries($this->connection, $result);
            
            if ($entries['count'] == 0) {
                logMessage('INFO', 'Nenhum usuário encontrado no LDAP');
                return [];
            }
            
            $users = [];
            
            for ($i = 0; $i < $entries['count']; $i++) {
                $entry = $entries[$i];
                
                $user = [
                    'dn' => $entry['dn'] ?? '',
                    'username' => $entry['samaccountname'][0] ?? '',
                    'name' => $entry['displayname'][0] ?? $entry['cn'][0] ?? '',
                    'email' => $entry['mail'][0] ?? '',
                    'description' => $entry['description'][0] ?? '',
                    'phone' => $entry['telephonenumber'][0] ?? '',
                    'department' => $entry['department'][0] ?? '',
                    'created' => $this->convertLdapDate($entry['whencreated'][0] ?? ''),
                    'last_logon' => $this->convertWindowsTimestamp($entry['lastlogon'][0] ?? ''),
                    'status' => $this->getUserStatus($entry['useraccountcontrol'][0] ?? 512),
                    'account_control' => $entry['useraccountcontrol'][0] ?? 512
                ];
                
                $users[] = $user;
            }
            
            logMessage('INFO', 'Encontrados ' . count($users) . ' usuários no LDAP');
            return $users;
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao buscar usuários: ' . $e->getMessage());
            return $this->getFallbackUsers($limit, $search);
        }
    }
    
    /**
     * Obter um usuário específico
     */
    public function getUser($username) {
        try {
            if (!$this->isConnected && !$this->connect()) {
                logMessage('WARNING', 'Conexão LDAP não disponível para buscar usuário');
                return null;
            }
            
            $baseDn = $this->config['base_dn'] ?? 'DC=empresa,DC=local';
            $usernameEscaped = ldap_escape($username, '', LDAP_ESCAPE_FILTER);
            $filter = "(&(objectClass=user)(sAMAccountName={$usernameEscaped}))";
            
            $attributes = [
                'cn', 'sAMAccountName', 'displayName', 'mail', 'description',
                'telephoneNumber', 'department', 'whenCreated', 'lastLogon',
                'userAccountControl', 'distinguishedName'
            ];
            
            $result = @ldap_search($this->connection, $baseDn, $filter, $attributes);
            
            if (!$result) {
                logMessage('ERROR', 'Erro ao buscar usuário: ' . ldap_error($this->connection));
                return null;
            }
            
            $entries = ldap_get_entries($this->connection, $result);
            
            if ($entries['count'] == 0) {
                logMessage('INFO', "Usuário {$username} não encontrado no LDAP");
                return null;
            }
            
            $entry = $entries[0];
            
            return [
                'dn' => $entry['dn'] ?? '',
                'username' => $entry['samaccountname'][0] ?? '',
                'name' => $entry['displayname'][0] ?? $entry['cn'][0] ?? '',
                'email' => $entry['mail'][0] ?? '',
                'description' => $entry['description'][0] ?? '',
                'phone' => $entry['telephonenumber'][0] ?? '',
                'department' => $entry['department'][0] ?? '',
                'created' => $this->convertLdapDate($entry['whencreated'][0] ?? ''),
                'last_logon' => $this->convertWindowsTimestamp($entry['lastlogon'][0] ?? ''),
                'status' => $this->getUserStatus($entry['useraccountcontrol'][0] ?? 512),
                'account_control' => $entry['useraccountcontrol'][0] ?? 512
            ];
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao obter usuário: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Autenticar usuário
     */
    public function authenticateUser($username, $password) {
        try {
            if (!$this->isConnected && !$this->connect()) {
                logMessage('ERROR', 'Conexão LDAP não disponível para autenticação');
                return false;
            }
            
            $userDn = $this->buildUserDn($username);
            
            logMessage('INFO', "Tentando autenticar usuário: {$userDn}");
            
            // Tentar bind com credenciais do usuário
            $bind = @ldap_bind($this->connection, $userDn, $password);
            
            if (!$bind) {
                $error = ldap_error($this->connection);
                logMessage('WARNING', "Falha na autenticação para {$username}: {$error}");
                return false;
            }
            
            logMessage('INFO', "Usuário {$username} autenticado com sucesso");
            return true;
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro na autenticação: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Criar usuário
     */
    public function createUser($userData) {
        try {
            if (!$this->isConnected && !$this->connect()) {
                return [
                    'success' => false,
                    'message' => 'Conexão LDAP não disponível'
                ];
            }
            
            $baseDn = $this->config['base_dn'] ?? 'DC=empresa,DC=local';
            $userDn = "CN={$userData['name']},CN=Users,{$baseDn}";
            
            // Atributos do novo usuário
            $attributes = [
                'objectClass' => ['top', 'person', 'organizationalPerson', 'user'],
                'cn' => $userData['name'],
                'sAMAccountName' => $userData['username'],
                'userPrincipalName' => $userData['username'] . '@' . ($this->config['domain'] ?? 'empresa.local'),
                'displayName' => $userData['name'],
                'mail' => $userData['email'] ?? '',
                'description' => $userData['description'] ?? '',
                'userAccountControl' => 544, // Conta normal, senha deve ser alterada no próximo logon
            ];
            
            // Adicionar outros atributos opcionais
            if (!empty($userData['phone'])) {
                $attributes['telephoneNumber'] = $userData['phone'];
            }
            
            if (!empty($userData['department'])) {
                $attributes['department'] = $userData['department'];
            }
            
            logMessage('INFO', "Criando usuário: {$userDn}");
            
            $result = @ldap_add($this->connection, $userDn, $attributes);
            
            if (!$result) {
                $error = ldap_error($this->connection);
                logMessage('ERROR', "Falha ao criar usuário: {$error}");
                
                return [
                    'success' => false,
                    'message' => 'Falha ao criar usuário: ' . $error
                ];
            }
            
            logMessage('INFO', "Usuário {$userData['username']} criado com sucesso");
            
            return [
                'success' => true,
                'message' => 'Usuário criado com sucesso',
                'dn' => $userDn
            ];
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao criar usuário: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Erro ao criar usuário: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Bloquear/Desbloquear usuário
     */
    public function toggleUserStatus($username, $enable = true) {
        try {
            if (!$this->isConnected && !$this->connect()) {
                return [
                    'success' => false,
                    'message' => 'Conexão LDAP não disponível'
                ];
            }
            
            // Buscar o usuário para obter o DN
            $user = $this->getUser($username);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Usuário não encontrado'
                ];
            }
            
            $currentControl = (int)$user['account_control'];
            
            // Flag 2 = ACCOUNTDISABLE
            if ($enable) {
                // Habilitar: remover flag de desabilitado
                $newControl = $currentControl & ~2;
            } else {
                // Desabilitar: adicionar flag de desabilitado
                $newControl = $currentControl | 2;
            }
            
            $attributes = [
                'userAccountControl' => $newControl
            ];
            
            logMessage('INFO', "Alterando status do usuário {$username}: {$currentControl} -> {$newControl}");
            
            $result = @ldap_modify($this->connection, $user['dn'], $attributes);
            
            if (!$result) {
                $error = ldap_error($this->connection);
                logMessage('ERROR', "Falha ao alterar status do usuário: {$error}");
                
                return [
                    'success' => false,
                    'message' => 'Falha ao alterar status: ' . $error
                ];
            }
            
            $action = $enable ? 'habilitado' : 'desabilitado';
            logMessage('INFO', "Usuário {$username} {$action} com sucesso");
            
            return [
                'success' => true,
                'message' => "Usuário {$action} com sucesso"
            ];
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao alterar status do usuário: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Erro ao alterar status: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Resetar senha
     */
    public function resetPassword($username, $newPassword) {
        try {
            if (!$this->isConnected && !$this->connect()) {
                return [
                    'success' => false,
                    'message' => 'Conexão LDAP não disponível'
                ];
            }
            
            // Buscar o usuário para obter o DN
            $user = $this->getUser($username);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Usuário não encontrado'
                ];
            }
            
            // Codificar senha no formato Unicode para Active Directory
            $encodedPassword = mb_convert_encoding('"' . $newPassword . '"', 'UTF-16LE', 'UTF-8');
            
            $attributes = [
                'unicodePwd' => $encodedPassword
            ];
            
            logMessage('INFO', "Resetando senha para usuário: {$username}");
            
            $result = @ldap_modify($this->connection, $user['dn'], $attributes);
            
            if (!$result) {
                $error = ldap_error($this->connection);
                logMessage('ERROR', "Falha ao resetar senha: {$error}");
                
                return [
                    'success' => false,
                    'message' => 'Falha ao resetar senha: ' . $error
                ];
            }
            
            logMessage('INFO', "Senha resetada com sucesso para {$username}");
            
            return [
                'success' => true,
                'message' => 'Senha resetada com sucesso'
            ];
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao resetar senha: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Erro ao resetar senha: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obter estatísticas dos usuários
     */
    public function getUserStats() {
        try {
            if (!$this->isConnected && !$this->connect()) {
                logMessage('WARNING', 'Conexão LDAP não disponível para estatísticas');
                return [
                    'total' => 0,
                    'active' => 0,
                    'blocked' => 0,
                    'never_logged' => 0
                ];
            }
            
            $baseDn = $this->config['base_dn'] ?? 'DC=empresa,DC=local';
            
            $stats = [
                'total' => 0,
                'active' => 0,
                'blocked' => 0,
                'never_logged' => 0
            ];
            
            // Buscar todos os usuários
            $filter = '(&(objectClass=user)(!(objectClass=computer)))';
            $attributes = ['userAccountControl', 'lastLogon'];
            
            $result = @ldap_search($this->connection, $baseDn, $filter, $attributes);
            
            if (!$result) {
                logMessage('ERROR', 'Erro ao buscar estatísticas: ' . ldap_error($this->connection));
                return $stats;
            }
            
            $entries = ldap_get_entries($this->connection, $result);
            $stats['total'] = $entries['count'];
            
            for ($i = 0; $i < $entries['count']; $i++) {
                $entry = $entries[$i];
                $accountControl = (int)($entry['useraccountcontrol'][0] ?? 512);
                $lastLogon = $entry['lastlogon'][0] ?? '0';
                
                // Verificar se conta está bloqueada (flag 2)
                if ($accountControl & 2) {
                    $stats['blocked']++;
                } else {
                    $stats['active']++;
                }
                
                // Verificar se nunca fez logon
                if ($lastLogon == '0' || empty($lastLogon)) {
                    $stats['never_logged']++;
                }
            }
            
            logMessage('INFO', 'Estatísticas obtidas: ' . json_encode($stats));
            return $stats;
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao obter estatísticas: ' . $e->getMessage());
            return [
                'total' => 0,
                'active' => 0,
                'blocked' => 0,
                'never_logged' => 0
            ];
        }
    }
    
    /**
     * Construir DN do usuário para autenticação
     */
    private function buildUserDn($username) {
        $domain = $this->config['domain'] ?? 'empresa.local';
        
        // Se já contém @, usar como está
        if (strpos($username, '@') !== false) {
            return $username;
        }
        
        // Se contém \, usar como está (domain\user)
        if (strpos($username, '\\') !== false) {
            return $username;
        }
        
        // Caso contrário, adicionar domínio
        return $username . '@' . $domain;
    }
    
    /**
     * Construir DN do usuário a partir da configuração
     */
    private function buildUserDnFromConfig($username, $config) {
        $domain = $config['domain'] ?? 'empresa.local';
        
        // Se já contém @, usar como está
        if (strpos($username, '@') !== false) {
            return $username;
        }
        
        // Se contém \, usar como está (domain\user)
        if (strpos($username, '\\') !== false) {
            return $username;
        }
        
        // Caso contrário, adicionar domínio
        return $username . '@' . $domain;
    }
    
    /**
     * Retornar usuários de fallback quando LDAP não está disponível
     */
    private function getFallbackUsers($limit = 20, $search = '') {
        logMessage('WARNING', 'Usando dados de fallback - LDAP não disponível');
        
        $users = [
            [
                'dn' => 'CN=Administrador,CN=Users,DC=empresa,DC=local',
                'username' => 'admin',
                'name' => 'Administrador do Sistema',
                'email' => 'admin@empresa.local',
                'description' => 'Conta de administrador',
                'phone' => '+55 11 9999-0001',
                'department' => 'TI',
                'created' => '2024-01-01 10:00:00',
                'last_logon' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                'status' => 'Ativo',
                'account_control' => 512
            ]
        ];
        
        // Filtrar por busca se fornecida
        if (!empty($search)) {
            $users = array_filter($users, function($user) use ($search) {
                $searchLower = strtolower($search);
                return strpos(strtolower($user['username']), $searchLower) !== false ||
                       strpos(strtolower($user['name']), $searchLower) !== false ||
                       strpos(strtolower($user['email']), $searchLower) !== false;
            });
        }
        
        return array_slice($users, 0, $limit);
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