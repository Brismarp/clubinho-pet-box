<?php
/**
 * CLUBINHO PET - Controller Admin
 * Painel administrativo
 * 
 * @package ClubinhoPet
 * @version 1.0.0
 */

class AdminController {
    
    private $userModel;
    private $petModel;
    private $subscriptionModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->petModel = new Pet();
        $this->subscriptionModel = new Subscription();
    }
    
    /**
     * Verificar se é admin
     */
    private function checkAdmin() {
        if (!is_admin()) {
            redirect(BASE_URL . '/admin/login');
            exit;
        }
    }
    
    /**
     * Dashboard admin
     */
    public function index() {
        $this->checkAdmin();
        
        // Buscar estatísticas
        $stats = $this->getStats();
        
        // Buscar assinaturas recentes
        $recentSubscriptions = $this->subscriptionModel->findAll();
        $recentSubscriptions = array_slice($recentSubscriptions, 0, 10);
        
        require_once __DIR__ . '/../admin/views/dashboard.php';
    }
    
    /**
     * Login admin
     */
    public function login() {
        // Se já está logado, redirecionar
        if (is_admin()) {
            redirect(ADMIN_URL);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Buscar admin
            $admin = db()->findOne('admin_users', 'email = ? AND status = ?', [$email, 'active']);
            
            if ($admin && password_verify($password, $admin['password'])) {
                // Criar sessão
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['is_admin'] = true;
                
                // Atualizar último login
                db()->update('admin_users', 
                    ['last_login' => date('Y-m-d H:i:s')],
                    'id = ?',
                    [$admin['id']]
                );
                
                log_activity('admin_login', $admin['id']);
                
                session_regenerate_id(true);
                redirect(ADMIN_URL);
                return;
            } else {
                $error = 'E-mail ou senha inválidos';
            }
        }
        
        require_once __DIR__ . '/../admin/views/login.php';
    }
    
    /**
     * Logout admin
     */
    public function logout() {
        log_activity('admin_logout', $_SESSION['admin_id'] ?? null);
        
        $_SESSION = [];
        session_destroy();
        
        redirect(ADMIN_URL . '/login');
    }
    
    /**
     * Listar usuários
     */
    public function users() {
        $this->checkAdmin();
        
        $filters = [
            'status' => $_GET['status'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];
        
        $users = $this->userModel->findAll($filters);
        
        require_once __DIR__ . '/../admin/views/users.php';
    }
    
    /**
     * Listar pets
     */
    public function pets() {
        $this->checkAdmin();
        
        $filters = [
            'type' => $_GET['type'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];
        
        $pets = $this->petModel->findAll($filters);
        
        // Buscar dados dos tutores
        foreach ($pets as &$pet) {
            $pet['user'] = $this->userModel->findById($pet['user_id']);
        }
        
        require_once __DIR__ . '/../admin/views/pets.php';
    }
    
    /**
     * Listar assinaturas
     */
    public function subscriptions() {
        $this->checkAdmin();
        
        $filters = [
            'status' => $_GET['status'] ?? '',
            'box_type' => $_GET['box_type'] ?? '',
            'plan' => $_GET['plan'] ?? ''
        ];
        
        $subscriptions = $this->subscriptionModel->findAll($filters);
        
        // Buscar dados relacionados
        foreach ($subscriptions as &$subscription) {
            $subscription['user'] = $this->userModel->findById($subscription['user_id']);
            $subscription['pet'] = $this->petModel->findById($subscription['pet_id']);
            $subscription['box_info'] = BOX_TYPES[$subscription['box_type']];
        }
        
        require_once __DIR__ . '/../admin/views/subscriptions.php';
    }
    
    /**
     * Listar boxes
     */
    public function boxes() {
        $this->checkAdmin();
        
        $filters = [
            'status' => $_GET['status'] ?? ''
        ];
        
        $where = '1=1';
        $params = [];
        
        if (!empty($filters['status'])) {
            $where .= ' AND status = ?';
            $params[] = $filters['status'];
        }
        
        $boxes = db()->findAll('boxes', $where, $params, 'created_at DESC');
        
        // Buscar dados relacionados
        foreach ($boxes as &$box) {
            $box['user'] = $this->userModel->findById($box['user_id']);
            $box['pet'] = $this->petModel->findById($box['pet_id']);
            $box['box_info'] = BOX_TYPES[$box['box_type']];
        }
        
        require_once __DIR__ . '/../admin/views/boxes.php';
    }
    
    /**
     * Atualizar status da box
     */
    public function updateBoxStatus() {
        $this->checkAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'error' => 'Método inválido'], 405);
        }
        
        $boxId = $_POST['box_id'] ?? null;
        $status = $_POST['status'] ?? null;
        $trackingCode = $_POST['tracking_code'] ?? '';
        
        if (!$boxId || !$status) {
            json_response(['success' => false, 'error' => 'Dados incompletos'], 400);
        }
        
        $data = ['status' => $status];
        
        if ($status === 'shipped') {
            $data['shipped_at'] = date('Y-m-d H:i:s');
            $data['tracking_code'] = $trackingCode;
        } elseif ($status === 'delivered') {
            $data['delivered_at'] = date('Y-m-d H:i:s');
        }
        
        $success = db()->update('boxes', $data, 'id = ?', [$boxId]);
        
        if ($success) {
            log_activity('box_status_updated', $_SESSION['admin_id'], [
                'box_id' => $boxId,
                'status' => $status
            ]);
            
            json_response(['success' => true, 'message' => 'Status atualizado com sucesso']);
        } else {
            json_response(['success' => false, 'error' => 'Erro ao atualizar status'], 500);
        }
    }
    
    /**
     * Exportar dados
     */
    public function export() {
        $this->checkAdmin();
        
        $type = $_GET['type'] ?? 'users';
        $format = $_GET['format'] ?? 'csv';
        
        switch ($type) {
            case 'users':
                $data = $this->userModel->findAll();
                $filename = 'usuarios_' . date('Y-m-d');
                break;
                
            case 'subscriptions':
                $data = $this->subscriptionModel->findAll();
                $filename = 'assinaturas_' . date('Y-m-d');
                break;
                
            case 'boxes':
                $data = db()->findAll('boxes', '1=1', [], 'created_at DESC');
                $filename = 'boxes_' . date('Y-m-d');
                break;
                
            default:
                die('Tipo inválido');
        }
        
        if ($format === 'csv') {
            $this->exportCSV($data, $filename);
        } else {
            die('Formato inválido');
        }
    }
    
    /**
     * Exportar CSV
     */
    private function exportCSV($data, $filename) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        if (!empty($data)) {
            // Cabeçalhos
            fputcsv($output, array_keys($data[0]));
            
            // Dados
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Obter estatísticas gerais
     */
    private function getStats() {
        $result = db()->queryOne("SELECT * FROM v_dashboard_stats");
        
        return [
            'total_users' => $result['total_users'] ?? 0,
            'active_subscriptions' => $result['active_subscriptions'] ?? 0,
            'delivered_boxes' => $result['delivered_boxes'] ?? 0,
            'monthly_revenue' => $result['monthly_revenue'] ?? 0,
            'average_rating' => $result['average_rating'] ?? 0,
            'total_pets' => $this->petModel->getStats()['total'] ?? 0
        ];
    }
    
    /**
     * Perfis de pets (filtro por perfil)
     */
    public function profiles() {
        $this->checkAdmin();
        
        $profileType = $_GET['profile'] ?? '';
        
        $where = 'is_active = ?';
        $params = [true];
        
        if ($profileType) {
            $where .= ' AND profile_type = ?';
            $params[] = $profileType;
        }
        
        $profiles = db()->findAll('pet_profiles', $where, $params, 'created_at DESC');
        
        // Buscar dados relacionados
        foreach ($profiles as &$profile) {
            $profile['pet'] = $this->petModel->findById($profile['pet_id']);
            $profile['user'] = $this->userModel->findById($profile['pet']['user_id']);
        }
        
        require_once __DIR__ . '/../admin/views/profiles.php';
    }
    
    /**
     * Métricas e relatórios
     */
    public function metrics() {
        $this->checkAdmin();
        
        // Estatísticas gerais
        $stats = $this->getStats();
        
        // Distribuição de perfis
        $profileDistribution = db()->query(
            "SELECT profile_type, COUNT(*) as count 
             FROM pet_profiles 
             WHERE is_active = 1 
             GROUP BY profile_type"
        );
        
        // Distribuição de boxes
        $boxDistribution = db()->query(
            "SELECT box_type, COUNT(*) as count 
             FROM subscriptions 
             WHERE status = 'active' 
             GROUP BY box_type"
        );
        
        // Receita por mês (últimos 12 meses)
        $monthlyRevenue = db()->query(
            "SELECT 
                DATE_FORMAT(paid_at, '%Y-%m') as month,
                SUM(amount) as revenue
             FROM payments
             WHERE status = 'paid'
             AND paid_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY month
             ORDER BY month ASC"
        );
        
        require_once __DIR__ . '/../admin/views/metrics.php';
    }
}
