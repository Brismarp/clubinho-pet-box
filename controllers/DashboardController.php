<?php
/**
 * CLUBINHO PET - Controller Dashboard
 * Área do tutor
 * 
 * @package ClubinhoPet
 * @version 1.0.0
 */

class DashboardController {
    
    private $userModel;
    private $petModel;
    private $subscriptionModel;
    private $quizModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->petModel = new Pet();
        $this->subscriptionModel = new Subscription();
        $this->quizModel = new Quiz();
    }
    
    /**
     * Dashboard principal
     */
    public function index() {
        // Verificar se está logado
        if (!is_logged_in()) {
            redirect(BASE_URL . '/login');
            return;
        }
        
        $user = get_current_user();
        
        // Buscar dados do usuário
        $userData = $this->userModel->findById($user['id']);
        $stats = $this->userModel->getStats($user['id']);
        $pets = $this->petModel->findByUserId($user['id']);
        $subscriptions = $this->subscriptionModel->findByUserId($user['id']);
        
        // Buscar próximas entregas
        $nextDeliveries = $this->getNextDeliveries($user['id']);
        
        // Carregar view
        require_once __DIR__ . '/../views/dashboard/index.php';
    }
    
    /**
     * Perfil do usuário
     */
    public function profile() {
        if (!is_logged_in()) {
            redirect(BASE_URL . '/login');
            return;
        }
        
        $user = get_current_user();
        $userData = $this->userModel->findById($user['id']);
        
        require_once __DIR__ . '/../views/dashboard/profile.php';
    }
    
    /**
     * Atualizar perfil
     */
    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(BASE_URL . '/dashboard/profile');
            return;
        }
        
        if (!is_logged_in()) {
            json_response(['success' => false, 'error' => 'Não autenticado'], 401);
        }
        
        $user = get_current_user();
        
        // Validar CSRF
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            json_response(['success' => false, 'error' => 'Token inválido'], 403);
        }
        
        // Atualizar dados
        $data = [
            'name' => $_POST['name'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'document' => $_POST['document'] ?? '',
            'birth_date' => $_POST['birth_date'] ?? null,
            'address_zipcode' => $_POST['address_zipcode'] ?? '',
            'address_street' => $_POST['address_street'] ?? '',
            'address_number' => $_POST['address_number'] ?? '',
            'address_complement' => $_POST['address_complement'] ?? '',
            'address_neighborhood' => $_POST['address_neighborhood'] ?? '',
            'address_city' => $_POST['address_city'] ?? '',
            'address_state' => $_POST['address_state'] ?? ''
        ];
        
        $success = $this->userModel->update($user['id'], $data);
        
        if ($success) {
            // Atualizar sessão
            $_SESSION['user_name'] = $data['name'];
            json_response(['success' => true, 'message' => 'Perfil atualizado com sucesso']);
        } else {
            json_response(['success' => false, 'error' => 'Erro ao atualizar perfil'], 500);
        }
    }
    
    /**
     * Listar pets
     */
    public function pets() {
        if (!is_logged_in()) {
            redirect(BASE_URL . '/login');
            return;
        }
        
        $user = get_current_user();
        $pets = $this->petModel->findByUserId($user['id']);
        
        require_once __DIR__ . '/../views/dashboard/pets.php';
    }
    
    /**
     * Adicionar pet
     */
    public function addPet() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(BASE_URL . '/dashboard/pets');
            return;
        }
        
        if (!is_logged_in()) {
            json_response(['success' => false, 'error' => 'Não autenticado'], 401);
        }
        
        $user = get_current_user();
        
        // Validar CSRF
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            json_response(['success' => false, 'error' => 'Token inválido'], 403);
        }
        
        // Criar pet
        $data = [
            'user_id' => $user['id'],
            'name' => $_POST['name'] ?? '',
            'type' => $_POST['type'] ?? '',
            'breed' => $_POST['breed'] ?? '',
            'size' => $_POST['size'] ?? null,
            'age' => $_POST['age'] ?? null,
            'weight' => $_POST['weight'] ?? null,
            'gender' => $_POST['gender'] ?? null
        ];
        
        $petId = $this->petModel->create($data);
        
        if ($petId) {
            json_response(['success' => true, 'pet_id' => $petId, 'message' => 'Pet cadastrado com sucesso']);
        } else {
            json_response(['success' => false, 'error' => 'Erro ao cadastrar pet'], 500);
        }
    }
    
    /**
     * Assinaturas
     */
    public function subscriptions() {
        if (!is_logged_in()) {
            redirect(BASE_URL . '/login');
            return;
        }
        
        $user = get_current_user();
        $subscriptions = $this->subscriptionModel->findByUserId($user['id']);
        
        // Buscar detalhes de cada assinatura
        foreach ($subscriptions as &$subscription) {
            $subscription['pet'] = $this->petModel->findById($subscription['pet_id']);
            $subscription['box_info'] = BOX_TYPES[$subscription['box_type']];
        }
        
        require_once __DIR__ . '/../views/dashboard/subscriptions.php';
    }
    
    /**
     * Cancelar assinatura
     */
    public function cancelSubscription() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'error' => 'Método inválido'], 405);
        }
        
        if (!is_logged_in()) {
            json_response(['success' => false, 'error' => 'Não autenticado'], 401);
        }
        
        $user = get_current_user();
        
        // Validar CSRF
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            json_response(['success' => false, 'error' => 'Token inválido'], 403);
        }
        
        $subscriptionId = $_POST['subscription_id'] ?? null;
        $reason = $_POST['reason'] ?? '';
        
        if (!$subscriptionId) {
            json_response(['success' => false, 'error' => 'Assinatura não informada'], 400);
        }
        
        // Verificar se pertence ao usuário
        if (!$this->subscriptionModel->belongsToUser($subscriptionId, $user['id'])) {
            json_response(['success' => false, 'error' => 'Assinatura não encontrada'], 404);
        }
        
        // Buscar assinatura
        $subscription = $this->subscriptionModel->findById($subscriptionId);
        
        // Cancelar no gateway
        if ($subscription['gateway_subscription_id']) {
            AmploPay::cancelSubscription($subscription['gateway_subscription_id']);
        }
        
        // Cancelar localmente
        $success = $this->subscriptionModel->cancel($subscriptionId, $reason);
        
        if ($success) {
            json_response(['success' => true, 'message' => 'Assinatura cancelada com sucesso']);
        } else {
            json_response(['success' => false, 'error' => 'Erro ao cancelar assinatura'], 500);
        }
    }
    
    /**
     * Pausar assinatura
     */
    public function pauseSubscription() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'error' => 'Método inválido'], 405);
        }
        
        if (!is_logged_in()) {
            json_response(['success' => false, 'error' => 'Não autenticado'], 401);
        }
        
        $user = get_current_user();
        
        $subscriptionId = $_POST['subscription_id'] ?? null;
        
        if (!$subscriptionId) {
            json_response(['success' => false, 'error' => 'Assinatura não informada'], 400);
        }
        
        if (!$this->subscriptionModel->belongsToUser($subscriptionId, $user['id'])) {
            json_response(['success' => false, 'error' => 'Assinatura não encontrada'], 404);
        }
        
        $subscription = $this->subscriptionModel->findById($subscriptionId);
        
        // Pausar no gateway
        if ($subscription['gateway_subscription_id']) {
            // Amplo Pay não suporta pausa - implementar lógica alternativa se necessário
            // AmploPay::pauseSubscription($subscription['gateway_subscription_id']);
        }
        
        // Pausar localmente
        $success = $this->subscriptionModel->pause($subscriptionId);
        
        if ($success) {
            json_response(['success' => true, 'message' => 'Assinatura pausada com sucesso']);
        } else {
            json_response(['success' => false, 'error' => 'Erro ao pausar assinatura'], 500);
        }
    }
    
    /**
     * Histórico de boxes
     */
    public function boxes() {
        if (!is_logged_in()) {
            redirect(BASE_URL . '/login');
            return;
        }
        
        $user = get_current_user();
        
        // Buscar boxes
        $boxes = db()->findAll('boxes', 'user_id = ?', [$user['id']], 'created_at DESC');
        
        // Buscar detalhes
        foreach ($boxes as &$box) {
            $box['pet'] = $this->petModel->findById($box['pet_id']);
            $box['box_info'] = BOX_TYPES[$box['box_type']];
        }
        
        require_once __DIR__ . '/../views/dashboard/boxes.php';
    }
    
    /**
     * Pontos de fidelidade
     */
    public function points() {
        if (!is_logged_in()) {
            redirect(BASE_URL . '/login');
            return;
        }
        
        $user = get_current_user();
        
        // Buscar pontos
        $totalPoints = $this->userModel->getPoints($user['id']);
        
        // Buscar histórico
        $pointsHistory = db()->findAll('points', 'user_id = ?', [$user['id']], 'created_at DESC', '50');
        
        require_once __DIR__ . '/../views/dashboard/points.php';
    }
    
    /**
     * Obter próximas entregas
     */
    private function getNextDeliveries($userId) {
        return db()->query(
            "SELECT b.*, p.name as pet_name 
             FROM boxes b
             INNER JOIN pets p ON b.pet_id = p.id
             WHERE b.user_id = ? 
             AND b.status IN ('preparing', 'shipped')
             ORDER BY b.estimated_delivery ASC
             LIMIT 5",
            [$userId]
        );
    }
}
