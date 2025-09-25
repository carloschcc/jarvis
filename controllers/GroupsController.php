<?php
/**
 * Controller para gerenciar grupos do Active Directory
 */

class GroupsController {
    private $ldapModel;
    
    public function __construct() {
        $this->ldapModel = new LdapModel();
    }
    
    /**
     * Busca grupos do Active Directory
     */
    public function getGroups() {
        header('Content-Type: application/json');
        
        try {
            // Verificar se há uma sessão ativa
            if (!isset($_SESSION['user_authenticated'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Não autenticado']);
                return;
            }
            
            // Verificar token CSRF (relaxado para demonstração)
            $headers = getallheaders();
            $csrfToken = $headers['X-CSRF-Token'] ?? $_POST['csrf_token'] ?? '';
            
            // Permitir bypass do CSRF se não houver token de sessão (modo demonstração)
            if (!empty($_SESSION['csrf_token']) && !$this->validateCSRFToken($csrfToken)) {
                http_response_code(403);
                echo json_encode(['error' => 'Token CSRF inválido']);
                return;
            }
            
            // Buscar grupos do AD
            $groups = $this->ldapModel->getADGroups();
            
            // Se não conseguir conectar ao AD, usar grupos de demonstração
            if ($groups === false) {
                logMessage('WARNING', 'Conexão AD falhou, usando grupos de demonstração');
                $groups = $this->getDemoGroups();
            }
            
            // Filtrar e formatar grupos
            $formattedGroups = [];
            foreach ($groups as $group) {
                $groupName = $group['cn'][0] ?? $group['name'][0] ?? 'Grupo Desconhecido';
                $description = $group['description'][0] ?? 'Grupo do Active Directory';
                
                // Pular grupos do sistema que não devem aparecer
                if (in_array(strtolower($groupName), ['domain computers', 'domain controllers', 'schema admins', 'enterprise admins'])) {
                    continue;
                }
                
                // Determinar ícone baseado no nome do grupo
                $icon = $this->getGroupIcon($groupName);
                $color = $this->getGroupColor($groupName);
                
                $formattedGroups[] = [
                    'id' => strtolower(str_replace([' ', '-'], '_', $groupName)),
                    'name' => $groupName,
                    'description' => $description,
                    'icon' => $icon,
                    'color' => $color,
                    'dn' => $group['dn'] ?? ''
                ];
            }
            
            // Ordenar grupos alfabeticamente
            usort($formattedGroups, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });
            
            echo json_encode([
                'success' => true,
                'groups' => $formattedGroups,
                'total' => count($formattedGroups)
            ]);
            
        } catch (Exception $e) {
            error_log("Erro ao buscar grupos AD: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'error' => 'Erro interno do servidor: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Determina o ícone apropriado para o grupo
     */
    private function getGroupIcon($groupName) {
        $name = strtolower($groupName);
        
        if (strpos($name, 'admin') !== false) return 'fas fa-crown';
        if (strpos($name, 'user') !== false) return 'fas fa-users';
        if (strpos($name, 'vpn') !== false) return 'fas fa-shield-alt';
        if (strpos($name, 'remote') !== false || strpos($name, 'rdp') !== false) return 'fas fa-desktop';
        if (strpos($name, 'power') !== false) return 'fas fa-bolt';
        if (strpos($name, 'backup') !== false) return 'fas fa-hdd';
        if (strpos($name, 'print') !== false) return 'fas fa-print';
        if (strpos($name, 'server') !== false) return 'fas fa-server';
        if (strpos($name, 'network') !== false) return 'fas fa-network-wired';
        if (strpos($name, 'guest') !== false) return 'fas fa-user-friends';
        if (strpos($name, 'account') !== false) return 'fas fa-user-cog';
        if (strpos($name, 'crypto') !== false) return 'fas fa-key';
        if (strpos($name, 'replic') !== false) return 'fas fa-copy';
        if (strpos($name, 'security') !== false) return 'fas fa-lock';
        if (strpos($name, 'it') !== false || strpos($name, 'tech') !== false) return 'fas fa-laptop-code';
        if (strpos($name, 'finance') !== false) return 'fas fa-dollar-sign';
        if (strpos($name, 'hr') !== false || strpos($name, 'rh') !== false) return 'fas fa-user-tie';
        if (strpos($name, 'sales') !== false || strpos($name, 'venda') !== false) return 'fas fa-handshake';
        
        return 'fas fa-users'; // Ícone padrão
    }
    
    /**
     * Determina a cor apropriada para o grupo
     */
    private function getGroupColor($groupName) {
        $name = strtolower($groupName);
        
        if (strpos($name, 'admin') !== false) return '#dc3545'; // Vermelho para admins
        if (strpos($name, 'security') !== false) return '#fd7e14'; // Laranja para segurança
        if (strpos($name, 'power') !== false) return '#ffc107'; // Amarelo para power users
        if (strpos($name, 'vpn') !== false) return '#0d6efd'; // Azul para VPN
        
        return '#6c757d'; // Cinza padrão
    }
    
    /**
     * Grupos de demonstração quando AD não está disponível
     */
    private function getDemoGroups() {
        return [
            [
                'cn' => ['IT Support'],
                'name' => ['IT Support'],
                'description' => ['Suporte técnico e infraestrutura'],
                'dn' => 'CN=IT Support,OU=Groups,DC=empresa,DC=local',
                'objectclass' => ['group'],
                'grouptype' => null
            ],
            [
                'cn' => ['Finance'],
                'name' => ['Finance'],
                'description' => ['Departamento financeiro'],
                'dn' => 'CN=Finance,OU=Groups,DC=empresa,DC=local',
                'objectclass' => ['group'],
                'grouptype' => null
            ],
            [
                'cn' => ['HR'],
                'name' => ['HR'],
                'description' => ['Recursos humanos'],
                'dn' => 'CN=HR,OU=Groups,DC=empresa,DC=local',
                'objectclass' => ['group'],
                'grouptype' => null
            ],
            [
                'cn' => ['Sales'],
                'name' => ['Sales'],
                'description' => ['Equipe de vendas'],
                'dn' => 'CN=Sales,OU=Groups,DC=empresa,DC=local',
                'objectclass' => ['group'],
                'grouptype' => null
            ],
            [
                'cn' => ['Marketing'],
                'name' => ['Marketing'],
                'description' => ['Departamento de marketing'],
                'dn' => 'CN=Marketing,OU=Groups,DC=empresa,DC=local',
                'objectclass' => ['group'],
                'grouptype' => null
            ],
            [
                'cn' => ['Operations'],
                'name' => ['Operations'],
                'description' => ['Operações e logística'],
                'dn' => 'CN=Operations,OU=Groups,DC=empresa,DC=local',
                'objectclass' => ['group'],
                'grouptype' => null
            ],
            [
                'cn' => ['Security Team'],
                'name' => ['Security Team'],
                'description' => ['Equipe de segurança da informação'],
                'dn' => 'CN=Security Team,OU=Groups,DC=empresa,DC=local',
                'objectclass' => ['group'],
                'grouptype' => null
            ],
            [
                'cn' => ['Project Managers'],
                'name' => ['Project Managers'],
                'description' => ['Gerentes de projeto'],
                'dn' => 'CN=Project Managers,OU=Groups,DC=empresa,DC=local',
                'objectclass' => ['group'],
                'grouptype' => null
            ]
        ];
    }
    
    /**
     * Valida token CSRF
     */
    private function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

// Processar requisições se chamado diretamente
if (basename($_SERVER['PHP_SELF']) === 'GroupsController.php') {
    session_start();
    
    // Incluir dependências
    require_once __DIR__ . '/../models/LdapModel.php';
    require_once __DIR__ . '/../config/database.php';
    
    $controller = new GroupsController();
    
    $action = $_GET['action'] ?? $_POST['action'] ?? 'getGroups';
    
    switch ($action) {
        case 'getGroups':
            $controller->getGroups();
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Ação inválida']);
    }
}
?>