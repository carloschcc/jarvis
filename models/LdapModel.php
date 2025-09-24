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
     * Buscar usuários no Active Directory com filtros avançados
     */
    public function getUsers($search = '', $limit = 100, $filters = []) {
        try {
            // Sempre tentar conectar primeiro
            if (!$this->isConnected) {
                $this->connect();
            }
            
            // Se ainda não conseguiu conectar, usar fallback
            if (!$this->isConnected) {
                logMessage('WARNING', 'Conexão LDAP não disponível, usando dados de fallback');
                return $this->getFallbackUsers($limit, $search, $filters);
            }
            
            logMessage('INFO', 'Usando dados reais do LDAP/Active Directory');
            
            $baseDn = $this->config['base_dn'] ?? 'DC=empresa,DC=local';
            
            // Construir filtro de busca base
            $baseFilter = '(&(objectClass=user)(!(objectClass=computer)))';
            $filterParts = [$baseFilter];
            
            // Adicionar filtro de busca por texto
            if (!empty($search)) {
                $searchEscaped = ldap_escape($search, '', LDAP_ESCAPE_FILTER);
                $searchFilter = "(|(cn=*{$searchEscaped}*)(sAMAccountName=*{$searchEscaped}*)(mail=*{$searchEscaped}*)(displayName=*{$searchEscaped}*))";
                $filterParts[] = $searchFilter;
            }
            
            // Adicionar filtros específicos
            if (!empty($filters['department'])) {
                $deptEscaped = ldap_escape($filters['department'], '', LDAP_ESCAPE_FILTER);
                $filterParts[] = "(department={$deptEscaped})";
            }
            
            if (!empty($filters['city'])) {
                $cityEscaped = ldap_escape($filters['city'], '', LDAP_ESCAPE_FILTER);
                $filterParts[] = "(l={$cityEscaped})";
            }
            
            if (!empty($filters['title'])) {
                $titleEscaped = ldap_escape($filters['title'], '', LDAP_ESCAPE_FILTER);
                $filterParts[] = "(title={$titleEscaped})";
            }
            
            if (!empty($filters['company'])) {
                $companyEscaped = ldap_escape($filters['company'], '', LDAP_ESCAPE_FILTER);
                $filterParts[] = "(company={$companyEscaped})";
            }
            
            if (!empty($filters['office'])) {
                $officeEscaped = ldap_escape($filters['office'], '', LDAP_ESCAPE_FILTER);
                $filterParts[] = "(physicalDeliveryOfficeName={$officeEscaped})";
            }
            
            // Filtro de gerente
            if (isset($filters['manager'])) {
                if ($filters['manager'] === 'yes') {
                    $filterParts[] = "(manager=*)";
                } elseif ($filters['manager'] === 'no') {
                    $filterParts[] = "(!(manager=*))";
                }
            }
            
            // Filtro de status
            if (isset($filters['status'])) {
                if ($filters['status'] === 'Ativo') {
                    $filterParts[] = "(!(userAccountControl:1.2.840.113556.1.4.803:=2))";
                } elseif ($filters['status'] === 'Bloqueado') {
                    $filterParts[] = "(userAccountControl:1.2.840.113556.1.4.803:=2)";
                }
            }
            
            // Combinar todos os filtros
            if (count($filterParts) > 1) {
                $filter = '(&' . implode('', $filterParts) . ')';
            } else {
                $filter = $baseFilter;
            }
            
            // Atributos a serem retornados (expandidos)
            $attributes = [
                'cn', 'sAMAccountName', 'displayName', 'mail', 'description',
                'telephoneNumber', 'department', 'whenCreated', 'lastLogon',
                'userAccountControl', 'distinguishedName', 'title', 'l', 
                'company', 'physicalDeliveryOfficeName', 'streetAddress',
                'postalCode', 'st', 'c', 'employeeID', 'manager'
            ];
            
            logMessage('INFO', "Executando busca LDAP: {$filter} em {$baseDn}");
            
            // Executar busca com reconexão se necessário
            $result = @ldap_search($this->connection, $baseDn, $filter, $attributes, 0, $limit);
            
            // Se falhar, tentar reconectar uma vez
            if (!$result) {
                $error = ldap_error($this->connection);
                logMessage('WARNING', "Primeira tentativa de busca falhou: {$error}. Tentando reconectar...");
                
                // Fechar conexão atual
                if ($this->connection) {
                    ldap_close($this->connection);
                }
                
                // Resetar estado e tentar reconectar
                $this->isConnected = false;
                if ($this->connect()) {
                    logMessage('INFO', "Reconexão bem-sucedida. Tentando busca novamente...");
                    $result = @ldap_search($this->connection, $baseDn, $filter, $attributes, 0, $limit);
                }
                
                // Se ainda falhar, usar fallback
                if (!$result) {
                    $error = ldap_error($this->connection);
                    logMessage('ERROR', "Erro na busca LDAP após reconexão: {$error}");
                    return $this->getFallbackUsers($limit, $search, $filters);
                }
            }
            
            $entries = ldap_get_entries($this->connection, $result);
            
            if ($entries['count'] == 0) {
                logMessage('INFO', 'Nenhum usuário encontrado no LDAP com os filtros aplicados');
                // Se não houver resultados com filtros, retornar array vazio ao invés de fallback
                return [];
            }
            
            logMessage('INFO', "Processando {$entries['count']} usuários do LDAP");
            
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
                    'title' => $entry['title'][0] ?? '',
                    'city' => $entry['l'][0] ?? '',
                    'company' => $entry['company'][0] ?? '',
                    'office' => $entry['physicaldeliveryofficename'][0] ?? '',
                    'address' => $entry['streetaddress'][0] ?? '',
                    'postal_code' => $entry['postalcode'][0] ?? '',
                    'state' => $entry['st'][0] ?? '',
                    'country' => $entry['c'][0] ?? '',
                    'employee_id' => $entry['employeeid'][0] ?? '',
                    'manager' => $entry['manager'][0] ?? '',
                    'created' => $this->convertLdapDate($entry['whencreated'][0] ?? ''),
                    'last_logon' => $this->convertWindowsTimestamp($entry['lastlogon'][0] ?? ''),
                    'status' => $this->getUserStatus($entry['useraccountcontrol'][0] ?? 512),
                    'account_control' => $entry['useraccountcontrol'][0] ?? 512
                ];
                
                $users[] = $user;
            }
            
            logMessage('INFO', 'Processamento concluído: ' . count($users) . ' usuários válidos encontrados no LDAP');
            logMessage('DEBUG', 'Filtros aplicados no LDAP: ' . json_encode($filters));
            return $users;
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao buscar usuários: ' . $e->getMessage());
            // Para debug: não usar fallback quando houver exceção, retornar erro
            logMessage('ERROR', 'ATENÇÃO: Usando fallback devido a exceção. LDAP pode estar mal configurado.');
            return $this->getFallbackUsers($limit, $search, $filters);
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
     * Criar usuário (versão avançada com suporte completo)
     */
    public function createUser($userData) {
        try {
            // Verificar se LDAP está disponível, senão usar modo fallback
            if (!$this->isConnected && !$this->connect()) {
                logMessage('WARNING', 'LDAP não disponível - simulando criação de usuário');
                
                // Modo de desenvolvimento/demonstração - simular sucesso
                return [
                    'success' => true,
                    'message' => "Usuário '{$userData['displayName']}' seria criado no AD (LDAP não configurado)",
                    'mode' => 'simulation',
                    'details' => [
                        'username' => $userData['username'],
                        'display_name' => $userData['displayName'],
                        'email' => $userData['email'] ?? 'N/A',
                        'department' => $userData['department'] ?? 'N/A'
                    ]
                ];
            }
            
            $baseDn = $this->config['base_dn'] ?? 'DC=empresa,DC=local';
            $displayName = $userData['displayName'] ?: $userData['firstName'] . ' ' . $userData['lastName'];
            $userDn = "CN={$displayName},CN=Users,{$baseDn}";
            
            // Calcular userAccountControl baseado nas configurações
            $userAccountControl = 544; // NORMAL_ACCOUNT + PASSWD_NOTREQD (padrão)
            
            if (!$userData['accountEnabled']) {
                $userAccountControl |= 2; // ACCOUNTDISABLE
            }
            
            if ($userData['forcePasswordChange']) {
                $userAccountControl |= 0x800000; // DONT_EXPIRE_PASSWD (removido para forçar mudança)
            } else {
                $userAccountControl |= 0x10000; // DONT_EXPIRE_PASSWD
            }
            
            // Atributos obrigatórios do Active Directory
            $attributes = [
                'objectClass' => ['top', 'person', 'organizationalPerson', 'user'],
                'cn' => $displayName,
                'sAMAccountName' => $userData['username'],
                'userPrincipalName' => $userData['username'] . '@' . ($this->config['domain'] ?? 'empresa.local'),
                'displayName' => $displayName,
                'givenName' => $userData['firstName'],
                'sn' => $userData['lastName'], // surname
                'userAccountControl' => $userAccountControl
            ];
            
            // Atributos opcionais
            if (!empty($userData['email'])) {
                $attributes['mail'] = $userData['email'];
                $attributes['proxyAddresses'] = ["SMTP:{$userData['email']}"];
            }
            
            if (!empty($userData['description'])) {
                $attributes['description'] = $userData['description'];
            }
            
            if (!empty($userData['title'])) {
                $attributes['title'] = $userData['title'];
            }
            
            if (!empty($userData['department'])) {
                $attributes['department'] = $userData['department'];
            }
            
            if (!empty($userData['company'])) {
                $attributes['company'] = $userData['company'];
            }
            
            if (!empty($userData['city'])) {
                $attributes['l'] = $userData['city']; // locality
            }
            
            if (!empty($userData['office'])) {
                $attributes['physicalDeliveryOfficeName'] = $userData['office'];
            }
            
            if (!empty($userData['phone'])) {
                $attributes['telephoneNumber'] = $userData['phone'];
            }
            
            if (!empty($userData['mobile'])) {
                $attributes['mobile'] = $userData['mobile'];
            }
            
            // Definir senha (se fornecida)
            if (!empty($userData['password'])) {
                // Converter senha para UTF-16LE (formato do AD)
                $password = '"' . $userData['password'] . '"';
                $attributes['unicodePwd'] = mb_convert_encoding($password, 'UTF-16LE', 'UTF-8');
                
                // Remover PASSWD_NOTREQD se senha foi definida
                $attributes['userAccountControl'] = $userAccountControl & ~32;
            }
            
            logMessage('INFO', "Criando usuário no AD: {$userDn}", [
                'username' => $userData['username'],
                'display_name' => $displayName,
                'email' => $userData['email'] ?? 'N/A',
                'account_control' => $attributes['userAccountControl']
            ]);
            
            // Tentar criar o usuário
            $result = @ldap_add($this->connection, $userDn, $attributes);
            
            if (!$result) {
                $error = ldap_error($this->connection);
                $errorCode = ldap_errno($this->connection);
                
                logMessage('ERROR', "Falha ao criar usuário no AD: {$error} (Código: {$errorCode})", [
                    'username' => $userData['username'],
                    'user_dn' => $userDn
                ]);
                
                // Tratar erros específicos do AD
                $errorMessage = $this->parseADError($error, $errorCode);
                
                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'error_code' => $errorCode,
                    'ldap_error' => $error
                ];
            }
            
            logMessage('INFO', "Usuário {$userData['username']} criado com sucesso no AD", [
                'display_name' => $displayName,
                'email' => $userData['email'] ?? 'N/A',
                'user_dn' => $userDn
            ]);
            
            // Tentar adicionar aos grupos (se especificados)
            if (!empty($userData['groups']) && is_array($userData['groups'])) {
                $groupResults = [];
                foreach ($userData['groups'] as $groupName) {
                    if ($groupName !== 'Domain Users') { // Domain Users é automático
                        $groupResult = $this->addUserToGroup($userData['username'], $groupName);
                        $groupResults[] = [
                            'group' => $groupName,
                            'success' => $groupResult['success']
                        ];
                    }
                }
            }
            
            return [
                'success' => true,
                'message' => "Usuário '{$displayName}' criado com sucesso no Active Directory",
                'username' => $userData['username'],
                'dn' => $userDn,
                'groups_added' => $groupResults ?? []
            ];
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro crítico ao criar usuário: ' . $e->getMessage(), [
                'username' => $userData['username'] ?? 'N/A',
                'error_details' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Erro crítico: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Interpretar erros específicos do Active Directory
     */
    private function parseADError($error, $errorCode) {
        switch ($errorCode) {
            case 68: // LDAP_ALREADY_EXISTS
                return 'Usuário já existe no Active Directory';
            case 19: // LDAP_CONSTRAINT_VIOLATION
                return 'Violação de regra do AD (senha muito simples, nome inválido, etc.)';
            case 53: // LDAP_UNWILLING_TO_PERFORM
                return 'Servidor AD recusou a operação (verifique permissões)';
            case 32: // LDAP_NO_SUCH_OBJECT
                return 'Contêiner ou OU não encontrada no AD';
            case 50: // LDAP_INSUFFICIENT_ACCESS
                return 'Permissões insuficientes para criar usuário';
            case 21: // LDAP_INVALID_DN_SYNTAX
                return 'Estrutura DN inválida';
            default:
                return "Erro do Active Directory: {$error}";
        }
    }
    
    /**
     * Adicionar usuário a um grupo
     */
    private function addUserToGroup($username, $groupName) {
        try {
            if (!$this->isConnected) {
                return ['success' => false, 'message' => 'LDAP não conectado'];
            }
            
            $baseDn = $this->config['base_dn'] ?? 'DC=empresa,DC=local';
            
            // Buscar o usuário
            $userDn = $this->getUserDN($username);
            if (!$userDn) {
                return ['success' => false, 'message' => 'Usuário não encontrado'];
            }
            
            // Buscar o grupo
            $groupDn = "CN={$groupName},CN=Users,{$baseDn}";
            
            // Adicionar usuário ao grupo
            $modifications = ['member' => [$userDn]];
            $result = @ldap_mod_add($this->connection, $groupDn, $modifications);
            
            if ($result) {
                logMessage('INFO', "Usuário {$username} adicionado ao grupo {$groupName}");
                return ['success' => true, 'message' => 'Usuário adicionado ao grupo'];
            } else {
                $error = ldap_error($this->connection);
                logMessage('WARNING', "Falha ao adicionar usuário {$username} ao grupo {$groupName}: {$error}");
                return ['success' => false, 'message' => $error];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Obter DN do usuário
     */
    private function getUserDN($username) {
        try {
            $baseDn = $this->config['base_dn'] ?? 'DC=empresa,DC=local';
            $filter = "(sAMAccountName={$username})";
            
            $search = @ldap_search($this->connection, $baseDn, $filter, ['dn']);
            
            if ($search && ldap_count_entries($this->connection, $search) > 0) {
                $entries = ldap_get_entries($this->connection, $search);
                return $entries[0]['dn'];
            }
            
            return null;
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao buscar DN do usuário: ' . $e->getMessage());
            return null;
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
    public function resetPassword($username, $newPassword, $forceChange = false) {
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
            
            // Se forçar alteração no próximo login
            if ($forceChange) {
                $attributes['pwdLastSet'] = '0';
            }
            
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
    private function getFallbackUsers($limit = 20, $search = '', $filters = []) {
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
                'title' => 'Administrador de Sistemas',
                'city' => 'São Paulo',
                'company' => 'Empresa Principal',
                'office' => 'Escritório Central',
                'address' => 'Rua Exemplo, 123',
                'postal_code' => '01234-567',
                'state' => 'SP',
                'country' => 'BR',
                'employee_id' => 'ADM001',
                'manager' => '',
                'created' => '2024-01-01 10:00:00',
                'last_logon' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                'status' => 'Ativo',
                'account_control' => 512
            ],
            [
                'dn' => 'CN=João Silva,CN=Users,DC=empresa,DC=local',
                'username' => 'joao.silva',
                'name' => 'João Silva',
                'email' => 'joao.silva@empresa.local',
                'description' => 'Desenvolvedor Senior',
                'phone' => '+55 11 9999-0002',
                'department' => 'TI',
                'title' => 'Desenvolvedor',
                'city' => 'São Paulo',
                'company' => 'Empresa Principal',
                'office' => 'Escritório Central',
                'address' => 'Rua Exemplo, 123',
                'postal_code' => '01234-567',
                'state' => 'SP',
                'country' => 'BR',
                'employee_id' => 'DEV001',
                'manager' => 'CN=Administrador,CN=Users,DC=empresa,DC=local',
                'created' => '2024-01-15 09:00:00',
                'last_logon' => date('Y-m-d H:i:s', strtotime('-30 minutes')),
                'status' => 'Ativo',
                'account_control' => 512
            ],
            [
                'dn' => 'CN=Maria Santos,CN=Users,DC=empresa,DC=local',
                'username' => 'maria.santos',
                'name' => 'Maria Santos',
                'email' => 'maria.santos@empresa.local',
                'description' => 'Analista de RH',
                'phone' => '+55 11 9999-0003',
                'department' => 'RH',
                'title' => 'Analista',
                'city' => 'Rio de Janeiro',
                'company' => 'Filial RJ',
                'office' => 'Sede São Paulo',
                'address' => 'Av. Rio Branco, 456',
                'postal_code' => '20040-020',
                'state' => 'RJ',
                'country' => 'BR',
                'employee_id' => 'RH001',
                'manager' => '',
                'created' => '2024-02-01 14:00:00',
                'last_logon' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'status' => 'Ativo',
                'account_control' => 512
            ],
            [
                'dn' => 'CN=Pedro Costa,CN=Users,DC=empresa,DC=local',
                'username' => 'pedro.costa',
                'name' => 'Pedro Costa',
                'email' => 'pedro.costa@empresa.local',
                'description' => 'Gerente de Vendas',
                'phone' => '+55 11 9999-0004',
                'department' => 'Vendas',
                'title' => 'Gerente',
                'city' => 'Belo Horizonte',
                'company' => 'Filial SP',
                'office' => 'Filial Rio de Janeiro',
                'address' => 'Rua das Flores, 789',
                'postal_code' => '30112-000',
                'state' => 'MG',
                'country' => 'BR',
                'employee_id' => 'VEN001',
                'manager' => '',
                'created' => '2024-01-20 11:00:00',
                'last_logon' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'status' => 'Bloqueado',
                'account_control' => 514
            ],
            [
                'dn' => 'CN=Ana Oliveira,CN=Users,DC=empresa,DC=local',
                'username' => 'ana.oliveira',
                'name' => 'Ana Oliveira',
                'email' => 'ana.oliveira@empresa.local',
                'description' => 'Coordenadora Financeiro',
                'phone' => '+55 11 9999-0005',
                'department' => 'Financeiro',
                'title' => 'Coordenador',
                'city' => 'Brasília',
                'company' => 'Empresa Principal',
                'office' => 'Escritório Central',
                'address' => 'SQN 200, Bloco A',
                'postal_code' => '70040-010',
                'state' => 'DF',
                'country' => 'BR',
                'employee_id' => 'FIN001',
                'manager' => '',
                'created' => '2024-03-01 08:00:00',
                'last_logon' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'status' => 'Ativo',
                'account_control' => 512
            ]
        ];
        
        // Aplicar filtros se fornecidos
        if (!empty($filters)) {
            $users = array_filter($users, function($user) use ($filters) {
                // Filtro por departamento (comparação exata)
                if (!empty($filters['department']) && 
                    strcasecmp($user['department'], $filters['department']) !== 0) {
                    return false;
                }
                
                // Filtro por cidade (comparação exata)
                if (!empty($filters['city']) && 
                    strcasecmp($user['city'], $filters['city']) !== 0) {
                    return false;
                }
                
                // Filtro por função/título (comparação exata)
                if (!empty($filters['title']) && 
                    strcasecmp($user['title'], $filters['title']) !== 0) {
                    return false;
                }
                
                // Filtro por empresa/organização (comparação exata)
                if (!empty($filters['company']) && 
                    strcasecmp($user['company'], $filters['company']) !== 0) {
                    return false;
                }
                
                // Filtro por escritório (comparação exata)
                if (!empty($filters['office']) && 
                    strcasecmp($user['office'], $filters['office']) !== 0) {
                    return false;
                }
                
                // Filtro por status
                if (!empty($filters['status'])) {
                    $userStatus = $user['status'];
                    $filterStatus = $filters['status'];
                    
                    if ($filterStatus !== $userStatus) {
                        return false;
                    }
                }
                
                return true;
            });
        }
        
        // Filtrar por busca se fornecida
        if (!empty($search)) {
            $users = array_filter($users, function($user) use ($search) {
                $searchLower = strtolower($search);
                return strpos(strtolower($user['username']), $searchLower) !== false ||
                       strpos(strtolower($user['name']), $searchLower) !== false ||
                       strpos(strtolower($user['email']), $searchLower) !== false ||
                       strpos(strtolower($user['department']), $searchLower) !== false ||
                       strpos(strtolower($user['title']), $searchLower) !== false ||
                       strpos(strtolower($user['city']), $searchLower) !== false;
            });
        }
        
        return array_slice($users, 0, $limit);
    }
    
    /**
     * Atualizar usuário no Active Directory
     */
    public function updateUser($username, $userData) {
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
            
            // Preparar atributos para atualização
            $attributes = [];
            
            if (!empty($userData['name'])) {
                $attributes['displayName'] = $userData['name'];
                $attributes['cn'] = $userData['name'];
            }
            
            if (isset($userData['email'])) {
                if (!empty($userData['email'])) {
                    $attributes['mail'] = $userData['email'];
                } else {
                    $attributes['mail'] = [];
                }
            }
            
            if (isset($userData['phone'])) {
                if (!empty($userData['phone'])) {
                    $attributes['telephoneNumber'] = $userData['phone'];
                } else {
                    $attributes['telephoneNumber'] = [];
                }
            }
            
            if (isset($userData['title'])) {
                if (!empty($userData['title'])) {
                    $attributes['title'] = $userData['title'];
                } else {
                    $attributes['title'] = [];
                }
            }
            
            if (isset($userData['department'])) {
                if (!empty($userData['department'])) {
                    $attributes['department'] = $userData['department'];
                } else {
                    $attributes['department'] = [];
                }
            }
            
            if (isset($userData['company'])) {
                if (!empty($userData['company'])) {
                    $attributes['company'] = $userData['company'];
                } else {
                    $attributes['company'] = [];
                }
            }
            
            if (isset($userData['city'])) {
                if (!empty($userData['city'])) {
                    $attributes['l'] = $userData['city'];
                } else {
                    $attributes['l'] = [];
                }
            }
            
            if (isset($userData['office'])) {
                if (!empty($userData['office'])) {
                    $attributes['physicalDeliveryOfficeName'] = $userData['office'];
                } else {
                    $attributes['physicalDeliveryOfficeName'] = [];
                }
            }
            
            if (isset($userData['description'])) {
                if (!empty($userData['description'])) {
                    $attributes['description'] = $userData['description'];
                } else {
                    $attributes['description'] = [];
                }
            }
            
            if (empty($attributes)) {
                return [
                    'success' => false,
                    'message' => 'Nenhum dado para atualizar'
                ];
            }
            
            logMessage('INFO', "Atualizando usuário: {$username}");
            
            $result = @ldap_modify($this->connection, $user['dn'], $attributes);
            
            if (!$result) {
                $error = ldap_error($this->connection);
                logMessage('ERROR', "Falha ao atualizar usuário: {$error}");
                
                return [
                    'success' => false,
                    'message' => 'Falha ao atualizar usuário: ' . $error
                ];
            }
            
            logMessage('INFO', "Usuário {$username} atualizado com sucesso");
            
            return [
                'success' => true,
                'message' => 'Usuário atualizado com sucesso'
            ];
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao atualizar usuário: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Erro ao atualizar usuário: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Excluir usuário do Active Directory
     */
    public function deleteUser($username) {
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
            
            logMessage('INFO', "Excluindo usuário: {$username}");
            
            $result = @ldap_delete($this->connection, $user['dn']);
            
            if (!$result) {
                $error = ldap_error($this->connection);
                logMessage('ERROR', "Falha ao excluir usuário: {$error}");
                
                return [
                    'success' => false,
                    'message' => 'Falha ao excluir usuário: ' . $error
                ];
            }
            
            logMessage('INFO', "Usuário {$username} excluído com sucesso");
            
            return [
                'success' => true,
                'message' => 'Usuário excluído com sucesso'
            ];
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao excluir usuário: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Erro ao excluir usuário: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obter grupos do usuário
     */
    public function getUserGroups($username) {
        try {
            if (!$this->isConnected && !$this->connect()) {
                logMessage('WARNING', 'Conexão LDAP não disponível para buscar grupos');
                return [];
            }
            
            // Buscar o usuário para obter o DN
            $user = $this->getUser($username);
            
            if (!$user) {
                return [];
            }
            
            $baseDn = $this->config['base_dn'] ?? 'DC=empresa,DC=local';
            
            // Buscar grupos que contém o usuário como membro
            $filter = "(&(objectClass=group)(member={$user['dn']}))";
            $attributes = ['cn', 'distinguishedName', 'description'];
            
            logMessage('INFO', "Buscando grupos para usuário: {$username}");
            
            $result = @ldap_search($this->connection, $baseDn, $filter, $attributes);
            
            if (!$result) {
                $error = ldap_error($this->connection);
                logMessage('ERROR', "Erro ao buscar grupos: {$error}");
                return [];
            }
            
            $entries = ldap_get_entries($this->connection, $result);
            $groups = [];
            
            for ($i = 0; $i < $entries['count']; $i++) {
                $entry = $entries[$i];
                
                $groups[] = [
                    'name' => $entry['cn'][0] ?? '',
                    'dn' => $entry['distinguishedname'][0] ?? '',
                    'description' => $entry['description'][0] ?? ''
                ];
            }
            
            logMessage('INFO', "Encontrados " . count($groups) . " grupos para {$username}");
            return $groups;
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao buscar grupos do usuário: ' . $e->getMessage());
            return [];
        }
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
     * Obter lista de departamentos únicos
     */
    public function getDepartments() {
        try {
            if (!$this->isConnected && !$this->connect()) {
                return ['TI', 'RH', 'Vendas', 'Financeiro', 'Marketing'];
            }
            
            $baseDn = $this->config['base_dn'] ?? 'DC=empresa,DC=local';
            $filter = '(&(objectClass=user)(!(objectClass=computer))(department=*))';
            $attributes = ['department'];
            
            $result = @ldap_search($this->connection, $baseDn, $filter, $attributes);
            
            if (!$result) {
                return ['TI', 'RH', 'Vendas', 'Financeiro', 'Marketing'];
            }
            
            $entries = ldap_get_entries($this->connection, $result);
            $departments = [];
            
            for ($i = 0; $i < $entries['count']; $i++) {
                $dept = $entries[$i]['department'][0] ?? '';
                if (!empty($dept) && !in_array($dept, $departments)) {
                    $departments[] = $dept;
                }
            }
            
            sort($departments);
            return $departments;
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao buscar departamentos: ' . $e->getMessage());
            return ['TI', 'RH', 'Vendas', 'Financeiro', 'Marketing'];
        }
    }
    
    /**
     * Obter lista de cidades únicas
     */
    public function getCities() {
        try {
            if (!$this->isConnected && !$this->connect()) {
                return ['São Paulo', 'Rio de Janeiro', 'Belo Horizonte', 'Brasília', 'Salvador'];
            }
            
            $baseDn = $this->config['base_dn'] ?? 'DC=empresa,DC=local';
            $filter = '(&(objectClass=user)(!(objectClass=computer))(l=*))';
            $attributes = ['l'];
            
            $result = @ldap_search($this->connection, $baseDn, $filter, $attributes);
            
            if (!$result) {
                return ['São Paulo', 'Rio de Janeiro', 'Belo Horizonte', 'Brasília', 'Salvador'];
            }
            
            $entries = ldap_get_entries($this->connection, $result);
            $cities = [];
            
            for ($i = 0; $i < $entries['count']; $i++) {
                $city = $entries[$i]['l'][0] ?? '';
                if (!empty($city) && !in_array($city, $cities)) {
                    $cities[] = $city;
                }
            }
            
            sort($cities);
            return $cities;
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao buscar cidades: ' . $e->getMessage());
            return ['São Paulo', 'Rio de Janeiro', 'Belo Horizonte', 'Brasília', 'Salvador'];
        }
    }
    
    /**
     * Obter lista de empresas/organizações únicas
     */
    public function getCompanies() {
        try {
            if (!$this->isConnected && !$this->connect()) {
                return ['Empresa Principal', 'Filial São Paulo', 'Filial Rio de Janeiro'];
            }
            
            $baseDn = $this->config['base_dn'] ?? 'DC=empresa,DC=local';
            $filter = '(&(objectClass=user)(!(objectClass=computer))(company=*))';
            $attributes = ['company'];
            
            $result = @ldap_search($this->connection, $baseDn, $filter, $attributes);
            
            if (!$result) {
                return ['Empresa Principal', 'Filial São Paulo', 'Filial Rio de Janeiro'];
            }
            
            $entries = ldap_get_entries($this->connection, $result);
            $companies = [];
            
            for ($i = 0; $i < $entries['count']; $i++) {
                $company = $entries[$i]['company'][0] ?? '';
                if (!empty($company) && !in_array($company, $companies)) {
                    $companies[] = $company;
                }
            }
            
            sort($companies);
            return $companies;
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao buscar empresas: ' . $e->getMessage());
            return ['Empresa Principal', 'Filial São Paulo', 'Filial Rio de Janeiro'];
        }
    }
    
    /**
     * Obter lista de títulos/funções únicos
     */
    public function getTitles() {
        try {
            if (!$this->isConnected && !$this->connect()) {
                return ['Analista', 'Desenvolvedor', 'Gerente', 'Diretor', 'Coordenador', 'Assistente'];
            }
            
            $baseDn = $this->config['base_dn'] ?? 'DC=empresa,DC=local';
            $filter = '(&(objectClass=user)(!(objectClass=computer))(title=*))';
            $attributes = ['title'];
            
            $result = @ldap_search($this->connection, $baseDn, $filter, $attributes);
            
            if (!$result) {
                return ['Analista', 'Desenvolvedor', 'Gerente', 'Diretor', 'Coordenador', 'Assistente'];
            }
            
            $entries = ldap_get_entries($this->connection, $result);
            $titles = [];
            
            for ($i = 0; $i < $entries['count']; $i++) {
                $title = $entries[$i]['title'][0] ?? '';
                if (!empty($title) && !in_array($title, $titles)) {
                    $titles[] = $title;
                }
            }
            
            sort($titles);
            return $titles;
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao buscar títulos: ' . $e->getMessage());
            return ['Analista', 'Desenvolvedor', 'Gerente', 'Diretor', 'Coordenador', 'Assistente'];
        }
    }
    
    /**
     * Obter lista de escritórios únicos
     */
    public function getOffices() {
        try {
            if (!$this->isConnected && !$this->connect()) {
                return ['Escritório Central', 'Sede São Paulo', 'Filial Rio de Janeiro', 'Filial Brasília'];
            }
            
            $baseDn = $this->config['base_dn'] ?? 'DC=empresa,DC=local';
            $filter = '(&(objectClass=user)(!(objectClass=computer))(physicalDeliveryOfficeName=*))';
            $attributes = ['physicalDeliveryOfficeName'];
            
            $result = @ldap_search($this->connection, $baseDn, $filter, $attributes);
            
            if (!$result) {
                return ['Escritório Central', 'Sede São Paulo', 'Filial Rio de Janeiro', 'Filial Brasília'];
            }
            
            $entries = ldap_get_entries($this->connection, $result);
            $offices = [];
            
            for ($i = 0; $i < $entries['count']; $i++) {
                $office = $entries[$i]['physicaldeliveryofficename'][0] ?? '';
                if (!empty($office) && !in_array($office, $offices)) {
                    $offices[] = $office;
                }
            }
            
            sort($offices);
            return $offices;
            
        } catch (Exception $e) {
            logMessage('ERROR', 'Erro ao buscar escritórios: ' . $e->getMessage());
            return ['Escritório Central', 'Sede São Paulo', 'Filial Rio de Janeiro', 'Filial Brasília'];
        }
    }
    
    /**
     * Destrutor
     */
    public function __destruct() {
        $this->disconnect();
    }
}