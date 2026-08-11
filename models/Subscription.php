<?php
/**
 * CLUBINHO PET - Model Subscription
 * Gerenciamento de assinaturas
 * 
 * @package ClubinhoPet
 * @version 1.0.0
 */

class Subscription {
    
    private $db;
    
    public function __construct() {
        $this->db = db();
    }
    
    /**
     * Criar nova assinatura
     */
    public function create($data) {
        $subscriptionData = [
            'user_id' => $data['user_id'],
            'pet_id' => $data['pet_id'],
            'box_type' => $data['box_type'],
            'plan' => $data['plan'],
            'price' => $data['price'],
            'status' => $data['status'] ?? 'pending',
            'start_date' => $data['start_date'] ?? date('Y-m-d'),
            'next_billing_date' => $data['next_billing_date'] ?? date('Y-m-d', strtotime('+1 month')),
            'gateway_subscription_id' => $data['gateway_subscription_id'] ?? null,
            'payment_method' => $data['payment_method'] ?? null,
            'notes' => $data['notes'] ?? ''
        ];
        
        $subscriptionId = $this->db->insert('subscriptions', $subscriptionData);
        
        if ($subscriptionId) {
            log_activity('subscription_created', $data['user_id'], [
                'subscription_id' => $subscriptionId,
                'box_type' => $data['box_type'],
                'plan' => $data['plan']
            ]);
        }
        
        return $subscriptionId;
    }
    
    /**
     * Atualizar assinatura
     */
    public function update($id, $data) {
        $subscriptionData = [];
        
        if (isset($data['status'])) $subscriptionData['status'] = $data['status'];
        if (isset($data['next_billing_date'])) $subscriptionData['next_billing_date'] = $data['next_billing_date'];
        if (isset($data['gateway_subscription_id'])) $subscriptionData['gateway_subscription_id'] = $data['gateway_subscription_id'];
        if (isset($data['payment_method'])) $subscriptionData['payment_method'] = $data['payment_method'];
        if (isset($data['notes'])) $subscriptionData['notes'] = $data['notes'];
        
        $success = $this->db->update('subscriptions', $subscriptionData, 'id = ?', [$id]);
        
        if ($success) {
            log_activity('subscription_updated', null, ['subscription_id' => $id]);
        }
        
        return $success;
    }
    
    /**
     * Cancelar assinatura
     */
    public function cancel($id, $reason = '') {
        $success = $this->db->update('subscriptions',
            [
                'status' => 'cancelled',
                'cancelled_at' => date('Y-m-d H:i:s'),
                'cancellation_reason' => $reason
            ],
            'id = ?',
            [$id]
        );
        
        if ($success) {
            log_activity('subscription_cancelled', null, [
                'subscription_id' => $id,
                'reason' => $reason
            ]);
        }
        
        return $success;
    }
    
    /**
     * Pausar assinatura
     */
    public function pause($id) {
        $success = $this->db->update('subscriptions',
            [
                'status' => 'paused',
                'paused_at' => date('Y-m-d H:i:s')
            ],
            'id = ?',
            [$id]
        );
        
        if ($success) {
            log_activity('subscription_paused', null, ['subscription_id' => $id]);
        }
        
        return $success;
    }
    
    /**
     * Reativar assinatura
     */
    public function resume($id) {
        $success = $this->db->update('subscriptions',
            [
                'status' => 'active',
                'paused_at' => null
            ],
            'id = ?',
            [$id]
        );
        
        if ($success) {
            log_activity('subscription_resumed', null, ['subscription_id' => $id]);
        }
        
        return $success;
    }
    
    /**
     * Buscar assinatura por ID
     */
    public function findById($id) {
        return $this->db->findById('subscriptions', $id);
    }
    
    /**
     * Buscar assinaturas de um usuário
     */
    public function findByUserId($userId) {
        return $this->db->findAll('subscriptions', 'user_id = ?', [$userId], 'created_at DESC');
    }
    
    /**
     * Buscar assinatura ativa de um pet
     */
    public function findActiveByPetId($petId) {
        return $this->db->findOne('subscriptions', 'pet_id = ? AND status = ?', [$petId, 'active']);
    }
    
    /**
     * Buscar todas as assinaturas ativas
     */
    public function findAllActive() {
        return $this->db->findAll('subscriptions', 'status = ?', ['active'], 'next_billing_date ASC');
    }
    
    /**
     * Buscar assinaturas que vencem hoje
     */
    public function findDueToday() {
        return $this->db->findAll('subscriptions', 
            'status = ? AND next_billing_date = ?',
            ['active', date('Y-m-d')]
        );
    }
    
    /**
     * Buscar todas as assinaturas (admin)
     */
    public function findAll($filters = []) {
        $where = '1=1';
        $params = [];
        
        if (!empty($filters['status'])) {
            $where .= ' AND status = ?';
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['box_type'])) {
            $where .= ' AND box_type = ?';
            $params[] = $filters['box_type'];
        }
        
        if (!empty($filters['plan'])) {
            $where .= ' AND plan = ?';
            $params[] = $filters['plan'];
        }
        
        return $this->db->findAll('subscriptions', $where, $params, 'created_at DESC');
    }
    
    /**
     * Contar assinaturas
     */
    public function count($filters = []) {
        $where = '1=1';
        $params = [];
        
        if (!empty($filters['status'])) {
            $where .= ' AND status = ?';
            $params[] = $filters['status'];
        }
        
        return $this->db->count('subscriptions', $where, $params);
    }
    
    /**
     * Verificar se assinatura pertence ao usuário
     */
    public function belongsToUser($subscriptionId, $userId) {
        $subscription = $this->findById($subscriptionId);
        return $subscription && $subscription['user_id'] == $userId;
    }
    
    /**
     * Obter receita mensal
     */
    public function getMonthlyRevenue() {
        $result = $this->db->queryOne(
            "SELECT COALESCE(SUM(price), 0) as revenue 
             FROM subscriptions 
             WHERE status = 'active'"
        );
        
        return $result ? (float)$result['revenue'] : 0;
    }
    
    /**
     * Obter estatísticas de assinaturas
     */
    public function getStats() {
        $stats = [
            'total' => $this->count(),
            'active' => $this->count(['status' => 'active']),
            'paused' => $this->count(['status' => 'paused']),
            'cancelled' => $this->count(['status' => 'cancelled']),
            'monthly_revenue' => $this->getMonthlyRevenue()
        ];
        
        return $stats;
    }
}
